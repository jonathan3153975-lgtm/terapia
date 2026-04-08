<?php

namespace App\Models;

use Classes\Model;

class Payment extends Model
{
    protected string $table = 'payments';

    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
    }
}
