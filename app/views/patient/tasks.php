<?php $title = 'Minhas Tarefas'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <h3 class="mb-3">Minhas tarefas</h3>
  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>Data</th><th>Titulo</th><th>Status</th><th>Descricao</th><th>Material</th></tr></thead>
        <tbody>
          <?php if (empty($tasks)): ?>
          <tr><td colspan="5" class="text-center py-4 text-muted">Nenhuma tarefa recebida.</td></tr>
          <?php else: foreach ($tasks as $task): ?>
          <tr>
            <td><?php echo htmlspecialchars($task['due_date']); ?></td>
            <td><?php echo htmlspecialchars($task['title']); ?></td>
            <td><?php echo htmlspecialchars($task['status']); ?></td>
            <td><?php echo strip_tags((string)($task['description'] ?? '')); ?></td>
            <td>
              <?php if (!empty($task['material_id']) && !empty($task['send_to_patient'])): ?>
                <a class="btn btn-sm btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=task-material&id=<?php echo (int) $task['id']; ?>"><i class="fa-solid fa-link me-1"></i><?php echo htmlspecialchars((string) ($task['material_title'] ?? 'Abrir material')); ?></a>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
