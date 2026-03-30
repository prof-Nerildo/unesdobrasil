<?php

namespace Controllers;

use Models\RegisterRequestModelUsuario;
use Models\LoginRequestModelUsuario;
use Models\ChangePasswordRequestModelUsuario;
use Repositories\UsuarioRepository;
use Dependencies\JwtHandler;
use Dependencies\EmailHandler;
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
        return hash('sha256', $rawData ?? '');
    }

    private function montarStringCriptografia($senha, $identificadorUnico) {
        $passSHA256 = $this->computerSha256Hash($senha ?? '');
        $idSHA256 = $this->computerSha256Hash($identificadorUnico ?? '');
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
                "usuario" => [
                    "id" => $user->getIdUsuario(), 
                    "nome" => $user->getPrimeiroNome(), 
                    "nivelAcesso" => $user->getIdAcl()
                ]
            ]);
        } catch (Exception $ex) {
            http_response_code(500);
            return json_encode(["erro" => true, "message" => $ex->getMessage()]);
        }
    }

    public function getMe($userLogado) {
        try {
            $dadosCompletos = $this->repositoryUsuario->findMe($userLogado['id']);
            
            if (!$dadosCompletos) {
                throw new Exception("Usuário não localizado no banco.");
            }

            return json_encode([
                "erro" => false,
                "dados" => $dadosCompletos
            ]);
        } catch (Exception $ex) {
            http_response_code(404);
            return json_encode(["erro" => true, "message" => $ex->getMessage()]);
        }
    }

    /**
     * MÉTODO: Alterar Senha
     * Ajustado para suportar Troca Logada e Recuperação (Esqueci Senha)
     */
    public function changePassword($dadosJson, $userLogado) {
        try {
            $novaSenha = $dadosJson['novaSenha'] ?? '';
            $email = $userLogado['email'];

            // 1. Verifica se o token é de recuperação (não exige senha atual)
            $isRecuperacao = ($userLogado['tipo'] ?? '') === 'recuperacao';

            $user = $this->repositoryUsuario->findByLogin($email);
            if (!$user) throw new Exception("Usuário não encontrado.");

            // 2. Se não for recuperação, valida a senha atual por segurança
            if (!$isRecuperacao) {
                $senhaAtual = $dadosJson['senhaAtual'] ?? '';
                if (!password_verify($this->montarStringCriptografia($senhaAtual, $email), $user->getSenha())) {
                    throw new Exception("A senha atual informada está incorreta.", 401);
                }
            }

            // 3. Criptografa e salva a nova senha
            $novaSenhaHash = password_hash($this->montarStringCriptografia($novaSenha, $email), PASSWORD_BCRYPT);
            $sucesso = $this->repositoryUsuario->updateSenha($email, $novaSenhaHash);

            return json_encode([
                "erro" => !$sucesso, 
                "message" => $sucesso ? "Senha alterada com sucesso!" : "Erro ao atualizar banco."
            ]);
        } catch (Exception $ex) {
            http_response_code($ex->getCode() ?: 500);
            return json_encode(["erro" => true, "message" => $ex->getMessage()]);
        }
    }

   public function forgotPassword($dadosJson) {
        try {
            $login = $dadosJson['login'] ?? '';
            if (empty($login)) {
                http_response_code(400);
                return json_encode(["erro" => true, "message" => "Informe o e-mail ou usuário."]);
            }

            $user = $this->repositoryUsuario->findByLogin($login);

            if ($user) {
                $emailDestino = $user->getEmail();
                $nomeDestino = $user->getPrimeiroNome();

                $recoveryData = [
                    "id" => $user->getIdUsuario(),
                    "email" => $emailDestino,
                    "tipo" => "recuperacao"
                ];
                
                $tokenRecuperacao = $this->jwt->generate($recoveryData); 
                $emailHandler = new EmailHandler();
                $enviou = $emailHandler->enviarRecuperacao($emailDestino, $nomeDestino, $tokenRecuperacao);

                if (!$enviou) {
                     return json_encode(["erro" => true, "message" => "O servidor de e-mail recusou o envio. Tente novamente mais tarde."]);
                }

                return json_encode(["erro" => false, "message" => "Instruções enviadas com sucesso para o seu e-mail!"]);
            }

            return json_encode(["erro" => false, "message" => "Se o usuário existir, as instruções foram enviadas."]);

        } catch (Exception $ex) {
            http_response_code(500);
            return json_encode(["erro" => true, "message" => $ex->getMessage()]);
        }
    }
}