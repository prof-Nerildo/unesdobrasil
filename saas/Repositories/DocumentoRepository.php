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
     * Cria o documento estudantil com lógica de ID sequencial, fuso horário BR
     * e suporte ao ID Legado (Coringa para sistema 1.0)
     */
    /**
     * Cria o documento estudantil com lógica de ID sequencial corrigida (12 dígitos)
     */
    public function create(\Models\RegisterRequestModelDocumento $request) {
        try {
            $anoAtualBr = date('Y');

            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }

            // --- BUSCA A INSTITUIÇÃO PARA VERIFICAR ID LEGADO ---
            $sqlInst = "SELECT idInstituicao, idLegado FROM instituicao WHERE idInstituicao = :id";
            $stmtInst = $this->db->prepare($sqlInst);
            $stmtInst->execute([':id' => $request->idInsEnsino]);
            $inst = $stmtInst->fetch(PDO::FETCH_ASSOC);

            if (!$inst) {
                throw new Exception("Instituição não encontrada.");
            }

            // LÓGICA DO CORINGA: Usa o idLegado se existir, senão usa o idInstituicao
            $codigoIdentificador = !empty($inst['idLegado']) ? $inst['idLegado'] : $inst['idInstituicao'];

            // 1. LÓGICA DO IDNAC (Sequencial por instituição/ano)
            $sqlMax = "SELECT MAX(CAST(idNac AS UNSIGNED)) as ultimo 
                       FROM documento_estudantil 
                       WHERE idInsEnsino = :idInst AND anoLetivo = :ano";
            
            $stmtMax = $this->db->prepare($sqlMax);
            $stmtMax->execute([
                ':idInst' => $request->idInsEnsino, 
                ':ano'    => $anoAtualBr
            ]);
            $res = $stmtMax->fetch(PDO::FETCH_ASSOC);

            // GERA O PRÓXIMO NÚMERO (Ex: 1)
            $proximoSequencial = ($res['ultimo'] ?? 0) + 1;
            
            // --- CORREÇÃO MATADORA ---
            // 1. Convertemos para (int) para remover qualquer zero à esquerda que venha do banco
            $codigoLimpo = (int)$codigoIdentificador; 

            // 2. Agora sim, formatamos para ter EXATAMENTE 4 dígitos
            $idInstFormat = str_pad($codigoLimpo, 4, "0", STR_PAD_LEFT);

            // 3. O sequencial também travado em 4 dígitos
            $idNac = str_pad($proximoSequencial, 4, "0", STR_PAD_LEFT);

            // Resultado final: 2026 + 0005 + 0001 = 12 dígitos
            $idCard = $anoAtualBr . $idInstFormat . $idNac;
            // -------------------------
            // ---------------------------

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
                throw new Exception("Falha ao salvar a imagem.");
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
            $agora = date('Y-m-d H:i:s');

            $sqlFoto = "";
            $params = [
                ':nome'   => $dados['nome'],
                ':escola' => $dados['escola'],
                ':curso'  => $dados['curso'],
                ':nasc'   => $dados['nascimento'],
                ':cpf'    => $dados['cpf'],
                ':rg'     => $dados['rg'],
                ':agora'  => $agora, // Passamos a hora do BR
                ':idCard' => $idCard
            ];

            if (!empty($dados['foto'])) {
                $diretorioFotos = __DIR__ . '/../../img-validacao/fotos/';
                $nomeArquivo = $idCard . ".jpg";
                $fotoData = explode(',', $dados['foto']);
                $imagemFinal = base64_decode(end($fotoData));
                file_put_contents($diretorioFotos . $nomeArquivo, $imagemFinal);
                
                $sqlFoto = ", fotoDocumento = :foto";
                $params[':foto'] = "img-validacao/fotos/" . $nomeArquivo;
            }

            // Adicionado dataAtualizacao = :agora para rastreio correto
            $sql = "UPDATE documento_estudantil SET 
                        NomeDocumento = :nome,
                        InsEnsinoDocumento = :escola,
                        serieDocumento = :curso,
                        dataNascDocumento = :nasc,
                        nCPF = :cpf,
                        nRGDocumento = :rg,
                        dataAlteracao = :agora
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
            $hojeBrasil = date('Y-m-d');

            // 3. Na query, comparamos a data de criação com a data que o PHP gerou.
            // Isso ignora o relógio do servidor dos EUA.
            $sql = "UPDATE documento_estudantil 
                    SET idStatus = 5 
                    WHERE idStatus = 9 
                    AND DATE(dataCriacao) < :hoje";
            
            $stmt = $this->db->prepare($sql);
            // Enviamos a data correta do Brasil (Ex: 2026-04-23)
            return $stmt->execute([':hoje' => $hojeBrasil]);

        } catch (Exception $e) {
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