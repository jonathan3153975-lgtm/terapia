<?php

namespace App\Models;

use Classes\Model;

class FileStorage extends Model
{
    protected string $table = 'files';

    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
    }

    public function totalBytesByTherapist(int $therapistId): int
    {
        $stmt = $this->query('SELECT COALESCE(SUM(file_size),0) AS total FROM files WHERE therapist_id = ?', [$therapistId]);
        if (!$stmt) {
            return 0;
        }
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function totalBytes(): int
    {
        $stmt = $this->query('SELECT COALESCE(SUM(file_size),0) AS total FROM files');
        if (!$stmt) {
            return 0;
        }
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }
}
