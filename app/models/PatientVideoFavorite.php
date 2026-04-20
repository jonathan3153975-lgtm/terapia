<?php

namespace App\Models;

use Classes\Model;

class PatientVideoFavorite extends Model
{
    protected string $table = 'patient_video_favorites';

    public function exists(int $patientId, int $videoId): bool
    {
        $stmt = $this->query(
            'SELECT id FROM patient_video_favorites WHERE patient_id = ? AND video_id = ? LIMIT 1',
            [$patientId, $videoId]
        );
        if (!$stmt) {
            return false;
        }

        return (bool) $stmt->fetch();
    }

    public function listVideoIdsByPatient(int $patientId): array
    {
        $stmt = $this->query('SELECT video_id FROM patient_video_favorites WHERE patient_id = ?', [$patientId]);
        if (!$stmt) {
            return [];
        }

        return array_map(static fn (array $row): int => (int) ($row['video_id'] ?? 0), $stmt->fetchAll());
    }

    public function deleteByPatientAndVideo(int $patientId, int $videoId): bool
    {
        return (bool) $this->query('DELETE FROM patient_video_favorites WHERE patient_id = ? AND video_id = ?', [$patientId, $videoId]);
    }

    public function insertIgnore(array $data): bool
    {
        $stmt = $this->query(
            'INSERT IGNORE INTO patient_video_favorites (patient_id, video_id, therapist_id, created_at) VALUES (?, ?, ?, ?)',
            [
                (int) ($data['patient_id'] ?? 0),
                (int) ($data['video_id'] ?? 0),
                (int) ($data['therapist_id'] ?? 0),
                (string) ($data['created_at'] ?? date('Y-m-d H:i:s')),
            ]
        );

        return (bool) $stmt;
    }
}
