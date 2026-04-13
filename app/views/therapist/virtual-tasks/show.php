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

      <!-- Abas de seções -->
      <ul class="nav nav-tabs mb-4" role="tablist">
        <?php foreach ($responses as $index => $resp): ?>
          <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                    id="tab_<?php echo $resp['section_name']; ?>" 
                    data-bs-toggle="tab" 
                    data-bs-target="#content_<?php echo $resp['section_name']; ?>" 
                    type="button">
              <?php echo ucfirst(str_replace('_', ' ', $resp['section_name'])); ?>
            </button>
          </li>
        <?php endforeach; ?>
        <?php if (!empty($task['patient_response_html'])): ?>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tabReflection" data-bs-toggle="tab" data-bs-target="#contentReflection" type="button">
              Reflexão Final
            </button>
          </li>
        <?php endif; ?>
      </ul>

      <!-- Conteúdo das abas -->
      <div class="tab-content">
        <?php foreach ($responses as $index => $resp): 
          $answers = json_decode($resp['answers_json'], true) ?? [];
        ?>
          <div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>" 
               id="content_<?php echo $resp['section_name']; ?>" 
               role="tabpanel">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <?php foreach ($answers as $qIndex => $answer): ?>
                  <div class="mb-4 pb-4" style="border-bottom: 1px solid #f0f0f0;">
                    <h6 class="text-muted small mb-2">
                      <i class="fa-solid fa-question-circle me-1"></i>
                      Pergunta <?php echo $qIndex + 1; ?>
                    </h6>
                    <p class="card-text"><?php echo htmlspecialchars($answer); ?></p>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>

        <?php if (!empty($task['patient_response_html'])): ?>
          <div class="tab-pane fade" id="contentReflection" role="tabpanel">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <div class="ql-editor" style="padding: 0;">
                  <?php echo $task['patient_response_html']; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
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
