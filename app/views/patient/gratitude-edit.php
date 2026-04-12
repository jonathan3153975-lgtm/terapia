<?php $title = 'Editar gratidão'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="mb-3">
    <a class="btn btn-sm btn-outline-secondary" href="<?php echo $appUrl; ?>/patient.php?action=gratitude"><i class="fa-solid fa-arrow-left me-1"></i>Voltar</a>
  </div>

  <section class="card">
    <div class="card-body p-4">
      <h4 class="mb-1">Editar registro</h4>
      <p class="text-muted mb-3">Ciclo <?php echo (int) ($entry['cycle_number'] ?? 0); ?> - Dia <?php echo (int) ($entry['day_number'] ?? 0); ?></p>

      <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=gratitude-update" id="gratitudeEditForm">
        <input type="hidden" name="id" value="<?php echo (int) ($entry['id'] ?? 0); ?>">
        <div id="gratitudeEditEditor" style="height:240px;"></div>
        <input type="hidden" name="content_html" id="gratitudeEditHtml" required>
        <button class="btn btn-primary mt-3" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar alterações</button>
      </form>
    </div>
  </section>
</div>

<script>
window.addEventListener('load', function () {
  var editorEl = document.getElementById('gratitudeEditEditor');
  var htmlInput = document.getElementById('gratitudeEditHtml');
  var form = document.getElementById('gratitudeEditForm');
  var quill = null;

  if (editorEl && typeof Quill !== 'undefined') {
    quill = new Quill(editorEl, {
      theme: 'snow',
      placeholder: 'Sou feliz e grato porque...',
      modules: {
        toolbar: [[{ header: [1, 2, false] }], ['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['clean']]
      }
    });

    quill.root.innerHTML = <?php echo json_encode((string) ($entry['content_html'] ?? '')); ?>;
  }

  if (form) {
    form.addEventListener('submit', function (event) {
      if (!quill || !htmlInput) {
        return;
      }

      htmlInput.value = quill.root.innerHTML;
      var text = quill.getText().trim();
      if (!text) {
        event.preventDefault();
        window.alert('Escreva sua gratidão antes de salvar.');
      }
    });
  }
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
