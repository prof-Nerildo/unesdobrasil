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

    public function register($dadosJson, $userLogado) {
        try {
            $model = new \Models\RegisterRequestModelDocumento($dadosJson);
            $model->idUsuarioAlteracao = $userLogado['id'];
            $erros = $model->validate();
            if (!empty($erros)) return json_encode(["erro" => true, "message" => implode(" ", $erros)]);

            if ($this->repositoryDocumento->cpfJaCadastradoNoAno($model->cpf, $model->anoLetivo)) {
                return json_encode(["erro" => true, "message" => "Este CPF já possui um documento para o ano " . $model->anoLetivo]);
            }

            $idGerado = $this->repositoryDocumento->create($model);
            return json_encode(["erro" => false, "id" => $idGerado, "message" => "Solicitação enviada!"]);
        } catch (Exception $e) {
            http_response_code(500);
            return json_encode(["erro" => true, "message" => $e->getMessage()]);
        }
    }

    public function listarPorStatus($idInst, $status = 9) {
        try {
            $dados = $this->repositoryDocumento->buscarPorInstituicaoEStatus($idInst, $status);
            return json_encode(["erro" => false, "dados" => $dados]);
        } catch (Exception $e) { return json_encode(["erro" => true, "message" => $e->getMessage()]); }
    }

    public function suspenderDocumento($idCard) {
        try {
            $sucesso = $this->repositoryDocumento->suspender($idCard);
            return json_encode(["erro" => !$sucesso, "message" => $sucesso ? "Suspenso!" : "Erro ao suspender."]);
        } catch (Exception $e) { return json_encode(["erro" => true, "message" => $e->getMessage()]); }
    }

    public function buscarDetalhes($idCard) {
        try {
            $dados = $this->repositoryDocumento->buscarPorIdCard($idCard);
            return json_encode(["erro" => false, "dados" => $dados]);
        } catch (Exception $e) { return json_encode(["erro" => true, "message" => $e->getMessage()]); }
    }

    public function atualizarDocumento($idCard, $dadosJson) {
        try {
            $sucesso = $this->repositoryDocumento->update($idCard, $dadosJson);
            return json_encode(["erro" => !$sucesso, "message" => "Atualizado!"]);
        } catch (Exception $e) { return json_encode(["erro" => true, "message" => $e->getMessage()]); }
    }

    public function resumoDashboard($idInst) {
        try {
            $dados = $this->repositoryDocumento->buscarResumoStatus($idInst);
            return json_encode(["erro" => false, "dados" => $dados]);
        } catch (Exception $e) { return json_encode(["erro" => true, "message" => $e->getMessage()]); }
    }

    public function listarPorStatusGenerico($idInst, $status) {
        try {
            $dados = $this->repositoryDocumento->buscarPorInstituicaoEStatus($idInst, $status);
            return json_encode(["erro" => false, "dados" => $dados]);
        } catch (Exception $e) { return json_encode(["erro" => true, "message" => $e->getMessage()]); }
    }

    public function atualizarStatusDoc($idCard, $dadosJson) {
        try {
            $sucesso = $this->repositoryDocumento->mudarStatus($idCard, $dadosJson['novoStatus']);
            return json_encode(["erro" => !$sucesso, "message" => "Status alterado!"]);
        } catch (Exception $e) { return json_encode(["erro" => true, "message" => $e->getMessage()]); }
    }

    public function resumoGlobal() {
        try {
            $this->repositoryDocumento->atualizarStatusViradaDeDia();
            $dados = $this->repositoryDocumento->resumoDashboardGlobal(); 
            return json_encode(["erro" => false, "dados" => $dados]);
        } catch (Exception $e) { return json_encode(["erro" => true, "message" => $e->getMessage()]); }
    }

    public function listarProducaoGlobal($status) {
        try {
            $this->repositoryDocumento->atualizarStatusViradaDeDia();
            $dados = $this->repositoryDocumento->listarProducaoGlobal($status); 
            return json_encode(["erro" => false, "dados" => $dados ?: []]);
        } catch (Exception $e) { return json_encode(["erro" => true, "message" => $e->getMessage()]); }
    }

    public function gerarLoteZip($idsJson) {
        try {
            $ids = $idsJson['ids'] ?? [];
            if (empty($ids)) throw new Exception("Nenhum documento selecionado.");

            $documentos = $this->repositoryDocumento->buscarDadosParaLote($ids);
            
            $zip = new \ZipArchive();
            $zipName = "Lote_" . date('Ymd_His') . ".zip";
            
            $diretorioZip = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'zip_temp' . DIRECTORY_SEPARATOR;
            
            if (!is_dir($diretorioZip)) { 
                mkdir($diretorioZip, 0777, true); 
            }
            
            $zipPath = $diretorioZip . $zipName;

            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                throw new Exception("Falha ao criar ZIP.");
            }

            // --- GERAÇÃO DO EXCEL ---
            $excelContent = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?>
            <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
                <Worksheet ss:Name="docs">
                    <Table>
                        <Row>
                            <Cell><Data ss:Type="String">idCard</Data></Cell>
                            <Cell><Data ss:Type="String">nomeCompleto</Data></Cell>
                            <Cell><Data ss:Type="String">instituicaoEnsino</Data></Cell>
                            <Cell><Data ss:Type="String">serieCurso</Data></Cell>
                            <Cell><Data ss:Type="String">cpfIdentidade</Data></Cell>
                            <Cell><Data ss:Type="String">rgIdentidade</Data></Cell>
                            <Cell><Data ss:Type="String">dataNascimento</Data></Cell>
                            <Cell><Data ss:Type="String">dataSolicitacao</Data></Cell>
                        </Row>';

            foreach ($documentos as $doc) {
                // Formata as datas para o padrão brasileiro
                $dNasc = date("d/m/Y", strtotime($doc['dataNascDocumento']));
                $dSolicitacao = date("d/m/Y", strtotime($doc['dataCriacao']));

                $excelContent .= '<Row>
                    <Cell><Data ss:Type="String">'.$doc['idCard'].'</Data></Cell>
                    <Cell><Data ss:Type="String">'.$doc['NomeDocumento'].'</Data></Cell>
                    <Cell><Data ss:Type="String">'.$doc['InsEnsinoDocumento'].'</Data></Cell>
                    <Cell><Data ss:Type="String">'.$doc['serieDocumento'].'</Data></Cell>
                    <Cell><Data ss:Type="String">'.$doc['nCPF'].'</Data></Cell>
                    <Cell><Data ss:Type="String">'.$doc['nRGDocumento'].'</Data></Cell>
                    <Cell><Data ss:Type="String">'.$dNasc.'</Data></Cell>
                    <Cell><Data ss:Type="String">'.$dSolicitacao.'</Data></Cell>
                </Row>';
                
                $caminhoFoto = dirname(__DIR__) . '/../' . $doc['fotoDocumento'];
                if (file_exists($caminhoFoto)) {
                    $zip->addFile($caminhoFoto, "Fotos/" . basename($caminhoFoto));
                }
            }

            $excelContent .= '</Table></Worksheet></Workbook>';
            $zip->addFromString("baseDados.xls", $excelContent);
            $zip->close();

            // Atualiza status para PRODUZIDO
            foreach ($ids as $id) { 
                $this->repositoryDocumento->mudarStatus($id, 7); 
            }

            return json_encode(["erro" => false, "file" => $zipName]);

        } catch (Exception $e) { 
            return json_encode(["erro" => true, "message" => $e->getMessage()]); 
        }
    }
}