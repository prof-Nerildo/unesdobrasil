<?php

namespace Models;

class RegisterRequestModelUsuario {
    // Apenas os campos necessários para criar um novo usuário
    public $idAcl;
    public $idStatus;
    public $idPerfil;
    public $primeiro_nome;
    public $sobrenome;
    public $cargo;
    public $email;
    public $username;
    public $senha;

    /**
     * O construtor recebe o array decodificado do JSON do Frontend
     * e popula as propriedades do objeto de forma segura.
     */
    public function __construct(array $data = []) {
        $this->idAcl         = $data['idAcl'] ?? null;
        $this->idStatus      = $data['idStatus'] ?? null;
        $this->idPerfil      = $data['idPerfil'] ?? null;
        $this->primeiro_nome = $data['primeiro_nome'] ?? null;
        $this->sobrenome     = $data['sobrenome'] ?? null;
        $this->cargo         = $data['cargo'] ?? null;
        $this->email         = $data['email'] ?? null;
        $this->username      = $data['username'] ?? null;
        $this->senha         = $data['senha'] ?? null;
    }

    /**
     * Validação básica para garantir que a API não tente salvar dados vazios no banco.
     * Retorna um array de erros. Se estiver vazio, os dados estão válidos.
     */
    public function validate() {
        $erros = [];

        if (empty($this->primeiro_nome)) {
            $erros[] = "O primeiro nome é obrigatório.";
        }
        
        if (empty($this->sobrenome)) {
            $erros[] = "O sobrenome é obrigatório.";
        }

        if (empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = "Um e-mail válido é obrigatório.";
        }

        if (empty($this->username)) {
            $erros[] = "O nome de usuário (username) é obrigatório.";
        }

        if (empty($this->senha) || strlen($this->senha) < 6) {
            $erros[] = "A senha é obrigatória e deve ter pelo menos 6 caracteres.";
        }

        if (empty($this->idAcl) || empty($this->idStatus) || empty($this->idPerfil)) {
            $erros[] = "Os identificadores de ACL, Status e Perfil são obrigatórios.";
        }

        return $erros;
    }
}