<?php
use Helpers\Auth;

$currentAction = $_GET['action'] ?? '';
$role = (string) Auth::role();
?>
<aside class="app-sidebar">
  <div class="sidebar-brand">
    <i class="fa-solid fa-wave-square"></i>
    <span>Terapia SaaS</span>
  </div>

  <div class="sidebar-user"><?php echo htmlspecialchars((string) Auth::name()); ?></div>

  <nav class="sidebar-nav">
    <?php if ($role === 'super_admin'): ?>
      <a class="sidebar-link <?php echo $currentAction === 'admin-dashboard' ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=admin-dashboard"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapists') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapists"><i class="fa-solid fa-user-doctor"></i><span>Terapeutas</span></a>
    <?php elseif ($role === 'therapist'): ?>
      <a class="sidebar-link <?php echo $currentAction === 'therapist-dashboard' ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-dashboard"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-schedule') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-schedule"><i class="fa-solid fa-calendar-days"></i><span>Agenda</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-materials') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials"><i class="fa-solid fa-book-open"></i><span>Materiais</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-messages') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-messages"><i class="fa-solid fa-envelope-open-text"></i><span>Mensagens diárias</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-faith-words') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-faith-words"><i class="fa-solid fa-cross"></i><span>Pai, fala comigo</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-financial') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-financial"><i class="fa-solid fa-wallet"></i><span>Financeiro</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'patients') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=patients"><i class="fa-solid fa-users"></i><span>Pacientes</span></a>
    <?php elseif ($role === 'patient'): ?>
      <a class="sidebar-link <?php echo $currentAction === 'dashboard' ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=dashboard"><i class="fa-solid fa-gauge"></i><span>Dashboard</span></a>
      <a class="sidebar-link <?php echo $currentAction === 'tasks' ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=tasks"><i class="fa-solid fa-list-check"></i><span>Minhas tarefas</span></a>
      <a class="sidebar-link <?php echo $currentAction === 'materials' ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=materials"><i class="fa-solid fa-book"></i><span>Meus materiais</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'messenger') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=messenger"><i class="fa-solid fa-box-open"></i><span>Mensageiro</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'father-word') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=father-word"><i class="fa-solid fa-book-bible"></i><span>Pai, fala comigo</span></a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    <button id="themeToggle" class="btn btn-sm btn-outline-secondary w-100 mb-2"><i class="fa-solid fa-circle-half-stroke"></i> Tema</button>
    <a class="btn btn-sm btn-outline-danger w-100" href="<?php echo $appUrl; ?>/index.php?action=logout"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
  </div>
</aside>
