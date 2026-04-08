<?php $title = 'Dashboard Terapeuta'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <h3 class="mb-4">Dashboard do Terapeuta</h3>
  <div class="row g-3 mb-4">
    <div class="col-md-4 col-xl-2"><div class="card card-kpi"><div class="card-body"><small>Pacientes</small><h2><?php echo (int)$totalPatients; ?></h2></div></div></div>
    <div class="col-md-4 col-xl-2"><div class="card card-kpi"><div class="card-body"><small>Atendimentos</small><h2><?php echo (int)$totalAppointments; ?></h2></div></div></div>
    <div class="col-md-4 col-xl-2"><div class="card card-kpi"><div class="card-body"><small>Tarefas</small><h2><?php echo (int)$totalTasks; ?></h2></div></div></div>
    <div class="col-md-4 col-xl-2"><div class="card card-kpi"><div class="card-body"><small>Mensagens</small><h2><?php echo (int)$totalMessages; ?></h2></div></div></div>
    <div class="col-md-4 col-xl-2"><div class="card card-kpi"><div class="card-body"><small>Arquivos</small><h2><?php echo (int)$totalFiles; ?></h2></div></div></div>
  </div>
  <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=patients">Gerenciar pacientes</a>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
