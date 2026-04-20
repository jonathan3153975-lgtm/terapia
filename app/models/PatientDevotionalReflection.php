<?php

namespace App\Models;

use Classes\Model;

class PatientDevotionalReflection extends Model
{
    protected string $table = 'patient_devotional_reflections';

    public function listByPatient(int $patientId): array
    {
        $stmt = $this->query(
            'SELECT r.*, e.entry_date, e.title, e.word_of_god, d.theme
             FROM patient_devotional_reflections r
             INNER JOIN devotional_entries e ON e.id = r.devotional_entry_id
             INNER JOIN devotionals d ON d.id = r.devotional_id
             WHERE r.patient_id = ?
             ORDER BY e.entry_date DESC, r.id DESC',
            [$patientId]
        );

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function findByPatientAndId(int $patientId, int $id): ?array
    {
        $stmt = $this->query(
            'SELECT r.*, e.entry_date, e.title, e.word_of_god, d.theme
             FROM patient_devotional_reflections r
             INNER JOIN devotional_entries e ON e.id = r.devotional_entry_id
             INNER JOIN devotionals d ON d.id = r.devotional_id
             WHERE r.patient_id = ? AND r.id = ?
             LIMIT 1',
            [$patientId, $id]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByPatientAndEntry(int $patientId, int $devotionalEntryId): ?array
    {
        $stmt = $this->query(
            'SELECT *
             FROM patient_devotional_reflections
             WHERE patient_id = ? AND devotional_entry_id = ?
             LIMIT 1',
            [$patientId, $devotionalEntryId]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }
}
