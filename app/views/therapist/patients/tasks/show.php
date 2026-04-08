<?php $title = 'Visualizar Tarefa'; include __DIR__ . '/../../../partials/header.php'; include __DIR__ . '/../../../partials/nav.php'; ?>
<div class="page-wrap">
  <?php
  $taskStatusLabel = 'Pendente';
  $taskStatusClass = 'text-bg-warning';
  if (($task['status'] ?? '') === 'done') {
    $taskStatusLabel = 'Finalizado';
    $taskStatusClass = 'text-bg-success';
  } elseif (!empty($task['send_to_patient'])) {
    $taskStatusLabel = 'Enviado';
    $taskStatusClass = 'text-bg-info';
  }
  ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Tarefa de <?php echo htmlspecialchars((string) $patient['name']); ?></h3>
    <a class="btn btn-outline-dark" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-history&id=<?php echo (int) $patient['id']; ?>"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3"><strong>Data:</strong> <?php echo htmlspecialchars((string) date('d/m/Y', strtotime((string) ($task['due_date'] ?? 'now')))); ?></div>
        <div class="col-md-6"><strong>Título:</strong> <?php echo htmlspecialchars((string) ($task['title'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>Status:</strong> <span class="badge <?php echo $taskStatusClass; ?>"><?php echo $taskStatusLabel; ?></span></div>
        <div class="col-12">
          <strong>Descrição:</strong>
          <div class="mt-2 border rounded p-3 bg-light-subtle" style="max-height: 52vh; overflow: auto;">
            <?php echo (string) ($task['description'] ?? ''); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../partials/footer.php'; ?>