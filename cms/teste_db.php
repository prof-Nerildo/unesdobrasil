<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=webd_unesBD", "webd_userUnes", "WCRwebdn@2020Unes");
    echo "Conexão com o Banco OK!";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}