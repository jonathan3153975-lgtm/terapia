<?php
use App\Models\User;
use Helpers\Auth;

$currentAction = $_GET['action'] ?? '';
$role = (string) Auth::role();
$isPatientPreview = Auth::isPatientPreviewActive();
$displayRole = $isPatientPreview ? 'patient' : $role;
$displayName = $isPatientPreview ? (Auth::patientPreviewName() ?? Auth::name()) : Auth::name();
$therapistBrand = null;
$therapistBrandId = (int) (Auth::therapistId() ?? 0);
if ($therapistBrandId > 0) {
    $therapistBrand = (new User())->findTherapistById($therapistBrandId);
}

$sidebarBrandName = 'Tera-Tech';
$sidebarLogoPath = trim((string) ($therapistBrand['company_logo_path'] ?? ''));
$sidebarLogoUrl = $sidebarLogoPath !== '' ? ($appUrl . '/' . ltrim($sidebarLogoPath, '/')) : '';
?>
<aside class="app-sidebar">
  <div class="sidebar-brand">
    <div class="sidebar-brand-logo <?php echo $sidebarLogoUrl !== '' ? 'has-image' : ''; ?>">
      <?php if ($sidebarLogoUrl !== ''): ?>
        <img class="sidebar-brand-logo-image" src="<?php echo htmlspecialchars($sidebarLogoUrl); ?>" alt="Logo da empresa" width="46" height="46">
      <?php else: ?>
        <i class="fa-solid fa-wave-square"></i>
      <?php endif; ?>
    </div>
    <div class="sidebar-brand-copy">
      <span class="sidebar-brand-title"><?php echo $sidebarBrandName; ?></span>
      <small class="sidebar-brand-subtitle">Plataforma terapêutica</small>
    </div>
  </div>

  <div class="sidebar-user">
    <?php echo htmlspecialchars((string) $displayName); ?>
    <?php if ($isPatientPreview): ?>
      <div class="small text-warning-emphasis mt-1">Modo visualização do paciente</div>
    <?php endif; ?>
  </div>

  <nav class="sidebar-nav">
    <?php if ($displayRole === 'super_admin'): ?>
      <a class="sidebar-link <?php echo $currentAction === 'admin-dashboard' ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=admin-dashboard"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapists') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapists"><i class="fa-solid fa-user-doctor"></i><span>Terapeutas</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'patient-packages') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=patient-packages"><i class="fa-solid fa-box-open"></i><span>Pacotes pacientes</span></a>
    <?php elseif ($displayRole === 'therapist'): ?>
      <a class="sidebar-link <?php echo $currentAction === 'therapist-dashboard' ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-dashboard"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-schedule') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-schedule"><i class="fa-solid fa-calendar-days"></i><span>Agenda</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-materials') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials"><i class="fa-solid fa-book-open"></i><span>Materiais</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-books') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books"><i class="fa-solid fa-book"></i><span>Livros</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-teratube') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-teratube"><i class="fa-solid fa-circle-play"></i><span>teraTube</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-predefined-tasks') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-predefined-tasks"><i class="fa-solid fa-list-check"></i><span>Tarefas pré-definidas</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-messages') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-messages"><i class="fa-solid fa-envelope-open-text"></i><span>Mensagens diárias</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-devotionals') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals"><i class="fa-solid fa-sun"></i><span>Devocional</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-faith-words') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-faith-words"><i class="fa-solid fa-cross"></i><span>Pai, fala comigo</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-guided-meditations') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-guided-meditations"><i class="fa-solid fa-headphones"></i><span>Meditação guiada</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-prayers') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-prayers"><i class="fa-solid fa-hands-praying"></i><span>Orações</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-healing-letters') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-healing-letters"><i class="fa-solid fa-clover"></i><span>Cartas de cura</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'therapist-financial') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-financial"><i class="fa-solid fa-wallet"></i><span>Financeiro</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'virtual-tasks') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=virtual-tasks"><i class="fa-solid fa-star"></i><span>Tarefas dinâmicas</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'patients') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=patients"><i class="fa-solid fa-users"></i><span>Pacientes</span></a>
      <a class="sidebar-link <?php echo $currentAction === 'patients-preview-menu' ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-preview-menu"><i class="fa-solid fa-right-to-bracket"></i><span>Acessar como paciente</span></a>
    <?php elseif ($displayRole === 'patient'): ?>
      <?php $patientFreeTier = !$isPatientPreview && !($_SESSION['patient_has_active_plan'] ?? true); ?>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'subscription-') ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=subscription-plans"><i class="fa-solid fa-crown"></i><span>Minha assinatura</span></a>
      <a class="sidebar-link <?php echo $currentAction === 'dashboard' ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=dashboard"><i class="fa-solid fa-gauge"></i><span>Dashboard</span></a>
      <a class="sidebar-link <?php echo $currentAction === 'tasks' ? 'active' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=tasks"><i class="fa-solid fa-list-check"></i><span>Minhas tarefas</span></a>
      <a class="sidebar-link <?php echo $currentAction === 'materials' ? 'active' : ''; ?> <?php echo $patientFreeTier ? 'sidebar-link--locked' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=materials"><?php if ($patientFreeTier): ?><i class="fa-solid fa-lock"></i><?php else: ?><i class="fa-solid fa-book"></i><?php endif; ?><span>Meus materiais</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'book') || $currentAction === 'books' ? 'active' : ''; ?> <?php echo $patientFreeTier ? 'sidebar-link--locked' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=books"><?php if ($patientFreeTier): ?><i class="fa-solid fa-lock"></i><?php else: ?><i class="fa-solid fa-book-open-reader"></i><?php endif; ?><span>Livros</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'teratube') ? 'active' : ''; ?> <?php echo $patientFreeTier ? 'sidebar-link--locked' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=teratube"><?php if ($patientFreeTier): ?><i class="fa-solid fa-lock"></i><?php else: ?><i class="fa-solid fa-circle-play"></i><?php endif; ?><span>teraTube</span></a>
      <a class="sidebar-link <?php echo $currentAction === 'my-contents' ? 'active' : ''; ?> <?php echo $patientFreeTier ? 'sidebar-link--locked' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=my-contents"><?php if ($patientFreeTier): ?><i class="fa-solid fa-lock"></i><?php else: ?><i class="fa-solid fa-bookmark"></i><?php endif; ?><span>Meus conteúdos</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'messenger') ? 'active' : ''; ?> <?php echo $patientFreeTier ? 'sidebar-link--locked' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=messenger"><?php if ($patientFreeTier): ?><i class="fa-solid fa-lock"></i><?php else: ?><i class="fa-solid fa-box-open"></i><?php endif; ?><span>Mensageiro</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'gratitude') ? 'active' : ''; ?> <?php echo $patientFreeTier ? 'sidebar-link--locked' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=gratitude"><?php if ($patientFreeTier): ?><i class="fa-solid fa-lock"></i><?php else: ?><i class="fa-solid fa-heart"></i><?php endif; ?><span>Diário da gratidão</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'father-word') ? 'active' : ''; ?> <?php echo $patientFreeTier ? 'sidebar-link--locked' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=father-word"><?php if ($patientFreeTier): ?><i class="fa-solid fa-lock"></i><?php else: ?><i class="fa-solid fa-book-bible"></i><?php endif; ?><span>Pai, fala comigo</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'guided-meditation') || $currentAction === 'guided-meditations' ? 'active' : ''; ?> <?php echo $patientFreeTier ? 'sidebar-link--locked' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=guided-meditations"><?php if ($patientFreeTier): ?><i class="fa-solid fa-lock"></i><?php else: ?><i class="fa-solid fa-compact-disc"></i><?php endif; ?><span>Meditação guiada</span></a>
      <a class="sidebar-link <?php echo $currentAction === 'breathing-game' ? 'active' : ''; ?> <?php echo $patientFreeTier ? 'sidebar-link--locked' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=breathing-game"><?php if ($patientFreeTier): ?><i class="fa-solid fa-lock"></i><?php else: ?><i class="fa-solid fa-lungs"></i><?php endif; ?><span>Exercício de respiração</span></a>
      <a class="sidebar-link <?php echo str_starts_with($currentAction, 'prayer') || $currentAction === 'prayers' ? 'active' : ''; ?> <?php echo $patientFreeTier ? 'sidebar-link--locked' : ''; ?>" href="<?php echo $appUrl; ?>/patient.php?action=prayers"><?php if ($patientFreeTier): ?><i class="fa-solid fa-lock"></i><?php else: ?><i class="fa-solid fa-hands-praying"></i><?php endif; ?><span>Orações</span></a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    <?php if ($isPatientPreview): ?>
      <a class="btn btn-sm btn-outline-warning w-100 mb-2" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-preview-stop"><i class="fa-solid fa-rotate-left"></i> Voltar ao terapeuta</a>
    <?php endif; ?>
    <a class="btn btn-sm btn-outline-danger w-100" href="<?php echo $appUrl; ?>/index.php?action=logout"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
  </div>
</aside>

