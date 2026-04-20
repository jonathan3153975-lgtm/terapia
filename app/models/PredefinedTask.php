<?php

namespace App\Models;

use Classes\Model;

class PredefinedTask extends Model
{
    protected string $table = 'predefined_tasks';

    public function listByTherapist(int $therapistId, string $search = ''): array
    {
        $params = [$therapistId];
        $sql = 'SELECT *
                FROM predefined_tasks
                WHERE therapist_id = ?';

        if ($search !== '') {
            $sql .= ' AND (title LIKE ? OR description LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' ORDER BY updated_at DESC, id DESC';

        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function findByTherapistAndId(int $therapistId, int $id): ?array
    {
        $stmt = $this->query(
            'SELECT * FROM predefined_tasks WHERE therapist_id = ? AND id = ? LIMIT 1',
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
            'DELETE FROM predefined_tasks WHERE therapist_id = ? AND id = ?',
            [$therapistId, $id]
        );
    }
}
