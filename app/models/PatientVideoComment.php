<?php

namespace App\Models;

use Classes\Model;

class PatientVideoComment extends Model
{
    protected string $table = 'patient_video_comments';

    public function listByVideo(int $videoId): array
    {
        $stmt = $this->query(
            "SELECT c.*,
                    SUBSTRING_INDEX(COALESCE(p.name, 'Paciente'), ' ', 1) AS patient_first_name,
                    COUNT(cr.id) AS rating_count,
                    COALESCE(AVG(cr.rating), 0) AS average_rating
             FROM patient_video_comments c
             INNER JOIN patients p ON p.id = c.patient_id
             LEFT JOIN patient_video_comment_ratings cr ON cr.comment_id = c.id
             WHERE c.video_id = ? AND c.is_active = 1
             GROUP BY c.id
             ORDER BY average_rating DESC, rating_count DESC, c.created_at DESC, c.id DESC",
            [$videoId]
        );

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function findByIdActive(int $commentId): ?array
    {
        $stmt = $this->query(
            'SELECT * FROM patient_video_comments WHERE id = ? AND is_active = 1 LIMIT 1',
            [$commentId]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }
}
