<?php

namespace App\Models;

use Classes\Model;

class Payment extends Model
{
    protected string $table = 'payments';

    /**
     * Busca pagamentos do paciente
     */
    public function findByPatient(int $patientId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE patient_id = ? ORDER BY created_at DESC";
        $stmt = $this->query($sql, [$patientId]);
        
        if (!$stmt) return [];
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Busca pagamentos com filtros
     */
    public function search(string $status = '', string $month = '', int $offset = 0, int $limit = 15): array
    {
        $sql = "SELECT p.*, pa.name as patient_name FROM {$this->table} p
                JOIN patients pa ON p.patient_id = pa.id WHERE 1=1";
        $params = [];

        if (!empty($status)) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }

        if (!empty($month)) {
            $sql .= " AND DATE_FORMAT(p.created_at, '%Y-%m') = ?";
            $params[] = $month;
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->query($sql, $params);
        
        if (!$stmt) return [];
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Conta pagamentos com filtro
     */
    public function countSearch(string $status = '', string $month = ''): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($status)) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        if (!empty($month)) {
            $sql .= " AND DATE_FORMAT(created_at, '%Y-%m') = ?";
            $params[] = $month;
        }

        $stmt = $this->query($sql, $params);
        
        if (!$stmt) return 0;
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Calcula total de pagamentos
     */
    public function getTotalAmount(string $status = '', string $month = ''): float
    {
        $sql = "SELECT SUM(amount) as total FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($status)) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        if (!empty($month)) {
            $sql .= " AND DATE_FORMAT(created_at, '%Y-%m') = ?";
            $params[] = $month;
        }

        $stmt = $this->query($sql, $params);
        
        if (!$stmt) return 0;
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Obtém pagamentos por mês
     */
    public function getByMonth(string $month): array
    {
        $sql = "SELECT p.*, pa.name as patient_name FROM {$this->table} p
                JOIN patients pa ON p.patient_id = pa.id
                WHERE DATE_FORMAT(p.created_at, '%Y-%m') = ?
                ORDER BY p.created_at DESC";
        $stmt = $this->query($sql, [$month]);
        
        if (!$stmt) return [];
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
