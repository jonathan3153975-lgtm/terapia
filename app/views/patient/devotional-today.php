<?php $title = 'Devocional do dia'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="mb-3">
    <a class="btn btn-sm btn-outline-secondary" href="<?php echo $appUrl; ?>/patient.php?action=devotionals"><i class="fa-solid fa-arrow-left me-1"></i>Voltar</a>
  </div>

  <section class="card mb-4 overflow-hidden">
    <img src="<?php echo $appUrl; ?>/app/images/mensageiro.png" alt="Devocional" class="w-100" style="height:220px;object-fit:cover;">
    <div class="card-body p-4">
      <div class="text-uppercase small text-muted mb-2">Hoje</div>
      <h5 class="mb-2"><?php echo htmlspecialchars((string) ($currentDateLabel ?? '')); ?></h5>
      <h3 class="mb-1" style="font-size:1.8rem;line-height:1.2;"><?php echo htmlspecialchars((string) ($entry['title'] ?? '-')); ?></h3>
      <p class="fw-semibold text-primary mb-3"><?php echo htmlspecialchars((string) ($entry['word_of_god'] ?? '-')); ?></p>
      <div class="border rounded p-3 bg-light-subtle"><?php echo (string) ($entry['text_content'] ?? ''); ?></div>
    </div>
  </section>

  <section class="card">
    <div class="card-body p-4">
      <h5 class="card-title mb-2">Sua reflexão</h5>
      <p class="text-muted mb-3">Escreva aqui sua reflexão, comentário ou observação sobre o assunto. Como isso se encaixa na sua vida</p>

      <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=devotional-save" id="devotionalReflectionForm">
        <input type="hidden" name="devotional_entry_id" value="<?php echo (int) ($entry['id'] ?? 0); ?>">
        <div id="devotionalReflectionEditor" style="height:240px;"></div>
        <input type="hidden" name="reflection_html" id="devotionalReflectionHtml" required>
        <button class="btn btn-primary mt-3" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar reflexão</button>
      </form>
    </div>
  </section>
</div>

<script>
window.addEventListener('load', function () {
  var editorEl = document.getElementById('devotionalReflectionEditor');
  var htmlInput = document.getElementById('devotionalReflectionHtml');
  var form = document.getElementById('devotionalReflectionForm');
  var quill = null;

  if (editorEl && typeof Quill !== 'undefined') {
    quill = new Quill(editorEl, {
      theme: 'snow',
      placeholder: 'Escreva aqui sua reflexão...',
      modules: {
        toolbar: [[{ header: [1, 2, false] }], ['bold', 'italic', 'underline', 'blockquote'], [{ list: 'ordered' }, { list: 'bullet' }], ['clean']]
      }
    });

    quill.root.innerHTML = <?php echo json_encode((string) ($existingReflection['reflection_html'] ?? '')); ?>;
  }

  if (form) {
    form.addEventListener('submit', function (event) {
      if (!quill || !htmlInput) {
        return;
      }

      htmlInput.value = quill.root.innerHTML;
      if (!quill.getText().trim()) {
        event.preventDefault();
        window.alert('Escreva sua reflexão antes de salvar.');
      }
    });
  }
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
