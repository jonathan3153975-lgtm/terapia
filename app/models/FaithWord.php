<?php

namespace App\Models;

use Classes\Model;

class FaithWord extends Model
{
    protected string $table = 'faith_words';

    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
    }

    public function listByTherapist(int $therapistId, string $search = ''): array
    {
        $sql = 'SELECT * FROM faith_words WHERE therapist_id = ?';
        $params = [$therapistId];

        if ($search !== '') {
            $sql .= ' AND (reference_text LIKE ? OR verse_text LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' ORDER BY created_at DESC, id DESC';
        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function listIdsByTherapist(int $therapistId): array
    {
        $stmt = $this->query('SELECT id FROM faith_words WHERE therapist_id = ? ORDER BY id ASC', [$therapistId]);
        if (!$stmt) {
            return [];
        }

        $rows = $stmt->fetchAll();
        return array_map(static fn (array $row): int => (int) ($row['id'] ?? 0), $rows);
    }

    public function randomByTherapistExcludingIds(int $therapistId, array $excludedIds): ?array
    {
        $excludedIds = array_values(array_filter(array_map('intval', $excludedIds), static fn (int $id): bool => $id > 0));

        if ($excludedIds === []) {
            $stmt = $this->query('SELECT * FROM faith_words WHERE therapist_id = ? ORDER BY RAND() LIMIT 1', [$therapistId]);
        } else {
            $marks = implode(',', array_fill(0, count($excludedIds), '?'));
            $params = array_merge([$therapistId], $excludedIds);
            $stmt = $this->query(
                'SELECT * FROM faith_words WHERE therapist_id = ? AND id NOT IN (' . $marks . ') ORDER BY RAND() LIMIT 1',
                $params
            );
        }

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByTherapistAndId(int $therapistId, int $id): ?array
    {
        $stmt = $this->query('SELECT * FROM faith_words WHERE therapist_id = ? AND id = ? LIMIT 1', [$therapistId, $id]);
        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteByTherapistAndId(int $therapistId, int $id): bool
    {
        return (bool) $this->query('DELETE FROM faith_words WHERE therapist_id = ? AND id = ? LIMIT 1', [$therapistId, $id]);
    }
}
