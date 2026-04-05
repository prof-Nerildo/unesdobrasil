<?php
namespace Data\Models;

class Usuario {
    private $idUsuario;
    private $idAcl;
    private $idInstituicao; // <-- ADICIONADO
    private $idStatus;
    private $idPerfil;
    private $primeiro_nome;
    private $sobrenome;
    private $cargo;
    private $email;
    private $username;
    private $senha;
    private $last_login;

    public function __construct() {}

    // Getters e Setters Essenciais
    public function getIdUsuario() { return $this->idUsuario; }
    public function setIdUsuario($id) { $this->idUsuario = $id; }

    public function getIdAcl() { return $this->idAcl; }
    public function setIdAcl($id) { $this->idAcl = $id; }

    public function getIdInstituicao() { return $this->idInstituicao; } // <-- ADICIONADO
    public function setIdInstituicao($id) { $this->idInstituicao = $id; } // <-- ADICIONADO

    public function getPrimeiroNome() { return $this->primeiro_nome; }
    public function setPrimeiroNome($nome) { $this->primeiro_nome = $nome; }

    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }

    public function getSenha() { return $this->senha; }
    public function setSenha($senha) { $this->senha = $senha; }

    // Converte para Array para o Front-end (Usado no login)
    public function toArray() {
        return [
            'id' => $this->idAcl, // Mapeamos idAcl como 'id' para o JS da index
            'real_id' => $this->idUsuario,
            'idInstituicao' => $this->idInstituicao,
            'nome' => $this->primeiro_nome,
            'email' => $this->email
        ];
    }
}