<?php
/**
 * env.php — Gera a variável API_URL dinamicamente baseada no ambiente ativo.
 * Incluído nos headers ANTES do api.js.
 * 
 * Uso: <script src="../js/env.php"></script>
 */
header('Content-Type: application/javascript; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Caminho para o appsettings.json (de cms/js/ até saas/)
$jsonPath = __DIR__ . '/../../saas/appsettings.json';

if (file_exists($jsonPath)) {
    $config = json_decode(file_get_contents($jsonPath), true);
    $ambiente = $config['Ambiente'] ?? 'dev';
    
    if (isset($config['Ambientes'][$ambiente]['ApiUrl'])) {
        $apiUrl = $config['Ambientes'][$ambiente]['ApiUrl'];
        $siteUrl = $config['Ambientes'][$ambiente]['SiteUrl'] ?? '';
    } else {
        // Fallback para dev
        $apiUrl = 'http://localhost/unesdobrasil/saas/index.php/api';
        $siteUrl = 'http://localhost/unesdobrasil/cms/';
    }
} else {
    $apiUrl = 'http://localhost/unesdobrasil/saas/index.php/api';
    $siteUrl = 'http://localhost/unesdobrasil/cms/';
    $ambiente = 'dev';
}

echo "// Ambiente ativo: {$ambiente}\n";
echo "var API_URL = \"{$apiUrl}\";\n";
echo "var SITE_URL = \"{$siteUrl}\";\n";
echo "var AMBIENTE = \"{$ambiente}\";\n";
?>
