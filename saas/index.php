<?php
// 1. Configurações de Erro (condicional ao ambiente)
$_configTemp = json_decode(file_get_contents(__DIR__ . '/appsettings.json'), true);
$_ambiente = $_configTemp['Ambiente'] ?? 'producao';
if ($_ambiente === 'dev') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone centralizado (Brasil)
date_default_timezone_set('America/Sao_Paulo');

// 2. Headers de API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }

try {
    // --- INCLUDES ---
    require_once __DIR__ . '/Data/Database.php';
    require_once __DIR__ . '/Dependencies/JwtHandler.php';
    require_once __DIR__ . '/Dependencies/EmailHandler.php';
    require_once __DIR__ . '/Data/Models/Usuario.php';
    require_once __DIR__ . '/Models/RegisterRequestModelUsuario.php';
    require_once __DIR__ . '/Repositories/UsuarioRepository.php';
    require_once __DIR__ . '/Controllers/UsuarioController.php';
    require_once __DIR__ . '/Data/Models/InstituicaoEnsino.php';
    require_once __DIR__ . '/Models/RegisterRequestModelInstituicaoEnsino.php';
    require_once __DIR__ . '/Repositories/InstituicaoEnsinoRepository.php';
    require_once __DIR__ . '/Controllers/InstituicaoEnsinoController.php';
    require_once __DIR__ . '/Models/RegisterRequestModelDocumento.php';
    require_once __DIR__ . '/Repositories/DocumentoRepository.php';
    require_once __DIR__ . '/Controllers/DocumentoController.php';

    // --- SETUP ---
    $uri = isset($_GET['url']) ? $_GET['url'] : parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];
    $dadosJson = json_decode(file_get_contents("php://input"), true) ?? [];

    $jwt = new \Dependencies\JwtHandler(); 
    $userController = new \Controllers\UsuarioController();
    $instController = new \Controllers\InstituicaoEnsinoController();
    $docController = new \Controllers\DocumentoController();

    // --- HELPER DE TOKEN ---
    $validarToken = function($nivelRequerido = null) use ($jwt) {
        $auth = '';
        
        // 1. Tenta getallheaders() (funciona na maioria dos cenários)
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }
        
        // 2. Fallback: Apache com mod_rewrite repassa via $_SERVER
        if (empty($auth) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'];
        }
        
        // 3. Fallback: Apache com RewriteRule repassa via REDIRECT_
        if (empty($auth) && !empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        $token = str_replace('Bearer ', '', $auth);
        $dados = $jwt->decode($token);
        if (!$dados) { throw new Exception("Sessão expirada.", 401); }
        if ($nivelRequerido !== null && $dados['idAcl'] != $nivelRequerido) { 
            throw new Exception("Acesso negado.", 403);
        }
        return $dados; 
    };

    // --- ROTEAMENTO (MATCH) ---
    $response = match (true) {
        // LOGIN E CONTA
        $method === 'POST' && str_contains($uri, 'api/account/login') => 
            $userController->login($dadosJson),

        $method === 'POST' && str_contains($uri, 'api/account/register') => 
            $userController->register($dadosJson),

        $method === 'POST' && str_contains($uri, 'api/account/forgot-password') => 
            $userController->forgotPassword($dadosJson),

        $method === 'PUT' && str_contains($uri, 'api/account/change-password') => 
            $userController->changePassword($dadosJson, $validarToken()),

        $method === 'GET' && str_contains($uri, 'api/account/me') => 
            $userController->getMe($validarToken()),

        // CADASTRO COMPLETO ATÔMICO (Instituição + Usuário numa só transação)
        $method === 'POST' && str_contains($uri, 'api/cadastro/completo') => 
            $instController->cadastrarCompleto($dadosJson, $userController),

        // INSTITUIÇÃO
        $method === 'POST' && str_contains($uri, 'api/instituicao/registrar') => 
            $instController->registrar($dadosJson),

        $method === 'GET' && str_contains($uri, 'api/instituicao/todas') => 
            $instController->listarTodas($validarToken()),

        $method === 'PUT' && str_contains($uri, 'api/instituicao/status/') => 
            $instController->atualizarStatus(basename($uri), $dadosJson, $validarToken()),

        $method === 'GET' && (str_contains($uri, 'api/instituicao/buscar/') || str_contains($uri, 'api/instituicao/detalhes/')) => 
            $instController->buscarPorId(basename($uri)),

        $method === 'PUT' && (str_contains($uri, 'api/instituicao/atualizar/') || str_contains($uri, 'api/instituicao/alterar/')) => 
            $instController->atualizarCompleto(basename($uri), $dadosJson, $validarToken()),

        $method === 'PUT' && str_contains($uri, 'api/instituicao/perfil-atualizar/') => 
            $instController->atualizarPerfilInstituicao(basename($uri), $dadosJson, $validarToken()),

        $method === 'GET' && str_contains($uri, 'api/instituicao/resumo-unes') => 
            $instController->resumoDashboardUnes($validarToken()),

        // DOCUMENTO ESTUDANTIL
        $method === 'POST' && str_contains($uri, 'api/documento/registrar') => 
            $docController->register($dadosJson, $validarToken()),
        
        $method === 'GET' && str_contains($uri, 'api/documento/listar-criados/') => 
            $docController->listarPorStatus(basename($uri), 9),

        $method === 'POST' && str_contains($uri, 'api/documento/suspender/') => 
            $docController->suspenderDocumento(basename($uri), $validarToken()),

        $method === 'GET' && str_contains($uri, 'api/documento/detalhes/') => 
            $docController->buscarDetalhes(basename($uri), $validarToken()),
        
        $method === 'POST' && str_contains($uri, 'api/documento/atualizar/') => 
            $docController->atualizarDocumento(basename($uri), $dadosJson, $validarToken()),

        $method === 'GET' && str_contains($uri, 'api/documento/resumo-dashboard/') => 
            $docController->resumoDashboard(basename($uri)),

        $method === 'GET' && str_contains($uri, 'api/documento/listar-por-status/') => (function() use ($uri, $docController) {
            $partes = explode('/', rtrim($uri, '/'));
            $status = end($partes); 
            $idInst = prev($partes); 
            return $docController->listarPorStatusGenerico($idInst, $status);
        })(),

        $method === 'PUT' && str_contains($uri, 'api/documento/status/') => (function() use ($uri, $dadosJson, $docController, $validarToken) {
            $validarToken();
            $idCard = basename(rtrim($uri, '/'));
            return $docController->atualizarStatusDoc($idCard, $dadosJson);
        })(),

        $method === 'GET' && str_contains($uri, 'api/documento/resumo-global') => 
            $docController->resumoGlobal($validarToken()),

        $method === 'GET' && str_contains($uri, 'api/documento/producao-global/') => 
            $docController->listarProducaoGlobal(basename(rtrim($uri, '/')), $validarToken()),

        // ROTA PARA GERAR LOTE (POST)
        $method === 'POST' && str_contains($uri, 'api/documento/gerar-lote') => 
            $docController->gerarLoteZip($dadosJson, $validarToken()),

        $method === 'GET' && str_contains($uri, 'api/usuarios/todos') => 
            $userController->listarTodos($validarToken()),

        $method === 'GET' && str_contains($uri, 'api/usuarios/buscar/') => 
            $userController->buscarUsuario(basename($uri), $validarToken()),

        $method === 'POST' && str_contains($uri, 'api/usuarios/criar') => 
            $userController->criarUsuarioUnes($dadosJson, $validarToken()),

        $method === 'PUT' && str_contains($uri, 'api/usuarios/atualizar/') => 
            $userController->atualizarUsuario(basename($uri), $dadosJson, $validarToken()),

        $method === 'PUT' && str_contains($uri, 'api/usuarios/suspender/') => 
            $userController->suspenderUsuario(basename($uri), $dadosJson, $validarToken()),

        // NOVA ROTA PÚBLICA PARA VALIDAÇÃO (QR CODE / FISCAL)
        $method === 'GET' && str_contains($uri, 'api/documento/validar-publico/') => 
            $docController->buscarDetalhesPublicos(basename($uri)),
        
        default => throw new Exception("Endpoint não encontrado: " . $uri, 404)
    };

    echo $response;

} catch (Exception $e) {
    http_response_code($e->getCode() < 100 || $e->getCode() > 599 ? 500 : $e->getCode());
    echo json_encode([
        "erro" => true, 
        "message" => $e->getMessage()
    ]);
}