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
        $stmt = $this->query('SELECT * FROM tasks WHERE patient_id = ? ORDER BY due_date DESC', [$patientId]);
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
}
