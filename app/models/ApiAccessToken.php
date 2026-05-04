<?php

namespace App\Models;

use Classes\Model;

class ApiAccessToken extends Model
{
    protected string $table = 'api_access_tokens';

    public function createToken(array $data): int|false
    {
        return $this->insert([
            'user_id' => (int) ($data['user_id'] ?? 0),
            'patient_id' => (int) ($data['patient_id'] ?? 0),
            'name' => trim((string) ($data['name'] ?? 'flutter-app')),
            'token_hash' => (string) ($data['token_hash'] ?? ''),
            'abilities' => (string) ($data['abilities'] ?? 'patient:full'),
            'last_used_at' => $data['last_used_at'] ?? null,
            'expires_at' => (string) ($data['expires_at'] ?? date('Y-m-d H:i:s')),
            'revoked_at' => $data['revoked_at'] ?? null,
            'created_at' => (string) ($data['created_at'] ?? date('Y-m-d H:i:s')),
            'updated_at' => (string) ($data['updated_at'] ?? date('Y-m-d H:i:s')),
        ]);
    }

    public function findValidByHash(string $tokenHash): ?array
    {
        $stmt = $this->query(
            "SELECT t.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone,
                    u.role AS user_role, u.status AS user_status,
                    p.name AS patient_name, p.email AS patient_email, p.phone AS patient_phone,
                    p.therapist_id, p.status AS patient_status, p.review_status
             FROM api_access_tokens t
             INNER JOIN users u ON u.id = t.user_id
             INNER JOIN patients p ON p.id = t.patient_id
             WHERE t.token_hash = ?
               AND t.revoked_at IS NULL
               AND t.expires_at >= NOW()
               AND u.role = 'patient'
             LIMIT 1",
            [$tokenHash]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function touchUsage(int $tokenId): bool
    {
        return (bool) $this->query(
            'UPDATE api_access_tokens SET last_used_at = ?, updated_at = ? WHERE id = ?',
            [date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $tokenId]
        );
    }

    public function revokeByHash(string $tokenHash): bool
    {
        $now = date('Y-m-d H:i:s');

        return (bool) $this->query(
            'UPDATE api_access_tokens SET revoked_at = ?, updated_at = ? WHERE token_hash = ? AND revoked_at IS NULL',
            [$now, $now, $tokenHash]
        );
    }

    public function revokeByUser(int $userId): bool
    {
        $now = date('Y-m-d H:i:s');

        return (bool) $this->query(
            'UPDATE api_access_tokens SET revoked_at = ?, updated_at = ? WHERE user_id = ? AND revoked_at IS NULL',
            [$now, $now, $userId]
        );
    }
}