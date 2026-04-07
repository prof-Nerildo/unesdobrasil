<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0); 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }

// --- INCLUDES EXISTENTES ---
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

// --- NOVOS INCLUDES (DOCUMENTOS ESTUDANTIS) ---
require_once __DIR__ . '/Models/RegisterRequestModelDocumento.php';
require_once __DIR__ . '/Repositories/DocumentoRepository.php';
require_once __DIR__ . '/Controllers/DocumentoController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$dadosJson = json_decode(file_get_contents("php://input"), true) ?? [];

$jwt = new \Dependencies\JwtHandler(); 
$userController = new \Controllers\UsuarioController();
$instController = new \Controllers\InstituicaoEnsinoController();

// --- NOVO CONTROLLER ---
$docController = new \Controllers\DocumentoController();

$validarToken = function($nivelRequerido = null) use ($jwt) {
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    $token = str_replace('Bearer ', '', $auth);
    $dados = $jwt->decode($token);
    
    if (!$dados) { 
        http_response_code(401); 
        echo json_encode(["erro" => true, "message" => "Sessão expirada."]); 
        exit; 
    }
    
    if ($nivelRequerido !== null && $dados['idAcl'] != $nivelRequerido) { 
        http_response_code(403); 
        echo json_encode(["erro" => true, "message" => "Acesso negado."]);
        exit; 
    }
    return $dados; 
};

try {
    echo match (true) {
        // --- LOGIN E CONTA ---
        $method === 'POST' && str_contains($uri, '/api/account/login') => 
            $userController->login($dadosJson),

        $method === 'POST' && str_contains($uri, '/api/account/register') => 
            $userController->register($dadosJson),

        $method === 'POST' && str_contains($uri, '/api/account/forgot-password') => 
            $userController->forgotPassword($dadosJson),

        $method === 'PUT' && str_contains($uri, '/api/account/change-password') => 
            $userController->changePassword($dadosJson, $validarToken()),

        $method === 'GET' && str_contains($uri, '/api/account/me') => 
            $userController->getMe($validarToken()),

        // --- INSTITUIÇÃO ---
        $method === 'POST' && str_contains($uri, '/api/instituicao/registrar') => 
            $instController->registrar($dadosJson),

        $method === 'GET' && str_contains($uri, '/api/instituicao/todas') => 
            $instController->listarTodas($validarToken()),

        $method === 'PUT' && str_contains($uri, '/api/instituicao/status/') => 
            $instController->atualizarStatus(basename($uri), $dadosJson),

        $method === 'GET' && str_contains($uri, '/api/instituicao/buscar/') => 
            $instController->buscarPorId(basename($uri)),
        
        $method === 'GET' && str_contains($uri, '/api/instituicao/detalhes/') => 
            $instController->buscarPorId(basename($uri)),

        $method === 'PUT' && str_contains($uri, '/api/instituicao/atualizar/') => 
            $instController->atualizarCompleto(basename($uri), $dadosJson),

        $method === 'PUT' && str_contains($uri, '/api/instituicao/alterar/') => 
            $instController->atualizarCompleto(basename($uri), $dadosJson),

        // --- NOVO: DOCUMENTO ESTUDANTIL ---
        // Rota que o Adriano vai usar para salvar a nova carteirinha
        $method === 'POST' && str_contains($uri, '/api/documento/registrar') => 
            $docController->register($dadosJson, $validarToken()),
        
        default => throw new Exception("Endpoint não encontrado: " . $uri, 404)
    };
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(["erro" => true, "message" => $e->getMessage()]);
}