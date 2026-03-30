<?php
namespace Models;

class RegisterRequestModelInstituicaoEnsino {
    // Identidade PJ
    public $razao_social;
    public $nome_fantasia;
    public $cnpj;
    public $insc_estadual;
    public $insc_municipal;
    
    // Endereço
    public $cep;
    public $logradouro;
    public $numero;
    public $complemento;
    public $bairro;
    public $cidade;
    public $uf;
    
    // Contatos
    public $email_contato;
    public $telefone;
    public $fax;

    // Vínculo (Obrigatório para o Callback)
    public $idUsuarioDono;

    public function __construct($dados = []) {
        // Dados da Instituição
        $this->razao_social  = $dados['razaoSocial'] ?? '';
        $this->nome_fantasia = $dados['nomeFantasia'] ?? '';
        $this->cnpj          = $dados['cnpj'] ?? '';
        $this->insc_estadual = $dados['inscEstadual'] ?? '';
        $this->insc_municipal= $dados['inscMunicipal'] ?? '';
        
        // Dados de Endereço (Mapeado do HTML)
        $this->cep           = $dados['cep'] ?? '';
        $this->logradouro    = $dados['endereco'] ?? ''; 
        $this->numero        = $dados['numero'] ?? 'S/N';
        $this->complemento   = $dados['complemento'] ?? '';
        $this->bairro        = $dados['bairro'] ?? '';
        $this->cidade        = $dados['cidade'] ?? '';
        $this->uf            = $dados['uf'] ?? '';
        
        // Dados de Contato
        $this->email_contato = $dados['email'] ?? '';
        $this->telefone      = $dados['telefone'] ?? '';
        $this->fax           = $dados['fax'] ?? '';

        // ID do Usuário para fazer o vínculo no banco
        $this->idUsuarioDono = $dados['idUsuarioDono'] ?? null;
    }

    public function validate() {
        $erros = [];
        
        if (empty($this->razao_social))  $erros[] = "Razão Social obrigatória.";
        if (empty($this->cnpj))          $erros[] = "CNPJ obrigatório.";
        if (empty($this->logradouro))    $erros[] = "Endereço obrigatório.";
        if (empty($this->cidade))        $erros[] = "Cidade obrigatória.";
        if (empty($this->idUsuarioDono)) $erros[] = "ID do proprietário não informado.";

        return $erros;
    }
}