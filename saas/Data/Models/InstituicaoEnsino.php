<?php
namespace Data\Models;

class InstituicaoEnsino {
    public $idInstituicao;
    public $razao_social;
    public $nome_fantasia;
    public $cnpj;
    public $insc_estadual;
    public $insc_municipal;
    public $valor_documento_nacional;
    public $valor_frete;
    public $pode_editar_instituicao;
    public $pode_editar_curso;
    public $idStatus;
    
    // Objetos Relacionados
    public $endereco; 
    public $contatos = []; 
    public $catracas = []; 

    public function __construct($dados = []) {
        $this->idInstituicao = $dados['idInstituicao'] ?? null;
        $this->razao_social  = $dados['razao_social'] ?? null;
        $this->nome_fantasia = $dados['nome_fantasia'] ?? null;
        $this->cnpj          = $dados['cnpj'] ?? null;
        $this->insc_estadual = $dados['insc_estadual'] ?? null;
        $this->insc_municipal = $dados['insc_municipal'] ?? null;
        $this->valor_documento_nacional = $dados['valor_documento_nacional'] ?? 0.00;
        $this->valor_frete   = $dados['valor_frete'] ?? 0.00;
        $this->pode_editar_instituicao = $dados['pode_editar_instituicao'] ?? 'nao';
        $this->pode_editar_curso = $dados['pode_editar_curso'] ?? 'nao';
        $this->idStatus      = $dados['idStatus'] ?? 3;
    }
}