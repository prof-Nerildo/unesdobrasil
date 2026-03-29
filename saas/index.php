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

// 2. Imports
require_once __DIR__ . '/Data/Database.php';
require_once __DIR__ . '/Data/Models/Usuario.php';
require_once __DIR__ . '/Models/RegisterRequestModelUsuario.php';
require_once __DIR__ . '/Models/LoginRequestModelUsuario.php';
require_once __DIR__ . '/Models/ChangePasswordRequestModelUsuario.php';
require_once __DIR__ . '/Repositories/UsuarioRepository.php';
require_once __DIR__ . '/Controllers/UsuarioController.php';
require_once __DIR__ . '/Dependencies/JwtHandler.php';
require_once __DIR__ . '/Dependencies/EmailHandler.php';

// 3. Captura
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$dadosJson = json_decode(file_get_contents("php://input"), true) ?? [];

// 4. Instâncias
$controller = new \Controllers\UsuarioController();
$jwt = new \Dependencies\JwtHandler(); 

/**
 * Middleware ACL:
 * null = Logado | 1 = Admin Sist | 2 = UNES | 3 = Inst | 4 = Aluno
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
        // PÚBLICAS
        $method === 'POST' && str_contains($uri, '/api/account/register') => $controller->register($dadosJson),
        $method === 'POST' && str_contains($uri, '/api/account/login')    => $controller->login($dadosJson),

        // PROTEGIDAS (Qualquer nível)
        $method === 'GET' && str_contains($uri, '/api/account/me') => (function() use ($validarToken, $controller) {
            $user = $validarToken(); 
            return $controller->getMe($user);
        })(),

        // PROTEGIDAS (Apenas Aluno - Nível 4)
        $method === 'GET' && str_contains($uri, '/api/student/grades') => (function() use ($validarToken) {
            $validarToken(4); 
            return json_encode(["erro" => false, "message" => "Notas do aluno liberadas."]);
        })(),

        // Rota: Solicitar recuperação de senha (POST)
        $method === 'POST' && str_contains($uri, '/api/account/forgot-password') 
        => $controller->forgotPassword($dadosJson),

        $method === 'PUT' && str_contains($uri, '/api/account/change-password') => (function() use ($validarToken, $controller, $dadosJson) {
            // Aqui o validarToken vai ler o "debug_token" que o usuário recebeu
            $usuario = $validarToken(); 
            return $controller->changePassword($dadosJson, $usuario);
        })(),

        // Rota para validar se o token do e-mail ainda presta (GET)
        $method === 'GET' && str_contains($uri, '/api/account/validate-reset-token') 
        => (function() use ($validarToken) {
            $usuario = $validarToken(); // O porteiro checa se o token expirou ou é falso
            return json_encode([
                "erro" => false, 
                "message" => "Token válido!", 
                "usuario" => $usuario['nome']
            ]);
        })(),


        default => throw new Exception("Endpoint não encontrado.", 404)
    };
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(["erro" => true, "message" => $e->getMessage()]);
}