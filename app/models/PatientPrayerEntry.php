<?php

namespace App\Models;

use Classes\Model;

class PatientPrayerEntry extends Model
{
    protected string $table = 'patient_prayer_entries';

    public function listByPatient(int $patientId, int $prayerId = 0): array
    {
        $sql = 'SELECT ppe.*
                FROM patient_prayer_entries ppe
                WHERE ppe.patient_id = ?';
        $params = [$patientId];

        if ($prayerId > 0) {
            $sql .= ' AND ppe.prayer_id = ?';
            $params[] = $prayerId;
        }

        $sql .= ' ORDER BY ppe.created_at DESC, ppe.id DESC';
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
            'SELECT ppe.*, p.name AS patient_name, pr.title AS prayer_title
             FROM patient_prayer_entries ppe
             INNER JOIN patients p ON p.id = ppe.patient_id
             INNER JOIN prayers pr ON pr.id = ppe.prayer_id
             WHERE ppe.therapist_id = ?
               AND ppe.share_with_therapist = 1
             ORDER BY ppe.created_at DESC, ppe.id DESC
             LIMIT ' . (int) $limit,
            [$therapistId]
        );

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }
}
