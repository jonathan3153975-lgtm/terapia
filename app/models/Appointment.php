<?php

namespace App\Models;

use Classes\Model;

class Appointment extends Model
{
    protected string $table = 'appointments';

    /**
     * Busca agendamentos do paciente
     */
    public function findByPatient(int $patientId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE patient_id = ? ORDER BY appointment_date DESC";
        $stmt = $this->query($sql, [$patientId]);
        
        if (!$stmt) return [];
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Busca agendamentos por data
     */
    public function findByDate(string $date): array
    {
        $sql = "SELECT a.*, p.name as patient_name FROM {$this->table} a 
                JOIN patients p ON a.patient_id = p.id
                WHERE DATE(a.appointment_date) = ? 
                ORDER BY a.appointment_date";
        $stmt = $this->query($sql, [$date]);
        
        if (!$stmt) return [];
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Busca agendamentos entre datas
     */
    public function findBetweenDates(string $startDate, string $endDate): array
    {
        $sql = "SELECT a.*, p.name as patient_name FROM {$this->table} a 
                JOIN patients p ON a.patient_id = p.id
                WHERE DATE(a.appointment_date) >= ? AND DATE(a.appointment_date) <= ? 
                ORDER BY a.appointment_date";
        $stmt = $this->query($sql, [$startDate, $endDate]);
        
        if (!$stmt) return [];
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se tem horário conflitante
     */
    public function hasConflict(string $appointmentDate, int $appointmentId = 0): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE appointment_date = ? AND status != 'cancelled'";
        $params = [$appointmentDate];

        if ($appointmentId > 0) {
            $sql .= " AND id != ?";
            $params[] = $appointmentId;
        }

        $stmt = $this->query($sql, $params);
        
        if (!$stmt) return false;
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0) > 0;
    }

    /**
     * Busca agendamentos pendentes
     */
    public function findPending(): array
    {
        $sql = "SELECT a.*, p.name as patient_name FROM {$this->table} a 
                JOIN patients p ON a.patient_id = p.id
                WHERE a.status = 'pending' 
                ORDER BY a.appointment_date";
        $stmt = $this->query($sql);
        
        if (!$stmt) return [];
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
