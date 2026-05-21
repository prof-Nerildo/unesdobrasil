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
            $agora = date('Y-m-d H:i:s');

            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }

            // 1. Grava a Instituição
            $sqlInst = "INSERT INTO instituicao (razao_social, nome_fantasia, cnpj, idStatus, created_at) VALUES (?, ?, ?, 3, ?)";
            $stmt = $this->db->prepare($sqlInst);
            $stmt->execute([
                $model->razao_social, 
                $model->nome_fantasia, 
                $model->cnpj,
                $agora
            ]);
            
            $idInst = $this->db->lastInsertId();

            // Sincroniza idLegado com idInstituicao para novos cadastros
            $sqlUpdLegado = "UPDATE instituicao SET idLegado = ? WHERE idInstituicao = ?";
            $this->db->prepare($sqlUpdLegado)->execute([$idInst, $idInst]);

            // 2. Grava o Endereço
            $sqlEnd = "INSERT INTO endereco (idReferencia, tipo_entidade, cep, logradouro, numero, complemento, bairro, cidade, uf) 
                       VALUES (?, 'instituicao', ?, ?, ?, ?, ?, ?, ?)";
            $stmtEnd = $this->db->prepare($sqlEnd);
            $stmtEnd->execute([
                $idInst, 
                $model->cep, 
                $model->logradouro,
                $model->numero,
                $model->complemento ?? '',
                $model->bairro, 
                $model->cidade, 
                $model->uf
            ]);

            // 3. Grava os Contatos
            $sqlCont = "INSERT INTO contato (idReferencia, tipo_entidade, tipo_contato, valor) VALUES (?, 'instituicao', ?, ?)";
            $stmtCont = $this->db->prepare($sqlCont);

            if (!empty($model->email_contato)) {
                $stmtCont->execute([$idInst, 'email_secretaria', $model->email_contato]);
            }
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
                    i.idLegado, 
                    i.idStatus, 
                    i.nome_fantasia, 
                    e.cidade, 
                    
                    -- CATRACA
                    COALESCE((SELECT ic.usa_catraca FROM instituicao_catraca ic WHERE ic.idInstituicao = i.idInstituicao LIMIT 1), 'nao') AS usa_catraca,
                    
                    -- RESPONSÁVEL (CORRIGIDO): Usa CONCAT_WS para evitar erro se o sobrenome for vazio
                    COALESCE(
                        (SELECT CONCAT_WS(' ', u.primeiro_nome, u.sobrenome) 
                        FROM usuario u 
                        WHERE u.idInstituicao = i.idInstituicao 
                        ORDER BY u.idUsuario ASC LIMIT 1), 
                        'Sem Responsável'
                    ) AS responsavel, 
                    
                    -- TELEFONE
                    COALESCE(
                        (SELECT valor FROM contato 
                        WHERE idReferencia = i.idInstituicao 
                        AND tipo_entidade = 'instituicao' 
                        AND tipo_contato IN ('fixo', 'celular', 'whatsapp') 
                        LIMIT 1),
                        '---'
                    ) AS telefone,
                    
                    -- E-MAIL
                    COALESCE(
                        (SELECT email FROM usuario 
                        WHERE idInstituicao = i.idInstituicao 
                        ORDER BY idUsuario ASC LIMIT 1),
                        (SELECT valor FROM contato 
                        WHERE idReferencia = i.idInstituicao 
                        AND tipo_entidade = 'instituicao' 
                        AND tipo_contato = 'email_secretaria' 
                        LIMIT 1),
                        '---'
                    ) AS email_usuario

                FROM instituicao i
                LEFT JOIN endereco e ON e.idReferencia = i.idInstituicao AND e.tipo_entidade = 'instituicao'
                WHERE i.idStatus != 1   
                ORDER BY i.idInstituicao DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $idStatus) {
        try {
            $sql = "UPDATE instituicao SET idStatus = ? WHERE idInstituicao = ?";
            return $this->db->prepare($sql)->execute([$idStatus, $id]);
        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar status: " . $e->getMessage());
        }
    }

    public function updatePerfilPelaInstituicao($id, $dados) {
        try {
            $agora = date('Y-m-d H:i:s');
            if (!$this->db->inTransaction()) { $this->db->beginTransaction(); }

            $sql = "UPDATE instituicao SET razao_social = ?, nome_fantasia = ?, updated_at = ? WHERE idInstituicao = ?";
            $this->db->prepare($sql)->execute([$dados['razao_social'] ?? '', $dados['nome_fantasia'] ?? '', $agora, $id]);

            $sqlEnd = "UPDATE endereco SET cep = ?, logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, uf = ? 
                       WHERE idReferencia = ? AND tipo_entidade = 'instituicao'";
            $this->db->prepare($sqlEnd)->execute([
                $dados['cep'] ?? '', $dados['logradouro'] ?? '', $dados['numero'] ?? '', 
                $dados['complemento'] ?? '', $dados['bairro'] ?? '', $dados['cidade'] ?? '', $dados['uf'] ?? '', $id
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function updateCompleto($id, $dados) {
        try {
            $agora = date('Y-m-d H:i:s');
            if (!$this->db->inTransaction()) { $this->db->beginTransaction(); }

            $sql = "UPDATE instituicao SET 
                        razao_social = ?, nome_fantasia = ?, cnpj = ?, 
                        pode_editar_instituicao = ?, label_edita_instituicao = ?, 
                        pode_editar_curso = ?, label_edita_curso = ?, 
                        valor_documento_nacional = ?, valor_frete = ?, 
                        idStatus = 2, updated_at = ? 
                    WHERE idInstituicao = ?";

            $this->db->prepare($sql)->execute([
                $dados['razao_social'] ?? '', $dados['nome_fantasia'] ?? '', $dados['cnpj'] ?? '',
                $dados['pode_editar_instituicao'] ?? 'nao', $dados['label_edita_instituicao'] ?? '',
                $dados['pode_editar_curso'] ?? 'nao', $dados['label_edita_curso'] ?? '',
                $dados['valor_documento_nacional'] ?? 0, $dados['valor_frete'] ?? 0, $agora, $id
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function findById($id) {
        $sql = "SELECT i.*, e.cep, e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.uf,
                       ic.modelo, ic.quantidade, COALESCE(ic.usa_catraca, 'nao') as usa_catraca,
                       (SELECT valor FROM contato WHERE idReferencia = i.idInstituicao AND tipo_entidade = 'instituicao' AND tipo_contato = 'email_secretaria' LIMIT 1) as email_contato,
                       (SELECT valor FROM contato WHERE idReferencia = i.idInstituicao AND tipo_entidade = 'instituicao' AND (tipo_contato = 'fixo' OR tipo_contato = 'celular') LIMIT 1) as telefone
                FROM instituicao i
                LEFT JOIN endereco e ON e.idReferencia = i.idInstituicao AND e.tipo_entidade = 'instituicao'
                LEFT JOIN instituicao_catraca ic ON ic.idInstituicao = i.idInstituicao
                WHERE i.idInstituicao = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function resumoDashboardUnes() {
        try {
            // Agora cada contagem ignora quem tem status 1 (Inativo/Excluído)
            $sql = "SELECT 
                        (SELECT COUNT(*) FROM instituicao 
                        WHERE idStatus = 3) as validar,
                        
                        (SELECT COUNT(*) FROM instituicao i 
                        LEFT JOIN instituicao_catraca ic ON i.idInstituicao = ic.idInstituicao 
                        WHERE i.idStatus = 2 
                        AND (ic.usa_catraca = 'nao' OR ic.usa_catraca IS NULL)) as sem_catraca,
                        
                        (SELECT COUNT(*) FROM instituicao i 
                        INNER JOIN instituicao_catraca ic ON i.idInstituicao = ic.idInstituicao 
                        WHERE i.idStatus = 2 
                        AND ic.usa_catraca = 'sim') as com_catraca";
                        
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erro no Dashboard: " . $e->getMessage());
        }
    }
}