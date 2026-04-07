<?php

namespace Models;

class RegisterRequestModelDocumento {
    public $idInsEnsino;
    public $idStatus;
    public $anoLetivo;
    public $tipoDocumento;
    public $nomeAluno;
    public $nomeEscola;
    public $serieCurso;
    public $cpf;
    public $rg;
    public $dataNascimento;
    public $fotoBase64; // A string do Cropper
    public $idUsuarioAlteracao;

    public function __construct($dados = []) {
        // Mapeia o JSON vindo do Front-end (Nova Solicitação)
        $this->idInsEnsino        = $dados['idInstituicao'] ?? null;
        $this->idStatus           = $dados['idStatus'] ?? 9; // 9: Criado (Rascunho) conforme sua tabela
        $this->anoLetivo          = date('Y');
        $this->tipoDocumento      = $dados['tipoDocumento'] ?? 'Nacional';
        $this->nomeAluno          = $dados['nome'] ?? '';
        $this->nomeEscola         = $dados['escola'] ?? '';
        $this->serieCurso         = $dados['curso'] ?? '';
        $this->cpf                = $dados['cpf'] ?? '';
        $this->rg                 = $dados['rg'] ?? '';
        $this->dataNascimento     = $dados['nascimento'] ?? null;
        $this->fotoBase64         = $dados['foto'] ?? null; // Base64 do Cropper 
        $this->idUsuarioAlteracao = $dados['idUsuario'] ?? null; // ID de quem está logado
    }

    /**
     * Validação baseada no seu padrão
     */
    public function validate() {
        $erros = [];

        if (empty($this->idInsEnsino))    $erros[] = "ID da Instituição não identificado.";
        if (empty($this->nomeAluno))      $erros[] = "O nome do aluno é obrigatório.";
        if (empty($this->nomeEscola))     $erros[] = "A instituição de ensino é obrigatória.";
        if (empty($this->serieCurso))     $erros[] = "A série ou curso é obrigatório.";
        if (empty($this->dataNascimento)) $erros[] = "A data de nascimento é obrigatória.";
        if (empty($this->cpf))            $erros[] = "O CPF é obrigatório.";
        if (empty($this->rg))             $erros[] = "O RG é obrigatório.";
        if (empty($this->fotoBase64))     $erros[] = "A foto do documento é obrigatória.";
        
        // Validação extra de CPF (Mínimo de caracteres)
        if (!empty($this->cpf) && strlen(preg_replace('/\D/', '', $this->cpf)) < 11) {
            $erros[] = "O CPF informado parece incompleto.";
        }

        return $erros;
    }
}