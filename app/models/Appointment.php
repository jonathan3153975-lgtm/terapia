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

    public function findByTherapistPatientAndId(int $therapistId, int $patientId, int $appointmentId): ?array
    {
        $stmt = $this->query(
            'SELECT * FROM appointments WHERE id = ? AND therapist_id = ? AND patient_id = ? LIMIT 1',
            [$appointmentId, $therapistId, $patientId]
        );
        if (!$stmt) {
            return null;
        }
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteByTherapistPatientAndId(int $therapistId, int $patientId, int $appointmentId): bool
    {
        return (bool) $this->query(
            'DELETE FROM appointments WHERE id = ? AND therapist_id = ? AND patient_id = ?',
            [$appointmentId, $therapistId, $patientId]
        );
    }
}
