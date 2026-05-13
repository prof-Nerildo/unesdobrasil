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
            $this->apiKey = $config['AppSettings']['JwtSecret'] ?? '';
        } else {
            throw new \Exception("Arquivo appsettings.json não encontrado.");
        }
        if (empty($this->apiKey)) {
            throw new \Exception("JwtSecret não configurado no appsettings.json.");
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

    public function update($id, $dadosJson, $userLogado) {
        try {
            // Se mudar a senha, aplica sua lógica complexa de segurança
            if (!empty($dadosJson['senha'])) {
                $stringComplexa = $this->montarStringCriptografia($dadosJson['senha'], $dadosJson['email']);
                $dadosJson['senha'] = password_hash($stringComplexa, PASSWORD_BCRYPT);
            }

            $sucesso = $this->repositoryUsuario->update($id, $dadosJson);
            return json_encode(["erro" => !$sucesso, "message" => $sucesso ? "Usuário atualizado!" : "Sem alterações."]);
        } catch (Exception $e) {
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }

    public function listarTodos($userLogado) {
        // Admin (ACL 1 = Master) e Unes (ACL 2) podem listar usuários
        if($userLogado['idAcl'] != 1 && $userLogado['idAcl'] != 2) {
            return json_encode(["erro" => true, "message" => "Acesso não autorizado."]);
        }
        return json_encode(["erro" => false, "dados" => $this->repositoryUsuario->listarTodos()]);
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

            // Usa o padrão complexo (SHA256 + apiKey) — o mesmo do register/login
            $stringComplexa = $this->montarStringCriptografia($novaSenha, $email);
            $novaSenhaHash = password_hash($stringComplexa, PASSWORD_BCRYPT);
            
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

    /**
     * Busca um usuário UNES por ID
     */
    public function buscarUsuario($id, $userLogado) {
        try {
            if ($userLogado['idAcl'] != 1 && $userLogado['idAcl'] != 2) {
                return json_encode(["erro" => true, "message" => "Acesso não autorizado."]);
            }
            $usuario = $this->repositoryUsuario->buscarPorId($id);
            if (!$usuario) {
                return json_encode(["erro" => true, "message" => "Usuário não encontrado."]);
            }
            return json_encode(["erro" => false, "dados" => $usuario]);
        } catch (Exception $e) {
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }

    /**
     * Cria um novo usuário UNES (ACL 2)
     */
    public function criarUsuarioUnes($dadosJson, $userLogado) {
        try {
            if ($userLogado['idAcl'] != 1 && $userLogado['idAcl'] != 2) {
                return json_encode(["erro" => true, "message" => "Acesso não autorizado."]);
            }

            // Força ACL 2 (UNES), Status 2 (Ativo), Perfil conforme enviado (2=Admin, 3=Colaborador)
            $dadosJson['idAcl'] = 2;
            $dadosJson['idStatus'] = 2;
            $dadosJson['idPerfil'] = $dadosJson['idPerfil'] ?? 3; // Default: Colaborador UNES

            // Validação básica
            if (empty($dadosJson['primeiro_nome']) || empty($dadosJson['email']) || 
                empty($dadosJson['username']) || empty($dadosJson['senha'])) {
                return json_encode(["erro" => true, "message" => "Preencha todos os campos obrigatórios."]);
            }

            // Verifica se email/username já existem
            if ($this->repositoryUsuario->emailOuUsernameExiste($dadosJson['email'], $dadosJson['username'])) {
                return json_encode(["erro" => true, "message" => "E-mail ou usuário já cadastrado."]);
            }

            $model = new RegisterRequestModelUsuario($dadosJson);
            $passCrip = $this->montarStringCriptografia($dadosJson['senha'], $model->email);
            $model->senha = password_hash($passCrip, PASSWORD_BCRYPT);

            $id = $this->repositoryUsuario->create($model);
            return json_encode(["erro" => false, "id" => $id, "message" => "Usuário UNES criado com sucesso!"]);
        } catch (Exception $e) {
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }

    /**
     * Atualiza dados de um usuário UNES
     */
    public function atualizarUsuario($id, $dadosJson, $userLogado) {
        try {
            if ($userLogado['idAcl'] != 1 && $userLogado['idAcl'] != 2) {
                return json_encode(["erro" => true, "message" => "Acesso não autorizado."]);
            }

            // Se mudar a senha, aplica criptografia
            if (!empty($dadosJson['senha'])) {
                $stringComplexa = $this->montarStringCriptografia($dadosJson['senha'], $dadosJson['email']);
                $dadosJson['senha'] = password_hash($stringComplexa, PASSWORD_BCRYPT);
            }

            $sucesso = $this->repositoryUsuario->update($id, $dadosJson);
            return json_encode(["erro" => !$sucesso, "message" => $sucesso ? "Usuário atualizado!" : "Sem alterações."]);
        } catch (Exception $e) {
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }

    /**
     * Suspende ou reativa um usuário (alterna status entre 2=Ativo e 4=Suspenso)
     */
    public function suspenderUsuario($id, $dadosJson, $userLogado) {
        try {
            if ($userLogado['idAcl'] != 1 && $userLogado['idAcl'] != 2) {
                return json_encode(["erro" => true, "message" => "Acesso não autorizado."]);
            }

            $novoStatus = $dadosJson['idStatus'] ?? 4; // Default: Suspenso

            // Só permite alternar entre Ativo (2) e Suspenso (4)
            if (!in_array($novoStatus, [2, 4])) {
                return json_encode(["erro" => true, "message" => "Status inválido. Use 2 (Ativo) ou 4 (Suspenso)."]);
            }

            $sucesso = $this->repositoryUsuario->atualizarStatus($id, $novoStatus);
            $statusNome = $novoStatus == 4 ? 'suspenso' : 'reativado';
            return json_encode(["erro" => !$sucesso, "message" => $sucesso ? "Usuário {$statusNome} com sucesso!" : "Erro ao alterar status."]);
        } catch (Exception $e) {
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }
}