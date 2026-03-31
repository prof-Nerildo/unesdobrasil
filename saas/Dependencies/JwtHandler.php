<?php

namespace Dependencies;

use Exception;

class JwtHandler {
    private $secret = "Leite_Com_MangaRosa_Mata_todos_kkk@2026";

    public function __construct() {
        $jsonPath = __DIR__ . '/../appsettings.json';
        if (file_exists($jsonPath)) {
            $config = json_decode(file_get_contents($jsonPath), true);
            if (isset($config['AppSettings']['JwtSecret'])) {
                $this->secret = $config['AppSettings']['JwtSecret'];
            }
        }
    }

    public function generate($data) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(array_merge($data, [
            'iat' => time(),
            'exp' => time() + (3600 * 8) 
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

    public function decode($token) {
        if (empty($token)) return false;
        $partes = explode('.', $token);
        if (count($partes) !== 3) return false;

        [$header, $payload, $signature] = $partes;

        $validSignature = hash_hmac('sha256', "$header.$payload", $this->secret, true);
        $validSignature = $this->base64UrlEncode($validSignature);

        if ($signature !== $validSignature) return false;

        $dados = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);
        if (isset($dados['exp']) && $dados['exp'] < time()) return false;

        return $dados;
    }
}