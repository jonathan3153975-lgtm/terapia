<?php $title = 'Editar Atendimento'; include __DIR__ . '/../../../partials/header.php'; include __DIR__ . '/../../../partials/nav.php'; ?>
<div class="container page-wrap">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="card">
        <div class="card-body">
          <?php include __DIR__ . '/../../../partials/flash-alert.php'; ?>

          <h4 class="mb-3">Editar atendimento</h4>
          <form id="appointmentEditForm" method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-appointments-update">
            <input type="hidden" name="patient_id" value="<?php echo (int) $patient['id']; ?>">
            <input type="hidden" name="id" value="<?php echo (int) $appointment['id']; ?>">
            <input type="hidden" name="history" id="historyInput" value="<?php echo htmlspecialchars((string) ($appointment['history'] ?? '')); ?>">

            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Data do atendimento</label>
                <input class="form-control" type="datetime-local" name="session_date" required value="<?php echo htmlspecialchars(date('Y-m-d\\TH:i', strtotime((string) ($appointment['session_date'] ?? 'now')))); ?>">
              </div>
              <div class="col-md-8">
                <label class="form-label">Descrição</label>
                <input class="form-control" name="description" value="<?php echo htmlspecialchars((string) ($appointment['description'] ?? '')); ?>">
              </div>
              <div class="col-12">
                <label class="form-label">Histórico</label>
                <div id="historyEditor" style="height: 260px;"></div>
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
  const quill = new Quill('#historyEditor', {
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

  quill.root.innerHTML = $('#historyInput').val() || '';

  $('#appointmentEditForm').on('submit', function(e) {
    const html = quill.root.innerHTML;
    const plain = quill.getText().trim();
    if (!plain) {
      e.preventDefault();
      Swal.fire('Campo obrigatório', 'Preencha o histórico do atendimento.', 'warning');
      return;
    }
    $('#historyInput').val(html);
  });
});
</script>
<?php include __DIR__ . '/../../../partials/footer.php'; ?>