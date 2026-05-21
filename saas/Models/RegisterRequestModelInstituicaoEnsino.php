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

        // CNPJ: obrigatório e deve ter 14 dígitos (após remover máscara)
        if (empty($this->cnpj)) {
            $erros[] = "CNPJ é obrigatório.";
        } else {
            $cnpjDigitos = preg_replace('/\D/', '', $this->cnpj);
            if (strlen($cnpjDigitos) !== 14) {
                $erros[] = "CNPJ deve ter 14 dígitos.";
            }
        }

        if (empty($this->logradouro))    $erros[] = "Logradouro é obrigatório.";
        if (empty($this->cidade))        $erros[] = "Cidade é obrigatória.";

        // E-mail institucional obrigatório
        if (empty($this->email_contato)) {
            $erros[] = "E-mail institucional é obrigatório.";
        } elseif (!filter_var($this->email_contato, FILTER_VALIDATE_EMAIL)) {
            $erros[] = "E-mail institucional inválido.";
        }

        // Telefone institucional obrigatório
        if (empty($this->telefone)) {
            $erros[] = "Telefone institucional é obrigatório.";
        } else {
            $telDigitos = preg_replace('/\D/', '', $this->telefone);
            if (strlen($telDigitos) < 10) {
                $erros[] = "Telefone institucional inválido (mínimo 10 dígitos).";
            }
        }

        return $erros;
    }
}