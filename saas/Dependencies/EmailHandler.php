<?php

namespace Dependencies;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verifica se o autoload existe antes de carregar
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

class EmailHandler {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->mail->SMTPDebug = 0;
        
        // 1. Configurações do Servidor
        $this->mail->isSMTP();
        $this->mail->Host       = 'mail.webdna.com.br'; 
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = 'send@webdna.com.br'; 
        $this->mail->Password   = 'Aero@418731';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL (Porta 465)
        $this->mail->Port       = 465;
        $this->mail->CharSet    = 'UTF-8';

        // 2. A "Vacina" para Localhost (ignora erro de certificado SSL no Laragon/XAMPP)
        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $this->mail->setFrom('send@webdna.com.br', 'UNES Brasil');
    }

    public function enviarRecuperacao($emailDestino, $nomeUsuario, $token) {
        try {
            // Limpa destinatários anteriores para não enviar em duplicidade

            if (empty($emailDestino)) {
                return false; // Evita o erro "Invalid address"
            }


            $this->mail->clearAddresses();
            
            // O link aponta para o arquivo que criamos no passo anterior
            $link = "http://localhost/unesdobrasil/cms/redefinir-senha.html?token=" . $token;
            
            $this->mail->addAddress($emailDestino, $nomeUsuario);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Recuperação de Senha - UNES Brasil';
            
            // Corpo HTML
            $this->mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h2 style='color: #007bff;'>Olá, {$nomeUsuario}!</h2>
                    <p>Recebemos uma solicitação para redefinir sua senha no sistema UNES.</p>
                    <p>Clique no botão abaixo para prosseguir com a criação da nova senha:</p>
                    <div style='margin: 30px 0;'>
                        <a href='{$link}' style='background:#28a745; color:white; padding:15px 25px; text-decoration:none; border-radius:5px; font-weight:bold; display: inline-block;'>REDEFINIR MINHA SENHA</a>
                    </div>
                    <p style='font-size: 12px; color: #777;'>Se você não solicitou esta alteração, ignore este e-mail.</p>
                    <p style='font-size: 12px; color: #777;'>O link é válido por tempo limitado.</p>
                    <hr style='border: 0; border-top: 1px solid #eee;'>
                    <p style='font-size: 10px; color: #aaa; text-align: center;'>UNES SaaS - Gestão Educacional</p>
                </div>
            ";

            // Texto Puro (Para leitores de e-mail que não aceitam HTML)
            $this->mail->AltBody = "Olá {$nomeUsuario}, para redefinir sua senha acesse este link: {$link}";

            return $this->mail->send();
        } catch (Exception $e) {
            // Em caso de erro, você pode conferir o log com $this->mail->ErrorInfo se necessário
            throw new \Exception("Erro PHPMailer: " . $this->mail->ErrorInfo);
            //return false;
        }
    }
}