<?php

namespace App\Models;

use Classes\Model;

class PatientRecord extends Model
{
    protected string $table = 'patient_records';

    /**
     * Busca atendimentos do paciente
     */
    public function findByPatient(int $patientId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE patient_id = ? ORDER BY record_date DESC";
        $stmt = $this->query($sql, [$patientId]);
        
        if (!$stmt) return [];
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Cria novo atendimento
     */
    public function createRecord(array $data): int|false
    {
        return $this->insert($data);
    }

    /**
     * Atualiza atendimento
     */
    public function updateRecord(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    /**
     * Busca atendimentos entre datas
     */
    public function getByDateRange(string $startDate, string $endDate): array
    {
        $sql = "SELECT pr.*, p.name as patient_name FROM {$this->table} pr
                JOIN patients p ON pr.patient_id = p.id
                WHERE DATE(pr.record_date) >= ? AND DATE(pr.record_date) <= ?
                ORDER BY pr.record_date DESC";
        $stmt = $this->query($sql, [$startDate, $endDate]);
        
        if (!$stmt) return [];
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Conta atendimentos de um paciente
     */
    public function countByPatient(int $patientId): int
    {
        return $this->count("patient_id = ?", [$patientId]);
    }
}
