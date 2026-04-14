<?php

namespace Controllers;

use Models\RegisterRequestModelUsuario;
use Repositories\UsuarioRepository;
use Dependencies\JwtHandler;
use Exception;

class UsuarioController {

    private $repositoryUsuario;
    private $apiKey;
    private $jwt;

    public function __construct() {
        $this->repositoryUsuario = new \Repositories\UsuarioRepository();
        $this->jwt = new \Dependencies\JwtHandler();
        $this->carregarApiKey();
    }

    private function carregarApiKey() {
        $jsonPath = __DIR__ . '/../appsettings.json';
        if (file_exists($jsonPath)) {
            $config = json_decode(file_get_contents($jsonPath), true);
            $this->apiKey = $config['AppSettings']['JwtSecret'] ?? "Leite_Com_MangaRosa_Mata_todos_kkk@2026";
        } else {
            $this->apiKey = "Leite_Com_MangaRosa_Mata_todos_kkk@2026";
        }
    }

    private function computerSha256Hash($rawData) {
        return hash('sha256', $rawData ?? '');
    }

    private function montarStringCriptografia($senha, $identificadorUnico) {
        // Forçamos o identificador (email) para minúsculo para garantir consistência
        $passSHA256 = $this->computerSha256Hash($senha);
        $idSHA256 = $this->computerSha256Hash(strtolower(trim($identificadorUnico)));
        return $passSHA256 . $this->apiKey . $idSHA256;
    }

    public function login($dadosJson) {
        try {
            $loginInput = $dadosJson['login'] ?? '';
            $senhaInput = $dadosJson['senha'] ?? '';

            $user = $this->repositoryUsuario->findByLogin($loginInput);

            if (!$user) {
                return json_encode(["erro" => true, "message" => "Usuário não encontrado."]);
            }

            // --- LÓGICA DE VALIDAÇÃO COM TRANSIÇÃO DE SENHA ---
            $senhaOk = false;
            $senhaPrecisaAtualizar = false;

            // 1. Tenta o padrão complexo novo (String Complexa + Bcrypt)
            $stringComplexa = $this->montarStringCriptografia($senhaInput, $user->getEmail());
            if (password_verify($stringComplexa, $user->getSenha())) {
                $senhaOk = true;
            } 
            // 2. Tenta o padrão simples (Bcrypt direto na senha pura)
            else if (password_verify($senhaInput, $user->getSenha())) {
                $senhaOk = true;
            }
            // 3. Tenta o PADRÃO ANTIGO (Texto Limpo - Migrado do sistema antigo)
            else if ($senhaInput === $user->getSenha()) {
                $senhaOk = true;
                $senhaPrecisaAtualizar = true; // Marca que precisamos salvar o hash novo
            }

            if ($senhaOk) {
                // Se a senha estava em texto limpo, salvamos o hash agora para segurança máxima
                if ($senhaPrecisaAtualizar) {
                    // Usamos o padrão complexo do seu sistema novo
                    $novoHashSeguro = password_hash($stringComplexa, PASSWORD_BCRYPT);
                    $this->repositoryUsuario->updateSenha($user->getEmail(), $novoHashSeguro);
                }

                $tokenData = [
                    "id" => $user->getIdUsuario(),
                    "nome" => $user->getPrimeiroNome(),
                    "idAcl" => $user->getIdAcl(),
                    "idInstituicao" => $user->getIdInstituicao()
                ];

                $token = $this->jwt->generate($tokenData);
                $this->repositoryUsuario->updateLastLogin($user->getIdUsuario());

                return json_encode([
                    "erro" => false,
                    "message" => "Login realizado com sucesso!",
                    "token" => $token,
                    "usuario" => $user->toArray()
                ]);
            }

            return json_encode(["erro" => true, "message" => "Senha incorreta."]);

        } catch (Exception $e) {
            return json_encode(["erro" => true, "message" => "Erro: " . $e->getMessage()]);
        }
    }

