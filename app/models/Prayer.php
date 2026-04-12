<?php

namespace App\Models;

use Classes\Model;

class Prayer extends Model
{
    protected string $table = 'prayers';

    public function listByTherapist(int $therapistId, string $search = ''): array
    {
        $sql = 'SELECT * FROM prayers WHERE therapist_id = ?';
        $params = [$therapistId];

        if ($search !== '') {
            $sql .= ' AND title LIKE ?';
            $params[] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY created_at DESC, id DESC';
        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function findByTherapistAndId(int $therapistId, int $id): ?array
    {
        $stmt = $this->query(
            'SELECT * FROM prayers WHERE therapist_id = ? AND id = ? LIMIT 1',
            [$therapistId, $id]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteByTherapistAndId(int $therapistId, int $id): bool
    {
        return (bool) $this->query(
            'DELETE FROM prayers WHERE therapist_id = ? AND id = ? LIMIT 1',
            [$therapistId, $id]
        );
    }
}
