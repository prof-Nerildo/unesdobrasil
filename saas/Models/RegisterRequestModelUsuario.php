<?php

namespace Models;

class RegisterRequestModelUsuario {
    public $idAcl;
    public $idStatus;
    public $idPerfil;
    public $idInstituicao; // <--- ADICIONE ESTA LINHA AQUI PARA CORRIGIR O ERRO
    public $primeiro_nome;
    public $sobrenome;
    public $cargo;
    public $email;
    public $username;
    public $senha;
    public $celular; 

    public function __construct($dados = []) {
        // Mapeia o JSON vindo do CMS para as propriedades da classe
        $this->idAcl         = $dados['idAcl'] ?? 3; 
        $this->idStatus      = $dados['idStatus'] ?? 2; 
        $this->idPerfil      = $dados['idPerfil'] ?? 2; 
        $this->idInstituicao = $dados['idInstituicao'] ?? null; // Agora a variável existe!
        $this->primeiro_nome = $dados['primeiro_nome'] ?? '';
        $this->sobrenome     = $dados['sobrenome'] ?? '';
        $this->cargo         = $dados['cargo'] ?? '';
        $this->email         = $dados['email'] ?? '';
        $this->username      = $dados['username'] ?? '';
        $this->senha         = isset($dados['senha']) ? password_hash($dados['senha'], PASSWORD_BCRYPT) : null;
        $this->celular       = $dados['celular'] ?? ''; 
    }

    /**
     * Validação básica de entrada
     */
    public function validate() {
        $erros = [];
        if (empty($this->primeiro_nome)) $erros[] = "O nome é obrigatório.";
        if (empty($this->email))         $erros[] = "O e-mail é obrigatório.";
        if (empty($this->username))      $erros[] = "O usuário é obrigatório.";
        if (empty($this->senha))         $erros[] = "A senha é obrigatória.";
        
        return $erros;
    }
}