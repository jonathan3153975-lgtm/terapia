<?php $title = 'Editar Livro'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Editar livro</h4>
    <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books">Voltar</a>
  </div>

  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="card">
    <div class="card-body">
      <form id="bookEditForm" method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books-update" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo (int) ($book['id'] ?? 0); ?>">

        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Título</label>
            <input class="form-control" name="title" required maxlength="180" value="<?php echo htmlspecialchars((string) ($book['title'] ?? '')); ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Publicar para pacientes?</label>
            <select class="form-select" name="is_published" required>
              <option value="1" <?php echo (int) ($book['is_published'] ?? 0) === 1 ? 'selected' : ''; ?>>Sim, liberar acesso</option>
              <option value="0" <?php echo (int) ($book['is_published'] ?? 0) === 0 ? 'selected' : ''; ?>>Não, manter como rascunho</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">PDF atual</label>
            <div class="border rounded p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div>
                <div class="fw-semibold"><?php echo htmlspecialchars((string) ($book['pdf_original_name'] ?? 'PDF atual')); ?></div>
                <small class="text-muted"><?php echo !empty($book['pdf_size']) ? number_format(((int) $book['pdf_size']) / (1024 * 1024), 2, ',', '.') . ' MB' : 'Tamanho indisponível'; ?></small>
              </div>
              <a class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books-file&id=<?php echo (int) ($book['id'] ?? 0); ?>">Abrir PDF</a>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Substituir PDF</label>
            <input class="form-control" type="file" name="pdf_file" accept=".pdf,application/pdf">
            <div class="form-text">Envie um novo PDF apenas se desejar substituir o atual.</div>
          </div>
        </div>

        <div class="mt-4 d-flex gap-2">
          <button class="btn btn-primary" type="submit">Salvar alterações</button>
          <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books-show&id=<?php echo (int) ($book['id'] ?? 0); ?>">Visualizar</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var form = document.getElementById('bookEditForm');
  if (!form) {
    return;
  }

  form.addEventListener('submit', function (event) {
    if (!window.FormSubmitGuard.lock(form, 'Salvando...')) {
      event.preventDefault();
    }
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>