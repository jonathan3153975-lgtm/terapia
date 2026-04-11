<?php

namespace App\Models;

use Classes\Model;

class PatientFaithEntry extends Model
{
    protected string $table = 'patient_faith_entries';

    public function listByPatient(int $patientId): array
    {
        $stmt = $this->query(
            'SELECT pfe.*
             FROM patient_faith_entries pfe
             WHERE pfe.patient_id = ?
             ORDER BY pfe.created_at DESC, pfe.id DESC',
            [$patientId]
        );

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function listSharedByTherapist(int $therapistId, int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));

        $stmt = $this->query(
            'SELECT pfe.*, p.name AS patient_name
             FROM patient_faith_entries pfe
             INNER JOIN patients p ON p.id = pfe.patient_id
             WHERE pfe.therapist_id = ?
               AND pfe.share_with_therapist = 1
             ORDER BY pfe.created_at DESC, pfe.id DESC
             LIMIT ' . (int) $limit,
            [$therapistId]
        );

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }
}
