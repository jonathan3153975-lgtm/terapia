<?php $title = 'Orações'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0">Orações</h3>
    <span class="badge text-bg-light border"><?php echo count($prayers ?? []); ?> oração(ões)</span>
  </div>

  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="row g-3 mb-3">
    <div class="col-12 col-xl-5">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title mb-2">Nova oração</h5>
          <p class="text-muted small mb-3">Cadastre um áudio de oração com imagem de referência.</p>
          <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-prayers-store" enctype="multipart/form-data" id="prayerCreateForm">
            <div class="mb-2">
              <label class="form-label">Título</label>
              <input class="form-control" type="text" name="title" maxlength="180" required placeholder="Ex.: Oração para renovar a esperança">
            </div>
            <div class="mb-2">
              <label class="form-label">Imagem de referência (opcional)</label>
              <input class="form-control" type="file" name="reference_image" id="prayerReferenceImageInput" accept="image/*">
            </div>
            <div class="mb-2">
              <div class="guided-image-preview" id="prayerImagePreviewBox">
                <img id="prayerImagePreview" alt="Pré-visualização da imagem da oração">
                <span id="prayerImagePreviewHint">Pré-visualização da imagem</span>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Áudio (obrigatório)</label>
              <input class="form-control" type="file" name="audio_file" accept="audio/*" required>
            </div>
            <button class="btn btn-primary" type="submit" id="prayerCreateBtn"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar oração</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-7">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title mb-2">Reflexões compartilhadas</h5>
          <?php if (empty($sharedEntries)): ?>
            <p class="text-muted mb-0">Nenhuma reflexão compartilhada no momento.</p>
          <?php else: ?>
            <div class="vstack gap-2 guided-shared-list">
              <?php foreach ($sharedEntries as $entry): ?>
                <article class="messenger-shared-card">
                  <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <strong><?php echo htmlspecialchars((string) ($entry['patient_name'] ?? 'Paciente')); ?></strong>
                    <span class="small text-muted"><?php echo !empty($entry['created_at']) ? date('d/m/Y H:i', strtotime((string) $entry['created_at'])) : '-'; ?></span>
                  </div>
                  <div class="small text-muted mb-1"><?php echo htmlspecialchars((string) ($entry['prayer_title'] ?? 'Oração')); ?></div>
                  <div class="small text-muted mb-1">Reflexão</div>
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
        <input type="hidden" name="action" value="therapist-prayers">
        <div class="col-12 col-md-10">
          <label class="form-label mb-1">Buscar título</label>
          <input class="form-control" type="search" name="q" value="<?php echo htmlspecialchars((string) ($filters['q'] ?? '')); ?>" placeholder="Digite parte do título...">
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
              <th>Título</th>
              <th style="width: 170px;">Áudio</th>
              <th style="width: 180px;">Criada em</th>
              <th style="width: 180px;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($prayers)): ?>
              <tr>
                <td colspan="5" class="text-center text-muted py-4">Nenhuma oração cadastrada.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($prayers as $prayer): ?>
                <tr>
                  <td>
                    <?php if (!empty($prayer['reference_image_path'])): ?>
                      <img src="<?php echo $appUrl . '/' . ltrim((string) $prayer['reference_image_path'], '/'); ?>" alt="Imagem" class="guided-table-thumb">
                    <?php else: ?>
                      <div class="guided-table-thumb guided-table-thumb--empty"><i class="fa-regular fa-image"></i></div>
                    <?php endif; ?>
                  </td>
                  <td><?php echo htmlspecialchars((string) ($prayer['title'] ?? '')); ?></td>
                  <td>
                    <audio controls preload="none" class="w-100" style="max-width: 160px; height: 36px;">
                      <source src="<?php echo $appUrl . '/' . ltrim((string) ($prayer['audio_path'] ?? ''), '/'); ?>">
                    </audio>
                  </td>
                  <td><?php echo !empty($prayer['created_at']) ? date('d/m/Y H:i', strtotime((string) $prayer['created_at'])) : '-'; ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-1 flex-nowrap">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary js-edit-prayer-btn"
                        data-id="<?php echo (int) ($prayer['id'] ?? 0); ?>"
                        data-title="<?php echo htmlspecialchars((string) ($prayer['title'] ?? '')); ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#editPrayerModal"
                        title="Editar"
                      >
                        <i class="fa-solid fa-pen"></i>
                      </button>
                      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-prayers-delete" class="m-0 js-delete-prayer-form">
                        <input type="hidden" name="id" value="<?php echo (int) ($prayer['id'] ?? 0); ?>">
                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                      </form>
                    </div>
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

