<?php

namespace App\Models;

use Classes\Model;

class Task extends Model
{
    protected string $table = 'tasks';

    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
    }

    public function listByPatient(int $patientId): array
    {
        $stmt = $this->query(
            'SELECT t.*, m.title AS material_title, m.type AS material_type
             FROM tasks t
             LEFT JOIN materials m ON m.id = t.material_id
             WHERE t.patient_id = ?
             ORDER BY t.due_date DESC',
            [$patientId]
        );
        if (!$stmt) {
            return [];
        }
        return $stmt->fetchAll();
    }

    public function countPendingByPatient(int $patientId): int
    {
        return $this->count("patient_id = ? AND status = 'pending'", [$patientId]);
    }

    public function countDoneByPatient(int $patientId): int
    {
        return $this->count("patient_id = ? AND status = 'done'", [$patientId]);
    }

    public function findByTherapistPatientAndId(int $therapistId, int $patientId, int $taskId): ?array
    {
        $stmt = $this->query(
            'SELECT t.*, m.title AS material_title, m.type AS material_type
             FROM tasks t
             LEFT JOIN materials m ON m.id = t.material_id
             WHERE t.id = ? AND t.therapist_id = ? AND t.patient_id = ?
             LIMIT 1',
            [$taskId, $therapistId, $patientId]
        );
        if (!$stmt) {
            return null;
        }
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByPatientAndId(int $patientId, int $taskId): ?array
    {
        $stmt = $this->query(
            'SELECT t.*, m.title AS material_title, m.type AS material_type
             FROM tasks t
             LEFT JOIN materials m ON m.id = t.material_id
             WHERE t.id = ? AND t.patient_id = ?
             LIMIT 1',
            [$taskId, $patientId]
        );
        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteByTherapistPatientAndId(int $therapistId, int $patientId, int $taskId): bool
    {
        return (bool) $this->query(
            'DELETE FROM tasks WHERE id = ? AND therapist_id = ? AND patient_id = ?',
            [$taskId, $therapistId, $patientId]
        );
    }

    public function listLinkedMaterials(int $taskId): array
    {
        $stmt = $this->query(
            'SELECT m.*
             FROM task_material_links tml
             INNER JOIN materials m ON m.id = tml.material_id
             WHERE tml.task_id = ?
             ORDER BY m.title ASC',
            [$taskId]
        );
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function listLinkedMaterialsGroupedByTask(array $taskIds): array
    {
        $taskIds = array_values(array_unique(array_map('intval', $taskIds)));
        $taskIds = array_filter($taskIds, static fn (int $id): bool => $id > 0);
        if (empty($taskIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
        $stmt = $this->query(
            'SELECT tml.task_id, m.*
             FROM task_material_links tml
             INNER JOIN materials m ON m.id = tml.material_id
             WHERE tml.task_id IN (' . $placeholders . ')
             ORDER BY m.title ASC',
            $taskIds
        );
        if (!$stmt) {
            return [];
        }

        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $grouped[(int) $row['task_id']][] = $row;
        }

        return $grouped;
    }

    public function syncLinkedMaterials(int $taskId, array $materialIds): bool
    {
        $materialIds = array_values(array_unique(array_map('intval', $materialIds)));
        $materialIds = array_filter($materialIds, static fn (int $id): bool => $id > 0);

        $deleted = $this->query('DELETE FROM task_material_links WHERE task_id = ?', [$taskId]);
        if (!$deleted) {
            return false;
        }

        foreach ($materialIds as $materialId) {
            $ok = $this->query(
                'INSERT INTO task_material_links (task_id, material_id, created_at) VALUES (?, ?, ?)',
                [$taskId, $materialId, date('Y-m-d H:i:s')]
            );
            if (!$ok) {
                return false;
            }
        }

        return true;
    }
}
