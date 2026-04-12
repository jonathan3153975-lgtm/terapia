<?php

namespace App\Models;

use Classes\Model;

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->query("SELECT * FROM users WHERE email = ? LIMIT 1", [$email]);
        if (!$stmt) {
            return null;
        }
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function listTherapists(): array
    {
        $stmt = $this->query("SELECT * FROM users WHERE role = 'therapist' ORDER BY id DESC");
        if (!$stmt) {
            return [];
        }
        return $stmt->fetchAll();
    }

    public function countByRole(string $role): int
    {
        return $this->count('role = ?', [$role]);
    }

    public function countTherapistsCreatedInMonth(string $yearMonth): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) AS total
             FROM users
             WHERE role = 'therapist'
               AND DATE_FORMAT(created_at, '%Y-%m') = ?",
            [$yearMonth]
        );
        if (!$stmt) {
            return 0;
        }

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function findTherapistById(int $id): ?array
    {
        $stmt = $this->query("SELECT * FROM users WHERE id = ? AND role = 'therapist' LIMIT 1", [$id]);
        if (!$stmt) {
            return null;
        }
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findPatientAccessByTherapistAndPatient(int $therapistId, int $patientId): ?array
    {
        $stmt = $this->query(
            "SELECT * FROM users WHERE role = 'patient' AND therapist_id = ? AND patient_id = ? LIMIT 1",
            [$therapistId, $patientId]
        );
        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteTherapistById(int $id): bool
    {
        return (bool) $this->query("DELETE FROM users WHERE id = ? AND role = 'therapist'", [$id]);
    }

    public function deletePatientAccessByTherapistAndPatient(int $therapistId, int $patientId): bool
    {
        return (bool) $this->query(
            "DELETE FROM users WHERE role = 'patient' AND therapist_id = ? AND patient_id = ?",
            [$therapistId, $patientId]
        );
    }
}
