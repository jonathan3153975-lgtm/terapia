<?php

namespace Helpers;

use App\Models\Payment;
use App\Models\PatientSubscription;
use App\Models\User;
use Config\Config;

class PatientSubscriptionPaymentSync
{
    private MercadoPagoGateway $gateway;
    private Payment $paymentModel;
    private PatientSubscription $subscriptionModel;
    private User $userModel;

    public function __construct()
    {
        $this->gateway = new MercadoPagoGateway();
        $this->paymentModel = new Payment();
        $this->subscriptionModel = new PatientSubscription();
        $this->userModel = new User();
    }

    private function formatSubscriptionDurationLabel(array $subscription): string
    {
        return match ((string) ($subscription['billing_cycle'] ?? 'mensal')) {
            'anual' => '12 meses',
            'semestral' => '6 meses',
            default => '1 mês',
        };
    }

    private function sendSubscriptionConfirmation(array $subscription): void
    {
        $patientId = (int) ($subscription['patient_id'] ?? 0);
        $therapistId = (int) ($subscription['therapist_id'] ?? 0);
        if ($patientId <= 0 || $therapistId <= 0) {
            return;
        }

        $patientAccess = $this->userModel->findPatientAccessByTherapistAndPatient($therapistId, $patientId);
        if (!$patientAccess) {
            return;
        }

        $patientName = (string) ($patientAccess['name'] ?? 'Paciente');
        $patientEmail = trim((string) ($patientAccess['email'] ?? ''));
        if ($patientEmail === '') {
            return;
        }

        $endsAtLabel = !empty($subscription['ends_at'])
            ? date('d/m/Y H:i', strtotime((string) $subscription['ends_at']))
            : 'Conforme vigência da assinatura';

        try {
            $mail = new MailService();
            $mail->send(
                $patientEmail,
                $patientName,
                'Assinatura confirmada - Tera-Tech',
                EmailTemplate::subscriptionConfirmed(
                    $patientName,
                    $this->formatSubscriptionDurationLabel($subscription),
                    $endsAtLabel,
                    Config::get('APP_URL', '') . '/index.php?action=login'
                )
            );
        } catch (\Throwable $e) {
            error_log('[subscription-confirmation-email] ' . $e->getMessage());
        }
    }

    public function syncByPaymentId(int $paymentId): array
    {
        if ($paymentId <= 0) {
            return ['ok' => false, 'message' => 'Pagamento inválido.'];
        }

        $paymentResult = $this->gateway->getPaymentById($paymentId);
        if (($paymentResult['ok'] ?? false) !== true) {
            return ['ok' => false, 'message' => (string) ($paymentResult['message'] ?? 'Falha ao consultar pagamento.')];
        }

        $data = (array) ($paymentResult['data'] ?? []);
        $externalReference = trim((string) ($data['external_reference'] ?? ''));
        if ($externalReference === '') {
            return ['ok' => false, 'message' => 'Pagamento sem referência externa.'];
        }

        $localPayment = $this->paymentModel->findByProviderReference($externalReference);
        $subscription = $this->subscriptionModel->findByProviderReference($externalReference);

        if (!$localPayment || !$subscription) {
            return ['ok' => false, 'message' => 'Assinatura não encontrada para este pagamento.'];
        }

        $status = strtolower((string) ($data['status'] ?? 'pending'));
        $statusDetail = strtolower((string) ($data['status_detail'] ?? ''));

        if ($status === 'approved') {
            $paidAt = !empty($data['date_approved']) ? date('Y-m-d H:i:s', strtotime((string) $data['date_approved'])) : date('Y-m-d H:i:s');
            $shouldSendConfirmation = (string) ($localPayment['status'] ?? '') !== 'paid'
                && (string) ($subscription['status'] ?? '') !== 'active';

            $this->paymentModel->markStatusById((int) $localPayment['id'], 'paid', $paidAt);
            $this->subscriptionModel->activateById((int) $subscription['id'], (int) $localPayment['id'], $paidAt);

            if ($shouldSendConfirmation) {
                $updatedSubscription = $this->subscriptionModel->findById((int) $subscription['id']);
                if ($updatedSubscription) {
                    $this->sendSubscriptionConfirmation($updatedSubscription);
                }
            }

            return [
                'ok' => true,
                'state' => 'paid',
                'status' => $status,
                'detail' => $statusDetail,
                'subscription_id' => (int) $subscription['id'],
            ];
        }

        if (in_array($status, ['rejected', 'cancelled', 'refunded', 'charged_back'], true)) {
            $this->paymentModel->markStatusById((int) $localPayment['id'], 'failed');
            $this->subscriptionModel->markStatusById((int) $subscription['id'], $status === 'cancelled' ? 'canceled' : 'failed');

            return [
                'ok' => true,
                'state' => 'failed',
                'status' => $status,
                'detail' => $statusDetail,
                'subscription_id' => (int) $subscription['id'],
            ];
        }

        $this->paymentModel->markStatusById((int) $localPayment['id'], 'pending');
        $this->subscriptionModel->markStatusById((int) $subscription['id'], 'pending');

        return [
            'ok' => true,
            'state' => 'pending',
            'status' => $status,
            'detail' => $statusDetail,
            'subscription_id' => (int) $subscription['id'],
        ];
    }
}