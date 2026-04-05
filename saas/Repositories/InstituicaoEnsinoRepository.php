<?php
namespace Repositories;

use PDO;
use Exception;

class InstituicaoEnsinoRepository {
    private $db;

    public function __construct() {
        $this->db = \Data\Database::getConnection();
    }

    public function createSimples(\Models\RegisterRequestModelInstituicaoEnsino $model) {
        try {
            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }

            // 1. Grava a Instituição
            $sqlInst = "INSERT INTO instituicao (razao_social, nome_fantasia, cnpj, idStatus) VALUES (?, ?, ?, 3)";
            $stmt = $this->db->prepare($sqlInst);
            $stmt->execute([
                $model->razao_social, 
                $model->nome_fantasia, 
                $model->cnpj
            ]);
            $idInst = $this->db->lastInsertId();

            // 2. Grava o Endereço (CONTAGEM CORRIGIDA: 8 campos e 8 valores)
            $sqlEnd = "INSERT INTO endereco (idReferencia, tipo_entidade, cep, logradouro, numero, complemento, bairro, cidade, uf) 
                       VALUES (?, 'instituicao', ?, ?, ?, ?, ?, ?, ?)";
            $stmtEnd = $this->db->prepare($sqlEnd);
            $stmtEnd->execute([
                $idInst,           // 1
                $model->cep,       // 2
                $model->logradouro,// 3
                $model->numero,    // 4
                $model->complemento,// 5 (ESTE ESTAVA FALTANDO NA CONTAGEM!)
                $model->bairro,    // 6
                $model->cidade,    // 7
                $model->uf         // 8
            ]);

            // 3. Grava os Contatos na tabela 'contato'
            $sqlCont = "INSERT INTO contato (idReferencia, tipo_entidade, tipo_contato, valor) VALUES (?, 'instituicao', ?, ?)";
            $stmtCont = $this->db->prepare($sqlCont);

            // E-mail da escola
            if (!empty($model->email_contato)) {
                $stmtCont->execute([$idInst, 'email_secretaria', $model->email_contato]);
            }
            // Telefone Fixo
            if (!empty($model->telefone)) {
                $stmtCont->execute([$idInst, 'fixo', $model->telefone]);
            }

            $this->db->commit();
            return $idInst;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw new Exception("Erro SQL: " . $e->getMessage());
        }
    }

    public function findAll() {
        $sql = "SELECT 
                    i.idInstituicao, 
                    i.idStatus, 
                    i.nome_fantasia, 
                    e.cidade, 
                    COALESCE((SELECT ic.usa_catraca FROM instituicao_catraca ic WHERE ic.idInstituicao = i.idInstituicao LIMIT 1), 'nao') AS usa_catraca,
                    (SELECT CONCAT(primeiro_nome, ' ', sobrenome) FROM usuario WHERE idInstituicao = i.idInstituicao LIMIT 1) AS responsavel, 
                    
                    COALESCE(
                        (SELECT valor FROM contato WHERE idReferencia = i.idInstituicao AND tipo_entidade = 'instituicao' AND tipo_contato IN ('fixo', 'celular') LIMIT 1),
                        (SELECT c.valor FROM contato c INNER JOIN usuario u ON c.idReferencia = u.idUsuario WHERE u.idInstituicao = i.idInstituicao LIMIT 1)
                    ) AS telefone,
                    
                    COALESCE(
                        (SELECT valor FROM contato WHERE idReferencia = i.idInstituicao AND tipo_entidade = 'instituicao' AND tipo_contato = 'email_secretaria' LIMIT 1),
                        (SELECT email FROM usuario WHERE idInstituicao = i.idInstituicao LIMIT 1)
                    ) AS email_usuario

                FROM instituicao i
                LEFT JOIN endereco e ON e.idReferencia = i.idInstituicao AND e.tipo_entidade = 'instituicao'
                ORDER BY i.idInstituicao DESC";
                    
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $idStatus) {
        try {
            $sql = "UPDATE instituicao SET idStatus = ? WHERE idInstituicao = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$idStatus, $id]);
        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar status: " . $e->getMessage());
        }
    }

    public function updateCompleto($id, $dados) {
        try {
            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }

            // 1. ATUALIZA INSTITUIÇÃO
            $sql = "UPDATE instituicao SET 
                        razao_social = ?, nome_fantasia = ?, cnpj = ?, 
                        pode_editar_instituicao = ?, label_edita_instituicao = ?, 
                        pode_editar_curso = ?, label_edita_curso = ?, 
                        valor_documento_nacional = ?, valor_frete = ?, idStatus = 2 
                    WHERE idInstituicao = ?";

            $this->db->prepare($sql)->execute([
                $dados['razao_social'] ?? '', $dados['nome_fantasia'] ?? '', $dados['cnpj'] ?? '',
                $dados['pode_editar_instituicao'] ?? 'nao', $dados['label_edita_instituicao'] ?? '',
                $dados['pode_editar_curso'] ?? 'nao', $dados['label_edita_curso'] ?? '',
                $dados['valor_documento_nacional'] ?? 0, $dados['valor_frete'] ?? 0, $id
            ]);

            // 2. ATUALIZA ENDEREÇO
            $sqlEnd = "UPDATE endereco SET 
                        cep = ?, logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, uf = ? 
                    WHERE idReferencia = ? AND tipo_entidade = 'instituicao'";
            $this->db->prepare($sqlEnd)->execute([
                $dados['cep'] ?? '', $dados['logradouro'] ?? '', $dados['numero'] ?? '', 
                $dados['complemento'] ?? '', $dados['bairro'] ?? '', $dados['cidade'] ?? '', 
                $dados['uf'] ?? '', $id
            ]);

            // 3. ATUALIZA CONTATOS (E-mail e Telefone) - LÓGICA SEM DUPLICAR
            $contatos = [
                ['tipo' => 'email_secretaria', 'valor' => $dados['email_contato'] ?? ''],
                ['tipo' => 'fixo', 'valor' => $dados['telefone'] ?? '']
            ];

            foreach ($contatos as $c) {
                // Primeiro: Tenta dar UPDATE na linha que já existe
                $upd = "UPDATE contato SET valor = ? 
                        WHERE idReferencia = ? AND tipo_entidade = 'instituicao' AND tipo_contato = ?";
                $stmt = $this->db->prepare($upd);
                $stmt->execute([$c['valor'], $id, $c['tipo']]);

                // Segundo: Se o UPDATE não achou nada (rowCount 0), a gente verifica se precisa dar INSERT
                if ($stmt->rowCount() == 0) {
                    // Checa se a linha realmente não existe
                    $check = "SELECT idContato FROM contato WHERE idReferencia = ? AND tipo_entidade = 'instituicao' AND tipo_contato = ?";
                    $resCheck = $this->db->prepare($check);
                    $resCheck->execute([$id, $c['tipo']]);

                    if (!$resCheck->fetch()) {
                        // Só insere se o SELECT acima voltar vazio
                        $ins = "INSERT INTO contato (idReferencia, tipo_entidade, tipo_contato, valor) VALUES (?, 'instituicao', ?, ?)";
                        $this->db->prepare($ins)->execute([$id, $c['tipo'], $c['valor']]);
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function findById($id) {
        try {
            $sql = "SELECT 
                        i.*, 
                        e.cep, e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.uf,
                        ic.modelo, ic.quantidade, COALESCE(ic.usa_catraca, 'nao') as usa_catraca,
                        
                        -- Busca APENAS o e-mail da instituição
                        (SELECT valor FROM contato 
                        WHERE idReferencia = i.idInstituicao 
                        AND tipo_entidade = 'instituicao' 
                        AND tipo_contato = 'email_secretaria' LIMIT 1) as email_contato,
                        
                        -- Busca APENAS o telefone da instituição (fixo ou celular dela)
                        (SELECT valor FROM contato 
                        WHERE idReferencia = i.idInstituicao 
                        AND tipo_entidade = 'instituicao' 
                        AND (tipo_contato = 'fixo' OR tipo_contato = 'celular') LIMIT 1) as telefone

                    FROM instituicao i
                    LEFT JOIN endereco e ON e.idReferencia = i.idInstituicao AND e.tipo_entidade = 'instituicao'
                    LEFT JOIN instituicao_catraca ic ON ic.idInstituicao = i.idInstituicao
                    WHERE i.idInstituicao = ?";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar dados: " . $e->getMessage());
        }
    }
}