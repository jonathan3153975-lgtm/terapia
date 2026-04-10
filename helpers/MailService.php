<?php

namespace Helpers;

use Config\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private PHPMailer $mailer;
    private bool $smtpConfigured = false;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configureSmtp();
    }

    private function configureSmtp(): void
    {
        try {
            $smtpEnabled = strtolower((string) Config::get('MAIL_DRIVER', 'smtp')) === 'smtp';
            if (!$smtpEnabled) {
                return;
            }

            $host = Config::get('MAIL_HOST', 'smtplw.com.br');
            $port = (int) Config::get('MAIL_PORT', 587);
            $username = Config::get('MAIL_USERNAME', '');
            $password = Config::get('MAIL_PASSWORD', '');
            $encryption = Config::get('MAIL_ENCRYPTION', 'tls');

            if ($username === '' || $password === '') {
                error_log('MailService: MAIL_USERNAME or MAIL_PASSWORD not configured');
                return;
            }

            $this->mailer->isSMTP();
            $this->mailer->Host = $host;
            $this->mailer->Port = $port;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $username;
            $this->mailer->Password = $password;

            if (strtolower($encryption) === 'ssl') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $this->smtpConfigured = true;
        } catch (Exception $e) {
            error_log('MailService configuration error: ' . $e->getMessage());
        }
    }

    public function send(string $toEmail, string $toName, string $subject, string $bodyHtml): bool
    {
        try {
            if (!$this->smtpConfigured) {
                error_log('MailService: SMTP not configured. Falling back to mail() function.');
                return $this->sendViaPhpMail($toEmail, $subject, $bodyHtml);
            }

            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $fromEmail = Config::get('MAIL_FROM_ADDRESS', Config::get('MAIL_USERNAME', ''));
            $fromName = Config::get('MAIL_FROM_NAME', 'Sistema de Terapia');

            $this->mailer->setFrom($fromEmail, $fromName);
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $bodyHtml;
            $this->mailer->AltBody = strip_tags($bodyHtml);
            $this->mailer->CharSet = 'UTF-8';

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log('MailService send error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendBatch(array $recipients, string $subject, string $bodyHtml): int
    {
        $sent = 0;
        foreach ($recipients as $recipient) {
            $email = (string) ($recipient['email'] ?? '');
            $name = (string) ($recipient['name'] ?? 'Usuário');
            if ($this->send($email, $name, $subject, $bodyHtml)) {
                $sent++;
            }
        }
        return $sent;
    }

    private function sendViaPhpMail(string $email, string $subject, string $html): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $fromName = Config::get('MAIL_FROM_NAME', 'Sistema de Terapia');
        $fromEmail = Config::get('MAIL_FROM_ADDRESS', Config::get('MAIL_USERNAME', 'noreply@terapia.local'));

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $fromName . ' <' . $fromEmail . '>',
        ];

        return @mail($email, $subject, $html, implode("\r\n", $headers));
    }

    public static function getLastError(): string
    {
        try {
            $mailer = new PHPMailer(true);
            return $mailer->ErrorInfo;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
