<?php $title = 'Editar Tarefa'; include __DIR__ . '/../../../partials/header.php'; include __DIR__ . '/../../../partials/nav.php'; ?>
<div class="container page-wrap">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="card">
        <div class="card-body">
          <?php include __DIR__ . '/../../../partials/flash-alert.php'; ?>

          <h4 class="mb-3">Editar tarefa</h4>
          <form id="taskEditForm" method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-tasks-update" enctype="multipart/form-data">
            <input type="hidden" name="patient_id" value="<?php echo (int) $patient['id']; ?>">
            <input type="hidden" name="id" value="<?php echo (int) $task['id']; ?>">
            <input type="hidden" name="description" id="taskDescriptionInput" value="<?php echo htmlspecialchars((string) ($task['description'] ?? '')); ?>">

            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label">Data</label>
                <input class="form-control" type="date" name="due_date" required value="<?php echo htmlspecialchars((string) ($task['due_date'] ?? '')); ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Título</label>
                <input class="form-control" name="title" required value="<?php echo htmlspecialchars((string) ($task['title'] ?? '')); ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                  <option value="pending" <?php echo (($task['status'] ?? 'pending') === 'pending') ? 'selected' : ''; ?>>Pendente</option>
                  <option value="done" <?php echo (($task['status'] ?? '') === 'done') ? 'selected' : ''; ?>>Finalizado</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Descrição</label>
                <div id="taskDescriptionEditor" style="height: 260px;"></div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Anexos (PDF e imagens)</label>
                <input class="form-control" type="file" name="task_attachments[]" accept=".pdf,image/*" multiple>
              </div>
              <div class="col-md-6">
                <label class="form-label">Link</label>
                <input class="form-control" type="url" name="attachment_link" placeholder="https://...">
              </div>
              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="send_to_patient" id="send_to_patient" value="1" <?php echo !empty($task['send_to_patient']) ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="send_to_patient">Enviar para o paciente</label>
                </div>
              </div>
            </div>

            <div class="mt-3 d-flex gap-2">
              <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-history&id=<?php echo (int) $patient['id']; ?>">Voltar</a>
              <button class="btn btn-primary" type="submit">Salvar alterações</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
window.addEventListener('load', function() {
  const quill = new Quill('#taskDescriptionEditor', {
    theme: 'snow',
    modules: {
      toolbar: [
        [{ header: [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['blockquote', 'code-block', 'link'],
        [{ color: [] }, { background: [] }],
        ['clean']
      ]
    }
  });

  quill.root.innerHTML = $('#taskDescriptionInput').val() || '';

  $('#taskEditForm').on('submit', function(e) {
    const form = this;
    if (!window.FormSubmitGuard.lock(form, 'Salvando...')) {
      e.preventDefault();
      return;
    }

    const html = quill.root.innerHTML;
    const plain = quill.getText().trim();
    if (!plain) {
      e.preventDefault();
      window.FormSubmitGuard.unlock(form);
      Swal.fire('Campo obrigatório', 'Preencha a descrição da tarefa.', 'warning');
      return;
    }

    $('#taskDescriptionInput').val(html);
  });
});
</script>
<?php include __DIR__ . '/../../../partials/footer.php'; ?>