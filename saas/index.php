<?php
// 1. Configurações de Erro (Em produção, display_errors deve ser 0)
error_reporting(E_ALL); 
ini_set('display_errors', 1); // Deixe 1 para debugar, depois mude para 0

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
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];
    $dadosJson = json_decode(file_get_contents("php://input"), true) ?? [];

    $jwt = new \Dependencies\JwtHandler(); 
    $userController = new \Controllers\UsuarioController();
    $instController = new \Controllers\InstituicaoEnsinoController();
    $docController = new \Controllers\DocumentoController();

    // --- HELPER DE TOKEN ---
    $validarToken = function($nivelRequerido = null) use ($jwt) {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        $token = str_replace('Bearer ', '', $auth);
        $dados = $jwt->decode($token);
        
        if (!$dados) { 
            throw new Exception("Sessão expirada.", 401); 
        }
        
        if ($nivelRequerido !== null && $dados['idAcl'] != $nivelRequerido) { 
            throw new Exception("Acesso negado.", 403);
        }
        return $dados; 
    };

    // --- ROTEAMENTO (MATCH) ---
    $response = match (true) {
        // LOGIN E CONTA
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

        // INSTITUIÇÃO
        $method === 'POST' && str_contains($uri, '/api/instituicao/registrar') => 
            $instController->registrar($dadosJson),

        $method === 'GET' && str_contains($uri, '/api/instituicao/todas') => 
            $instController->listarTodas($validarToken()),

        $method === 'PUT' && str_contains($uri, '/api/instituicao/status/') => 
            $instController->atualizarStatus(basename($uri), $dadosJson),

        $method === 'GET' && str_contains($uri, '/api/instituicao/buscar/') || str_contains($uri, '/api/instituicao/detalhes/') => 
            $instController->buscarPorId(basename($uri)),

        $method === 'PUT' && str_contains($uri, '/api/instituicao/atualizar/') || str_contains($uri, '/api/instituicao/alterar/') => 
            $instController->atualizarCompleto(basename($uri), $dadosJson),

        // DOCUMENTO ESTUDANTIL
        $method === 'POST' && str_contains($uri, '/api/documento/registrar') => 
            $docController->register($dadosJson, $validarToken()),
        
        $method === 'GET' && str_contains($uri, '/api/documento/listar-criados/') => 
            $docController->listarPorStatus(basename($uri), 9),

        // ROTA DE SUSPENSÃO (EXCLUSÃO)
        $method === 'POST' && str_contains($uri, '/api/documento/suspender/') => 
            $docController->suspenderDocumento(basename($uri)),

        // Adicione esta rota GET para carregar os dados
        $method === 'GET' && str_contains($uri, '/api/documento/detalhes/') => 
            $docController->buscarDetalhes(basename($uri)),
        
        // ROTA PARA ATUALIZAR O DOCUMENTO
        $method === 'POST' && str_contains($uri, '/api/documento/atualizar/') => 
            $docController->atualizarDocumento(basename($uri), $dadosJson),

        $method === 'GET' && str_contains($uri, '/api/documento/resumo-dashboard/') => 
            $docController->resumoDashboard(basename($uri)),

        // Listar documentos por qualquer status (DINÂMICO E SEGURO)
        $method === 'GET' && str_contains($uri, '/api/documento/listar-por-status/') => (function() use ($uri, $docController) {
            // Remove barras vazias e divide a URL
            $partes = explode('/', rtrim($uri, '/'));
            
            // Pegamos os dois últimos valores da URL independente de quantas pastas existam antes
            $status = end($partes); 
            $idInst = prev($partes); 
            
            return $docController->listarPorStatusGenerico($idInst, $status);
        })(),
        // ROTA PARA ALTERAR STATUS DO DOCUMENTO (AVANÇAR)
        $method === 'PUT' && str_contains($uri, '/api/documento/status/') => (function() use ($uri, $dadosJson, $docController) {
            $partes = explode('/', rtrim($uri, '/'));
            $idCard = end($partes);
            return $docController->atualizarStatusDoc($idCard, $dadosJson);
        })(),

        // NOVA ROTA: Atualiza apenas dados permitidos para a própria escola
        $method === 'PUT' && str_contains($uri, '/api/instituicao/perfil-atualizar/') => 
            $instController->atualizarPerfilInstituicao(basename($uri), $dadosJson),
        
        default => throw new Exception("Endpoint não encontrado: " . $uri, 404)
    };

    echo $response;

} catch (Exception $e) {
    // Garante que o erro sempre seja um JSON válido
    http_response_code($e->getCode() < 100 || $e->getCode() > 599 ? 500 : $e->getCode());
    echo json_encode([
        "erro" => true, 
        "message" => $e->getMessage()
    ]);
}