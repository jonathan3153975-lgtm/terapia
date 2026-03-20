<?php
// Recebe $activeMenu para destacar o item ativo (ex: 'patients', 'appointments', 'payments', 'reports')
$activeMenu = $activeMenu ?? '';
?>
<nav class="sidebar">
    <div class="sidebar-header">
        <div class="logo"><i class="fas fa-heartbeat"></i><span>Terapia</span></div>
        <button class="btn-menu-toggle" id="menuToggle"><i class="fas fa-times"></i></button>
    </div>
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=dashboard" class="nav-link <?php echo $activeMenu === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i><span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients" class="nav-link <?php echo $activeMenu === 'patients' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i><span>Pacientes</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=calendar" class="nav-link <?php echo $activeMenu === 'appointments' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i><span>Agenda</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=payments" class="nav-link <?php echo $activeMenu === 'payments' ? 'active' : ''; ?>">
                <i class="fas fa-wallet"></i><span>Valores</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports" class="nav-link <?php echo $activeMenu === 'reports' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i><span>Relatórios</span>
            </a>
        </li>
        <li class="nav-divider"></li>
        <li class="nav-item">
            <a href="<?php echo \Config\Config::APP_URL; ?>/index.php?action=logout" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i><span>Sair</span>
            </a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"><i class="fas fa-user"></i></div>
            <div class="user-details">
                <p class="user-name"><?php echo \Helpers\Auth::userName(); ?></p>
                <p class="user-role">Administrador</p>
            </div>
        </div>
    </div>
</nav>
