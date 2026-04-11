<?php

namespace App\Models;

use Classes\Model;

class DailyMessage extends Model
{
    protected string $table = 'daily_messages';

    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
    }

    public function listByTherapist(int $therapistId, string $category = '', string $search = ''): array
    {
        $sql = 'SELECT * FROM daily_messages WHERE therapist_id = ?';
        $params = [$therapistId];

        if ($category !== '') {
            $sql .= ' AND category = ?';
            $params[] = $category;
        }

        if ($search !== '') {
            $sql .= ' AND message_text LIKE ?';
            $params[] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY created_at DESC, id DESC';
        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function randomByTherapist(int $therapistId, string $category = ''): ?array
    {
        $sql = 'SELECT * FROM daily_messages WHERE therapist_id = ?';
        $params = [$therapistId];

        if ($category !== '') {
            $sql .= ' AND category = ?';
            $params[] = $category;
        }

        $sql .= ' ORDER BY RAND() LIMIT 1';
        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByTherapistAndId(int $therapistId, int $id): ?array
    {
        $stmt = $this->query(
            'SELECT * FROM daily_messages WHERE therapist_id = ? AND id = ? LIMIT 1',
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
            'DELETE FROM daily_messages WHERE therapist_id = ? AND id = ? LIMIT 1',
            [$therapistId, $id]
        );
    }
}
