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

    public function findTherapistById(int $id): ?array
    {
        $stmt = $this->query("SELECT * FROM users WHERE id = ? AND role = 'therapist' LIMIT 1", [$id]);
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
}
