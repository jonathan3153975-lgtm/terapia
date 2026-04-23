<?php $title = 'Minhas Tarefas'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap portal-stack">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <?php if (!($_SESSION['patient_has_active_plan'] ?? true)): ?>
  <div class="free-tier-notice mb-4">
    <div class="free-tier-notice__icon"><i class="fa-solid fa-lock-open"></i></div>
    <div class="free-tier-notice__body">
      <strong>Você está no plano gratuito</strong>
      <p class="mb-0">Aqui você pode visualizar e responder as tarefas enviadas pelo seu terapeuta. Para acessar todos os recursos — materiais, mensageiro, diário da gratidão, meditação guiada e orações — ative um plano.</p>
    </div>
    <a class="btn btn-primary btn-sm free-tier-notice__cta" href="<?php echo $appUrl; ?>/patient.php?action=subscription-plans">
      <i class="fa-solid fa-crown me-1"></i>Ver planos
    </a>
  </div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h3 class="mb-0">Minhas tarefas</h3>
    <a class="btn btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=materials"><i class="fa-solid fa-book me-1"></i>Meus materiais</a>
  </div>

  <section class="card portal-search-card">
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-lg-7">
          <label class="form-label mb-2" for="patientTasksSearchInput">Buscar nas tarefas</label>
          <div class="portal-search-field">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input id="patientTasksSearchInput" class="form-control" type="search" placeholder="Digite título, data ou status da tarefa...">
          </div>
        </div>
        <div class="col-12 col-lg-5">
          <p class="portal-inline-meta mb-0">Filtre os registros recebidos sem sair da página.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="card portal-list-card">
    <div class="portal-list-card__header">
      <div>
        <h5 class="card-title mb-1">Registros recebidos</h5>
        <p class="text-muted mb-0">Visualize materiais vinculados e abra cada tarefa para responder.</p>
      </div>
      <span class="badge text-bg-light border"><?php echo count($tasks ?? []); ?> tarefa(s)</span>
    </div>
    <div class="portal-list-card__body">
      <div class="table-responsive">
      <table class="table">
        <thead><tr><th>Miniatura</th><th>Data</th><th>Título</th><th>Status</th><th>Material</th><th>Ação</th></tr></thead>
        <tbody id="patientTasksTableBody">
          <?php if (empty($tasks)): ?>
          <tr id="patientTasksEmptyRow"><td colspan="6" class="text-center py-4 text-muted">Nenhuma tarefa recebida.</td></tr>
          <?php else: foreach ($tasks as $task): ?>
          <?php $linkedMaterials = $taskLinkedMaterials[(int) $task['id']] ?? []; ?>
          <?php
            $coverPath = trim((string) ($task['cover_image_path'] ?? ''));
            $therapistId = (int) ($task['therapist_id'] ?? 0);
            $therapistLogoPath = trim((string) (($therapistLogoMap[$therapistId] ?? '') ?: ''));
            $thumbPath = $coverPath !== '' ? $coverPath : $therapistLogoPath;
            $taskStatusLabel = (($task['status'] ?? '') === 'done') ? 'respondida' : 'pendente';
            $taskDateLabel = date('d/m/Y', strtotime((string) $task['due_date']));
          ?>
          <tr class="patient-task-row" data-search="<?php echo htmlspecialchars(strtolower(trim((string) ($taskDateLabel . ' ' . ($task['title'] ?? '') . ' ' . $taskStatusLabel)))); ?>">
            <td>
              <?php if ($thumbPath !== ''): ?>
                <img
                  src="<?php echo $appUrl; ?>/<?php echo htmlspecialchars(ltrim($thumbPath, '/')); ?>"
                  alt="Miniatura da tarefa"
                  style="width: 72px; height: 48px; object-fit: cover; border-radius: .45rem; border: 1px solid #dee2e6;"
                >
              <?php else: ?>
                <div class="d-inline-flex align-items-center justify-content-center" style="width:72px;height:48px;border-radius:.45rem;border:1px solid #dee2e6;background:#f8f9fa;color:#6c757d;">
                  <i class="fa-solid fa-image"></i>
                </div>
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars((string) $taskDateLabel); ?></td>
            <td><?php echo htmlspecialchars($task['title']); ?></td>
            <td>
              <?php if (($task['status'] ?? '') === 'done'): ?>
                <span class="badge text-bg-success">Respondida</span>
              <?php else: ?>
                <span class="badge text-bg-warning">Pendente</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($linkedMaterials) && !empty($task['send_to_patient'])): ?>
                <a class="btn btn-sm btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=task-material&id=<?php echo (int) $task['id']; ?>"><i class="fa-solid fa-link me-1"></i><?php echo count($linkedMaterials); ?> material(is)</a>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <td>
              <a class="btn btn-sm btn-primary" href="<?php echo $appUrl; ?>/patient.php?action=task-respond&id=<?php echo (int) $task['id']; ?>">
                <i class="fa-solid fa-eye me-1"></i>Acessar tarefa
              </a>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
      </div>
    </div>
  </section>
</div>
<script>
window.addEventListener('load', function () {
  var searchInput = document.getElementById('patientTasksSearchInput');
  var tableBody = document.getElementById('patientTasksTableBody');
  var emptyRow = document.getElementById('patientTasksEmptyRow');
  var taskRows = Array.prototype.slice.call(document.querySelectorAll('.patient-task-row'));

  var clearNoMatchRow = function () {
    var row = document.getElementById('patientTasksNoMatchRow');
    if (row) {
      row.remove();
    }
  };

  var applySearch = function () {
    var term = searchInput ? searchInput.value.toLowerCase().trim() : '';
    var visibleCount = 0;

    clearNoMatchRow();

    taskRows.forEach(function (row) {
      var text = (row.getAttribute('data-search') || '').toLowerCase();
      var match = term === '' || text.indexOf(term) !== -1;
      row.style.display = match ? '' : 'none';
      if (match) {
        visibleCount += 1;
      }
    });

    if (emptyRow) {
      emptyRow.style.display = taskRows.length === 0 ? '' : 'none';
    }

    if (taskRows.length > 0 && visibleCount === 0 && tableBody) {
      var tr = document.createElement('tr');
      tr.id = 'patientTasksNoMatchRow';
      tr.innerHTML = '<td colspan="6" class="text-center text-muted py-4">Nenhuma tarefa encontrada para essa busca.</td>';
      tableBody.appendChild(tr);
    }
  };

  if (searchInput) {
    searchInput.addEventListener('input', applySearch);
  }
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
