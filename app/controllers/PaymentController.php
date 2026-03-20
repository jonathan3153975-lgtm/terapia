<?php

namespace App\Controllers;

use Classes\Controller;
use App\Models\Payment;
use App\Models\Patient;
use Helpers\Auth;
use Helpers\Utils;

class PaymentController extends Controller
{
    private Payment $paymentModel;
    private Patient $patientModel;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->paymentModel = new Payment();
        $this->patientModel = new Patient();
    }

    /**
     * Lista pagamentos com filtros
     */
    public function index(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $status = $_GET['status'] ?? '';
        $month = $_GET['month'] ?? '';
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $payments = $this->paymentModel->search($status, $month, $offset, $limit);
        $totalPayments = $this->paymentModel->countSearch($status, $month);
        $totalPages = ceil($totalPayments / $limit);
        $totalAmount = $this->paymentModel->getTotalAmount($status, $month);

        $this->view('admin/payments/index', [
            'payments' => $payments,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalPayments' => $totalPayments,
            'totalAmount' => $totalAmount,
            'statusFilter' => $status,
            'monthFilter' => $month
        ]);
    }

    /**
     * Exibe formulário de criação
     */
    public function create(): void
    {
        $patients = $this->patientModel->findAll();
        $this->view('admin/payments/create', ['patients' => $patients]);
    }

    /**
     * Processa criação de pagamento
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $patientId = (int)($_POST['patient_id'] ?? 0);
        $amount = Utils::parseMoneyToFloat($_POST['amount'] ?? '0');
        $description = Utils::sanitize($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'pending';

        if ($patientId <= 0) {
            $this->error('Paciente não selecionado');
        }

        if ($amount <= 0) {
            $this->error('Valor deve ser maior que zero');
        }

        if (empty($description)) {
            $this->error('Descrição é obrigatória');
        }

        $patient = $this->patientModel->findById($patientId);

        if (!$patient) {
            $this->error('Paciente não encontrado');
        }

        $data = [
            'patient_id' => $patientId,
            'amount' => $amount,
            'description' => $description,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $paymentId = $this->paymentModel->insert($data);

        if ($paymentId) {
            $this->success('Pagamento registrado com sucesso', [
                'redirect' => \Config\Config::APP_URL . '/dashboard.php?action=payments'
            ]);
        } else {
            $this->error('Erro ao registrar pagamento');
        }
    }

    /**
     * Exibe detalhes do pagamento
     */
    public function show(): void
    {
        $paymentId = (int)($_GET['id'] ?? 0);

        if ($paymentId <= 0) {
            $this->error('ID inválido', 404);
        }

        $payment = $this->paymentModel->findById($paymentId);

        if (!$payment) {
            $this->error('Pagamento não encontrado', 404);
        }

        $patient = $this->patientModel->findById($payment['patient_id']);

        $this->view('admin/payments/show', [
            'payment' => $payment,
            'patient' => $patient
        ]);
    }

    /**
     * Exibe formulário de edição
     */
    public function edit(): void
    {
        $paymentId = (int)($_GET['id'] ?? 0);

        if ($paymentId <= 0) {
            $this->error('ID inválido', 404);
        }

        $payment = $this->paymentModel->findById($paymentId);

        if (!$payment) {
            $this->error('Pagamento não encontrado', 404);
        }

        $patients = $this->patientModel->findAll();

        $this->view('admin/payments/edit', [
            'payment' => $payment,
            'patients' => $patients
        ]);
    }

    /**
     * Processa atualização
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $paymentId = (int)($_POST['id'] ?? 0);

        if ($paymentId <= 0) {
            $this->error('ID inválido');
        }

        $payment = $this->paymentModel->findById($paymentId);

        if (!$payment) {
            $this->error('Pagamento não encontrado');
        }

        $patientId = (int)($_POST['patient_id'] ?? $payment['patient_id']);
        $amount = Utils::parseMoneyToFloat($_POST['amount'] ?? '0');
        $description = Utils::sanitize($_POST['description'] ?? '');
        $status = $_POST['status'] ?? $payment['status'];

        if ($patientId <= 0) {
            $this->error('Paciente não selecionado');
        }

        if ($amount <= 0) {
            $this->error('Valor deve ser maior que zero');
        }

        if (empty($description)) {
            $this->error('Descrição é obrigatória');
        }

        $data = [
            'patient_id' => $patientId,
            'amount' => $amount,
            'description' => $description,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->paymentModel->update($paymentId, $data)) {
            $this->success('Pagamento atualizado com sucesso');
        } else {
            $this->error('Erro ao atualizar pagamento');
        }
    }

    /**
     * Deleta pagamento
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $paymentId = (int)($_POST['id'] ?? 0);

        if ($paymentId <= 0) {
            $this->error('ID inválido');
        }

        if ($this->paymentModel->delete($paymentId)) {
            $this->success('Pagamento removido com sucesso');
        } else {
            $this->error('Erro ao remover pagamento');
        }
    }
}
