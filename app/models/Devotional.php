<?php

namespace App\Models;

use Classes\Model;

class Devotional extends Model
{
    protected string $table = 'devotionals';

    public function listByTherapist(int $therapistId, string $search = ''): array
    {
        $sql = 'SELECT d.*, COUNT(e.id) AS entries_count
                FROM devotionals d
                LEFT JOIN devotional_entries e ON e.devotional_id = d.id
                WHERE d.therapist_id = ?';
        $params = [$therapistId];

        if ($search !== '') {
            $sql .= ' AND (
                d.theme LIKE ?
                OR LPAD(d.month_number, 2, "0") LIKE ?
                OR CAST(d.year_number AS CHAR) LIKE ?
            )';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' GROUP BY d.id
                  ORDER BY d.year_number DESC, d.month_number DESC, d.id DESC';

        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function findByTherapistAndId(int $therapistId, int $id): ?array
    {
        $stmt = $this->query(
            'SELECT * FROM devotionals WHERE therapist_id = ? AND id = ? LIMIT 1',
            [$therapistId, $id]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByTherapistMonthYear(int $therapistId, int $monthNumber, int $yearNumber, int $ignoreId = 0): ?array
    {
        $sql = 'SELECT * FROM devotionals WHERE therapist_id = ? AND month_number = ? AND year_number = ?';
        $params = [$therapistId, $monthNumber, $yearNumber];

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

    public function deleteByTherapistAndId(int $therapistId, int $id): bool
    {
        return (bool) $this->query(
            'DELETE FROM devotionals WHERE therapist_id = ? AND id = ? LIMIT 1',
            [$therapistId, $id]
        );
    }
}
