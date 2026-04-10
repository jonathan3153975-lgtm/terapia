<?php $title = 'Minhas Tarefas'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Minhas tarefas</h3>
    <a class="btn btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=materials"><i class="fa-solid fa-book me-1"></i>Meus materiais</a>
  </div>
  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>Data</th><th>Título</th><th>Status</th><th>Descrição</th><th>Material</th><th>Ação</th></tr></thead>
        <tbody>
          <?php if (empty($tasks)): ?>
          <tr><td colspan="6" class="text-center py-4 text-muted">Nenhuma tarefa recebida.</td></tr>
          <?php else: foreach ($tasks as $task): ?>
          <?php $linkedMaterials = $taskLinkedMaterials[(int) $task['id']] ?? []; ?>
          <tr>
            <td><?php echo htmlspecialchars((string) date('d/m/Y', strtotime((string) $task['due_date']))); ?></td>
            <td><?php echo htmlspecialchars($task['title']); ?></td>
            <td>
              <?php if (($task['status'] ?? '') === 'done'): ?>
                <span class="badge text-bg-success">Respondida</span>
              <?php else: ?>
                <span class="badge text-bg-warning">Pendente</span>
              <?php endif; ?>
            </td>
            <td><?php echo strip_tags((string)($task['description'] ?? '')); ?></td>
            <td>
              <?php if (!empty($linkedMaterials) && !empty($task['send_to_patient'])): ?>
                <a class="btn btn-sm btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=task-material&id=<?php echo (int) $task['id']; ?>"><i class="fa-solid fa-link me-1"></i><?php echo count($linkedMaterials); ?> material(is)</a>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <td>
              <a class="btn btn-sm btn-primary" href="<?php echo $appUrl; ?>/patient.php?action=task-respond&id=<?php echo (int) $task['id']; ?>">
                <i class="fa-solid fa-reply me-1"></i><?php echo (($task['status'] ?? '') === 'done') ? 'Editar resposta' : 'Responder'; ?>
              </a>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
