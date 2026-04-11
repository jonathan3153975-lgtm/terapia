<?php

namespace App\Models;

use Classes\Model;

class PatientSubscription extends Model
{
    protected string $table = 'patient_subscriptions';

    public function findLatestByPatient(int $patientId): ?array
    {
        $stmt = $this->query(
            "SELECT ps.*, p.name AS plan_name, p.description_text, u.name AS therapist_name
             FROM patient_subscriptions ps
             INNER JOIN plans p ON p.id = ps.plan_id
             INNER JOIN users u ON u.id = ps.therapist_id
             WHERE ps.patient_id = ?
             ORDER BY ps.id DESC
             LIMIT 1",
            [$patientId]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findActiveByPatient(int $patientId): ?array
    {
        $stmt = $this->query(
            "SELECT ps.*, p.name AS plan_name, p.description_text, u.name AS therapist_name
             FROM patient_subscriptions ps
             INNER JOIN plans p ON p.id = ps.plan_id
             INNER JOIN users u ON u.id = ps.therapist_id
             WHERE ps.patient_id = ?
               AND ps.status = 'active'
               AND (ps.ends_at IS NULL OR ps.ends_at >= NOW())
             ORDER BY ps.id DESC
             LIMIT 1",
            [$patientId]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByProviderReference(string $providerReference): ?array
    {
        $stmt = $this->query(
            'SELECT * FROM patient_subscriptions WHERE provider_reference = ? LIMIT 1',
            [$providerReference]
        );

        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function markExpiredSubscriptions(): void
    {
        $this->query(
            "UPDATE patient_subscriptions
             SET status = 'expired', updated_at = NOW()
             WHERE status = 'active' AND ends_at IS NOT NULL AND ends_at < NOW()"
        );
    }

    public function activateById(int $subscriptionId, ?int $paymentId, ?string $paidAt = null): bool
    {
        $subscription = $this->findById($subscriptionId);
        if (!$subscription) {
            return false;
        }

        $cycle = (string) ($subscription['billing_cycle'] ?? 'mensal');
        $start = new \DateTimeImmutable();
        $end = match ($cycle) {
            'anual' => $start->modify('+1 year'),
            'semestral' => $start->modify('+6 months'),
            default => $start->modify('+1 month'),
        };

        return $this->updateById($subscriptionId, [
            'status' => 'active',
            'payment_id' => $paymentId,
            'starts_at' => $start->format('Y-m-d H:i:s'),
            'ends_at' => $end->format('Y-m-d H:i:s'),
            'paid_at' => $paidAt ?: $start->format('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function markStatusById(int $subscriptionId, string $status): bool
    {
        return $this->updateById($subscriptionId, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
