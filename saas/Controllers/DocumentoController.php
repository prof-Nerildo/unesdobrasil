<?php

namespace Controllers;

use Models\RegisterRequestModelDocumento;
use Repositories\DocumentoRepository;
use Exception;

class DocumentoController {

    private $repositoryDocumento;

    public function __construct() {
        $this->repositoryDocumento = new \Repositories\DocumentoRepository();
    }

    /**
     * Endpoint para criar uma nova solicitação de documento
     */
    public function register($dadosJson, $userLogado) {
        try {
            $model = new \Models\RegisterRequestModelDocumento($dadosJson);
            $model->idUsuarioAlteracao = $userLogado['id'];

            // 1. Validação básica de campos
            $erros = $model->validate();
            if (!empty($erros)) {
                return json_encode(["erro" => true, "message" => implode(" ", $erros)]);
            }

            // 2. NOVA TRAVA: Verifica duplicidade de CPF no ano atual
            if ($this->repositoryDocumento->cpfJaCadastradoNoAno($model->cpf, $model->anoLetivo)) {
                return json_encode([
                    "erro" => true, 
                    "message" => "Este CPF já possui um documento emitido para o ano letivo " . $model->anoLetivo
                ]);
            }

            // 3. Se passou pela trava, cria o registro
            $idGerado = $this->repositoryDocumento->create($model);

            return json_encode([
                "erro" => false, 
                "id" => $idGerado, 
                "message" => "Solicitação enviada com sucesso!"
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            return json_encode(["erro" => true, "message" => "Erro no Controller: " . $e->getMessage()]);
        }
    }

    public function listarPorStatus($idInst, $status = 9) {
        try {
            $dados = $this->repositoryDocumento->buscarPorInstituicaoEStatus($idInst, $status);
            return json_encode(["erro" => false, "dados" => $dados]);
        } catch (Exception $e) {
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }
    /**
     * Altera o status do documento para Suspenso (4) através do Repository
     */
    public function suspenderDocumento($idCard) {
        try {
            if (empty($idCard)) {
                return json_encode(["erro" => true, "message" => "idCard não informado."]);
            }

            // Chama o Repository corretamente
            $sucesso = $this->repositoryDocumento->suspender($idCard);

            return json_encode([
                "erro" => !$sucesso, 
                "message" => $sucesso ? "Documento suspenso com sucesso!" : "Não foi possível suspender."
            ]);
        } catch (Exception $e) {
            // Importante: use \Exception ou garanta o 'use Exception' no topo
            return json_encode(["erro" => true, "message" => "Erro no Controller: " . $e->getMessage()]);
        }
    }

    public function buscarDetalhes($idCard) {
        try {
            $dados = $this->repositoryDocumento->buscarPorIdCard($idCard);
            if (!$dados) throw new Exception("Documento não encontrado.");
            return json_encode(["erro" => false, "dados" => $dados]);
        } catch (Exception $e) {
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }

    public function atualizarDocumento($idCard, $dadosJson) {
        try {
            if (empty($idCard)) throw new Exception("idCard não fornecido.");

            // Chama o Repository para atualizar
            $sucesso = $this->repositoryDocumento->update($idCard, $dadosJson);

            return json_encode([
                "erro" => !$sucesso, 
                "message" => $sucesso ? "Documento atualizado com sucesso!" : "Nenhuma alteração realizada."
            ]);
        } catch (Exception $e) {
            return json_encode(["erro" => true, "message" => "Erro no Controller: " . $e->getMessage()]);
        }
    }

    public function resumoDashboard($idInst) {
        try {
            $dados = $this->repositoryDocumento->buscarResumoStatus($idInst);
            return json_encode(["erro" => false, "dados" => $dados]);
        } catch (Exception $e) {
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }

    public function listarPorStatusGenerico($idInst, $status) {
        try {
            $dados = $this->repositoryDocumento->buscarPorInstituicaoEStatus($idInst, $status);
            return json_encode(["erro" => false, "dados" => $dados]);
        } catch (Exception $e) {
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }

    public function atualizarStatusDoc($idCard, $dadosJson) {
        try {
            $novoStatus = $dadosJson['novoStatus'] ?? null;
            if (!$idCard || !$novoStatus) throw new Exception("Dados insuficientes.");

            // Reutilizamos a lógica de update do repository focada apenas no status
            $sucesso = $this->repositoryDocumento->mudarStatus($idCard, $novoStatus);

            return json_encode(["erro" => !$sucesso, "message" => $sucesso ? "Status atualizado!" : "Erro ao atualizar."]);
        } catch (Exception $e) {
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }
    
}