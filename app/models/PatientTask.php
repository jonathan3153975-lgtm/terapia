<?php

namespace App\Models;

use Classes\Model;

class PatientTask extends Model
{
    protected string $table = 'patient_tasks';

    public function findByPatient(int $patientId, ?int $therapistId = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE patient_id = ?";
        $params = [$patientId];

        if ($therapistId !== null) {
            $sql .= " AND therapist_id = ?";
            $params[] = $therapistId;
        }

        $sql .= ' ORDER BY due_date DESC, id DESC';
        $stmt = $this->query($sql, $params);

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
    }

    public function countSentToPatientByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ? AND sent_to_patient = 1', [$therapistId]);
    }

    public function countByPatient(int $patientId): int
    {
        return $this->count('patient_id = ?', [$patientId]);
    }

    public function countPendingByPatient(int $patientId): int
    {
        return $this->count("patient_id = ? AND status = 'pending'", [$patientId]);
    }

    public function countDoneByPatient(int $patientId): int
    {
        return $this->count("patient_id = ? AND status = 'done'", [$patientId]);
    }

    public function createTask(array $data): int|false
    {
        return $this->insert($data);
    }
}
