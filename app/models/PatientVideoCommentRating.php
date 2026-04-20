<?php

namespace App\Models;

use Classes\Model;

class PatientVideoCommentRating extends Model
{
    protected string $table = 'patient_video_comment_ratings';

    public function listByPatientAndVideo(int $patientId, int $videoId): array
    {
        $stmt = $this->query(
            'SELECT cr.comment_id, cr.rating
             FROM patient_video_comment_ratings cr
             INNER JOIN patient_video_comments c ON c.id = cr.comment_id
             WHERE cr.patient_id = ? AND c.video_id = ?',
            [$patientId, $videoId]
        );

        if (!$stmt) {
            return [];
        }

        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $commentId = (int) ($row['comment_id'] ?? 0);
            if ($commentId > 0) {
                $map[$commentId] = (int) ($row['rating'] ?? 0);
            }
        }

        return $map;
    }

    public function upsertRating(int $commentId, int $patientId, int $rating): bool
    {
        $stmt = $this->query(
            'INSERT INTO patient_video_comment_ratings (comment_id, patient_id, rating, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE rating = VALUES(rating), updated_at = VALUES(updated_at)',
            [
                $commentId,
                $patientId,
                $rating,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s'),
            ]
        );

        return (bool) $stmt;
    }
}
