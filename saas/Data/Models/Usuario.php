<?php

namespace Data\Models;

class Usuario {
    // Propriedades (espelhando a nova tabela do banco)
    private $idUsuario;
    private $idAcl;
    private $idStatus;
    private $idPerfil;
    
    // Dados Pessoais e Profissionais
    private $primeiro_nome;
    private $sobrenome;
    private $cargo;
    
    // Credenciais
    private $email;
    private $username;
    private $senha;
    
    // Controle de Senha
    private $reset_token;
    private $reset_token_expira_em;
    
    // Logs e Datas
    private $last_login;
    private $created_at;
    private $updated_at;

    public function __construct() {}

    // --- GETTERS E SETTERS ---

    public function getIdUsuario() { return $this->idUsuario; }
    public function setIdUsuario($idUsuario) { $this->idUsuario = $idUsuario; }

    public function getIdAcl() { return $this->idAcl; }
    public function setIdAcl($idAcl) { $this->idAcl = $idAcl; }

    public function getIdStatus() { return $this->idStatus; }
    public function setIdStatus($idStatus) { $this->idStatus = $idStatus; }

    public function getIdPerfil() { return $this->idPerfil; }
    public function setIdPerfil($idPerfil) { $this->idPerfil = $idPerfil; }

    public function getPrimeiroNome() { return $this->primeiro_nome; }
    public function setPrimeiroNome($primeiro_nome) { $this->primeiro_nome = $primeiro_nome; }

    public function getSobrenome() { return $this->sobrenome; }
    public function setSobrenome($sobrenome) { $this->sobrenome = $sobrenome; }

    public function getCargo() { return $this->cargo; }
    public function setCargo($cargo) { $this->cargo = $cargo; }

    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }

    public function getUsername() { return $this->username; }
    public function setUsername($username) { $this->username = $username; }

    public function getSenha() { return $this->senha; }
    public function setSenha($senha) { $this->senha = $senha; }

    public function getResetToken() { return $this->reset_token; }
    public function setResetToken($reset_token) { $this->reset_token = $reset_token; }

    public function getResetTokenExpiraEm() { return $this->reset_token_expira_em; }
    public function setResetTokenExpiraEm($reset_token_expira_em) { $this->reset_token_expira_em = $reset_token_expira_em; }

    public function getLastLogin() { return $this->last_login; }
    public function setLastLogin($last_login) { $this->last_login = $last_login; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }

    public function getUpdatedAt() { return $this->updated_at; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }

    // --- MÉTODO PARA A API (JSON) ---
    // Converte o objeto para array ocultando dados sensíveis
    public function toArray() {
        return [
            'idUsuario'     => $this->idUsuario,
            'idAcl'         => $this->idAcl,
            'idStatus'      => $this->idStatus,
            'idPerfil'      => $this->idPerfil,
            'primeiro_nome' => $this->primeiro_nome,
            'sobrenome'     => $this->sobrenome,
            'cargo'         => $this->cargo,
            'email'         => $this->email,
            'username'      => $this->username,
            'last_login'    => $this->last_login,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at
            // Senha e tokens de reset NUNCA vão para o FrontEnd!
        ];
    }
}