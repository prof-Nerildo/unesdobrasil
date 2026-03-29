<?php

namespace Dependencies;

use Exception;

class JwtHandler {
    private $secret;

    public function __construct() {
        // Busca a chave mestra no appsettings.json
        $jsonPath = __DIR__ . '/../appsettings.json';
        if (file_exists($jsonPath)) {
            $config = json_decode(file_get_contents($jsonPath), true);
            $this->secret = $config['AppSettings']['JwtSecret'] ?? "Leite_Com_MangaRosa_Mata_todos_kkk@2026";
        } else {
            $this->secret = "Leite_Com_MangaRosa_Mata_todos_kkk@2026";
        }
    }

    /**
     * Gera um Token JWT
     */
    public function generate($data) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(array_merge($data, [
            'iat' => time(),          // Data de criação
            'exp' => time() + (3600 * 8) // Expira em 8 horas
        ]));

        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    private function base64UrlEncode($text) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }

    /**
     * Valida um Token JWT e retorna os dados (payload)
     */
    public function decode($token) {
        $partes = explode('.', $token);
        if (count($partes) !== 3) return false;

        [$header, $payload, $signature] = $partes;

        // Refaz a assinatura para comparar
        $validSignature = hash_hmac('sha256', "$header.$payload", $this->secret, true);
        $validSignature = $this->base64UrlEncode($validSignature);

        if ($signature !== $validSignature) return false;

        $dados = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);

        // Verifica se expirou
        if (isset($dados['exp']) && $dados['exp'] < time()) return false;

        return $dados;
    }
}