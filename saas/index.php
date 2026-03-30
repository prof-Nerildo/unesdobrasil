<?php
// 1. Cabeçalhos (CORS e JSON)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. Imports (A ordem aqui é vital)
require_once __DIR__ . '/Data/Database.php';
require_once __DIR__ . '/Dependencies/JwtHandler.php';
require_once __DIR__ . '/Dependencies/EmailHandler.php';

// Models e Repositories de Usuário
require_once __DIR__ . '/Data/Models/Usuario.php';
require_once __DIR__ . '/Models/RegisterRequestModelUsuario.php';
require_once __DIR__ . '/Models/LoginRequestModelUsuario.php';
require_once __DIR__ . '/Models/ChangePasswordRequestModelUsuario.php';
require_once __DIR__ . '/Repositories/UsuarioRepository.php';
require_once __DIR__ . '/Controllers/UsuarioController.php';

// Models e Repositories de Instituição
require_once __DIR__ . '/Data/Models/InstituicaoEnsino.php';
require_once __DIR__ . '/Models/RegisterRequestModelInstituicaoEnsino.php';
require_once __DIR__ . '/Repositories/InstituicaoEnsinoRepository.php';
require_once __DIR__ . '/Controllers/InstituicaoEnsinoController.php';

// 3. Captura
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$dadosJson = json_decode(file_get_contents("php://input"), true) ?? [];

// 4. Instâncias
$jwt = new \Dependencies\JwtHandler(); 
$userController = new \Controllers\UsuarioController();
$instController = new \Controllers\InstituicaoEnsinoController();

/**
 * Middleware ACL
 */
$validarToken = function($nivelRequerido = null) use ($jwt) {
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    $token = str_replace('Bearer ', '', $auth);
    $dados = $jwt->decode($token);

    if (!$dados) {
        http_response_code(401);
        echo json_encode(["erro" => true, "message" => "Token inválido ou ausente."]);
        exit;
    }

    if ($nivelRequerido !== null && $dados['idAcl'] != $nivelRequerido) {
        http_response_code(403);
        echo json_encode(["erro" => true, "message" => "Acesso negado para o seu perfil."]);
        exit;
    }
    return $dados; 
};

// 5. Roteamento
try {
    echo match (true) {
        // --- CONTA / USUÁRIO (Públicas) ---
        $method === 'POST' && str_contains($uri, '/api/account/register') => $userController->register($dadosJson),
        $method === 'POST' && str_contains($uri, '/api/account/login')    => $userController->login($dadosJson),
        $method === 'POST' && str_contains($uri, '/api/account/forgot-password') => $userController->forgotPassword($dadosJson),

        // --- CONTA / USUÁRIO (Protegidas) ---
        $method === 'GET' && str_contains($uri, '/api/account/me') => (function() use ($validarToken, $userController) {
            $user = $validarToken(); 
            return $userController->getMe($user);
        })(),

        $method === 'PUT' && str_contains($uri, '/api/account/change-password') => (function() use ($validarToken, $userController, $dadosJson) {
            $usuario = $validarToken(); 
            return $userController->changePassword($dadosJson, $usuario);
        })(),

        $method === 'GET' && str_contains($uri, '/api/account/validate-reset-token') => (function() use ($validarToken) {
            $usuario = $validarToken();
            return json_encode(["erro" => false, "message" => "Token válido!", "usuario" => $usuario['nome']]);
        })(),

        // --- INSTITUIÇÃO DE ENSINO ---
        
        // Registrar Instituição (Pública para o fluxo de cadastro inicial do CMS)
        // O vínculo é feito pelo 'idUsuarioDono' que enviamos no JSON
        $method === 'POST' && str_contains($uri, '/api/instituicao/registrar') => 
            $instController->registrar($dadosJson),

        // Validar Instituição (Apenas Admin UNES - idAcl 2)
        $method === 'PUT' && str_contains($uri, '/api/instituicao/validar') => (function() use ($validarToken, $instController, $dadosJson) {
            $user = $validarToken(2); 
            return $instController->validar($dadosJson, $user);
        })(),

        // Listar Pendentes (Apenas Admin UNES - idAcl 2)
        $method === 'GET' && str_contains($uri, '/api/instituicao/pendentes') => (function() use ($validarToken, $instController) {
            $user = $validarToken(2);
            return $instController->listarPendentes($user);
        })(),

        // --- ALUNO (Nível 4) ---
        $method === 'GET' && str_contains($uri, '/api/student/grades') => (function() use ($validarToken) {
            $validarToken(4); 
            return json_encode(["erro" => false, "message" => "Notas do aluno liberadas."]);
        })(),

        default => throw new Exception("Endpoint não encontrado.", 404)
    };
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(["erro" => true, "message" => $e->getMessage()]);
}