<?php

namespace App\Models;

use Classes\Model;

class TeraTubeVideo extends Model
{
    protected string $table = 'teratube_videos';

    public function listByTherapist(int $therapistId, string $search = ''): array
    {
        $sql = 'SELECT v.*,
                       COUNT(DISTINCT f.id) AS favorite_count,
                       COUNT(DISTINCT r.id) AS rating_count,
                       COALESCE(AVG(r.rating), 0) AS average_rating,
                       COUNT(DISTINCT c.id) AS comment_count
                FROM teratube_videos v
                LEFT JOIN patient_video_favorites f ON f.video_id = v.id
                LEFT JOIN patient_video_ratings r ON r.video_id = v.id
                LEFT JOIN patient_video_comments c ON c.video_id = v.id AND c.is_active = 1
                WHERE v.therapist_id = ?';
        $params = [$therapistId];

        if ($search !== '') {
            $sql .= ' AND (v.title LIKE ? OR v.description_text LIKE ? OR v.keywords LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' GROUP BY v.id ORDER BY v.updated_at DESC, v.created_at DESC, v.id DESC';

        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function findByTherapistAndId(int $therapistId, int $videoId): ?array
    {
        $stmt = $this->query(
            'SELECT v.*,
                    COUNT(DISTINCT f.id) AS favorite_count,
                    COUNT(DISTINCT r.id) AS rating_count,
                    COALESCE(AVG(r.rating), 0) AS average_rating,
                    COUNT(DISTINCT c.id) AS comment_count
             FROM teratube_videos v
             LEFT JOIN patient_video_favorites f ON f.video_id = v.id
             LEFT JOIN patient_video_ratings r ON r.video_id = v.id
             LEFT JOIN patient_video_comments c ON c.video_id = v.id AND c.is_active = 1
             WHERE v.therapist_id = ? AND v.id = ?
             GROUP BY v.id
             LIMIT 1',
            [$therapistId, $videoId]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteByTherapistAndId(int $therapistId, int $videoId): bool
    {
        return (bool) $this->query('DELETE FROM teratube_videos WHERE therapist_id = ? AND id = ?', [$therapistId, $videoId]);
    }

    public function listPublishedByTherapist(int $therapistId, string $search = ''): array
    {
        $sql = 'SELECT v.*,
                       COUNT(DISTINCT r.id) AS rating_count,
                       COALESCE(AVG(r.rating), 0) AS average_rating,
                       COUNT(DISTINCT c.id) AS comment_count
                FROM teratube_videos v
                LEFT JOIN patient_video_ratings r ON r.video_id = v.id
                LEFT JOIN patient_video_comments c ON c.video_id = v.id AND c.is_active = 1
                WHERE v.therapist_id = ? AND v.is_published = 1';
        $params = [$therapistId];

        if ($search !== '') {
            $sql .= ' AND (v.title LIKE ? OR v.description_text LIKE ? OR v.keywords LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' GROUP BY v.id ORDER BY v.updated_at DESC, v.created_at DESC, v.id DESC';

        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function findPublishedByPatientAndId(int $patientId, int $videoId): ?array
    {
        $stmt = $this->query(
            'SELECT v.*,
                    COUNT(DISTINCT r.id) AS rating_count,
                    COALESCE(AVG(r.rating), 0) AS average_rating,
                    COUNT(DISTINCT c.id) AS comment_count
             FROM teratube_videos v
             INNER JOIN patients p ON p.therapist_id = v.therapist_id
             LEFT JOIN patient_video_ratings r ON r.video_id = v.id
             LEFT JOIN patient_video_comments c ON c.video_id = v.id AND c.is_active = 1
             WHERE p.id = ? AND v.id = ? AND v.is_published = 1
             GROUP BY v.id
             LIMIT 1',
            [$patientId, $videoId]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function listFavoriteVideosByPatient(int $patientId, string $search = ''): array
    {
        $sql = 'SELECT v.*, f.created_at AS favorited_at,
                       COUNT(DISTINCT r.id) AS rating_count,
                       COALESCE(AVG(r.rating), 0) AS average_rating,
                       COUNT(DISTINCT c.id) AS comment_count
                FROM patient_video_favorites f
                INNER JOIN teratube_videos v ON v.id = f.video_id
                LEFT JOIN patient_video_ratings r ON r.video_id = v.id
                LEFT JOIN patient_video_comments c ON c.video_id = v.id AND c.is_active = 1
                WHERE f.patient_id = ? AND v.is_published = 1';
        $params = [$patientId];

        if ($search !== '') {
            $sql .= ' AND (v.title LIKE ? OR v.description_text LIKE ? OR v.keywords LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' GROUP BY v.id, f.created_at ORDER BY f.created_at DESC, v.title ASC';
        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }
}
