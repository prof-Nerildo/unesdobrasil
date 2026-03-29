<?php

class Database {
    private static $pdo = null;

    // Construtor privado para evitar que a classe seja instanciada com "new"
    private function __construct() {}

    public static function getConnection() {
        // Se a conexão ainda não existe, ele cria
        if (self::$pdo === null) {
            try {
                // Caminho absoluto para o appsettings.json (voltando 1 nível a partir da pasta Data)
                $jsonPath = __DIR__ . '/../appsettings.json';
                
                if (!file_exists($jsonPath)) {
                    throw new Exception("Arquivo appsettings.json não encontrado no caminho: " . $jsonPath);
                }

                // Lê e decodifica o JSON
                $configJson = file_get_contents($jsonPath);
                $config = json_decode($configJson, true);

                // Pega o bloco de conexão
                $dbConfig = $config['ConnectionStrings']['DefaultConnection'];

                $host = $dbConfig['Host'];
                $port = $dbConfig['Port'];
                $db   = $dbConfig['Database'];
                $user = $dbConfig['Username'];
                $pass = $dbConfig['Password'];

                // Monta a string de conexão (DSN)
                $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
                
                // Configurações de segurança e padronização do PDO
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lança exceções em erros do SQL
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna dados como array associativo puro
                    PDO::ATTR_EMULATE_PREPARES   => false,                  // Mais segurança contra SQL Injection
                ];

                // Instancia o PDO
                self::$pdo = new PDO($dsn, $user, $pass, $options);

            } catch (PDOException $e) {
                // Como é uma Web API, devolvemos o erro em formato JSON
                http_response_code(500);
                die(json_encode([
                    "status" => "error",
                    "message" => "Falha ao conectar no banco de dados.",
                    "debug" => $e->getMessage() // Em produção, você pode tirar essa linha
                ]));
            } catch (Exception $e) {
                http_response_code(500);
                die(json_encode([
                    "status" => "error",
                    "message" => "Erro interno de configuração.",
                    "debug" => $e->getMessage()
                ]));
            }
        }

        // Retorna a conexão pronta
        return self::$pdo;
    }
}