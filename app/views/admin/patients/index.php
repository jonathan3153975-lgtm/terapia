<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacientes - <?php echo \Config\Config::APP_NAME; ?></title>
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
                    <div class="search-box">
                        <input type="text" placeholder="Pesquisar..." class="search-input" id="searchInput">
                        <i class="fas fa-search"></i>
                    </div>

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
                <div class="d-flex justify-content-between align-items-center mb-30">
                    <div>
                        <h1 class="mb-0">Pacientes</h1>
                        <p class="text-muted mt-2">Total de <?php echo $totalPatients; ?> paciente(s) cadastrado(s)</p>
                    </div>
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Novo Paciente
                    </a>
                </div>

                <!-- Card com tabela -->
                <div class="card">
                    <!-- Search Bar -->
                    <div class="card-header">
                        <h4 class="card-title">Lista de Pacientes</h4>
                        <form method="GET" class="mt-3">
                            <input type="hidden" name="action" value="patients">
                            <div class="input-group">
                                <input 
                                    type="text" 
                                    name="search" 
                                    class="form-control" 
                                    placeholder="Buscar por nome, CPF ou email..."
                                    value="<?php echo htmlspecialchars($search); ?>"
                                >
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <?php if (!empty($search)): ?>
                                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Limpar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>CPF</th>
                                    <th>Telefone</th>
                                    <th>E-mail</th>
                                    <th>Data de Nascimento</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($patients)): ?>
                                    <?php foreach ($patients as $patient): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($patient['name']); ?></strong>
                                            </td>
                                            <td>
                                                <?php echo \Helpers\Validator::formatCPF($patient['cpf']); ?>
                                            </td>
                                            <td>
                                                <?php echo \Helpers\Validator::formatPhone($patient['phone']); ?>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($patient['email'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo \Helpers\Utils::formatDate($patient['birth_date']); ?></small>
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=show&id=<?php echo $patient['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Visualizar">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=edit&id=<?php echo $patient['id']; ?>" 
                                                       class="btn btn-sm btn-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=delete&id=<?php echo $patient['id']; ?>', '<?php echo htmlspecialchars($patient['name']); ?>')" title="Deletar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox"></i>
                                            <p>Nenhum paciente encontrado</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <small class="text-muted">
                                Mostrando página <?php echo $currentPage; ?> de <?php echo $totalPages; ?> 
                                (<?php echo $totalPatients; ?> total)
                            </small>
                            <nav>
                                <ul class="pagination mb-0">
                                    <?php if ($currentPage > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&page=<?php echo $currentPage - 1; ?>&search=<?php echo htmlspecialchars($search); ?>">
                                                Anterior
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                            <a class="page-link" href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($currentPage < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&page=<?php echo $currentPage + 1; ?>&search=<?php echo htmlspecialchars($search); ?>">
                                                Próxima
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
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

        // Search
        $('#searchInput').on('input', function() {
            // Implementar busca dinâmica se necessário
        });
    </script>
</body>
</html>
