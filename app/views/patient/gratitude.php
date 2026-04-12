<?php $title = 'Diário da gratidão'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap messenger-page">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <section class="messenger-hero-image mb-4" style="background-image: url('<?php echo $appUrl; ?>/app/images/gratidao.png');">
    <div class="messenger-hero-overlay">
      <div class="messenger-hero-content">
        <h3 class="messenger-hero-image-title">Diário da gratidão</h3>
        <p class="messenger-hero-image-copy">Registre diariamente motivos de gratidão. Ao completar 30 dias, o diário é consolidado e enviado ao terapeuta.</p>
      </div>
      <a class="btn btn-dark messenger-draw-btn" href="#novoRegistroCard"><i class="fa-solid fa-plus me-1"></i>Cadastrar novo registro</a>
    </div>
  </section>

  <section id="novoRegistroCard" class="card mb-4">
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h5 class="mb-0">Novo registro</h5>
        <span class="badge text-bg-primary">Ciclo <?php echo (int) ($nextCycle ?? 1); ?> - Dia <?php echo (int) ($nextDay ?? 1); ?></span>
      </div>

      <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=gratitude-store" id="gratitudeCreateForm">
        <div id="gratitudeCreateEditor" style="height:220px;"></div>
        <input type="hidden" name="content_html" id="gratitudeCreateHtml" required>
        <button class="btn btn-primary mt-3" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar registro</button>
      </form>
    </div>
  </section>

  <section class="card">
    <div class="card-body p-4">
      <h5 class="card-title mb-3">Dias registrados (Ciclo <?php echo (int) ($currentCycle ?? 1); ?>)</h5>

      <?php if (empty($entries)): ?>
        <div class="messenger-empty-state">
          <i class="fa-regular fa-bookmark"></i>
          <p class="mb-0">Nenhum dia registrado ainda. Comece pelo Dia <?php echo (int) ($nextDay ?? 1); ?>.</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Dia</th><th>Registrado em</th><th>Ações</th></tr></thead>
            <tbody>
              <?php foreach ($entries as $entry): ?>
                <tr>
                  <td><strong>Dia <?php echo (int) ($entry['day_number'] ?? 0); ?></strong></td>
                  <td><?php echo !empty($entry['created_at']) ? date('d/m/Y H:i', strtotime((string) $entry['created_at'])) : '-'; ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-1">
                      <a class="btn btn-sm btn-outline-secondary" href="<?php echo $appUrl; ?>/patient.php?action=gratitude-show&id=<?php echo (int) ($entry['id'] ?? 0); ?>" title="Visualizar"><i class="fa-solid fa-eye"></i></a>
                      <?php if (empty($isCurrentCycleLocked)): ?>
                        <a class="btn btn-sm btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=gratitude-edit&id=<?php echo (int) ($entry['id'] ?? 0); ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
                        <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=gratitude-delete" class="m-0 js-delete-gratitude-form">
                          <input type="hidden" name="id" value="<?php echo (int) ($entry['id'] ?? 0); ?>">
                          <button class="btn btn-sm btn-outline-danger" type="submit" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </section>
</div>

<script>
window.addEventListener('load', function () {
  var editorEl = document.getElementById('gratitudeCreateEditor');
  var htmlInput = document.getElementById('gratitudeCreateHtml');
  var form = document.getElementById('gratitudeCreateForm');
  var quill = null;

  if (editorEl && typeof Quill !== 'undefined') {
    quill = new Quill(editorEl, {
      theme: 'snow',
      placeholder: 'Sou feliz e grato porque...',
      modules: {
        toolbar: [[{ header: [1, 2, false] }], ['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['clean']]
      }
    });
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

  document.querySelectorAll('.js-delete-gratitude-form').forEach(function (deleteForm) {
    deleteForm.addEventListener('submit', function (event) {
      if (!confirm('Deseja realmente excluir este registro do diário?')) {
        event.preventDefault();
      }
    });
  });
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
