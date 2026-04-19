<?php $title = 'Novo Livro'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Cadastrar livro</h4>
    <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books">Voltar</a>
  </div>

  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="card">
    <div class="card-body">
      <form id="bookCreateForm" method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books-store" enctype="multipart/form-data">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Título</label>
            <input class="form-control" name="title" required maxlength="180">
          </div>
          <div class="col-md-4">
            <label class="form-label">Publicar para pacientes?</label>
            <select class="form-select" name="is_published" required>
              <option value="" selected disabled>Selecione</option>
              <option value="1">Sim, liberar acesso</option>
              <option value="0">Não, manter como rascunho</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Arquivo PDF</label>
            <input class="form-control" type="file" name="pdf_file" accept=".pdf,application/pdf" required>
            <div class="form-text">Apenas arquivos PDF.</div>
          </div>
        </div>

        <div class="mt-4 d-flex gap-2">
          <button class="btn btn-primary" type="submit">Salvar livro</button>
          <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var form = document.getElementById('bookCreateForm');
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