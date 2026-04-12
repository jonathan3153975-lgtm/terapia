<?php

namespace App\Models;

use Classes\Model;

class Patient extends Model
{
    protected string $table = 'patients';

    public function deleteByTherapistAndId(int $therapistId, int $patientId): bool
    {
        return (bool) $this->query('DELETE FROM patients WHERE id = ? AND therapist_id = ?', [$patientId, $therapistId]);
    }

    public function countPendingReviewByTherapist(int $therapistId): int
    {
        return $this->count("therapist_id = ? AND review_status = 'pending_review'", [$therapistId]);
    }

    public function countCreatedInMonthByTherapist(int $therapistId, string $yearMonth): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) AS total
             FROM patients
             WHERE therapist_id = ?
               AND DATE_FORMAT(created_at, '%Y-%m') = ?",
            [$therapistId, $yearMonth]
        );

        if (!$stmt) {
            return 0;
        }

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function countCreatedInMonth(string $yearMonth): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) AS total
             FROM patients
             WHERE DATE_FORMAT(created_at, '%Y-%m') = ?",
            [$yearMonth]
        );

        if (!$stmt) {
            return 0;
        }

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function findByTherapistAndId(int $therapistId, int $patientId): ?array
    {
        $stmt = $this->query('SELECT * FROM patients WHERE therapist_id = ? AND id = ? LIMIT 1', [$therapistId, $patientId]);
        if (!$stmt) {
            return null;
        }
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function searchByTherapist(int $therapistId, string $term = ''): array
    {
        $sql = 'SELECT * FROM patients WHERE therapist_id = ?';
        $params = [$therapistId];

        if ($term !== '') {
            $sql .= ' AND (name LIKE ? OR email LIKE ? OR cpf LIKE ?)';
            $like = "%{$term}%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' ORDER BY name ASC';
        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return [];
        }
        return $stmt->fetchAll();
    }

    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
    }
}
