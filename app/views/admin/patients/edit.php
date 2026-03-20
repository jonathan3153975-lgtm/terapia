<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Paciente - <?php echo \Config\Config::APP_NAME; ?></title>
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
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=show&id=<?php echo $patient['id']; ?>" class="btn btn-secondary mb-20">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <h1>Editar Paciente</h1>
                    <p class="text-muted mt-2">Preencha os dados do novo paciente</p>
                </div>

                <!-- Form Card -->
                <div class="card" style="max-width: 900px;">
                    <form id="patientForm" method="POST" action="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=update">
                        <input type="hidden" name="id" value="<?php echo $patient['id']; ?>">
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
                                        value="<?php echo htmlspecialchars($patient['name']); ?>"
                                        required
                                        placeholder="Digite o nome completo"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="birth_date" class="form-label">Data de Nascimento *</label>
                                    <input 
                                        type="date" 
                                        class="form-control" 
                                        id="birth_date" 
                                        name="birth_date" 
                                        value="<?php echo $patient['birth_date']; ?>"
                                        required
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
                                        value="<?php echo htmlspecialchars($patient['cpf']); ?>"
                                        placeholder="000.000.000-00"
                                        required
                                        maxlength="14"
                                    >
                                    <small class="text-muted">Formato: 000.000.000-00</small>
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">Telefone *</label>
                                    <input 
                                        type="text" 
                                        class="form-control mask-phone" 
                                        id="phone" 
                                        name="phone" 
                                        value="<?php echo htmlspecialchars($patient['phone']); ?>"
                                        placeholder="(00) 0000-0000"
                                        required
                                        maxlength="15"
                                    >
                                    <small class="text-muted">Formato: (00) 0000-0000</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">E-mail</label>
                                <input 
                                    type="email" 
                                    class="form-control" 
                                    id="email" 
                                    name="email" 
                                    value="<?php echo htmlspecialchars($patient['email']); ?>"
                                    placeholder="seu@email.com"
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
                                            value="<?php echo htmlspecialchars($patient['cep']); ?>"
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
                                        value="<?php echo htmlspecialchars($patient['address']); ?>"
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
                                        value="<?php echo htmlspecialchars($patient['number']); ?>"
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
                                        value="<?php echo htmlspecialchars($patient['complement']); ?>"
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
                                        value="<?php echo htmlspecialchars($patient['neighborhood']); ?>"
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
                                        value="<?php echo htmlspecialchars($patient['city']); ?>"
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
                                        value="<?php echo htmlspecialchars($patient['state']); ?>"
                                        maxlength="2"
                                        required
                                    >
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
                                ><?php echo htmlspecialchars($patient['observations']); ?></textarea>
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

        // Form submit via AJAX
        $('#patientForm').on('submit', function(e) {
            e.preventDefault();
            submitFormAjax(this);
        });

        // Inicializar máscaras
        $(document).ready(function() {
            initializeMasks();
        });
    </script>
</body>
</html>
