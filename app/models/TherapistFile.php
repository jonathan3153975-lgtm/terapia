<?php

namespace App\Models;

use Classes\Model;

class TherapistFile extends Model
{
    protected string $table = 'therapist_files';

    public function totalBytes(?int $therapistId = null): int
    {
        $sql = "SELECT COALESCE(SUM(file_size), 0) as total FROM {$this->table}";
        $params = [];

        if ($therapistId !== null) {
            $sql .= ' WHERE therapist_id = ?';
            $params[] = $therapistId;
        }

        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return 0;
        }

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) ($result['total'] ?? 0);
    }

    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
    }
}
