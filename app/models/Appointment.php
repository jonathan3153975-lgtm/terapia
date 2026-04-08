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
    public function findByDate(string $date, ?int $therapistId = null): array
    {
        $sql = "SELECT a.*, p.name as patient_name FROM {$this->table} a 
                JOIN patients p ON a.patient_id = p.id
                WHERE DATE(a.appointment_date) = ?";
        $params = [$date];
        if ($therapistId !== null) {
            $sql .= " AND a.therapist_id = ?";
            $params[] = $therapistId;
        }
        $sql .= "
                ORDER BY a.appointment_date";
        $stmt = $this->query($sql, $params);
        
        if (!$stmt) return [];
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Busca agendamentos entre datas
     */
    public function findBetweenDates(string $startDate, string $endDate, ?int $therapistId = null): array
    {
        $sql = "SELECT a.*, p.name as patient_name FROM {$this->table} a 
                JOIN patients p ON a.patient_id = p.id
                WHERE DATE(a.appointment_date) >= ? AND DATE(a.appointment_date) <= ?";
        $params = [$startDate, $endDate];
        if ($therapistId !== null) {
            $sql .= " AND a.therapist_id = ?";
            $params[] = $therapistId;
        }
        $sql .= " 
                ORDER BY a.appointment_date";
        $stmt = $this->query($sql, $params);
        
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
     * Próximos agendamentos (com nome do paciente)
     */
    public function findUpcoming(int $limit = 5, ?int $therapistId = null): array
    {
        $sql = "SELECT a.*, p.name as patient_name FROM {$this->table} a
                JOIN patients p ON a.patient_id = p.id
                WHERE a.appointment_date >= NOW() AND a.status != 'cancelled'";
        $params = [];
        if ($therapistId !== null) {
            $sql .= " AND a.therapist_id = ?";
            $params[] = $therapistId;
        }
        $sql .= "
                ORDER BY a.appointment_date ASC LIMIT {$limit}";
        $stmt = $this->query($sql, $params);
        if (!$stmt) return [];
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Agendamentos de hoje (com nome do paciente)
     */
    public function findToday(?int $therapistId = null): array
    {
        $today = date('Y-m-d');
        $sql = "SELECT a.*, p.name as patient_name FROM {$this->table} a
                JOIN patients p ON a.patient_id = p.id
                WHERE DATE(a.appointment_date) = ?";
        $params = [$today];
        if ($therapistId !== null) {
            $sql .= " AND a.therapist_id = ?";
            $params[] = $therapistId;
        }
        $sql .= "
                ORDER BY a.appointment_date ASC";
        $stmt = $this->query($sql, $params);
        if (!$stmt) return [];
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Contagem por status para gráfico
     */
    public function countByStatus(?int $therapistId = null): array
    {
        $statuses = ['confirmed', 'pending', 'cancelled', 'completed'];
        $result = [];
        foreach ($statuses as $s) {
            if ($therapistId !== null) {
                $result[$s] = $this->count("status = ? AND therapist_id = ?", [$s, $therapistId]);
                continue;
            }
            $result[$s] = $this->count("status = ?", [$s]);
        }
        return $result;
    }

    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
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
