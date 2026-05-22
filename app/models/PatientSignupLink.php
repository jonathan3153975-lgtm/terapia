<?php

namespace App\Models;

use Classes\Model;

class PatientSignupLink extends Model
{
    protected string $table = 'patient_signup_links';

    public function listByTherapist(int $therapistId, int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        $stmt = $this->query(
            "SELECT * FROM patient_signup_links WHERE therapist_id = ? ORDER BY id DESC LIMIT {$limit}",
            [$therapistId]
        );
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function findActiveByToken(string $token): ?array
    {
        $stmt = $this->query(
            "SELECT *
             FROM patient_signup_links
             WHERE token = ?
               AND status = 'active'
               AND expires_at >= NOW()
               AND used_count < max_uses
             LIMIT 1",
            [$token]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function incrementUsage(int $id): bool
    {
        return (bool) $this->query(
            'UPDATE patient_signup_links SET used_count = used_count + 1, updated_at = NOW() WHERE id = ?',
            [$id]
        );
    }

    public function ensureDefaultToken(string $token): void
    {
        $stmt = $this->query(
            'SELECT id FROM patient_signup_links WHERE token = ? LIMIT 1',
            [$token]
        );

        if ($stmt && $stmt->fetch()) {
            $this->query(
                "UPDATE patient_signup_links
                 SET status = 'active', expires_at = DATE_ADD(NOW(), INTERVAL 10 YEAR),
                     used_count = 0, max_uses = 999999, updated_at = NOW()
                 WHERE token = ?",
                [$token]
            );
        } else {
            $this->query(
                "INSERT INTO patient_signup_links
                     (therapist_id, token, recipient_email, expires_at, used_count, max_uses, status, created_at, updated_at)
                 VALUES (0, ?, NULL, DATE_ADD(NOW(), INTERVAL 10 YEAR), 0, 999999, 'active', NOW(), NOW())",
                [$token]
            );
        }
    }
}
