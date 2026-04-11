<?php

namespace App\Controllers;

use Classes\Controller;
use Config\Config;
use Helpers\PatientSubscriptionPaymentSync;

class PaymentWebhookController extends Controller
{
    public function mercadoPago(): void
    {
        $secret = trim((string) Config::get('MP_WEBHOOK_SECRET', ''));
        if ($secret !== '') {
            $incomingToken = trim((string) ($_GET['token'] ?? ''));
            if (!hash_equals($secret, $incomingToken)) {
                $this->error('Webhook token inválido.', 401);
            }
        }

        $paymentId = (int) ($_GET['data_id'] ?? $_GET['id'] ?? 0);
        if ($paymentId <= 0) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode((string) $raw, true);
            if (is_array($decoded)) {
                $paymentId = (int) (($decoded['data']['id'] ?? 0));
            }
        }

        if ($paymentId <= 0) {
            $this->success('Webhook recebido sem pagamento para sincronizar.');
        }

        $sync = (new PatientSubscriptionPaymentSync())->syncByPaymentId($paymentId);
        if (($sync['ok'] ?? false) !== true) {
            $this->error((string) ($sync['message'] ?? 'Falha ao sincronizar assinatura.'), 422);
        }

        $this->success('Assinatura sincronizada.', [
            'payment_id' => $paymentId,
            'state' => (string) ($sync['state'] ?? 'pending'),
        ]);
    }
}
