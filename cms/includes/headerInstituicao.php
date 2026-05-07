<?php
// Não pode ter NADA (nem espaço, nem comentário) antes do DOCTYPE abaixo
?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Institucional - UNES</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="../js/env.php?v=<?php echo time(); ?>"></script>
    <script src="../js/api.js?v=<?php echo time(); ?>"></script>
    <script>
        // Verificação de segurança imediata
        const token = localStorage.getItem('token_unes');
        const userJson = localStorage.getItem('user_unes');
        
        if (!token || !userJson) { 
            window.location.href = '../login.html'; 
        } else {
            const user = JSON.parse(userJson);
            // Se o nível não for 3 (Instituição) e nem 1 (Master), volta pro login
            if (user.id !== 3 && user.id !== 1) {
                window.location.href = '../login.html';
            }
        }
    </script>
</head>
<body>
<div class="wrapper">