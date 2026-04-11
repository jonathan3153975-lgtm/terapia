<?php $title = 'Meditacao guiada'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0">Meditacao guiada</h3>
    <span class="badge text-bg-light border"><?php echo count($meditations ?? []); ?> meditacao(oes)</span>
  </div>

  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="row g-3 mb-3">
    <div class="col-12 col-xl-5">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title mb-2">Nova meditacao</h5>
          <p class="text-muted small mb-3">Cadastre um audio guiado com imagem de referencia.</p>
          <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-guided-meditations-store" enctype="multipart/form-data" id="guidedMeditationCreateForm">
            <div class="mb-2">
              <label class="form-label">Titulo</label>
              <input class="form-control" type="text" name="title" maxlength="180" required placeholder="Ex.: Respiracao para acolhimento">
            </div>
            <div class="mb-2">
              <label class="form-label">Imagem de referencia (opcional)</label>
              <input class="form-control" type="file" name="reference_image" accept="image/*">
            </div>
            <div class="mb-3">
              <label class="form-label">Audio (obrigatorio)</label>
              <input class="form-control" type="file" name="audio_file" accept="audio/*" required>
            </div>
            <button class="btn btn-primary" type="submit" id="guidedMeditationCreateBtn"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar meditacao</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-7">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title mb-2">Reflexoes compartilhadas</h5>
          <?php if (empty($sharedEntries)): ?>
            <p class="text-muted mb-0">Nenhuma reflexao compartilhada no momento.</p>
          <?php else: ?>
            <div class="vstack gap-2 guided-shared-list">
              <?php foreach ($sharedEntries as $entry): ?>
                <article class="messenger-shared-card">
                  <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <strong><?php echo htmlspecialchars((string) ($entry['patient_name'] ?? 'Paciente')); ?></strong>
                    <span class="small text-muted"><?php echo !empty($entry['created_at']) ? date('d/m/Y H:i', strtotime((string) $entry['created_at'])) : '-'; ?></span>
                  </div>
                  <div class="small text-muted mb-1"><?php echo htmlspecialchars((string) ($entry['meditation_title'] ?? 'Meditacao')); ?></div>
                  <p class="mb-2"><?php echo nl2br(htmlspecialchars((string) ($entry['letter_text'] ?? ''))); ?></p>
                  <div class="small text-muted mb-1">Reflexao</div>
                  <p class="mb-0"><?php echo nl2br(htmlspecialchars((string) ($entry['patient_note'] ?? ''))); ?></p>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body pb-0">
      <form method="GET" action="<?php echo $appUrl; ?>/dashboard.php" class="row g-2 align-items-end">
        <input type="hidden" name="action" value="therapist-guided-meditations">
        <div class="col-12 col-md-10">
          <label class="form-label mb-1">Buscar titulo</label>
          <input class="form-control" type="search" name="q" value="<?php echo htmlspecialchars((string) ($filters['q'] ?? '')); ?>" placeholder="Digite parte do titulo...">
        </div>
        <div class="col-12 col-md-2 d-grid">
          <button class="btn btn-dark" type="submit"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th style="width: 100px;">Imagem</th>
              <th>Titulo</th>
              <th style="width: 170px;">Audio</th>
              <th style="width: 180px;">Criada em</th>
              <th style="width: 120px;">Acoes</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($meditations)): ?>
              <tr>
                <td colspan="5" class="text-center text-muted py-4">Nenhuma meditacao cadastrada.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($meditations as $meditation): ?>
                <tr>
                  <td>
                    <?php if (!empty($meditation['reference_image_path'])): ?>
                      <img src="<?php echo $appUrl . '/' . ltrim((string) $meditation['reference_image_path'], '/'); ?>" alt="Imagem" class="guided-table-thumb">
                    <?php else: ?>
                      <div class="guided-table-thumb guided-table-thumb--empty"><i class="fa-regular fa-image"></i></div>
                    <?php endif; ?>
                  </td>
                  <td><?php echo htmlspecialchars((string) ($meditation['title'] ?? '')); ?></td>
                  <td>
                    <audio controls preload="none" class="w-100" style="max-width: 160px; height: 36px;">
                      <source src="<?php echo $appUrl . '/' . ltrim((string) ($meditation['audio_path'] ?? ''), '/'); ?>">
                    </audio>
                  </td>
                  <td><?php echo !empty($meditation['created_at']) ? date('d/m/Y H:i', strtotime((string) $meditation['created_at'])) : '-'; ?></td>
                  <td>
                    <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-guided-meditations-delete" class="m-0 js-delete-guided-form">
                      <input type="hidden" name="id" value="<?php echo (int) ($meditation['id'] ?? 0); ?>">
                      <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash me-1"></i>Excluir</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var form = document.getElementById('guidedMeditationCreateForm');
  var btn = document.getElementById('guidedMeditationCreateBtn');

  if (form && btn) {
    form.addEventListener('submit', function () {
      if (btn.disabled) {
        return false;
      }

      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-hourglass-half me-1"></i>Salvando...';
      return true;
    });
  }

  document.querySelectorAll('.js-delete-guided-form').forEach(function (deleteForm) {
    deleteForm.addEventListener('submit', function (event) {
      var ok = window.confirm('Deseja realmente excluir esta meditacao guiada?');
      if (!ok) {
        event.preventDefault();
      }
    });
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
