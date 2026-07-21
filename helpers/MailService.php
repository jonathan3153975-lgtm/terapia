<?php

namespace Helpers;

use Config\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private PHPMailer $mailer;
    private bool $smtpConfigured = false;
    private string $bootstrapLogFile;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->bootstrapLogFile = dirname(__DIR__) . '/bootstrap-error.log';
        $this->configureSmtp();
    }

    private function logBootstrapError(string $message): void
    {
        @file_put_contents(
            $this->bootstrapLogFile,
            '[' . date('Y-m-d H:i:s') . '] [mail] ' . $message . PHP_EOL,
            FILE_APPEND
        );
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
                $msg = 'MAIL_USERNAME or MAIL_PASSWORD not configured';
                error_log('MailService: ' . $msg);
                $this->logBootstrapError($msg);
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
            $msg = 'configuration error: ' . $e->getMessage();
            error_log('MailService ' . $msg);
            $this->logBootstrapError($msg);
        }
    }

    public function send(string $toEmail, string $toName, string $subject, string $bodyHtml): bool
    {
        try {
            if (!$this->smtpConfigured) {
                $msg = 'SMTP not configured. Falling back to mail() function.';
                error_log('MailService: ' . $msg);
                $this->logBootstrapError($msg);
                return $this->sendViaPhpMail($toEmail, $subject, $bodyHtml);
            }

            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $fromEmail = Config::get(
                'MAIL_FROM_ADDRESS',
                Config::get('MAIL_FROM', Config::get('MAIL_USERNAME', ''))
            );
            $fromName = Config::get('MAIL_FROM_NAME', 'Tera-Tech');

            $this->mailer->setFrom($fromEmail, $fromName);
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $bodyHtml;
            $this->mailer->AltBody = strip_tags($bodyHtml);
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->Encoding = 'base64';

            $sent = $this->mailer->send();
            if (!$sent) {
                $msg = 'send returned false. ErrorInfo: ' . (string) $this->mailer->ErrorInfo;
                error_log('MailService: ' . $msg);
                $this->logBootstrapError($msg);
            }

            return $sent;
        } catch (Exception $e) {
            $host = (string) Config::get('MAIL_HOST', '');
            $port = (string) Config::get('MAIL_PORT', '');
            $username = (string) Config::get('MAIL_USERNAME', '');
            $msg = sprintf(
                'send error: %s | host=%s port=%s username=%s to=%s subject=%s',
                $e->getMessage(),
                $host,
                $port,
                $username,
                $toEmail,
                $subject
            );

            error_log('MailService ' . $msg);
            $this->logBootstrapError($msg);
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

        $fromName = Config::get('MAIL_FROM_NAME', 'Tera-Tech');
        $fromEmail = Config::get(
            'MAIL_FROM_ADDRESS',
            Config::get('MAIL_FROM', Config::get('MAIL_USERNAME', 'noreply@teratech.local'))
        );
        $encodedSubject = function_exists('mb_encode_mimeheader')
            ? mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n")
            : $subject;

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'From: ' . $fromName . ' <' . $fromEmail . '>',
        ];

        $sent = @mail($email, $encodedSubject, $html, implode("\r\n", $headers));
        if (!$sent) {
            $msg = 'fallback mail() failed | to=' . $email . ' subject=' . $subject;
            error_log('MailService: ' . $msg);
            $this->logBootstrapError($msg);
        }

        return $sent;
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
