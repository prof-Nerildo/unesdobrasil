<?php
namespace Repositories;

use Data\Database;
use Models\RegisterRequestModelInstituicaoEnsino;
use PDO;
use Exception;

class InstituicaoEnsinoRepository {
    private $db;

    public function __construct() {
        $this->db = \Data\Database::getConnection();
    }

    public function create(RegisterRequestModelInstituicaoEnsino $model) {
        try {
            $this->db->beginTransaction();

            // 1. Inserir Instituição
            $sqlInst = "INSERT INTO instituicao (razao_social, nome_fantasia, cnpj, insc_estadual, idStatus) 
                        VALUES (?, ?, ?, ?, 3)";
            $stmt = $this->db->prepare($sqlInst);
            $stmt->execute([
                $model->razao_social, 
                $model->nome_fantasia, 
                $model->cnpj, 
                $model->insc_estadual
            ]);
            $idInst = $this->db->lastInsertId();

            // 2. Inserir Endereço
            $sqlEnd = "INSERT INTO endereco (idReferencia, tipo_entidade, cep, logradouro, numero, bairro, cidade, uf) 
                       VALUES (?, 'instituicao', ?, ?, ?, ?, ?, ?)";
            $stmtEnd = $this->db->prepare($sqlEnd);
            $stmtEnd->execute([
                $idInst, 
                $model->cep, 
                $model->logradouro, 
                $model->numero, 
                $model->bairro, 
                $model->cidade, 
                $model->uf
            ]);

            // 3. Inserir Contatos
            $sqlCont = "INSERT INTO contato (idReferencia, tipo_entidade, tipo_contato, valor) VALUES (?, 'instituicao', ?, ?)";
            $stmtCont = $this->db->prepare($sqlCont);
            
            // E-mail Institucional
            $stmtCont->execute([$idInst, 'email_secretaria', $model->email_contato]);
            
            // Telefone Fixo (se preenchido)
            if (!empty($model->telefone)) {
                $stmtCont->execute([$idInst, 'fixo', $model->telefone]);
            }
            
            // Fax (se preenchido)
            if (!empty($model->fax)) {
                $stmtCont->execute([$idInst, 'fax', $model->fax]);
            }

            $this->db->commit();
            return $idInst;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw new Exception("Falha ao gravar tabelas: " . $e->getMessage());
        }
    }
}