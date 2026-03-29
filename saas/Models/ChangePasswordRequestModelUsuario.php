<?php

namespace Models;

class ChangePasswordRequestModelUsuario {
    public $email;
    public $senhaAtual;
    public $novaSenha;

    public function __construct(array $data = []) {
        $this->email = $data['email'] ?? null;
        $this->senhaAtual = $data['senhaAtual'] ?? null;
        $this->novaSenha = $data['novaSenha'] ?? null;
    }

    public function validate() {
        $erros = [];
        if (empty($this->email)) $erros[] = "E-mail é obrigatório.";
        if (empty($this->senhaAtual)) $erros[] = "Senha atual é obrigatória.";
        if (empty($this->novaSenha) || strlen($this->novaSenha) < 6) {
            $erros[] = "Nova senha deve ter pelo menos 6 caracteres.";
        }
        return $erros;
    }
}