<div class="modal fade" id="editPrayerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar oração</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-prayers-update" enctype="multipart/form-data" id="editPrayerForm">
        <div class="modal-body">
          <input type="hidden" name="id" id="editPrayerId" value="">
          <div class="mb-2">
            <label class="form-label">Título</label>
            <input class="form-control" type="text" name="title" id="editPrayerTitle" maxlength="180" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Nova imagem de referência (opcional)</label>
            <input class="form-control" type="file" name="reference_image" accept="image/*">
          </div>
          <div class="mb-0">
            <label class="form-label">Novo áudio (opcional)</label>
            <input class="form-control" type="file" name="audio_file" accept="audio/*">
            <div class="form-text">Se não anexar novos arquivos, os atuais serão mantidos.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="editPrayerSaveBtn"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar alterações</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var form = document.getElementById('prayerCreateForm');
  var btn = document.getElementById('prayerCreateBtn');
  var editForm = document.getElementById('editPrayerForm');
  var editSaveBtn = document.getElementById('editPrayerSaveBtn');
  var editIdInput = document.getElementById('editPrayerId');
  var editTitleInput = document.getElementById('editPrayerTitle');

  var imageInput = document.getElementById('prayerReferenceImageInput');
  var imagePreview = document.getElementById('prayerImagePreview');
  var imagePreviewHint = document.getElementById('prayerImagePreviewHint');
  var imagePreviewBox = document.getElementById('prayerImagePreviewBox');

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

  if (imageInput && imagePreview && imagePreviewHint && imagePreviewBox) {
    imageInput.addEventListener('change', function () {
      var file = imageInput.files && imageInput.files[0] ? imageInput.files[0] : null;
      if (!file) {
        imagePreview.removeAttribute('src');
        imagePreview.classList.remove('is-visible');
        imagePreviewHint.classList.remove('d-none');
        imagePreviewBox.classList.remove('has-image');
        return;
      }

      var reader = new FileReader();
      reader.onload = function (event) {
        imagePreview.setAttribute('src', String((event.target && event.target.result) || ''));
        imagePreview.classList.add('is-visible');
        imagePreviewHint.classList.add('d-none');
        imagePreviewBox.classList.add('has-image');
      };
      reader.readAsDataURL(file);
    });
  }

  document.querySelectorAll('.js-edit-prayer-btn').forEach(function (btnEdit) {
    btnEdit.addEventListener('click', function () {
      if (!editIdInput || !editTitleInput) {
        return;
      }

      editIdInput.value = btnEdit.getAttribute('data-id') || '';
      editTitleInput.value = btnEdit.getAttribute('data-title') || '';

      if (editSaveBtn) {
        editSaveBtn.disabled = false;
        editSaveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i>Salvar alterações';
      }
    });
  });

  if (editForm && editSaveBtn) {
    editForm.addEventListener('submit', function () {
      if (editSaveBtn.disabled) {
        return false;
      }

      editSaveBtn.disabled = true;
      editSaveBtn.innerHTML = '<i class="fa-solid fa-hourglass-half me-1"></i>Salvando...';
      return true;
    });
  }

  document.querySelectorAll('.js-delete-prayer-form').forEach(function (deleteForm) {
    deleteForm.addEventListener('submit', function (event) {
      var ok = window.confirm('Deseja realmente excluir esta oração?');
      if (!ok) {
        event.preventDefault();
      }
    });
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
