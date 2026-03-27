<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Paciente - <?php echo \Config\Config::APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?php echo \Config\Config::APP_URL; ?>/public/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span>Terapia</span>
                </div>
                <button class="btn-menu-toggle" id="menuToggle">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=dashboard" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients" class="nav-link active">
                        <i class="fas fa-users"></i>
                        <span>Pacientes</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=calendar" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Agenda</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=payments" class="nav-link">
                        <i class="fas fa-wallet"></i>
                        <span>Valores</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports" class="nav-link">
                        <i class="fas fa-file-chart-line"></i>
                        <span>Relatórios</span>
                    </a>
                </li>

                <li class="nav-divider"></li>

                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/index.php?action=logout" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Sair</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <p class="user-name"><?php echo \Helpers\Auth::userName(); ?></p>
                        <p class="user-role">Administrador</p>
                    </div>
                </div>
            </div>
        </nav>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- TOPBAR -->
            <header class="topbar">
                <button class="btn-menu" id="menuBtn">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="topbar-actions">
                    <div class="notification-bell">
                        <i class="fas fa-bell"></i>
                        <span class="badge">0</span>
                    </div>

                    <div class="user-menu">
                        <img src="https://via.placeholder.com/40" alt="Avatar" class="user-avatar">
                    </div>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <div class="page-content">
                <!-- Page Header -->
                <div class="mb-30">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients" class="btn btn-secondary mb-20">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <h1 class="mb-0">Novo Paciente</h1>
                    <p class="text-muted mt-2">Preencha os dados do novo paciente</p>
                </div>

                <!-- Form Card -->
                <div class="card" style="max-width: 900px;">
                    <form id="patientForm" method="POST" action="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=store">
                        <!-- Dados Pessoais -->
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="fas fa-user-circle"></i> Dados Pessoais
                            </h4>
                        </div>

                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name" class="form-label">Nome Completo *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="name"
                                        name="name"
                                        required
                                        placeholder="Digite o nome completo"
                                    >
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="birth_date" class="form-label">Data de Nascimento *</label>
                                    <input
                                        type="date"
                                        class="form-control"
                                        id="birth_date"
                                        name="birth_date"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="age_display" class="form-label">Idade</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="age_display"
                                        readonly
                                        placeholder="Calculada automaticamente"
                                        style="background:#f9fafb;"
                                    >
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="cpf" class="form-label">CPF *</label>
                                    <input
                                        type="text"
                                        class="form-control mask-cpf"
                                        id="cpf"
                                        name="cpf"
                                        placeholder="000.000.000-00"
                                        required
                                        maxlength="14"
                                        autocomplete="off"
                                    >
                                    <div id="cpf-feedback" class="invalid-feedback">CPF inválido.</div>
                                    <small class="text-muted" id="cpf-hint">Formato: 000.000.000-00</small>
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">Telefone *</label>
                                    <input
                                        type="text"
                                        class="form-control mask-phone"
                                        id="phone"
                                        name="phone"
                                        placeholder="(00) 0000-0000"
                                        required
                                        maxlength="15"
                                    >
                                    <small class="text-muted">Formato: (00) 0000-0000</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input
                                        type="email"
                                        class="form-control"
                                        id="email"
                                        name="email"
                                        placeholder="seu@email.com"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="marital_status" class="form-label">Estado Civil</label>
                                    <select class="form-control" id="marital_status" name="marital_status">
                                        <option value="">Selecione...</option>
                                        <option value="solteiro">Solteiro(a)</option>
                                        <option value="casado">Casado(a)</option>
                                        <option value="divorciado">Divorciado(a)</option>
                                        <option value="viuvo">Viúvo(a)</option>
                                        <option value="uniao_estavel">União Estável</option>
                                        <option value="separado">Separado(a)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="children" class="form-label">Filhos</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="children"
                                    name="children"
                                    placeholder="Ex: 2 filhos, idades 5 e 8 anos"
                                >
                            </div>
                        </div>

                        <!-- Endereço -->
                        <div class="card-body border-top">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i class="fas fa-map-marker-alt"></i> Endereço
                                </h4>
                            </div>

                            <div class="form-row mt-3">
                                <div class="form-group" style="flex: 0 0 calc(50% - 10px); margin-right: 10px;">
                                    <label for="cep" class="form-label">CEP *</label>
                                    <div class="input-group">
                                        <input 
                                            type="text" 
                                            class="form-control mask-cep" 
                                            id="cep" 
                                            name="cep" 
                                            placeholder="00000-000"
                                            maxlength="9"
                                            required
                                        >
                                        <button type="button" class="btn btn-outline-primary" id="searchCepBtn">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                    </div>
                                    <small class="text-muted">Formato: 00000-000</small>
                                </div>

                                <div class="form-group" style="flex: 1;">
                                    <label for="address" class="form-label">Logradouro *</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="address" 
                                        name="address" 
                                        placeholder="Rua, Avenida, etc"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group" style="flex: 0 0 120px; margin-right: 10px;">
                                    <label for="number" class="form-label">Número *</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="number" 
                                        name="number" 
                                        placeholder="123"
                                        required
                                    >
                                </div>

                                <div class="form-group" style="flex: 1;">
                                    <label for="complement" class="form-label">Complemento</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="complement" 
                                        name="complement" 
                                        placeholder="Apto 101, Bloco A, etc"
                                    >
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="neighborhood" class="form-label">Bairro *</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="neighborhood" 
                                        name="neighborhood" 
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="city" class="form-label">Cidade *</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="city" 
                                        name="city" 
                                        required
                                    >
                                </div>

                                <div class="form-group" style="flex: 0 0 120px;">
                                    <label for="state" class="form-label">Estado *</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="state" 
                                        name="state" 
                                        maxlength="2"
                                        required
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Saúde -->
                        <div class="card-body border-top">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i class="fas fa-heartbeat"></i> Saúde
                                </h4>
                            </div>

                            <div class="form-row mt-3">
                                <div class="form-group d-flex align-items-center gap-3" style="flex-direction:row; align-items:center;">
                                    <div class="form-check me-4">
                                        <input class="form-check-input" type="checkbox" id="depression" name="depression" value="1">
                                        <label class="form-check-label" for="depression">Depressão</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="anxiety" name="anxiety" value="1">
                                        <label class="form-check-label" for="anxiety">Ansiedade</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="medications" class="form-label">Medicamentos</label>
                                <textarea
                                    class="form-control"
                                    id="medications"
                                    name="medications"
                                    rows="2"
                                    placeholder="Descreva os medicamentos em uso"
                                ></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="bowel" class="form-label">Intestino</label>
                                    <textarea
                                        class="form-control"
                                        id="bowel"
                                        name="bowel"
                                        rows="2"
                                        placeholder="Descreva o funcionamento intestinal"
                                    ></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="menstruation" class="form-label">Menstruação</label>
                                    <textarea
                                        class="form-control"
                                        id="menstruation"
                                        name="menstruation"
                                        rows="2"
                                        placeholder="Descreva"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Histórico Terapêutico -->
                        <div class="card-body border-top">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i class="fas fa-brain"></i> Histórico Terapêutico
                                </h4>
                            </div>

                            <div class="mt-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="had_therapy" name="had_therapy" value="1">
                                    <label class="form-check-label fw-semibold" for="had_therapy">Já fez terapia?</label>
                                </div>

                                <div class="form-group" id="therapy_duration_group" style="display:none;">
                                    <label for="therapy_duration" class="form-label">Quanto tempo?</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="therapy_duration"
                                        name="therapy_duration"
                                        placeholder="Ex: 6 meses, 1 ano, 2 anos"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="therapy_reason" class="form-label">O que fez buscar terapia?</label>
                                    <textarea
                                        class="form-control"
                                        id="therapy_reason"
                                        name="therapy_reason"
                                        rows="3"
                                        placeholder="Descreva o motivo que levou a buscar a terapia"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Observações -->
                        <div class="card-body border-top">
                            <div class="form-group">
                                <label for="observations" class="form-label">Observações</label>
                                <textarea
                                    class="form-control"
                                    id="observations"
                                    name="observations"
                                    rows="4"
                                    placeholder="Observações adicionais sobre o paciente"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="card-body border-top d-flex gap-2 justify-content-end">
                            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Paciente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.0/dist/sweetalert2.all.min.js"></script>
    <script src="<?php echo \Config\Config::APP_URL; ?>/public/js/main.js"></script>

    <script>
        // Menu toggle
        $('#menuBtn').on('click', function() {
            $('.sidebar').toggleClass('active');
        });

        $('#menuToggle').on('click', function() {
            $('.sidebar').removeClass('active');
        });

        // Buscar CEP
        $('#searchCepBtn').on('click', function() {
            const cep = $('#cep').val();
            if (cep) {
                searchCEP(cep);
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'CEP pode ário',
                    text: 'Por favor, digite um CEP',
                    confirmButtonColor: '#f59e0b'
                });
            }
        });

        // Enter para buscar CEP
        $('#cep').on('keypress', function(e) {
            if (e.keyCode === 13) {
                e.preventDefault();
                $('#searchCepBtn').click();
            }
        });

        // ── Validação de CPF ──────────────────────────────────────
        function validarCPF(cpf) {
            cpf = cpf.replace(/\D/g, '');
            if (cpf.length !== 11) return false;
            if (/^(\d)\1{10}$/.test(cpf)) return false; // todos iguais

            var soma = 0;
            for (var i = 0; i < 9; i++) soma += parseInt(cpf[i]) * (10 - i);
            var resto = (soma * 10) % 11;
            if (resto === 10 || resto === 11) resto = 0;
            if (resto !== parseInt(cpf[9])) return false;

            soma = 0;
            for (var i = 0; i < 10; i++) soma += parseInt(cpf[i]) * (11 - i);
            resto = (soma * 10) % 11;
            if (resto === 10 || resto === 11) resto = 0;
            return resto === parseInt(cpf[10]);
        }

        function setCpfState(valid) {
            var $input = $('#cpf');
            var $hint  = $('#cpf-hint');
            if (valid) {
                $input.removeClass('is-invalid').addClass('is-valid');
                $hint.hide();
            } else {
                $input.removeClass('is-valid').addClass('is-invalid');
                $hint.hide();
            }
        }

        $('#cpf').on('blur', function() {
            var raw = $(this).val();
            if (raw.replace(/\D/g, '').length === 0) return; // vazio, não valida ainda
            setCpfState(validarCPF(raw));
        });

        $('#cpf').on('input', function() {
            // Remove feedback ao digitar de novo
            $(this).removeClass('is-valid is-invalid');
            $('#cpf-hint').show();
        });

        // Calcular idade a partir da data de nascimento
        function calcularIdade(dataNasc) {
            if (!dataNasc) return '';
            var hoje = new Date();
            var nasc = new Date(dataNasc);
            var idade = hoje.getFullYear() - nasc.getFullYear();
            var m = hoje.getMonth() - nasc.getMonth();
            if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) idade--;
            return idade >= 0 ? idade + ' anos' : '';
        }

        $('#birth_date').on('change', function() {
            $('#age_display').val(calcularIdade($(this).val()));
        });

        // Mostrar/ocultar campo "quanto tempo" conforme checkbox
        $('#had_therapy').on('change', function() {
            if ($(this).is(':checked')) {
                $('#therapy_duration_group').show();
            } else {
                $('#therapy_duration_group').hide();
                $('#therapy_duration').val('');
            }
        });

        // Form submit via AJAX
        $('#patientForm').on('submit', function(e) {
            e.preventDefault();
            var cpf = $('#cpf').val();
            if (!validarCPF(cpf)) {
                setCpfState(false);
                $('#cpf').focus();
                Swal.fire({
                    icon: 'warning',
                    title: 'CPF Inválido',
                    text: 'Por favor, informe um CPF válido.',
                    confirmButtonColor: '#f59e0b'
                });
                return;
            }
            submitFormAjax(this);
        });

        // Inicializar máscaras
        $(document).ready(function() {
            initializeMasks();
        });
    </script>
</body>
</html>
