<?php

namespace Helpers;

use App\Models\Payment;
use App\Models\PatientSubscription;

class PatientSubscriptionPaymentSync
{
    private MercadoPagoGateway $gateway;
    private Payment $paymentModel;
    private PatientSubscription $subscriptionModel;

    public function __construct()
    {
        $this->gateway = new MercadoPagoGateway();
        $this->paymentModel = new Payment();
        $this->subscriptionModel = new PatientSubscription();
    }

    public function syncByPaymentId(int $paymentId): array
    {
        if ($paymentId <= 0) {
            return ['ok' => false, 'message' => 'Pagamento inválido.'];
        }

        $paymentResult = $this->gateway->getPaymentById($paymentId);
        if (($paymentResult['ok'] ?? false) !== true) {
            return ['ok' => false, 'message' => (string) ($paymentResult['message'] ?? 'Falha ao consultar pagamento.')] ;
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
            $this->paymentModel->markStatusById((int) $localPayment['id'], 'paid', $paidAt);
            $this->subscriptionModel->activateById((int) $subscription['id'], (int) $localPayment['id'], $paidAt);

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
