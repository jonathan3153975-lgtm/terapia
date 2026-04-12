<?php

namespace App\Models;

use Classes\Model;

class PatientGratitudeEntry extends Model
{
    protected string $table = 'patient_gratitude_entries';

    public function getLatestCycleStatsByPatient(int $patientId): ?array
    {
        $stmt = $this->query(
            'SELECT cycle_number, COUNT(*) AS total_entries, COALESCE(MAX(day_number), 0) AS max_day
             FROM patient_gratitude_entries
             WHERE patient_id = ?
             GROUP BY cycle_number
             ORDER BY cycle_number DESC
             LIMIT 1',
            [$patientId]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getCycleStatsByPatient(int $patientId, int $cycleNumber): array
    {
        $stmt = $this->query(
            'SELECT COUNT(*) AS total_entries, COALESCE(MAX(day_number), 0) AS max_day
             FROM patient_gratitude_entries
             WHERE patient_id = ? AND cycle_number = ?',
            [$patientId, $cycleNumber]
        );

        if (!$stmt) {
            return ['total_entries' => 0, 'max_day' => 0];
        }

        $row = $stmt->fetch();
        return [
            'total_entries' => (int) ($row['total_entries'] ?? 0),
            'max_day' => (int) ($row['max_day'] ?? 0),
        ];
    }

    public function listByPatientAndCycle(int $patientId, int $cycleNumber): array
    {
        $stmt = $this->query(
            'SELECT * FROM patient_gratitude_entries
             WHERE patient_id = ? AND cycle_number = ?
             ORDER BY day_number ASC, id ASC',
            [$patientId, $cycleNumber]
        );

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function findByPatientAndId(int $patientId, int $entryId): ?array
    {
        $stmt = $this->query(
            'SELECT * FROM patient_gratitude_entries WHERE id = ? AND patient_id = ? LIMIT 1',
            [$entryId, $patientId]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteByPatientAndId(int $patientId, int $entryId): bool
    {
        return (bool) $this->query('DELETE FROM patient_gratitude_entries WHERE id = ? AND patient_id = ?', [$entryId, $patientId]);
    }
}
