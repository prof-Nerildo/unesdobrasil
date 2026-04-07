<?php
namespace App\Data\Models;

class DocumentoEstudantil {
    public $idDocumento;
    public $idInsEnsino;
    public $idStatus;
    public $idUsuarioAlteracao;
    public $tipoDocumento;
    public $anoLetivo;
    public $idNac;
    public $idCard;
    public $nome;
    public $escola;
    public $curso;
    public $cpf;
    public $rg;
    public $nascimento;
    public $foto;

    public function __construct($dados = []) {
        $this->idInsEnsino = $dados['idInstituicao'] ?? null;
        $this->idStatus    = 9; // Criado (Rascunho)
        $this->tipoDocumento = 'Nacional';
        $this->anoLetivo   = date('Y');
        $this->nome        = $dados['nome'] ?? null;
        $this->escola      = $dados['escola'] ?? null;
        $this->curso       = $dados['curso'] ?? null;
        $this->cpf         = $dados['cpf'] ?? null;
        $this->rg          = $dados['rg'] ?? null;
        $this->nascimento  = $dados['nascimento'] ?? null;
        $this->idUsuarioAlteracao = $dados['idUsuario'] ?? null;
    }
}