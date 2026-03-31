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
}