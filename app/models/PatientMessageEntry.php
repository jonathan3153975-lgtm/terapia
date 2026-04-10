<?php

namespace App\Models;

use Classes\Model;

class PatientMessageEntry extends Model
{
    protected string $table = 'patient_message_entries';

    public function listByPatient(int $patientId): array
    {
        $stmt = $this->query(
            'SELECT pme.*
             FROM patient_message_entries pme
             WHERE pme.patient_id = ?
             ORDER BY pme.created_at DESC, pme.id DESC',
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
            'SELECT pme.*, p.name AS patient_name
             FROM patient_message_entries pme
             INNER JOIN patients p ON p.id = pme.patient_id
             WHERE pme.therapist_id = ?
               AND pme.share_with_therapist = 1
             ORDER BY pme.created_at DESC, pme.id DESC
             LIMIT ' . (int) $limit,
            [$therapistId]
        );

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }
}
