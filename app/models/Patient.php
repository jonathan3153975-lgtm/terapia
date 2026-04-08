<?php

namespace App\Models;

use Classes\Model;

class Patient extends Model
{
    protected string $table = 'patients';

    /**
     * Busca paciente por CPF
     */
    public function findByCPF(string $cpf): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE cpf = ? LIMIT 1";
        $stmt = $this->query($sql, [$cpf]);
        
        if (!$stmt) return null;
        
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Busca pacientes com filtro
     */
    public function search(string $searchTerm = '', int $offset = 0, int $limit = 15, ?int $therapistId = null): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        $conditions = [];

        if ($therapistId !== null) {
            $conditions[] = "therapist_id = ?";
            $params[] = $therapistId;
        }

        if (!empty($searchTerm)) {
            $conditions[] = "(name LIKE ? OR cpf LIKE ? OR email LIKE ?)";
            $search = "%{$searchTerm}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY name ASC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->query($sql, $params);
        
        if (!$stmt) return [];
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Conta pacientes com filtro
     */
    public function countSearch(string $searchTerm = '', ?int $therapistId = null): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        $conditions = [];

        if ($therapistId !== null) {
            $conditions[] = "therapist_id = ?";
            $params[] = $therapistId;
        }

        if (!empty($searchTerm)) {
            $conditions[] = "(name LIKE ? OR cpf LIKE ? OR email LIKE ?)";
            $search = "%{$searchTerm}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->query($sql, $params);
        
        if (!$stmt) return 0;
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Cria novo paciente
     */
    public function createPatient(array $data): int|false
    {
        return $this->insert($data);
    }

    /**
     * Atualiza dados do paciente
     */
    public function updatePatient(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    /**
     * Conta pacientes por terapeuta
     */
    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
    }
}
