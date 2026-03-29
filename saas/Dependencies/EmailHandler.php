<?php

namespace Dependencies;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class EmailHandler {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        // 1. Configurações de Debug (Ativado para vermos o erro se falhar)
        $this->mail->SMTPDebug = 0; // Mude para 2 se quiser ver o log técnico completo

        // 2. Configurações do Servidor
        $this->mail->isSMTP();
        $this->mail->Host       = 'mail.webdna.com.br'; 
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = 'send@webdna.com.br'; 
        $this->mail->Password   = 'Aero@418731';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
        $this->mail->Port       = 465;

        // --- A VACINA PARA LOCALHOST (Onde o erro (0) costuma morrer) ---
        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        // ---------------------------------------------------------------

        $this->mail->setFrom('atendimento@webdna.com.br', 'UNES Brasil');
        $this->mail->CharSet = 'UTF-8';
    }

    public function enviarRecuperacao($emailDestino, $nomeUsuario, $token) {
        try {
            // O link agora aponta para o seu arquivo HTML na raiz do projeto
            $link = "http://localhost/unesdobrasil/saas/reset-password.html?token=" . $token;
            
            $this->mail->addAddress($emailDestino, $nomeUsuario);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Recuperação de Senha - UNES Brasil';
            
            $this->mail->Body = "
                <div style='font-family: Arial, sans-serif;'>
                    <h2>Olá, {$nomeUsuario}!</h2>
                    <p>Recebemos uma solicitação para redefinir sua senha no sistema UNES.</p>
                    <p>Clique no botão abaixo para prosseguir:</p>
                    <div style='margin: 30px 0;'>
                        <a href='{$link}' style='background:#007bff; color:white; padding:12px 25px; text-decoration:none; border-radius:5px; font-weight:bold;'>REDEFINIR MINHA SENHA</a>
                    </div>
                    <p style='font-size: 12px; color: #777;'>Este link é válido por 1 hora.</p>
                </div>
            ";

            return $this->mail->send();
        } catch (Exception $e) {
            return false;
        }
    }
}