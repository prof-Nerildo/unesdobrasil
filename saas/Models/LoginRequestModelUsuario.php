<?php

namespace Models;

class LoginRequestModelUsuario {
    // Usamos 'login' genérico para permitir que o usuário entre com e-mail OU username
    public $login; 
    public $senha;

    /**
     * O construtor recebe o array decodificado do JSON do Frontend
     */
    public function __construct(array $data = []) {
        $this->login = $data['login'] ?? null;
        $this->senha = $data['senha'] ?? null;
    }

    /**
     * Validação básica para o Login
     */
    public function validate() {
        $erros = [];

        if (empty($this->login)) {
            $erros[] = "O campo de login (e-mail ou usuário) é obrigatório.";
        }

        if (empty($this->senha)) {
            $erros[] = "A senha é obrigatória.";
        }

        return $erros;
    }
}