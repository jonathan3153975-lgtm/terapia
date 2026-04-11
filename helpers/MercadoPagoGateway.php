<?php

namespace Helpers;

use Config\Config;

class MercadoPagoGateway
{
    private string $accessToken;
    private string $baseUrl;

    public function __construct()
    {
        $this->accessToken = (string) Config::get('MP_ACCESS_TOKEN', '');
        $this->baseUrl = rtrim((string) Config::get('MP_API_BASE_URL', 'https://api.mercadopago.com'), '/');
    }

    public function isConfigured(): bool
    {
        return $this->accessToken !== '';
    }

    public function createPreference(array $payload): array
    {
        return $this->request('POST', '/checkout/preferences', $payload, true);
    }

    public function getPaymentById(int $paymentId): array
    {
        return $this->request('GET', '/v1/payments/' . $paymentId);
    }

    private function request(string $method, string $path, ?array $payload = null, bool $idempotent = false): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'status' => 500, 'message' => 'Mercado Pago não configurado.'];
        }

        if (!function_exists('curl_init')) {
            return ['ok' => false, 'status' => 500, 'message' => 'Extensão cURL não habilitada no PHP.'];
        }

        $url = $this->baseUrl . $path;
        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
        ];

        if ($idempotent) {
            $headers[] = 'X-Idempotency-Key: ' . bin2hex(random_bytes(16));
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        }

        $raw = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $curlError !== '') {
            return ['ok' => false, 'status' => 502, 'message' => 'Falha na comunicação com Mercado Pago: ' . $curlError];
        }

        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'status' => $statusCode > 0 ? $statusCode : 502, 'message' => 'Resposta inválida do Mercado Pago.'];
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            $message = (string) ($decoded['message'] ?? $decoded['error'] ?? 'Erro ao processar requisição no Mercado Pago.');
            return ['ok' => false, 'status' => $statusCode, 'message' => $message, 'data' => $decoded];
        }

        return ['ok' => true, 'status' => $statusCode, 'data' => $decoded];
    }
}
