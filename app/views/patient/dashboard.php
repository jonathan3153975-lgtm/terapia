<?php $title = 'Portal do Paciente'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <?php
  $nextLabel = 'Sem sessão agendada';
  if (!empty($nextAppointment['session_date'])) {
    $nextLabel = date('d/m/Y H:i', strtotime((string) $nextAppointment['session_date']));
  }
  ?>

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="mb-0">Meu dashboard</h3>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=tasks"><i class="fa-solid fa-list-check me-1"></i>Minhas tarefas</a>
      <a class="btn btn-primary" href="<?php echo $appUrl; ?>/patient.php?action=materials"><i class="fa-solid fa-book me-1"></i>Meus materiais</a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-md-6 col-xl-3">
      <div class="card card-kpi h-100">
        <div class="card-body">
          <small class="text-muted">Dias desde o cadastro</small>
          <h2><?php echo (int) $daysSinceRegister; ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card card-kpi h-100">
        <div class="card-body">
          <small class="text-muted">Sessões realizadas</small>
          <h2><?php echo (int) $sessionsDone; ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card card-kpi h-100">
        <div class="card-body">
          <small class="text-muted">Lembrete de sessão</small>
          <div class="fw-semibold mt-2"><?php echo htmlspecialchars($nextLabel); ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card card-kpi h-100">
        <div class="card-body">
          <small class="text-muted">Tarefas recebidas</small>
          <h2><?php echo (int) $receivedTasks; ?></h2>
          <small class="text-muted">Materiais recebidos: <?php echo (int) $receivedMaterials; ?></small>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
