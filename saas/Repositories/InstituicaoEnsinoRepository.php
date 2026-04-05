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

            // 1. Atualiza dados básicos, labels E FINANCEIRO
            $sql = "UPDATE instituicao SET 
                        razao_social = ?, nome_fantasia = ?, cnpj = ?, 
                        pode_editar_instituicao = ?, label_edita_instituicao = ?, 
                        pode_editar_curso = ?, label_edita_curso = ?, 
                        valor_documento_nacional = ?, valor_frete = ?,
                        idStatus = 2 
                    WHERE idInstituicao = ?";

            $this->db->prepare($sql)->execute([
                $dados['razao_social'], $dados['nome_fantasia'], $dados['cnpj'],
                $dados['pode_editar_instituicao'], $dados['label_edita_instituicao'],
                $dados['pode_editar_curso'], $dados['label_edita_curso'],
                $dados['valor_documento_nacional'], $dados['valor_frete'],
                $id
            ]);

            // 2. Atualiza Endereço
            $sqlEnd = "UPDATE endereco SET 
                    cep = ?, logradouro = ?, numero = ?, complemento = ?, 
                    bairro = ?, cidade = ?, uf = ? 
                    WHERE idReferencia = ? AND tipo_entidade = 'instituicao'";
            $this->db->prepare($sqlEnd)->execute([
                $dados['cep'], $dados['logradouro'], $dados['numero'], $dados['complemento'],
                $dados['bairro'], $dados['cidade'], $dados['uf'], $id
            ]);

            // 3. Atualiza ou Insere Catraca (Garante que só exista UMA linha devido ao UNIQUE KEY)
            $sqlCat = "INSERT INTO instituicao_catraca (idInstituicao, modelo, quantidade, usa_catraca) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    modelo = VALUES(modelo), 
                    quantidade = VALUES(quantidade), 
                    usa_catraca = VALUES(usa_catraca)";

            $this->db->prepare($sqlCat)->execute([
                $id, 
                $dados['modelo_catraca'] ?? '', 
                $dados['quantidade_catraca'] ?? 0, 
                $dados['usa_catraca'] ?? 'nao'
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw new Exception("Erro ao atualizar: " . $e->getMessage());
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