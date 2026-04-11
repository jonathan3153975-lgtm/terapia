<?php

namespace App\Models;

use Classes\Model;

class PatientGuidedMeditationEntry extends Model
{
    protected string $table = 'patient_guided_meditation_entries';

    public function listByPatient(int $patientId, int $meditationId = 0): array
    {
        $sql = 'SELECT pgme.*
                FROM patient_guided_meditation_entries pgme
                WHERE pgme.patient_id = ?';
        $params = [$patientId];

        if ($meditationId > 0) {
            $sql .= ' AND pgme.meditation_id = ?';
            $params[] = $meditationId;
        }

        $sql .= ' ORDER BY pgme.created_at DESC, pgme.id DESC';
        $stmt = $this->query($sql, $params);

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function listSharedByTherapist(int $therapistId, int $limit = 80): array
    {
        $limit = max(1, min(200, $limit));

        $stmt = $this->query(
            'SELECT pgme.*, p.name AS patient_name, gm.title AS meditation_title
             FROM patient_guided_meditation_entries pgme
             INNER JOIN patients p ON p.id = pgme.patient_id
             INNER JOIN guided_meditations gm ON gm.id = pgme.meditation_id
             WHERE pgme.therapist_id = ?
               AND pgme.share_with_therapist = 1
             ORDER BY pgme.created_at DESC, pgme.id DESC
             LIMIT ' . (int) $limit,
            [$therapistId]
        );

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }
}
