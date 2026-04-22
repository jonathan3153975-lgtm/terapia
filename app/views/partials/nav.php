<?php $withSidebarLayout = true; ?>
<?php use Helpers\Auth; ?>
<?php $mobileTopbarTitle = trim((string) preg_replace('/\s*-\s*Tera-Tech\s*$/u', '', (string) ($title ?? 'Tera-Tech'))); ?>
<div class="app-layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
  <main class="app-content">
    <?php if (Auth::isPatientPreviewActive()): ?>
      <div class="preview-mode-banner">
        <div class="preview-mode-banner-text">
          <i class="fa-solid fa-user-check"></i>
          <span>Visualizando como paciente: <?php echo htmlspecialchars((string) (Auth::patientPreviewName() ?? 'Paciente')); ?></span>
        </div>
        <a class="btn btn-sm btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-preview-stop">
          Encerrar visualização
        </a>
      </div>
    <?php endif; ?>

    <div class="mobile-topbar d-lg-none mb-3">
      <button class="btn btn-outline-secondary mobile-topbar-toggle" id="sidebarToggle" type="button" aria-label="Abrir menu">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="mobile-topbar-copy">
        <span class="mobile-topbar-kicker">Espaço terapêutico</span>
        <span class="mobile-topbar-title"><?php echo htmlspecialchars($mobileTopbarTitle !== '' ? $mobileTopbarTitle : 'Tera-Tech'); ?></span>
      </div>
    </div>
    <div class="app-content-shell">
