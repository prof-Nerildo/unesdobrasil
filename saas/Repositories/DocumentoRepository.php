<?php

namespace Repositories;

use PDO;
use Exception;

class DocumentoRepository {
    private $db;

    public function __construct() {
        $this->db = \Data\Database::getConnection();
    }

    /**
     * Verifica se já existe um documento para este CPF no ano letivo atual
     * Trava preventiva no PHP antes de tentar o Insert
     */
    public function cpfJaCadastradoNoAno($cpf, $anoLetivo) {
        // Remove qualquer máscara do CPF para comparar apenas números
        $cpfLimpo = preg_replace('/\D/', '', $cpf);
        
        $sql = "SELECT COUNT(*) as total FROM documento_estudantil 
                WHERE REPLACE(REPLACE(nCPF, '.', ''), '-', '') = :cpf 
                AND anoLetivo = :ano";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cpf' => $cpfLimpo, ':ano' => $anoLetivo]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($res['total'] ?? 0) > 0;
    }

    /**
     * Busca documentos da instituição com status específico
     */
    public function buscarPorInstituicaoEStatus($idInst, $status = 9) {
        $sql = "SELECT * FROM documento_estudantil 
                WHERE idInsEnsino = :idInst AND idStatus = :status 
                ORDER BY dataCriacao DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idInst' => $idInst, ':status' => $status]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Cria o documento estudantil com lógica de ID sequencial e fuso horário BR
     */
    public function create(\Models\RegisterRequestModelDocumento $request) {
        try {
            // 0. AJUSTE DE FUSO HORÁRIO (Garante horário de Brasília na Contabo/EUA)
            date_default_timezone_set('America/Sao_Paulo');
            $anoAtualBr = date('Y');

            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }

            // 1. LÓGICA DO IDNAC E IDCARD
            $sqlMax = "SELECT MAX(CAST(idNac AS UNSIGNED)) as ultimo 
                       FROM documento_estudantil 
                       WHERE idInsEnsino = :idInst AND anoLetivo = :ano";
            
            $stmtMax = $this->db->prepare($sqlMax);
            $stmtMax->execute([
                ':idInst' => $request->idInsEnsino, 
                ':ano'    => $anoAtualBr
            ]);
            $res = $stmtMax->fetch(PDO::FETCH_ASSOC);

            $proximoSequencial = ($res['ultimo'] ?? 0) + 1;
            $idNac = str_pad($proximoSequencial, 4, "0", STR_PAD_LEFT);
            
            // DNA UNES: Ano(4) + Inst(4) + Nac(4)
            $idInstFormat = str_pad($request->idInsEnsino, 4, "0", STR_PAD_LEFT);
            $idCard = $anoAtualBr . $idInstFormat . $idNac;

            // 2. PROCESSAMENTO DA FOTO
            $nomeArquivo = $idCard . ".jpg";
            $diretorioFotos = __DIR__ . '/../../img-validacao/fotos/';
            
            if (!is_dir($diretorioFotos)) {
                mkdir($diretorioFotos, 0777, true);
            }

            $data = explode(',', $request->fotoBase64);
            $imagemFinal = base64_decode(end($data));
            $caminhoCompletoArquivo = $diretorioFotos . $nomeArquivo;

            if (file_put_contents($caminhoCompletoArquivo, $imagemFinal) === false) {
                throw new Exception("Falha ao salvar a imagem em: " . $diretorioFotos);
            }

            // 3. INSERÇÃO NO BANCO
            $sql = "INSERT INTO documento_estudantil (
                        idInsEnsino, idStatus, idUsuarioAlteracao, tipoDocumento, 
                        anoLetivo, idNac, idCard, NomeDocumento, 
                        InsEnsinoDocumento, serieDocumento, nCPF, 
                        nRGDocumento, dataNascDocumento, fotoDocumento
                    ) VALUES (
                        :idInst, :idStatus, :idUser, :tipo, 
                        :ano, :idNac, :idCard, :nome, 
                        :escola, :curso, :cpf, 
                        :rg, :nasc, :foto
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':idInst'  => $request->idInsEnsino,
                ':idStatus'=> $request->idStatus,
                ':idUser'  => $request->idUsuarioAlteracao,
                ':tipo'    => $request->tipoDocumento,
                ':ano'     => $anoAtualBr,
                ':idNac'   => $idNac,
                ':idCard'  => $idCard,
                ':nome'    => $request->nomeAluno,
                ':escola'  => $request->nomeEscola,
                ':curso'   => $request->serieCurso,
                ':cpf'     => $request->cpf,
                ':rg'      => $request->rg,
                ':nasc'    => $request->dataNascimento,
                ':foto'    => "img-validacao/fotos/" . $nomeArquivo
            ]);

            $this->db->commit();
            return $idCard; 

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            // --- TRATAMENTO DE DUPLICIDADE (UNIQUE KEY DO BANCO) ---
            // Código 23000 é erro de integridade (Duplicate Entry) no MySQL
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), '1062 Duplicate entry')) {
                throw new Exception("Este CPF já possui um documento registrado para o ano letivo atual.");
            }

            throw new Exception("Erro no Repository: " . $e->getMessage());
        }
    }

    /**
     * Altera o status do documento para Suspenso (4)
     */
    public function suspender($idCard) {
        try {
            $sql = "UPDATE documento_estudantil SET idStatus = 4 WHERE idCard = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $idCard]);
        } catch (Exception $e) {
            throw new Exception("Erro ao suspender no Repository: " . $e->getMessage());
        }
    }
}