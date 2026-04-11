<?php

namespace App\Models;

use Classes\Model;

class Appointment extends Model
{
    protected string $table = 'appointments';

    public function calendarByTherapist(int $therapistId, string $startDate, string $endDate): array
    {
        $stmt = $this->query(
            'SELECT a.*, p.name AS patient_name
             FROM appointments a
             LEFT JOIN patients p ON p.id = a.patient_id
             WHERE a.therapist_id = ?
               AND a.session_date BETWEEN ? AND ?
             ORDER BY a.session_date ASC',
            [$therapistId, $startDate . ' 00:00:00', $endDate . ' 23:59:59']
        );

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function hasConflictForTherapist(int $therapistId, string $sessionDate, ?int $ignoreAppointmentId = null): bool
    {
        $sql = 'SELECT COUNT(*) AS total FROM appointments WHERE therapist_id = ? AND session_date = ?';
        $params = [$therapistId, $sessionDate];

        if ($ignoreAppointmentId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreAppointmentId;
        }

        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return false;
        }

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0) > 0;
    }

    public function findByTherapistAndId(int $therapistId, int $appointmentId): ?array
    {
        $stmt = $this->query(
            'SELECT a.*, p.name AS patient_name
             FROM appointments a
             LEFT JOIN patients p ON p.id = a.patient_id
             WHERE a.id = ? AND a.therapist_id = ?
             LIMIT 1',
            [$appointmentId, $therapistId]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteByTherapistAndId(int $therapistId, int $appointmentId): bool
    {
        return (bool) $this->query(
            'DELETE FROM appointments WHERE id = ? AND therapist_id = ?',
            [$appointmentId, $therapistId]
        );
    }

    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
    }

    public function countCompletedByTherapist(int $therapistId): int
    {
        $stmt = $this->query(
            'SELECT COUNT(*) AS total FROM appointments WHERE therapist_id = ? AND session_date < NOW()',
            [$therapistId]
        );
        if (!$stmt) {
            return 0;
        }

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function countScheduledByTherapist(int $therapistId): int
    {
        $stmt = $this->query(
            'SELECT COUNT(*) AS total FROM appointments WHERE therapist_id = ? AND session_date >= NOW()',
            [$therapistId]
        );
        if (!$stmt) {
            return 0;
        }

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function countCreatedInMonthByTherapist(int $therapistId, string $yearMonth): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) AS total
             FROM appointments
             WHERE therapist_id = ?
               AND DATE_FORMAT(created_at, '%Y-%m') = ?",
            [$therapistId, $yearMonth]
        );
        if (!$stmt) {
            return 0;
        }

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function listByPatient(int $patientId): array
    {
        $stmt = $this->query('SELECT * FROM appointments WHERE patient_id = ? ORDER BY session_date DESC', [$patientId]);
        if (!$stmt) {
            return [];
        }
        return $stmt->fetchAll();
    }

    public function countCompletedByPatient(int $patientId): int
    {
        $stmt = $this->query(
            'SELECT COUNT(*) AS total
             FROM appointments
             WHERE patient_id = ?
               AND session_date <= NOW()',
            [$patientId]
        );
        if (!$stmt) {
            return 0;
        }
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function countCompletedInMonthByPatient(int $patientId, string $yearMonth): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) AS total
             FROM appointments
             WHERE patient_id = ?
               AND session_date <= NOW()
               AND DATE_FORMAT(session_date, '%Y-%m') = ?",
            [$patientId, $yearMonth]
        );

        if (!$stmt) {
            return 0;
        }

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function findNextByPatient(int $patientId): ?array
    {
        $stmt = $this->query(
            'SELECT *
             FROM appointments
             WHERE patient_id = ?
               AND session_date >= NOW()
             ORDER BY session_date ASC
             LIMIT 1',
            [$patientId]
        );
        if (!$stmt) {
            return null;
        }
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByTherapistPatientAndId(int $therapistId, int $patientId, int $appointmentId): ?array
    {
        $stmt = $this->query(
            'SELECT * FROM appointments WHERE id = ? AND therapist_id = ? AND patient_id = ? LIMIT 1',
            [$appointmentId, $therapistId, $patientId]
        );
        if (!$stmt) {
            return null;
        }
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteByTherapistPatientAndId(int $therapistId, int $patientId, int $appointmentId): bool
    {
        return (bool) $this->query(
            'DELETE FROM appointments WHERE id = ? AND therapist_id = ? AND patient_id = ?',
            [$appointmentId, $therapistId, $patientId]
        );
    }
}
