<?php

namespace App\Models;

use Classes\Model;

class Appointment extends Model
{
    protected string $table = 'appointments';

    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
    }

    public function listByPatient(int $patientId): array
    {
        $stmt = $this->query('SELECT * FROM appointments WHERE patient_id = ? ORDER BY session_date DESC', [$patientId]);
        if (!$stmt) {
            return [];
        }
        return $stmt->fetchAll();
    }
}
