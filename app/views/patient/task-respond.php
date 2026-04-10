<?php $title = 'Responder Tarefa'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Responder tarefa</h3>
    <a class="btn btn-outline-dark" href="<?php echo $appUrl; ?>/patient.php?action=tasks"><i class="fa-solid fa-arrow-left me-1"></i>Voltar</a>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3"><strong>Data limite:</strong> <?php echo htmlspecialchars((string) date('d/m/Y', strtotime((string) ($task['due_date'] ?? 'now')))); ?></div>
        <div class="col-md-6"><strong>Título:</strong> <?php echo htmlspecialchars((string) ($task['title'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>Status:</strong> <?php echo (($task['status'] ?? '') === 'done') ? 'Respondida' : 'Pendente'; ?></div>
        <div class="col-12">
          <strong>Descrição:</strong>
          <div class="border rounded p-3 mt-2 bg-light-subtle"><?php echo (string) ($task['description'] ?? ''); ?></div>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($therapistFiles)): ?>
    <div class="card mb-3">
      <div class="card-header bg-transparent"><strong>Anexos enviados pelo terapeuta</strong></div>
      <div class="card-body d-flex gap-2 flex-wrap">
        <?php foreach ($therapistFiles as $fi): ?>
          <?php if (($fi['file_type'] ?? '') === 'link'): ?>
            <a href="<?php echo htmlspecialchars((string) $fi['file_path']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-info"><i class="fa-solid fa-link me-1"></i><?php echo htmlspecialchars((string) $fi['file_name']); ?></a>
          <?php elseif (($fi['file_type'] ?? '') === 'pdf'): ?>
            <a href="<?php echo $appUrl; ?>/<?php echo htmlspecialchars((string) $fi['file_path']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-file-pdf me-1"></i><?php echo htmlspecialchars((string) $fi['file_name']); ?></a>
          <?php else: ?>
            <a href="<?php echo $appUrl; ?>/<?php echo htmlspecialchars((string) $fi['file_path']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-image me-1"></i><?php echo htmlspecialchars((string) $fi['file_name']); ?></a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header bg-transparent"><strong>Minha devolutiva</strong></div>
    <div class="card-body">
      <form id="taskResponseForm" method="POST" action="<?php echo $appUrl; ?>/patient.php?action=task-respond-submit" enctype="multipart/form-data">
        <input type="hidden" name="task_id" value="<?php echo (int) ($task['id'] ?? 0); ?>">
        <input type="hidden" name="response_html" id="responseHtmlInput" value="<?php echo htmlspecialchars((string) ($task['patient_response_html'] ?? '')); ?>">

        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Resposta</label>
            <div id="responseEditor" class="task-description-editor"></div>
          </div>
          <div class="col-12">
            <label class="form-label">Anexos da devolutiva (PDF e imagens)</label>
            <input class="form-control" type="file" name="task_attachments[]" accept=".pdf,image/*" multiple>
          </div>
          <div class="col-12">
            <label class="form-label">Link adicional</label>
            <input class="form-control" type="url" name="attachment_link" placeholder="https://...">
          </div>
          <div class="col-12">
            <label class="form-label d-block mb-2">Alertar terapeuta por</label>
            <div class="d-flex gap-3 flex-wrap">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="notify_channels[]" id="notify_email" value="email" checked>
                <label class="form-check-label" for="notify_email">E-mail</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="notify_channels[]" id="notify_whatsapp" value="whatsapp" checked>
                <label class="form-check-label" for="notify_whatsapp">WhatsApp</label>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane me-1"></i>Enviar resposta</button>
        </div>
      </form>
    </div>
  </div>

  <?php if (!empty($patientFiles)): ?>
    <div class="card mt-3">
      <div class="card-header bg-transparent"><strong>Últimos anexos enviados por você</strong></div>
      <div class="card-body d-flex gap-2 flex-wrap">
        <?php foreach ($patientFiles as $fi): ?>
          <?php if (($fi['file_type'] ?? '') === 'link'): ?>
            <a href="<?php echo htmlspecialchars((string) $fi['file_path']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-info"><i class="fa-solid fa-link me-1"></i><?php echo htmlspecialchars((string) $fi['file_name']); ?></a>
          <?php elseif (($fi['file_type'] ?? '') === 'pdf'): ?>
            <a href="<?php echo $appUrl; ?>/<?php echo htmlspecialchars((string) $fi['file_path']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-file-pdf me-1"></i><?php echo htmlspecialchars((string) $fi['file_name']); ?></a>
          <?php else: ?>
            <a href="<?php echo $appUrl; ?>/<?php echo htmlspecialchars((string) $fi['file_path']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-image me-1"></i><?php echo htmlspecialchars((string) $fi['file_name']); ?></a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
window.addEventListener('load', function() {
  var quill = new Quill('#responseEditor', {
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

  quill.root.innerHTML = document.getElementById('responseHtmlInput').value || '';

  $('#taskResponseForm').on('submit', function(e) {
    var form = this;
    if (!window.FormSubmitGuard.lock(form, 'Enviando...')) {
      e.preventDefault();
      return;
    }

    var html = quill.root.innerHTML;
    var plain = quill.getText().trim();
    if (!plain) {
      e.preventDefault();
      window.FormSubmitGuard.unlock(form);
      Swal.fire('Campo obrigatório', 'Preencha sua resposta para enviar a devolutiva.', 'warning');
      return;
    }

    document.getElementById('responseHtmlInput').value = html;
  });
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
