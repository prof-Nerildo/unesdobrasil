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
        $sql = "SELECT i.idInstituicao, i.razao_social, i.nome_fantasia, i.idStatus, i.valor_documento_nacional, u.primeiro_nome as dono_nome
                FROM instituicao i LEFT JOIN usuario u ON u.idInstituicao = i.idInstituicao ORDER BY i.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}