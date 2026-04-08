<?php $title = 'Historico do Paciente'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Historico: <?php echo htmlspecialchars((string) $patient['name']); ?></h3>
    <a class="btn btn-outline-dark" href="<?php echo $appUrl; ?>/dashboard.php?action=patients"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
  </div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header bg-transparent"><strong>Atendimentos</strong></div>
        <div class="card-body">
          <?php if (empty($appointments)): ?>
            <p class="text-muted mb-0">Nenhum atendimento registrado.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead><tr><th>Data</th><th>Descricao</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($appointments as $appointment): ?>
                  <tr>
                    <td><?php echo htmlspecialchars((string) ($appointment['session_date'] ?? '-')); ?></td>
                    <td><?php echo htmlspecialchars((string) ($appointment['description'] ?? '-')); ?></td>
                    <td><?php echo htmlspecialchars((string) ($appointment['status'] ?? '-')); ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-header bg-transparent"><strong>Tarefas</strong></div>
        <div class="card-body">
          <?php if (empty($tasks)): ?>
            <p class="text-muted mb-0">Nenhuma tarefa registrada.</p>
          <?php else: ?>
            <ul class="list-group list-group-flush">
              <?php foreach ($tasks as $task): ?>
                <li class="list-group-item px-0">
                  <div class="d-flex justify-content-between">
                    <strong><?php echo htmlspecialchars((string) ($task['title'] ?? '-')); ?></strong>
                    <span class="badge text-bg-light border"><?php echo htmlspecialchars((string) ($task['status'] ?? '-')); ?></span>
                  </div>
                  <small class="text-muted">Entrega: <?php echo htmlspecialchars((string) ($task['due_date'] ?? '-')); ?></small>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
