<?php
// Detecta se está em localhost ou online
$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$server_name = $_SERVER['HTTP_HOST'];

if ($server_name == 'localhost') {
    // Ajuste aqui se a sua pasta for diferente de /unesdobrasil/cms/
    define('BASE_URL', $protocolo . "://" . $server_name . "/unesdobrasil/cms/");
} else {
    // URL do servidor online
    define('BASE_URL', $protocolo . "://www.unesdobrasil.com.br/cms/");
}

// Caminho absoluto para o sistema de arquivos (útil para Includes e Uploads)
define('BASE_PATH', dirname(__DIR__) . '/');
?>