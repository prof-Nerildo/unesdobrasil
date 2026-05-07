<?php
namespace Controllers;

use Exception;

class InstituicaoEnsinoController {
    private $repoInst;

    public function __construct() {
        $this->repoInst = new \Repositories\InstituicaoEnsinoRepository();
    }

    // PASSO 1: Apenas a Instituição
    public function registrar($dadosJson) {
        try {
            $model = new \Models\RegisterRequestModelInstituicaoEnsino($dadosJson);
            
            $erros = $model->validate();
            if (!empty($erros)) {
                http_response_code(400);
                return json_encode(["erro" => true, "message" => implode(", ", $erros)]);
            }

            // Grava a instituição e retorna o ID gerado
            $id = $this->repoInst->createSimples($model);

            return json_encode([
                "erro" => false, 
                "idInstituicao" => $id, 
                "message" => "Instituição criada com sucesso!"
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }

    public function listarTodas($userLogado) {
        try {
            $lista = $this->repoInst->findAll(); 
            return json_encode(["erro" => false, "dados" => $lista]);
        } catch (Exception $e) {
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }

    public function atualizarStatus($id, $dadosJson, $userLogado = null) {
        try {
            $idStatus = $dadosJson['idStatus'] ?? null;
            if ($idStatus === null) {
                throw new Exception("idStatus não informado.", 400);
            }

            $sucesso = $this->repoInst->updateStatus($id, $idStatus);

            return json_encode([
                "erro" => false,
                "message" => "Status da instituição atualizado com sucesso!"
            ]);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }

    public function atualizarCompleto($id, $dadosJson, $userLogado = null) {
        try {
            $sucesso = $this->repoInst->updateCompleto($id, $dadosJson);
            return json_encode(["erro" => false, "message" => "Instituição validada e ativada com sucesso!"]);
        } catch (Exception $e) {
            http_response_code(500);
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }

    public function buscarPorId($id) {
        $dados = $this->repoInst->findById($id);
        return json_encode(["erro" => false, "dados" => $dados]);
    }

    public function atualizarPerfilInstituicao($id, $dadosJson, $userLogado = null) {
        try {
            // Chamaremos um método novo no Repository que protege os campos sensíveis
            $sucesso = $this->repoInst->updatePerfilPelaInstituicao($id, $dadosJson);
            
            return json_encode([
                "erro" => false, 
                "message" => "Dados cadastrais atualizados com sucesso!"
            ]);
        } catch (Exception $e) {
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }

    public function resumoDashboardUnes($userLogado = null) {
        try {
            $dados = $this->repoInst->resumoDashboardUnes(); 
            if (!$dados) {
                return json_encode(["erro" => false, "dados" => ["validar" => 0, "sem_catraca" => 0, "com_catraca" => 0]]);
            }
            return json_encode(["erro" => false, "dados" => $dados]);
        } catch (Exception $e) {
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }
    
}