    public function getMe($userLogado) {
        try {
            // Se o token for válido, o $userLogado terá o ID
            $dadosCompletos = $this->repositoryUsuario->findMe($userLogado['id']);
            
            if (!$dadosCompletos) {
                return json_encode(["erro" => true, "message" => "Usuário não encontrado no banco."]);
            }

            return json_encode([
                "erro" => false,
                "dados" => $dadosCompletos
            ]);
        } catch (Exception $ex) {
            http_response_code(500);
            return json_encode(["erro" => true, "message" => $ex->getMessage()]);
        }
    }

    public function forgotPassword($dadosJson) {
        try {
            $login = $dadosJson['login'] ?? '';
            if (empty($login)) {
                return json_encode(["erro" => true, "message" => "Informe o e-mail ou usuário."]);
            }

            $user = $this->repositoryUsuario->findByLogin($login);

            if ($user) {
                $emailDestino = $user->getEmail();
                $nomeDestino = $user->getPrimeiroNome();

                $tokenRecuperacao = $this->jwt->generate([
                    "id" => $user->getIdUsuario(),
                    "email" => $emailDestino,
                    "tipo" => "recuperacao"
                ]);

                // Tenta instanciar o EmailHandler
                if (!class_exists('\Dependencies\EmailHandler')) {
                    throw new Exception("Classe EmailHandler não encontrada. Verifique o require no index.php");
                }

                $emailHandler = new \Dependencies\EmailHandler();
                $enviou = $emailHandler->enviarRecuperacao($emailDestino, $nomeDestino, $tokenRecuperacao);

                if (!$enviou) {
                    return json_encode(["erro" => true, "message" => "O servidor de e-mail recusou o envio."]);
                }

                return json_encode(["erro" => false, "message" => "Instruções enviadas para o seu e-mail!"]);
            }

            // Por segurança, damos a mesma resposta se o user não existir
            return json_encode(["erro" => false, "message" => "Se o usuário existir, as instruções foram enviadas."]);

        } catch (Exception $ex) {
            // Isso aqui vai matar o Erro 500 e te mostrar o erro real na tela
            return json_encode(["erro" => true, "message" => "Erro no servidor: " . $ex->getMessage()]);
        }
    }

    // No UsuarioController.php
    public function changePassword($dadosJson, $userLogado) {
        try {
            $novaSenha = $dadosJson['novaSenha'] ?? '';
            
            // O $userLogado vem do $validarToken() no index.php
            if (!$userLogado || !isset($userLogado['email'])) {
                throw new Exception("Token de recuperação inválido ou expirado.", 401);
            }

            if (strlen($novaSenha) < 6) {
                throw new Exception("A senha deve ter no mínimo 6 caracteres.", 400);
            }

            $email = $userLogado['email'];

            // Criptografia simples (a mesma que estamos usando no Login agora)
            $novaSenhaHash = password_hash($novaSenha, PASSWORD_BCRYPT);
            
            $sucesso = $this->repositoryUsuario->updateSenha($email, $novaSenhaHash);

            if (!$sucesso) {
                throw new Exception("Não foi possível atualizar a senha no banco de dados.");
            }

            return json_encode([
                "erro" => false, 
                "message" => "Senha alterada com sucesso!"
            ]);

        } catch (Exception $ex) {
            // Define o código de erro para evitar o 500 genérico se possível
            http_response_code($ex->getCode() ?: 500);
            return json_encode([
                "erro" => true, 
                "message" => $ex->getMessage()
            ]);
        }
    }

    public function register($dadosJson) {
        try {
            $model = new RegisterRequestModelUsuario($dadosJson);
            
            // Criamos a senha complexa usando o email como chave
            $passCrip = $this->montarStringCriptografia($dadosJson['senha'], $model->email);
            $model->senha = password_hash($passCrip, PASSWORD_BCRYPT);
            
            $id = $this->repositoryUsuario->create($model);
            return json_encode(["erro" => false, "id" => $id, "message" => "Usuário registrado com sucesso!"]);
        } catch (Exception $e) {
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }
}