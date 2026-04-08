<?php $title = 'Portal do Paciente'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <h3 class="mb-4">Portal do Paciente</h3>
  <div class="row g-3">
    <div class="col-md-2"><div class="card card-kpi"><div class="card-body"><small>Sessoes</small><h2><?php echo (int)$sessions; ?></h2></div></div></div>
    <div class="col-md-2"><div class="card card-kpi"><div class="card-body"><small>Tarefas</small><h2><?php echo (int)$tasks; ?></h2></div></div></div>
    <div class="col-md-2"><div class="card card-kpi"><div class="card-body"><small>Pendentes</small><h2><?php echo (int)$pending; ?></h2></div></div></div>
    <div class="col-md-2"><div class="card card-kpi"><div class="card-body"><small>Concluidas</small><h2><?php echo (int)$done; ?></h2></div></div></div>
    <div class="col-md-2"><div class="card card-kpi"><div class="card-body"><small>Materiais</small><h2>0</h2></div></div></div>
    <div class="col-md-2"><div class="card card-kpi"><div class="card-body"><small>Mensagem diaria</small><h2>...</h2></div></div></div>
  </div>
  <a class="btn btn-primary mt-4" href="<?php echo $appUrl; ?>/patient.php?action=tasks">Minhas tarefas</a>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
