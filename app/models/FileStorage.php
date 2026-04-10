<?php

namespace App\Models;

use Classes\Model;

class FileStorage extends Model
{
    protected string $table = 'files';

    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
    }

    public function totalBytesByTherapist(int $therapistId): int
    {
        $stmt = $this->query('SELECT COALESCE(SUM(file_size),0) AS total FROM files WHERE therapist_id = ?', [$therapistId]);
        if (!$stmt) {
            return 0;
        }
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function totalBytes(): int
    {
        $stmt = $this->query('SELECT COALESCE(SUM(file_size),0) AS total FROM files');
        if (!$stmt) {
            return 0;
        }
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function listByTask(int $taskId): array
    {
        $stmt = $this->query('SELECT * FROM files WHERE task_id = ? ORDER BY created_at ASC', [$taskId]);
        if (!$stmt) {
            return [];
        }
        return $stmt->fetchAll();
    }

    public function listByTaskAndSourceRole(int $taskId, string $sourceRole): array
    {
        $role = $sourceRole === 'patient' ? 'patient' : 'therapist';
        $stmt = $this->query(
            'SELECT * FROM files WHERE task_id = ? AND COALESCE(source_role, "therapist") = ? ORDER BY created_at ASC',
            [$taskId, $role]
        );
        if (!$stmt) {
            return [];
        }
        return $stmt->fetchAll();
    }

    public function listByPatientGroupedByTask(int $patientId): array
    {
        $stmt = $this->query('SELECT * FROM files WHERE patient_id = ? ORDER BY task_id, created_at ASC', [$patientId]);
        if (!$stmt) {
            return [];
        }
        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $grouped[(int) $row['task_id']][] = $row;
        }
        return $grouped;
    }
}
