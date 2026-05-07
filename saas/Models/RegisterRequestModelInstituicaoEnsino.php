<?php
namespace Models;

class RegisterRequestModelInstituicaoEnsino {
    public $razao_social;
    public $nome_fantasia;
    public $cnpj;
    public $cep;
    public $logradouro;
    public $numero;
    public $complemento;
    public $bairro;
    public $cidade;
    public $uf;
    public $email_contato;
    public $telefone;
    public $idUsuarioDono;

    public function __construct($dados = []) {
        $this->razao_social  = $dados['razao_social'] ?? '';
        $this->nome_fantasia = $dados['nome_fantasia'] ?? ($dados['razao_social'] ?? '');
        $this->cnpj          = $dados['cnpj'] ?? '';
        $this->cep           = $dados['cep'] ?? '';
        $this->logradouro    = $dados['logradouro'] ?? ''; 
        $this->numero        = $dados['numero'] ?? '';
        $this->complemento   = $dados['complemento'] ?? '';
        $this->bairro        = $dados['bairro'] ?? '';
        $this->cidade        = $dados['cidade'] ?? '';
        $this->uf            = $dados['uf'] ?? '';
        $this->email_contato = $dados['email_contato'] ?? '';
        $this->telefone      = $dados['telefone'] ?? '';
        $this->idUsuarioDono = $dados['idUsuarioDono'] ?? null;
    }

    public function validate() {
        $erros = [];
        if (empty($this->razao_social))  $erros[] = "Razão Social é obrigatória.";
        if (empty($this->cnpj))          $erros[] = "CNPJ é obrigatório.";
        if (empty($this->logradouro))    $erros[] = "Logradouro é obrigatório.";
        if (empty($this->cidade))        $erros[] = "Cidade é obrigatória.";
        return $erros;
    }
}