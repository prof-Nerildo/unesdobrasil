<?php
namespace Data;

use PDO;
use PDOException;
use Exception;

class Database {
    private static $pdo = null;
    private static $config = null;

    private function __construct() {}

    /**
     * Carrega e cacheia o appsettings.json
     */
    public static function getConfig() {
        if (self::$config === null) {
            $jsonPath = __DIR__ . '/../appsettings.json';
            if (!file_exists($jsonPath)) {
                throw new Exception("Arquivo appsettings.json não encontrado.");
            }
            self::$config = json_decode(file_get_contents($jsonPath), true);
        }
        return self::$config;
    }

    /**
     * Retorna o ambiente ativo: "dev", "test" ou "producao"
     */
    public static function getAmbiente() {
        $config = self::getConfig();
        return $config['Ambiente'] ?? 'dev';
    }

    /**
     * Retorna as configurações do ambiente ativo
     */
    public static function getAmbienteConfig() {
        $config = self::getConfig();
        $ambiente = self::getAmbiente();
        
        if (!isset($config['Ambientes'][$ambiente])) {
            throw new Exception("Ambiente '{$ambiente}' não encontrado no appsettings.json.");
        }
        
        return $config['Ambientes'][$ambiente];
    }

    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                $ambienteConfig = self::getAmbienteConfig();
                $dbConfig = $ambienteConfig['Conexao'];

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