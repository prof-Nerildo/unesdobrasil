<?php
namespace Controllers;

use Repositories\InstituicaoEnsinoRepository;
use Repositories\UsuarioRepository;
use Models\RegisterRequestModelInstituicaoEnsino;
use Exception;

class InstituicaoEnsinoController {
    private $repoInst;
    private $repoUser;

    public function __construct() {
        $this->repoInst = new \Repositories\InstituicaoEnsinoRepository();
        $this->repoUser = new \Repositories\UsuarioRepository();
    }

    public function registrar($dadosJson) {
        try {
            // Mapeia os dados do formulário
            $request = new \Models\RegisterRequestModelInstituicaoEnsino($dadosJson);
            
            // Valida campos básicos
            $erros = $request->validate();
            if (!empty($erros)) throw new Exception(implode(", ", $erros), 400);

            // 1. Grava a Instituição, Endereço e Contatos (Tudo em uma Transaction)
            $idInst = $this->repoInst->create($request);
            
            // 2. Vincula o usuário ao ID da instituição criada
            if (!empty($request->idUsuarioDono)) {
                $this->repoUser->vincularInstituicao($request->idUsuarioDono, $idInst);
            }

            return json_encode([
                "erro" => false, 
                "message" => "Instituição cadastrada com sucesso!"
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            return json_encode(["erro" => true, "message" => "Erro no servidor: " . $e->getMessage()]);
        }
    }
}