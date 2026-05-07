<?php
/**
 * config.php — Configurações do CMS baseadas no ambiente ativo.
 * Lê o appsettings.json e define as constantes BASE_URL e BASE_PATH.
 */

// Carrega as configurações do ambiente
$jsonPath = __DIR__ . '/../../saas/appsettings.json';

if (file_exists($jsonPath)) {
    $config = json_decode(file_get_contents($jsonPath), true);
    $ambiente = $config['Ambiente'] ?? 'dev';
    $siteUrl = $config['Ambientes'][$ambiente]['SiteUrl'] ?? '';
    
    if (!empty($siteUrl)) {
        define('BASE_URL', $siteUrl);
    }
}

// Fallback: se não conseguiu definir pelo JSON, usa detecção automática
if (!defined('BASE_URL')) {
    $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $server_name = $_SERVER['HTTP_HOST'];
    
    if ($server_name == 'localhost') {
        define('BASE_URL', $protocolo . "://" . $server_name . "/unesdobrasil/cms/");
    } else {
        define('BASE_URL', $protocolo . "://www.unesdobrasil.com.br/cms/");
    }
}

// Caminho absoluto para o sistema de arquivos (útil para Includes e Uploads)
define('BASE_PATH', dirname(__DIR__) . '/');

// Ambiente ativo (para uso em PHP)
define('AMBIENTE', $ambiente ?? 'dev');
?>