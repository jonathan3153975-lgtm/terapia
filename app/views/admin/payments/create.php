<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Pagamento - <?php echo \Config\Config::APP_NAME; ?></title>
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
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients" class="nav-link">
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
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=payments" class="nav-link active">
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
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=payments" class="btn btn-secondary mb-20">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <h1>Novo Pagamento</h1>
                </div>

                <!-- Form Card -->
                <div class="card">
                    <form id="paymentForm" method="POST" action="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=payments&subaction=store">
                        <!-- Informações do Pagamento -->
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="fas fa-money-bill-wave"></i> Dados do Pagamento
                            </h4>
                        </div>

                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="patient_id" class="form-label">Paciente *</label>
                                    <select 
                                        id="patient_id" 
                                        name="patient_id" 
                                        class="form-control"
                                        required
                                    >
                                        <option value="">-- Selecione um paciente --</option>
                                        <?php foreach ($patients as $patient): ?>
                                        <option value="<?php echo $patient['id']; ?>">
                                            <?php echo htmlspecialchars($patient['name']); ?> (<?php echo htmlspecialchars($patient['cpf']); ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="amount" class="form-label">Valor (R$) *</label>
                                    <input
                                        type="text"
                                        class="form-control mask-money"
                                        id="amount"
                                        name="amount"
                                        placeholder="R$ 0,00"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="description" class="form-label">Descrição *</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="description" 
                                        name="description" 
                                        placeholder="Ex: Consulta de terapia"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="status" class="form-label">Status *</label>
                                    <select 
                                        id="status" 
                                        name="status" 
                                        class="form-control"
                                        required
                                    >
                                        <option value="pending">Pendente</option>
                                        <option value="paid">Pago</option>
                                        <option value="cancelled">Cancelado</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="card-body border-top d-flex gap-2 justify-content-end">
                            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=payments" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Pagamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.0/dist/sweetalert2.all.min.js"></script>
    <script src="<?php echo \Config\Config::APP_URL; ?>/public/js/main.js"></script>

    <script>
        $(document).ready(function() {
            initializeMasks();
        });

        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitFormAjax(this);
        });
    </script>
</body>
</html>