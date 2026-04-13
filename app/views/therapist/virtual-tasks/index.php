<?php
$title = 'Tarefas Dinâmicas - Terapeuta | Tera-Tech';
include __DIR__ . '/../../partials/header.php';
?>
<?php include __DIR__ . '/../../partials/nav.php'; ?>

<div class="page-wrap">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h3 mb-1"><i class="fa-solid fa-star me-2"></i>Tarefas Dinâmicas</h1>
        <small class="text-muted">Mini-sistemas interativos para seus pacientes</small>
      </div>
      <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=virtual-tasks-create">
        <i class="fa-solid fa-plus me-2"></i>Criar Tarefa
      </a>
    </div>

    <?php if (empty($tasks)): ?>
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
          <i class="fa-solid fa-inbox fs-1 text-muted mb-3" style="display: block;"></i>
          <h6 class="text-muted mb-2">Nenhuma tarefa dinâmica criada</h6>
          <p class="text-muted small mb-3">Comece criando tarefas interativas para seus pacientes</p>
          <a class="btn btn-outline-primary btn-sm" href="<?php echo $appUrl; ?>/dashboard.php?action=virtual-tasks-create">
            <i class="fa-solid fa-sparkles me-1"></i>Criar Primeira Tarefa
          </a>
        </div>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($tasks as $task): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 position-relative">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h6 class="card-title mb-0">
                    <?php echo $task['task_type'] === 'virtual_tree_of_life' ? '🌳' : '✨'; ?>
                    <?php echo htmlspecialchars($task['title'] ?? ''); ?>
                  </h6>
                  <span class="badge bg-<?php echo ($task['status'] ?? 'pending') === 'done' ? 'success' : 'warning'; ?>">
                    <?php echo ($task['status'] ?? 'pending') === 'done' ? 'Concluído' : 'Pendente'; ?>
                  </span>
                </div>

                <p class="text-muted small mb-2"><?php echo htmlspecialchars(substr($task['description'] ?? '', 0, 80)); ?>...</p>

                <small class="text-muted d-block mb-3">
                  <i class="fa-solid fa-calendar-days me-1"></i>
                  <?php echo date('d/m/Y', strtotime($task['created_at'])); ?>
                </small>

                <div class="d-flex gap-2">
                  <button class="btn btn-sm btn-outline-primary flex-grow-1" data-task-id="<?php echo $task['id']; ?>" onclick="previewTask(this)">
                    <i class="fa-solid fa-eye me-1"></i>Visualizar
                  </button>
                  <button class="btn btn-sm btn-outline-danger" data-task-id="<?php echo $task['id']; ?>" onclick="deleteTask(this)">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
function previewTask(btn) {
  const taskId = btn.getAttribute('data-task-id');
  window.location.href = '<?php echo $appUrl; ?>/dashboard.php?action=virtual-tasks-show&id=' + taskId;
}

function deleteTask(btn) {
  const taskId = btn.getAttribute('data-task-id');
  
  Swal.fire({
    title: 'Deletar Tarefa?',
    text: 'Essa ação não pode ser desfeita',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, deletar',
    cancelButtonText: 'Cancelar'
  }).then(result => {
    if (result.isConfirmed) {
      $.ajax({
        url: '<?php echo $appUrl; ?>/dashboard.php?action=virtual-tasks-delete',
        method: 'POST',
        data: { id: taskId },
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            Swal.fire('Deletado!', 'Tarefa removida', 'success').then(() => {
              location.reload();
            });
          } else {
            Swal.fire('Erro', res.message, 'error');
          }
        },
        error: function() {
          Swal.fire('Erro', 'Não foi possível deletar a tarefa', 'error');
        }
      });
    }
  });
}
</script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
