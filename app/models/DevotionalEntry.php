<?php

namespace App\Models;

use Classes\Model;

class DevotionalEntry extends Model
{
    protected string $table = 'devotional_entries';

    public function findByTherapistAndDate(int $therapistId, string $entryDate): ?array
    {
        $stmt = $this->query(
            'SELECT e.*, d.theme, d.month_number, d.year_number
             FROM devotional_entries e
             INNER JOIN devotionals d ON d.id = e.devotional_id
             WHERE e.therapist_id = ? AND e.entry_date = ?
             LIMIT 1',
            [$therapistId, $entryDate]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByTherapistAndId(int $therapistId, int $entryId): ?array
    {
        $stmt = $this->query(
            'SELECT e.*, d.theme, d.month_number, d.year_number
             FROM devotional_entries e
             INNER JOIN devotionals d ON d.id = e.devotional_id
             WHERE e.therapist_id = ? AND e.id = ?
             LIMIT 1',
            [$therapistId, $entryId]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

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
