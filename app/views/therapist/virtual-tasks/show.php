<?php
$title = 'Respostas - Árvore da Vida | Tera-Tech';
include __DIR__ . '/../../partials/header.php';
?>
<?php include __DIR__ . '/../../partials/nav.php'; ?>

<div class="page-wrap">
  <div class="container-fluid">
    <a class="btn btn-outline-secondary btn-sm mb-4" href="<?php echo $appUrl; ?>/dashboard.php?action=virtual-tasks">
      <i class="fa-solid fa-arrow-left me-1"></i>Tarefas Dinâmicas
    </a>

    <div class="mb-4">
      <h1 class="h3 mb-1">🌳 Árvore da Vida - Respostas do Paciente</h1>
      <small class="text-muted">Enviada em <?php echo date('d/m/Y H:i', strtotime($task['created_at'])); ?></small>
    </div>

    <?php if (($task['status'] ?? 'pending') === 'pending'): ?>
      <div class="alert alert-warning">
        <i class="fa-solid fa-hourglass-half me-2"></i>
        <strong>Pendente:</strong> O paciente ainda não respondeu a esta tarefa.
      </div>
    <?php else: ?>
      <div class="alert alert-success">
        <i class="fa-solid fa-check-circle me-2"></i>
        <strong>Concluído em:</strong> <?php echo date('d/m/Y H:i', strtotime($task['responded_at'])); ?>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
          <strong>Resposta formatada</strong>
          <button class="btn btn-outline-primary btn-sm" type="button" onclick="printResponses()">
            <i class="fa-solid fa-print me-1"></i>Imprimir HTML
          </button>
        </div>
        <div class="card-body">
          <?php if (!empty($formattedResponseHtml)): ?>
            <?php echo $formattedResponseHtml; ?>
          <?php else: ?>
            <div class="text-muted">Nenhuma resposta formatada disponível.</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Ações -->
      <div class="mt-4">
        <div class="card border-0 shadow-sm bg-light">
          <div class="card-body">
            <h6 class="mb-3">Ações</h6>
            <a class="btn btn-primary btn-sm" href="#" onclick="printResponses()">
              <i class="fa-solid fa-print me-1"></i>Imprimir
            </a>
            <a class="btn btn-outline-primary btn-sm" href="#" onclick="exportToWord()">
              <i class="fa-solid fa-file-word me-1"></i>Exportar
            </a>
            <button class="btn btn-outline-secondary btn-sm" onclick="addNote()">
              <i class="fa-solid fa-sticky-note me-1"></i>Adicionar Anotação
            </button>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
function printResponses() {
  window.print();
}

function exportToWord() {
  // Implementar exportação para Word
  Swal.fire('Em breve', 'Funcionalidade de exportação em desenvolvimento', 'info');
}

function addNote() {
  Swal.fire({
    title: 'Adicionar Anotação',
    input: 'textarea',
    inputPlaceholder: 'Digite sua anotação...',
    showCancelButton: true,
    confirmButtonText: 'Salvar'
  }).then(result => {
    if (result.isConfirmed) {
      // Implementar salvamento de anotação
      Swal.fire('Anotação salva', '', 'success');
    }
  });
}
</script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
