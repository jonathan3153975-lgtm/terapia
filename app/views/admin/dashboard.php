<?php $title = 'Dashboard Admin'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <h3 class="mb-4">Administrador Geral</h3>
  <div class="row g-3 mb-4">
    <div class="col-md-4 col-xl-2"><div class="card card-kpi"><div class="card-body"><small>Terapeutas</small><h2><?php echo (int)$totalTherapists; ?></h2></div></div></div>
    <div class="col-md-4 col-xl-2"><div class="card card-kpi"><div class="card-body"><small>Pacientes</small><h2><?php echo (int)$totalPatients; ?></h2></div></div></div>
    <div class="col-md-4 col-xl-2"><div class="card card-kpi"><div class="card-body"><small>Ativos</small><h2><?php echo (int)$activePatients; ?></h2></div></div></div>
    <div class="col-md-4 col-xl-2"><div class="card card-kpi"><div class="card-body"><small>Arquivos</small><h2><?php echo (int)$totalFiles; ?></h2></div></div></div>
    <div class="col-md-8 col-xl-4"><div class="card card-kpi"><div class="card-body"><small>Espaco em disco</small><h2><?php echo number_format($usedBytes/(1024*1024),2,',','.'); ?> MB</h2></div></div></div>
  </div>
  <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapists">Gerenciar terapeutas</a>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
