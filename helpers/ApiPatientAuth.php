<?php

namespace Helpers;

use App\Models\ApiAccessToken;

class ApiPatientAuth
{
    public static function extractBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if ($header === '' && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $header = (string) ($headers['Authorization'] ?? $headers['authorization'] ?? '');
        }

        if (!preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return null;
        }

        $token = trim((string) ($matches[1] ?? ''));
        return $token !== '' ? $token : null;
    }

    public static function issuePatientToken(array $user, string $deviceName = 'flutter-app', int $ttlDays = 30): array
    {
        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . max(1, $ttlDays) . ' days'));

        $tokenModel = new ApiAccessToken();
        $tokenId = $tokenModel->createToken([
            'user_id' => (int) ($user['id'] ?? 0),
            'patient_id' => (int) ($user['patient_id'] ?? 0),
            'name' => $deviceName,
            'token_hash' => $tokenHash,
            'abilities' => 'patient:full',
            'last_used_at' => date('Y-m-d H:i:s'),
            'expires_at' => $expiresAt,
        ]);

        if ($tokenId === false) {
            throw new \RuntimeException('Falha ao emitir token de acesso.');
        }

        return [
            'token' => $plainToken,
            'token_hash' => $tokenHash,
            'token_id' => $tokenId,
            'expires_at' => $expiresAt,
        ];
    }

    public static function authenticatedPatient(): ?array
    {
        $plainToken = self::extractBearerToken();
        if ($plainToken === null) {
            return null;
        }

        $tokenHash = hash('sha256', $plainToken);
        $tokenModel = new ApiAccessToken();
        $token = $tokenModel->findValidByHash($tokenHash);
        if (!$token) {
            return null;
        }

        if ((string) ($token['user_status'] ?? 'inactive') !== 'active') {
            return null;
        }

        if ((string) ($token['patient_status'] ?? 'inactive') !== 'active') {
            return null;
        }

        if ((string) ($token['review_status'] ?? 'approved') !== 'approved') {
            return null;
        }

        $tokenModel->touchUsage((int) ($token['id'] ?? 0));

        return $token;
    }
}