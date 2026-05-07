<?php
// Proteção simples de carregamento lateral (opcional)
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo - UNES</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="../js/env.php?v=<?php echo time(); ?>"></script>
    <script src="../js/api.js?v=<?php echo time(); ?>"></script>
    <script>
        // Verificação de segurança imediata no Front-end
        const token = localStorage.getItem('token_unes');
        if (!token) { window.location.href = '../login.html'; }
    </script>
</head>
<body>
<div class="wrapper">
    