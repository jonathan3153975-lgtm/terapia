<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo \Config\Config::APP_NAME; ?> - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.0/dist/quill.snow.css">
    <link href="<?php echo \Config\Config::APP_URL; ?>/public/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
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

        <div class="main-content">
            <header class="topbar">
                <button class="btn-menu" id="menuBtn">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="topbar-actions">
                    <div class="search-box">
                        <input type="text" placeholder="Pesquisar..." class="search-input">
                        <i class="fas fa-search"></i>
                    </div>

                    <div class="notification-bell">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>

                    <div class="user-menu">
                        <img src="https://via.placeholder.com/40" alt="Avatar" class="user-avatar">
                    </div>
                </div>
            </header>

            <div class="page-content">
                <?php
                // Será preenchido pelos controllers
                ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.0/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.0/dist/quill.js"></script>
    <script src="<?php echo \Config\Config::APP_URL; ?>/public/js/main.js"></script>
</body>
</html>
