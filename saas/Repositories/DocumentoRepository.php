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

    public function buscarPorIdCard($idCard) {
        $sql = "SELECT * FROM documento_estudantil WHERE idCard = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idCard]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($idCard, $dados) {
        try {
            // 1. Lógica para atualizar a foto apenas se uma nova for enviada
            $sqlFoto = "";
            $params = [
                ':nome'   => $dados['nome'],
                ':escola' => $dados['escola'],
                ':curso'  => $dados['curso'],
                ':nasc'   => $dados['nascimento'],
                ':cpf'    => $dados['cpf'],
                ':rg'     => $dados['rg'],
                ':idCard' => $idCard
            ];

            if (!empty($dados['foto'])) {
                // Processa e salva a nova imagem física
                $diretorioFotos = __DIR__ . '/../../img-validacao/fotos/';
                $nomeArquivo = $idCard . ".jpg";
                $fotoData = explode(',', $dados['foto']);
                $imagemFinal = base64_decode(end($fotoData));
                file_put_contents($diretorioFotos . $nomeArquivo, $imagemFinal);
                
                $sqlFoto = ", fotoDocumento = :foto";
                $params[':foto'] = "img-validacao/fotos/" . $nomeArquivo;
            }

            $sql = "UPDATE documento_estudantil SET 
                        NomeDocumento = :nome,
                        InsEnsinoDocumento = :escola,
                        serieDocumento = :curso,
                        dataNascDocumento = :nasc,
                        nCPF = :cpf,
                        nRGDocumento = :rg
                        $sqlFoto
                    WHERE idCard = :idCard";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);

        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar no Repository: " . $e->getMessage());
        }
    }

    public function buscarResumoStatus($idInst) {
        // SQL robusto que conta exatamente os IDs do seu novo padrão de banco
        $sql = "SELECT 
                    COUNT(CASE WHEN idStatus = 9 THEN 1 END) as criados,
                    COUNT(CASE WHEN idStatus = 5 THEN 1 END) as solicitados,
                    COUNT(CASE WHEN idStatus = 6 THEN 1 END) as producao,
                    COUNT(CASE WHEN idStatus = 7 THEN 1 END) as produzidos,
                    COUNT(CASE WHEN idStatus = 8 THEN 1 END) as entregues
                FROM documento_estudantil 
                WHERE idInsEnsino = :idInst";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idInst' => $idInst]);
        
        // Retorna o array pronto: ["criados" => X, "solicitados" => Y, ...]
        $resumo = $stmt->fetch(PDO::FETCH_ASSOC);

        // Garante que nenhum campo venha nulo para não quebrar o JS
        return [
            "criados"     => (int)($resumo['criados'] ?? 0),
            "solicitados" => (int)($resumo['solicitados'] ?? 0),
            "producao"    => (int)($resumo['producao'] ?? 0),
            "produzidos"  => (int)($resumo['produzidos'] ?? 0),
            "entregues"   => (int)($resumo['entregues'] ?? 0)
        ];
    }

    public function mudarStatus($idCard, $status) {
        $sql = "UPDATE documento_estudantil SET idStatus = :status WHERE idCard = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':status' => $status, ':id' => $idCard]);
    }

    public function resumoDashboardGlobal() {
        $sql = "SELECT 
                    COUNT(CASE WHEN idStatus = 9 THEN 1 END) as criados,
                    COUNT(CASE WHEN idStatus = 5 THEN 1 END) as solicitados,
                    COUNT(CASE WHEN idStatus = 6 THEN 1 END) as producao,
                    COUNT(CASE WHEN idStatus = 7 THEN 1 END) as produzidos,
                    COUNT(CASE WHEN idStatus = 8 THEN 1 END) as entregues
                FROM documento_estudantil";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    

    public function listarProducaoGlobal($status) {
        try {
            // Adicionamos o LIMIT 5000 para evitar sobrecarga no Array do JavaScript
            // Mantemos o ORDER BY dataCriacao DESC para que os mais novos apareçam primeiro
            $sql = "SELECT * FROM documento_estudantil 
                    WHERE idStatus = :status 
                    ORDER BY dataCriacao DESC 
                    LIMIT 5000";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':status', $status, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erro ao listar produção: " . $e->getMessage());
        }
    }

    public function atualizarStatusViradaDeDia() {
        try {
            // Altera de "Criado" (9) para "Solicitado" (5) 
            // se a data de criação for menor que o dia atual (CURDATE)
            $sql = "UPDATE documento_estudantil 
                    SET idStatus = 5 
                    WHERE idStatus = 9 
                    AND DATE(dataCriacao) < CURDATE()";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute();
        } catch (Exception $e) {
            // Não travamos o sistema por isso, apenas logamos se necessário
            return false;
        }
    }

    public function buscarDadosParaLote(array $ids) {
        // 1. Cria os placeholders (?, ?, ?) para os IDs
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        // 2. Busca os dados exatos desses documentos para montar o Excel
        $sql = "SELECT * FROM documento_estudantil WHERE idCard IN ($placeholders)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

}