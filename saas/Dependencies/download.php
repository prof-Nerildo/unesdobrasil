<?php
// Sanitização: basename() remove qualquer tentativa de Path Traversal (../../etc/passwd)
$file = basename($_GET['file'] ?? '');

// dirname(__DIR__) sobe de 'Dependencies' para a raiz 'saas'
$path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'zip_temp' . DIRECTORY_SEPARATOR . $file;

if (!empty($file) && file_exists($path) && strpos($file, 'Lote_') === 0) {
    
    // Limpa buffers para não corromper o arquivo
    if (ob_get_level()) ob_end_clean();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="'.$file.'"');
    header('Content-Length: ' . filesize($path));
    header('Pragma: no-cache');
    
    readfile($path);
    
    unlink($path); // Deleta após o download
    exit;
} else {
    http_response_code(404);
    die("Erro: Arquivo não encontrado.");
}