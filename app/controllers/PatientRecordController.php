<?php

namespace App\Controllers;

use Classes\Controller;
use App\Models\PatientRecord;
use App\Models\Patient;
use Helpers\Auth;
use Helpers\Utils;

class PatientRecordController extends Controller
{
    private PatientRecord $patientRecordModel;
    private Patient $patientModel;

    public function __construct()
    {
        parent::__construct();
        Auth::requireAdmin();
        $this->patientRecordModel = new PatientRecord();
        $this->patientModel = new Patient();
    }

    /**
     * Lista atendimentos do paciente
     */
    public function index(): void
    {
        $patientId = (int)($_GET['patient_id'] ?? 0);

        if ($patientId <= 0) {
            $this->error('ID inválido', 404);
        }

        $patient = $this->patientModel->findById($patientId);

        if (!$patient) {
            $this->error('Paciente não encontrado', 404);
        }

        $records = $this->patientRecordModel->findByPatient($patientId);

        $this->view('admin/records/index', [
            'patient' => $patient,
            'records' => $records
        ]);
    }

    /**
     * Exibe formulário de criação
     */
    public function create(): void
    {
        $patientId = (int)($_GET['patient_id'] ?? 0);

        if ($patientId <= 0) {
            $this->error('ID inválido', 404);
        }

        $patient = $this->patientModel->findById($patientId);

        if (!$patient) {
            $this->error('Paciente não encontrado', 404);
        }

        $this->view('admin/records/create', ['patient' => $patient]);
    }

    /**
     * Processa criação de atendimento
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $patientId = (int)($_POST['patient_id'] ?? 0);

        if ($patientId <= 0) {
            $this->error('Patient ID inválido');
        }

        $patient = $this->patientModel->findById($patientId);

        if (!$patient) {
            $this->error('Paciente não encontrado');
        }

        $data = [
            'patient_id' => $patientId,
            'record_date' => $_POST['record_date'] ?? date('Y-m-d'),
            'notes' => $_POST['notes'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];

        if (empty($data['notes'])) {
            $this->error('Notas do atendimento são obrigatórias');
        }

        $recordId = $this->patientRecordModel->createRecord($data);

        if ($recordId) {
            $this->success('Atendimento registrado com sucesso', ['recordId' => $recordId]);
        } else {
            $this->error('Erro ao registrar atendimento');
        }
    }

    /**
     * Exibe detalhes do atendimento
     */
    public function show(): void
    {
        $recordId = (int)($_GET['id'] ?? 0);

        if ($recordId <= 0) {
            $this->error('ID inválido', 404);
        }

        $record = $this->patientRecordModel->findById($recordId);

        if (!$record) {
            $this->error('Atendimento não encontrado', 404);
        }

        $this->view('admin/records/show', ['record' => $record]);
    }

    /**
     * Exibe formulário de edição
     */
    public function edit(): void
    {
        $recordId = (int)($_GET['id'] ?? 0);

        if ($recordId <= 0) {
            $this->error('ID inválido', 404);
        }

        $record = $this->patientRecordModel->findById($recordId);

        if (!$record) {
            $this->error('Atendimento não encontrado', 404);
        }

        $patient = $this->patientModel->findById($record['patient_id']);

        $this->view('admin/records/edit', [
            'record' => $record,
            'patient' => $patient
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

        $recordId = (int)($_POST['id'] ?? 0);

        if ($recordId <= 0) {
            $this->error('ID inválido');
        }

        $record = $this->patientRecordModel->findById($recordId);

        if (!$record) {
            $this->error('Atendimento não encontrado');
        }

        $data = [
            'record_date' => $_POST['record_date'] ?? $record['record_date'],
            'notes' => $_POST['notes'] ?? '',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($data['notes'])) {
            $this->error('Notas do atendimento são obrigatórias');
        }

        if ($this->patientRecordModel->updateRecord($recordId, $data)) {
            $this->success('Atendimento atualizado com sucesso');
        } else {
            $this->error('Erro ao atualizar atendimento');
        }
    }

    /**
     * Deleta atendimento
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $recordId = (int)($_POST['id'] ?? 0);

        if ($recordId <= 0) {
            $this->error('ID inválido');
        }

        if ($this->patientRecordModel->delete($recordId)) {
            $this->success('Atendimento removido com sucesso');
        } else {
            $this->error('Erro ao remover atendimento');
        }
    }
}
