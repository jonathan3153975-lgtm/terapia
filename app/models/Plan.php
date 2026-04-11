<?php

namespace App\Models;

use Classes\Model;

class Plan extends Model
{
    protected string $table = 'plans';

    public function listPatientPlansByTherapist(int $therapistId): array
    {
        $stmt = $this->query(
            "SELECT p.*, u.name AS therapist_name
             FROM plans p
             INNER JOIN users u ON u.id = p.therapist_id
             WHERE p.target = 'patient'
               AND p.is_active = 1
               AND p.therapist_id = ?
             ORDER BY FIELD(p.billing_cycle, 'mensal', 'semestral', 'anual'), p.price ASC",
            [$therapistId]
        );

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function listPatientPlansForAdmin(): array
    {
        $stmt = $this->query(
            "SELECT p.*, u.name AS therapist_name
             FROM plans p
             LEFT JOIN users u ON u.id = p.therapist_id
             WHERE p.target = 'patient'
             ORDER BY p.created_at DESC"
        );

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function findPatientPlanById(int $planId): ?array
    {
        $stmt = $this->query(
            "SELECT p.*, u.name AS therapist_name
             FROM plans p
             LEFT JOIN users u ON u.id = p.therapist_id
             WHERE p.id = ? AND p.target = 'patient'
             LIMIT 1",
            [$planId]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }
}
