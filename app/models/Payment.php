<?php

namespace App\Models;

use Classes\Model;

class Payment extends Model
{
    protected string $table = 'payments';

    public function findByAppointmentId(int $appointmentId): ?array
    {
        $stmt = $this->query('SELECT * FROM payments WHERE appointment_id = ? LIMIT 1', [$appointmentId]);
        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function ensurePendingForAppointment(int $therapistId, int $appointmentId, ?int $patientId): int|false
    {
        $existing = $this->findByAppointmentId($appointmentId);
        if ($existing) {
            return (int) $existing['id'];
        }

        return $this->insert([
            'therapist_id' => $therapistId,
            'appointment_id' => $appointmentId,
            'patient_id' => $patientId,
            'amount' => 0.00,
            'provider' => 'manual',
            'status' => 'pending',
            'paid_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function upsertAppointmentPayment(int $therapistId, int $appointmentId, ?int $patientId, float $amount, string $status): bool
    {
        $existing = $this->findByAppointmentId($appointmentId);
        $data = [
            'therapist_id' => $therapistId,
            'appointment_id' => $appointmentId,
            'patient_id' => $patientId,
            'amount' => $amount,
            'provider' => 'manual',
            'status' => $status,
            'paid_at' => $status === 'paid' ? date('Y-m-d H:i:s') : null,
        ];

        if ($existing) {
            return $this->updateById((int) $existing['id'], $data);
        }

        return (bool) $this->insert(array_merge($data, ['created_at' => date('Y-m-d H:i:s')]));
    }

    public function confirmPaymentByAppointment(int $therapistId, int $appointmentId): bool
    {
        $existing = $this->findByAppointmentId($appointmentId);
        if (!$existing) {
            return false;
        }

        return $this->updateById((int) $existing['id'], [
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function listAppointmentFinancialMonthly(int $therapistId, int $month, int $year): array
    {
        $stmt = $this->query(
            'SELECT
                a.id AS appointment_id,
                a.patient_id,
                a.guest_patient_name,
                a.session_date,
                a.description,
                p.name AS patient_name,
                pay.id AS payment_id,
                pay.amount,
                pay.status AS payment_status,
                pay.paid_at
             FROM appointments a
             LEFT JOIN patients p ON p.id = a.patient_id
             LEFT JOIN payments pay ON pay.appointment_id = a.id
             WHERE a.therapist_id = ?
               AND MONTH(a.session_date) = ?
               AND YEAR(a.session_date) = ?
             ORDER BY a.session_date ASC',
            [$therapistId, $month, $year]
        );

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
    }
}
