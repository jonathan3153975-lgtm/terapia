<?php

namespace App\Controllers;

use Classes\Controller;
use App\Models\Patient;
use App\Models\PatientRecord;
use Helpers\Auth;
use Helpers\Utils;
use Helpers\Validator;

class PatientController extends Controller
{
    private Patient $patientModel;
    private PatientRecord $patientRecordModel;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->patientModel = new Patient();
        $this->patientRecordModel = new PatientRecord();
    }

    /**
     * Lista todos os pacientes
     */
    public function index(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $patients = $this->patientModel->search($search, $offset, $limit);
        $totalPatients = $this->patientModel->countSearch($search);
        $totalPages = ceil($totalPatients / $limit);

        $this->view('admin/patients/index', [
            'patients' => $patients,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'totalPatients' => $totalPatients
        ]);
    }

    /**
     * Exibe formulário de criação de paciente
     */
    public function create(): void
    {
        $this->view('admin/patients/create');
    }

    /**
     * Processa criação de paciente
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $data = [
            'name' => Utils::sanitize($_POST['name'] ?? ''),
            'cpf' => Validator::removeCPFMask($_POST['cpf'] ?? ''),
            'birth_date' => $_POST['birth_date'] ?? '',
            'phone' => Validator::removePhoneMask($_POST['phone'] ?? ''),
            'email' => Utils::sanitize($_POST['email'] ?? ''),
            'address' => Utils::sanitize($_POST['address'] ?? ''),
            'number' => Utils::sanitize($_POST['number'] ?? ''),
            'complement' => Utils::sanitize($_POST['complement'] ?? ''),
            'neighborhood' => Utils::sanitize($_POST['neighborhood'] ?? ''),
            'city' => Utils::sanitize($_POST['city'] ?? ''),
            'state' => Utils::sanitize($_POST['state'] ?? ''),
            'cep' => Validator::removeCEPMask($_POST['cep'] ?? ''),
            'observations' => Utils::sanitize($_POST['observations'] ?? '')
        ];

        // Validações
        if (empty($data['name'])) {
            $this->error('Nome é obrigatório');
        }

        if (!Validator::validateCPF($data['cpf'])) {
            $this->error('CPF inválido');
        }

        if (!Validator::validatePhone($data['phone'])) {
            $this->error('Telefone inválido');
        }

        if (!empty($data['cep']) && !Validator::validateCEP($data['cep'])) {
            $this->error('CEP inválido');
        }

        if (!empty($data['email']) && !Utils::isValidEmail($data['email'])) {
            $this->error('Email inválido');
        }

        // Verifica se CPF já existe
        $existingPatient = $this->patientModel->findByCPF($data['cpf']);
        if ($existingPatient) {
            $this->error('Este CPF já está cadastrado');
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $patientId = $this->patientModel->createPatient($data);

        if ($patientId) {
            $this->success('Paciente cadastrado com sucesso', ['patientId' => $patientId]);
        } else {
            $this->error('Erro ao cadastrar paciente');
        }
    }

    /**
     * Exibe detalhes do paciente
     */
    public function show(): void
    {
        $patientId = (int)($_GET['id'] ?? 0);

        if ($patientId <= 0) {
            $this->error('ID inválido', 404);
        }

        $patient = $this->patientModel->findById($patientId);

        if (!$patient) {
            $this->error('Paciente não encontrado', 404);
        }

        $records = $this->patientRecordModel->findByPatient($patientId);

        $this->view('admin/patients/show', [
            'patient' => $patient,
            'records' => $records
        ]);
    }

    /**
     * Exibe formulário de edição
     */
    public function edit(): void
    {
        $patientId = (int)($_GET['id'] ?? 0);

        if ($patientId <= 0) {
            $this->error('ID inválido', 404);
        }

        $patient = $this->patientModel->findById($patientId);

        if (!$patient) {
            $this->error('Paciente não encontrado', 404);
        }

        $this->view('admin/patients/edit', ['patient' => $patient]);
    }

    /**
     * Processa atualização
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $patientId = (int)($_POST['id'] ?? 0);

        if ($patientId <= 0) {
            $this->error('ID inválido');
        }

        $patient = $this->patientModel->findById($patientId);

        if (!$patient) {
            $this->error('Paciente não encontrado');
        }

        $data = [
            'name' => Utils::sanitize($_POST['name'] ?? ''),
            'birth_date' => $_POST['birth_date'] ?? '',
            'phone' => Validator::removePhoneMask($_POST['phone'] ?? ''),
            'email' => Utils::sanitize($_POST['email'] ?? ''),
            'address' => Utils::sanitize($_POST['address'] ?? ''),
            'number' => Utils::sanitize($_POST['number'] ?? ''),
            'complement' => Utils::sanitize($_POST['complement'] ?? ''),
            'neighborhood' => Utils::sanitize($_POST['neighborhood'] ?? ''),
            'city' => Utils::sanitize($_POST['city'] ?? ''),
            'state' => Utils::sanitize($_POST['state'] ?? ''),
            'cep' => Validator::removeCEPMask($_POST['cep'] ?? ''),
            'observations' => Utils::sanitize($_POST['observations'] ?? '')
        ];

        // Validações
        if (empty($data['name'])) {
            $this->error('Nome é obrigatório');
        }

        if (!Validator::validatePhone($data['phone'])) {
            $this->error('Telefone inválido');
        }

        if (!empty($data['cep']) && !Validator::validateCEP($data['cep'])) {
            $this->error('CEP inválido');
        }

        if (!empty($data['email']) && !Utils::isValidEmail($data['email'])) {
            $this->error('Email inválido');
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($this->patientModel->updatePatient($patientId, $data)) {
            $this->success('Paciente atualizado com sucesso');
        } else {
            $this->error('Erro ao atualizar paciente');
        }
    }

    /**
     * Deleta paciente
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $patientId = (int)($_POST['id'] ?? 0);

        if ($patientId <= 0) {
            $this->error('ID inválido');
        }

        if ($this->patientModel->delete($patientId)) {
            $this->success('Paciente removido com sucesso');
        } else {
            $this->error('Erro ao remover paciente');
        }
    }

    /**
     * Busca CEP (API dos Correios)
     */
    public function searchCEP(): void
    {
        $cep = Validator::removeCEPMask($_GET['cep'] ?? '');

        if (!Validator::validateCEP($cep)) {
            $this->error('CEP inválido');
        }

        try {
            $url = "https://viacep.com.br/ws/{$cep}/json/";
            $response = file_get_contents($url);
            $data = json_decode($response, true);

            if (isset($data['erro'])) {
                $this->error('CEP não encontrado');
            }

            $this->success('CEP encontrado', [
                'address' => $data['logradouro'] ?? '',
                'neighborhood' => $data['bairro'] ?? '',
                'city' => $data['localidade'] ?? '',
                'state' => $data['uf'] ?? ''
            ]);
        } catch (\Exception $e) {
            $this->error('Erro ao buscar CEP');
        }
    }
}
