<?php
/**
 * Classe para gerenciar envio de emails
 * Utiliza PHPMailer ou função mail() nativa
 */

class EmailManager {
    private $config;
    private $use_phpmailer;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../config/email.php';

        if (!class_exists('PHPMailer\PHPMailer\PHPMailer') && file_exists(__DIR__ . '/../vendor/autoload.php')) {
            require_once __DIR__ . '/../vendor/autoload.php';
        }

        $this->use_phpmailer = class_exists('PHPMailer\PHPMailer\PHPMailer');
    }
    
    /**
     * Envia email de verificação
     */
    public function sendVerificationEmail($to_email, $first_name, $verification_token) {
        $verification_url = $this->config['BASE_URL'] . 
                          $this->config['EMAIL_VERIFICATION_URL'] . 
                          '?token=' . $verification_token;
        
        $subject = $this->config['EMAIL_TEMPLATES']['verification']['subject'];
        
        $html_body = $this->getVerificationEmailTemplate($first_name, $verification_url);
        
        return $this->send($to_email, $subject, $html_body);
    }
    
    /**
     * Template HTML para email de verificação
     */
    private function getVerificationEmailTemplate($first_name, $verification_url) {
        return "
        <!DOCTYPE html>
        <html lang=\"pt-br\">
        <head>
            <meta charset=\"UTF-8\">
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; }
                .header { text-align: center; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 20px; }
                .content { color: #666; line-height: 1.6; }
                .button { display: inline-block; background-color: #007bff; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
                .footer { text-align: center; color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px; }
            </style>
        </head>
        <body>
            <div class=\"container\">
                <div class=\"header\">
                    <h1>Portal MCM</h1>
                </div>
                
                <div class=\"content\">
                    <h2>Bem-vindo, {$first_name}!</h2>
                    
                    <p>Obrigado por se registrar no Portal MCM. Para ativar sua conta e acessar o portal, 
                    clique no botão abaixo:</p>
                    
                    <center>
                        <a href=\"{$verification_url}\" class=\"button\">Verificar Email</a>
                    </center>
                    
                    <p>Ou copie e cole este link no seu navegador:</p>
                    <p style=\"word-break: break-all;\"><code>{$verification_url}</code></p>
                    
                    <p><strong>Este link expira em 24 horas.</strong></p>
                    
                    <p>Se você não criou esta conta, ignore este email.</p>
                </div>
                
                <div class=\"footer\">
                    <p>&copy; Portal MCM - Todos os direitos reservados</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Envia email de recuperação de senha
     */
    public function sendPasswordResetEmail($to_email, $first_name, $reset_token) {
        $reset_url = $this->config['BASE_URL'] .
                     '/reset_password.php?token=' . $reset_token;

        $subject = $this->config['EMAIL_TEMPLATES']['password_reset']['subject'];

        $html_body = $this->getPasswordResetEmailTemplate($first_name, $reset_url);

        return $this->send($to_email, $subject, $html_body);
    }

    /**
     * Template HTML para email de recuperação de senha
     */
    private function getPasswordResetEmailTemplate($first_name, $reset_url) {
        return "
        <!DOCTYPE html>
        <html lang=\"pt-br\">
        <head>
            <meta charset=\"UTF-8\">
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; }
                .header { text-align: center; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 20px; }
                .content { color: #666; line-height: 1.6; }
                .button { display: inline-block; background-color: #007bff; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
                .footer { text-align: center; color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px; }
            </style>
        </head>
        <body>
            <div class=\"container\">
                <div class=\"header\">
                    <h1>Portal MCM</h1>
                </div>

                <div class=\"content\">
                    <h2>Olá, {$first_name}!</h2>

                    <p>Recebemos uma solicitação para redefinir a senha da sua conta no Portal MCM.
                    Para criar uma nova senha, clique no botão abaixo:</p>

                    <center>
                        <a href=\"{$reset_url}\" class=\"button\">Redefinir Senha</a>
                    </center>

                    <p>Ou copie e cole este link no seu navegador:</p>
                    <p style=\"word-break: break-all;\"><code>{$reset_url}</code></p>

                    <p><strong>Este link expira em 1 hora.</strong></p>

                    <p>Se você não solicitou a redefinição de senha, ignore este email.</p>
                </div>

                <div class=\"footer\">
                    <p>&copy; Portal MCM - Todos os direitos reservados</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Envia email genérico
     */
    public function send($to_email, $subject, $html_body, $plain_text_body = null) {
        if ($this->use_phpmailer) {
            return $this->sendWithPHPMailer($to_email, $subject, $html_body, $plain_text_body);
        } else {
            return $this->sendWithMailFunction($to_email, $subject, $html_body);
        }
    }
    
    /**
     * Envia usando PHPMailer (se instalado)
     */
    private function sendWithPHPMailer($to_email, $subject, $html_body, $plain_text_body = null) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configurações SMTP
            $mail->isSMTP();
            $mail->Host = $this->config['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['SMTP_USERNAME'];
            $mail->Password = $this->config['SMTP_PASSWORD'];
            $mail->SMTPSecure = strtoupper($this->config['SMTP_ENCRYPTION']);
            $mail->Port = $this->config['SMTP_PORT'];
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => $this->config['VERIFY_SSL'],
                    'verify_peer_name' => $this->config['VERIFY_SSL'],
                ]
            ];
            
            // Remetente e destinatário
            $mail->setFrom($this->config['FROM_EMAIL'], $this->config['FROM_NAME']);
            $mail->addAddress($to_email);
            $mail->addReplyTo($this->config['REPLY_TO_EMAIL'], $this->config['FROM_NAME']);
            
            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html_body;
            if ($plain_text_body) {
                $mail->AltBody = $plain_text_body;
            }
            
            // Envia
            $mail->send();
            return ['success' => true, 'message' => 'Email enviado com sucesso'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erro ao enviar email: ' . $e->getMessage()];
        }
    }
    
    /**
     * Envia usando função mail() nativa do PHP
     */
    private function sendWithMailFunction($to_email, $subject, $html_body) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $this->config['FROM_NAME'] . " <" . $this->config['FROM_EMAIL'] . ">\r\n";
        $headers .= "Reply-To: " . $this->config['REPLY_TO_EMAIL'] . "\r\n";
        
        if (mail($to_email, $subject, $html_body, $headers)) {
            return ['success' => true, 'message' => 'Email enviado com sucesso'];
        } else {
            return ['success' => false, 'message' => 'Erro ao enviar email (função mail)'];
        }
    }
}
?>
