<?php

namespace Controllers;

use Models\RegisterRequestModelUsuario;
use Models\LoginRequestModelUsuario;
use Models\ChangePasswordRequestModelUsuario;
use Repositories\UsuarioRepository;
use Dependencies\JwtHandler;
use Dependencies\EmailHandler; // <-- Importado
use Exception;

class UsuarioController {

    private $repositoryUsuario;
    private $apiKey;
    private $jwt;

    public function __construct() {
        $this->repositoryUsuario = new UsuarioRepository();
        $this->jwt = new JwtHandler();
        $this->carregarApiKey();
    }

    private function carregarApiKey() {
        $jsonPath = __DIR__ . '/../appsettings.json';
        if (file_exists($jsonPath)) {
            $config = json_decode(file_get_contents($jsonPath), true);
            $this->apiKey = $config['AppSettings']['JwtSecret'] ?? "Leite_Com_MangaRosa_Mata_todos_kkk@2026";
        }
    }

    private function computerSha256Hash($rawData) {
        return hash('sha256', $rawData);
    }

    private function montarStringCriptografia($senha, $identificadorUnico) {
        $passSHA256 = $this->computerSha256Hash($senha);
        $idSHA256 = $this->computerSha256Hash($identificadorUnico);
        return $passSHA256 . $this->apiKey . $idSHA256;
    }

    public function register($dadosJson) {
        try {
            $model = new RegisterRequestModelUsuario($dadosJson);
            $erros = $model->validate();
            
            if (!empty($erros)) {
                http_response_code(400);
                return json_encode(["erro" => true, "message" => "Dados inválidos", "detalhe" => $erros]);
            }

            if ($this->repositoryUsuario->emailOuUsernameExiste($model->email, $model->username)) {
                http_response_code(409);
                return json_encode(["erro" => true, "message" => "Este e-mail ou username já está em uso!"]);
            }

            $passCrip = $this->montarStringCriptografia($model->senha, $model->email);
            $model->senha = password_hash($passCrip, PASSWORD_BCRYPT);

            $idNovoUsuario = $this->repositoryUsuario->create($model);

            http_response_code(201);
            return json_encode([
                "erro" => false,
                "message" => "Usuário cadastrado com sucesso!",
                "usuario" => ["id" => $idNovoUsuario, "nome" => $model->primeiro_nome, "email" => $model->email]
            ]);
        } catch (Exception $ex) {
            http_response_code(500);
            return json_encode(["erro" => true, "message" => $ex->getMessage()]);
        }
    }

    public function login($dadosJson) {
        try {
            $model = new LoginRequestModelUsuario($dadosJson);
            $user = $this->repositoryUsuario->findByLogin($model->login);

            if ($user == null || !password_verify($this->montarStringCriptografia($model->senha, $user->getEmail()), $user->getSenha())) {
                http_response_code(401);
                return json_encode(["erro" => true, "message" => "Usuário ou senha inválidos."]);
            }

            $tokenData = [
                "id" => $user->getIdUsuario(),
                "nome" => $user->getPrimeiroNome(),
                "email" => $user->getEmail(),
                "idAcl" => $user->getIdAcl(), 
                "perfil" => $user->getIdPerfil()
            ];

            $token = $this->jwt->generate($tokenData);
            $this->repositoryUsuario->updateLastLogin($user->getIdUsuario());

            return json_encode([
                "erro" => false,
                "message" => "Login realizado com sucesso!",
                "token" => $token,
                "usuario" => ["id" => $user->getIdUsuario(), "nome" => $user->getPrimeiroNome(), "nivelAcesso" => $user->getIdAcl()]
            ]);
        } catch (Exception $ex) {
            http_response_code(500);
            return json_encode(["erro" => true, "message" => $ex->getMessage()]);
        }
    }

    public function getMe($userLogado) {
        $tipos = [1 => "Sistema", 2 => "UNES", 3 => "Instituição", 4 => "Aluno"];
        return json_encode([
            "erro" => false,
            "dados" => [
                "id" => $userLogado['id'],
                "nome" => $userLogado['nome'],
                "email" => $userLogado['email'],
                "nivelAcesso" => $userLogado['idAcl'],
                "tipoDescricao" => $tipos[$userLogado['idAcl']] ?? "Outros"
            ]
        ]);
    }

    public function changePassword($dadosJson, $userLogado = null) {
        try {
            $novaSenha = $dadosJson['novaSenha'] ?? '';
            $email = $userLogado['email'] ?? $dadosJson['email'] ?? '';

            if (empty($novaSenha) || empty($email)) {
                throw new Exception("Dados insuficientes para a troca.", 400);
            }

            $novaSenhaHash = password_hash($this->montarStringCriptografia($novaSenha, $email), PASSWORD_BCRYPT);
            $sucesso = $this->repositoryUsuario->updateSenha($email, $novaSenhaHash);

            return json_encode(["erro" => !$sucesso, "message" => $sucesso ? "Senha alterada com sucesso!" : "Erro ao atualizar banco."]);
        } catch (Exception $ex) {
            http_response_code($ex->getCode() ?: 500);
            return json_encode(["erro" => true, "message" => $ex->getMessage()]);
        }
    }

    /**
     * MÉTODO: Esqueci minha senha (Agora com envio de E-mail)
     */
    public function forgotPassword($dadosJson) {
        try {
            $login = $dadosJson['login'] ?? '';
            if (empty($login)) {
                http_response_code(400);
                return json_encode(["erro" => true, "message" => "Informe o e-mail ou usuário."]);
            }

            $user = $this->repositoryUsuario->findByLogin($login);

            // Resposta genérica por segurança
            $respostaPadrao = json_encode(["erro" => false, "message" => "Se o usuário existir, as instruções foram enviadas para o e-mail cadastrado."]);

            if ($user) {
                $recoveryData = [
                    "id" => $user->getIdUsuario(),
                    "email" => $user->getEmail(),
                    "idAcl" => $user->getIdAcl(),
                    "tipo" => "recuperacao"
                ];
                
                $tokenRecuperacao = $this->jwt->generate($recoveryData); 

                // Tenta enviar o e-mail real
                $emailHandler = new EmailHandler();
                $enviou = $emailHandler->enviarRecuperacao($user->getEmail(), $user->getPrimeiroNome(), $tokenRecuperacao);

                if (!$enviou) {
                    // Se o e-mail falhar (SMTP fora do ar), avisamos no debug para você não ficar travado
                    return json_encode([
                        "erro" => false, 
                        "message" => "Instruções geradas (Falha no envio SMTP)",
                        "debug_token" => $tokenRecuperacao 
                    ]);
                }
            }

            return $respostaPadrao;

        } catch (Exception $ex) {
            http_response_code(500);
            return json_encode(["erro" => true, "message" => "Erro ao processar solicitação"]);
        }
    }
}