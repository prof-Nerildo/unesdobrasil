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

    
}