<?php

namespace App\Models;

use Classes\Model;

class PatientMessage extends Model
{
    protected string $table = 'patient_messages';

    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
    }
}
