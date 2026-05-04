<?php

namespace App\Models;

use Classes\Model;

class PatientAppCycle extends Model
{
    protected string $table = 'patient_app_cycles';

    public function getDrawnIds(int $patientId, string $module, string $scopeKey = 'default'): array
    {
        $stmt = $this->query(
            'SELECT drawn_ids_json FROM patient_app_cycles WHERE patient_id = ? AND module_name = ? AND scope_key = ? LIMIT 1',
            [$patientId, $module, $scopeKey]
        );

        if (!$stmt) {
            return [];
        }

        $row = $stmt->fetch();
        if (!$row) {
            return [];
        }

        $decoded = json_decode((string) ($row['drawn_ids_json'] ?? '[]'), true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $decoded), static fn (int $id): bool => $id > 0));
    }

    public function saveDrawnIds(int $patientId, string $module, string $scopeKey, array $drawnIds): bool
    {
        $drawnIds = array_values(array_unique(array_filter(array_map('intval', $drawnIds), static fn (int $id): bool => $id > 0)));
        $now = date('Y-m-d H:i:s');

        $stmt = $this->query(
            'INSERT INTO patient_app_cycles (patient_id, module_name, scope_key, drawn_ids_json, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE drawn_ids_json = VALUES(drawn_ids_json), updated_at = VALUES(updated_at)',
            [$patientId, $module, $scopeKey, json_encode($drawnIds, JSON_UNESCAPED_UNICODE), $now, $now]
        );

        return (bool) $stmt;
    }
}