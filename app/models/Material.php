<?php

namespace App\Models;

use Classes\Model;

class Material extends Model
{
    protected string $table = 'materials';

    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
    }

    public function countCreatedInMonthByTherapist(int $therapistId, string $yearMonth): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) AS total
             FROM materials
             WHERE therapist_id = ?
               AND DATE_FORMAT(created_at, '%Y-%m') = ?",
            [$therapistId, $yearMonth]
        );
        if (!$stmt) {
            return 0;
        }

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function listByTherapist(int $therapistId, string $term = ''): array
    {
        $sql = 'SELECT m.*, COUNT(DISTINCT md.id) AS sent_count,
                       GROUP_CONCAT(DISTINCT ma.asset_type ORDER BY ma.asset_type SEPARATOR ",") AS asset_types,
                       COUNT(DISTINCT ma.id) AS asset_count
                FROM materials m
                LEFT JOIN material_deliveries md ON md.material_id = m.id
                LEFT JOIN material_assets ma ON ma.material_id = m.id
                WHERE m.therapist_id = ?';
        $params = [$therapistId];

        if ($term !== '') {
            $like = '%' . $term . '%';
            $sql .= ' AND (m.title LIKE ? OR m.description_html LIKE ? OR m.custom_html LIKE ?)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' GROUP BY m.id ORDER BY m.created_at DESC';
        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function findByTherapistAndId(int $therapistId, int $materialId): ?array
    {
        $stmt = $this->query('SELECT * FROM materials WHERE therapist_id = ? AND id = ? LIMIT 1', [$therapistId, $materialId]);
        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function listAssets(int $materialId): array
    {
        $stmt = $this->query('SELECT * FROM material_assets WHERE material_id = ? ORDER BY created_at ASC, id ASC', [$materialId]);
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function findById(int $materialId): ?array
    {
        $stmt = $this->query('SELECT * FROM materials WHERE id = ? LIMIT 1', [$materialId]);
        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function insertAsset(array $data): int|false
    {
        $cols = array_keys($data);
        $vals = array_values($data);
        $marks = implode(',', array_fill(0, count($cols), '?'));
        $sql = 'INSERT INTO material_assets (' . implode(',', $cols) . ') VALUES (' . $marks . ')';

        $stmt = $this->query($sql, $vals);
        if (!$stmt) {
            return false;
        }

        $conn = \Config\Database::getInstance()->getConnection();
        return (int) $conn->lastInsertId();
    }

    public function findAssetByTherapistAndId(int $therapistId, int $assetId): ?array
    {
        $stmt = $this->query(
            'SELECT a.*
             FROM material_assets a
             INNER JOIN materials m ON m.id = a.material_id
             WHERE a.id = ? AND m.therapist_id = ?
             LIMIT 1',
            [$assetId, $therapistId]
        );
        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findAssetByPatientAndId(int $patientId, int $assetId): ?array
    {
        $stmt = $this->query(
            'SELECT DISTINCT a.*
             FROM material_assets a
             INNER JOIN materials m ON m.id = a.material_id
             LEFT JOIN material_deliveries md
                ON md.material_id = m.id
               AND md.patient_id = ?
             LEFT JOIN task_material_links tml
                ON tml.material_id = m.id
             LEFT JOIN tasks t
                ON t.id = tml.task_id
               AND t.patient_id = ?
               AND t.send_to_patient = 1
             WHERE a.id = ?
               AND (md.id IS NOT NULL OR t.id IS NOT NULL)
             LIMIT 1',
            [$patientId, $patientId, $assetId]
        );
        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteAssetById(int $assetId): bool
    {
        return (bool) $this->query('DELETE FROM material_assets WHERE id = ?', [$assetId]);
    }

    public function deleteByTherapistAndId(int $therapistId, int $materialId): bool
    {
        return (bool) $this->query('DELETE FROM materials WHERE therapist_id = ? AND id = ?', [$therapistId, $materialId]);
    }
}
