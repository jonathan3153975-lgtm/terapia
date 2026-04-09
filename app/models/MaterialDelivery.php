<?php

namespace App\Models;

use Classes\Model;

class MaterialDelivery extends Model
{
    protected string $table = 'material_deliveries';

    public function sendToPatients(int $therapistId, int $materialId, array $patientIds, string $message): int
    {
        $sent = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($patientIds as $patientId) {
            $patientId = (int) $patientId;
            if ($patientId <= 0) {
                continue;
            }

            $inserted = $this->insert([
                'material_id' => $materialId,
                'therapist_id' => $therapistId,
                'patient_id' => $patientId,
                'message' => $message,
                'status' => 'sent',
                'sent_at' => $now,
                'created_at' => $now,
            ]);

            if ($inserted) {
                $sent++;
            }
        }

        return $sent;
    }

    public function listByMaterial(int $materialId): array
    {
        $stmt = $this->query(
            'SELECT md.*, p.name AS patient_name
             FROM material_deliveries md
             INNER JOIN patients p ON p.id = md.patient_id
             WHERE md.material_id = ?
             ORDER BY md.sent_at DESC',
            [$materialId]
        );

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }
}
