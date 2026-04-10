<?php

namespace Helpers;

class AlertDispatcher
{
    public static function dispatch(array $channels, ?string $email, ?string $phone, string $subject, string $message): array
    {
        $normalizedChannels = array_values(array_unique(array_filter(array_map(
            static fn ($channel): string => strtolower(trim((string) $channel)),
            $channels
        ))));

        if (empty($normalizedChannels)) {
            $normalizedChannels = ['email', 'whatsapp'];
        }

        $report = [];

        if (in_array('email', $normalizedChannels, true)) {
            $report['email'] = self::sendEmail($email, $subject, $message);
        }

        if (in_array('whatsapp', $normalizedChannels, true)) {
            $report['whatsapp'] = self::buildWhatsappAlert($phone, $message);
        }

        return $report;
    }

    public static function summarize(array $report): string
    {
        if (empty($report)) {
            return 'alertas não configurados';
        }

        $parts = [];
        foreach ($report as $channel => $info) {
            $status = (string) ($info['status'] ?? 'skipped');
            if ($status === 'sent') {
                $parts[] = $channel . ': enviado';
                continue;
            }
            if ($status === 'generated') {
                $parts[] = $channel . ': link gerado';
                continue;
            }
            if ($status === 'missing') {
                $parts[] = $channel . ': contato ausente';
                continue;
            }
            $parts[] = $channel . ': não enviado';
        }

        return implode(' | ', $parts);
    }

    private static function sendEmail(?string $email, string $subject, string $message): array
    {
        $email = trim((string) $email);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'missing'];
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/plain; charset=UTF-8',
        ];

        $sent = @mail($email, $subject, $message, implode("\r\n", $headers));
        return ['status' => $sent ? 'sent' : 'failed'];
    }

    private static function buildWhatsappAlert(?string $phone, string $message): array
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';
        if ($digits === '') {
            return ['status' => 'missing'];
        }

        if (!str_starts_with($digits, '55')) {
            $digits = '55' . $digits;
        }

        $url = 'https://wa.me/' . $digits . '?text=' . rawurlencode($message);
        return ['status' => 'generated', 'url' => $url];
    }
}
