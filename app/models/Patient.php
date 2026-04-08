<?php

namespace App\Models;

use Classes\Model;

class Patient extends Model
{
    protected string $table = 'patients';

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
