<?php

namespace App\Models;

use Classes\Model;

class PatientVideoRating extends Model
{
    protected string $table = 'patient_video_ratings';

    public function findByPatientAndVideo(int $patientId, int $videoId): ?array
    {
        $stmt = $this->query(
            'SELECT * FROM patient_video_ratings WHERE patient_id = ? AND video_id = ? LIMIT 1',
            [$patientId, $videoId]
        );
        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function upsertRating(int $patientId, int $videoId, int $therapistId, int $rating): bool
    {
        $stmt = $this->query(
            'INSERT INTO patient_video_ratings (patient_id, video_id, therapist_id, rating, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE rating = VALUES(rating), updated_at = VALUES(updated_at)',
            [
                $patientId,
                $videoId,
                $therapistId,
                $rating,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s'),
            ]
        );

        return (bool) $stmt;
    }
}
