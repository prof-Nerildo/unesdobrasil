<?php
namespace Data;

use PDO;
use PDOException;
use Exception;

class Database {
    private static $pdo = null;

    private function __construct() {}

    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                $jsonPath = __DIR__ . '/../appsettings.json';
                
                if (!file_exists($jsonPath)) {
                    throw new Exception("Arquivo appsettings.json não encontrado.");
                }

                $config = json_decode(file_get_contents($jsonPath), true);
                $dbConfig = $config['ConnectionStrings']['DefaultConnection'];

                $dsn = "mysql:host={$dbConfig['Host']};port={$dbConfig['Port']};dbname={$dbConfig['Database']};charset=utf8mb4";
                
                self::$pdo = new PDO($dsn, $dbConfig['Username'], $dbConfig['Password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);

            } catch (Exception $e) {
                http_response_code(500);
                die(json_encode(["erro" => true, "message" => "Erro de Banco: " . $e->getMessage()]));
            }
        }
        return self::$pdo;
    }
}