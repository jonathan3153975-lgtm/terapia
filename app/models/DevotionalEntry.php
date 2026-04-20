<?php

namespace App\Models;

use Classes\Model;

class DevotionalEntry extends Model
{
    protected string $table = 'devotional_entries';

    public function listByDevotional(int $therapistId, int $devotionalId): array
    {
        $stmt = $this->query(
            'SELECT *
             FROM devotional_entries
             WHERE therapist_id = ? AND devotional_id = ?
             ORDER BY entry_date ASC, id ASC',
            [$therapistId, $devotionalId]
        );

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function findByTherapistDevotionalAndId(int $therapistId, int $devotionalId, int $id): ?array
    {
        $stmt = $this->query(
            'SELECT *
             FROM devotional_entries
             WHERE therapist_id = ? AND devotional_id = ? AND id = ?
             LIMIT 1',
            [$therapistId, $devotionalId, $id]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByDevotionalAndDate(int $therapistId, int $devotionalId, string $entryDate, int $ignoreId = 0): ?array
    {
        $sql = 'SELECT *
                FROM devotional_entries
                WHERE therapist_id = ? AND devotional_id = ? AND entry_date = ?';
        $params = [$therapistId, $devotionalId, $entryDate];

        if ($ignoreId > 0) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }

        $sql .= ' LIMIT 1';
        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteByTherapistDevotionalAndId(int $therapistId, int $devotionalId, int $id): bool
    {
        return (bool) $this->query(
            'DELETE FROM devotional_entries WHERE therapist_id = ? AND devotional_id = ? AND id = ? LIMIT 1',
            [$therapistId, $devotionalId, $id]
        );
    }
}
