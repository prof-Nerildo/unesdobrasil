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

    /**
     * Rota: POST /api/cadastro/completo
     * Cria a instituição e o usuário administrador numa única transação.
     * Se email/username já existirem, faz rollback de tudo e retorna erro 409.
     *
     * @param array            $dadosJson   Payload com chaves 'instituicao' e 'usuario'
     * @param \Controllers\UsuarioController $userController  Para reutilizar o hash de senha
     */
    public function cadastrarCompleto(array $dadosJson, \Controllers\UsuarioController $userController) {
        try {
            $dadosInst = $dadosJson['instituicao'] ?? [];
            $dadosUser = $dadosJson['usuario']     ?? [];

            // --- Validação da Instituição ---
            $modelInst = new \Models\RegisterRequestModelInstituicaoEnsino($dadosInst);
            $errosInst = $modelInst->validate();
            if (!empty($errosInst)) {
                http_response_code(400);
                return json_encode(["erro" => true, "campo" => "instituicao", "message" => implode(" ", $errosInst)]);
            }

            // --- Validação do Usuário ---
            $modelUser = new \Models\RegisterRequestModelUsuario($dadosUser);
            $errosUser = $modelUser->validate();
            if (!empty($errosUser)) {
                http_response_code(400);
                return json_encode(["erro" => true, "campo" => "usuario", "message" => implode(" ", $errosUser)]);
            }

            // --- Gera o hash de senha usando o padrão do sistema (reutiliza lógica do UsuarioController) ---
            $senhaHash = $userController->gerarHashSenha($dadosUser['senha'] ?? '', $modelUser->email);

            // --- Executa a transação atômica ---
            $resultado = $this->repoInst->createComAdministrador($modelInst, $modelUser, $senhaHash);

            return json_encode([
                "erro"          => false,
                "idInstituicao" => $resultado['idInstituicao'],
                "idUsuario"     => $resultado['idUsuario'],
                "message"       => "Cadastro realizado com sucesso! Aguarde a aprovação da UNES."
            ]);

        } catch (Exception $e) {
            $code = $e->getCode();
            http_response_code($code >= 400 && $code <= 599 ? $code : 500);
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }
}