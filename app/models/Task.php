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
}
