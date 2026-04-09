<?php $title = 'Editar Material'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Editar material</h4>
    <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials">Voltar</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form id="materialEditForm" method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials-update" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo (int) $material['id']; ?>">
        <input type="hidden" name="description_html" id="descriptionHtmlInput" value="<?php echo htmlspecialchars((string) ($material['description_html'] ?? '')); ?>">

        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Título</label>
            <input class="form-control" name="title" required maxlength="180" value="<?php echo htmlspecialchars((string) ($material['title'] ?? '')); ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Tipo</label>
            <select class="form-select" name="type" required>
              <option value="support" <?php echo (($material['type'] ?? '') === 'support') ? 'selected' : ''; ?>>Material de apoio</option>
              <option value="exercise" <?php echo (($material['type'] ?? '') === 'exercise') ? 'selected' : ''; ?>>Exercício</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Descrição</label>
            <div id="materialDescriptionEditor" style="min-height: 220px;"></div>
          </div>

          <div class="col-12">
            <label class="form-label">Arquivos existentes</label>
            <?php if (empty($assets)): ?>
              <div class="text-muted">Nenhum arquivo/link cadastrado.</div>
            <?php else: ?>
              <div class="materials-preview-grid mb-2">
                <?php foreach ($assets as $asset): ?>
                  <label class="materials-preview-item" style="cursor: pointer;">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                      <div class="materials-preview-name"><?php echo htmlspecialchars((string) ($asset['file_name'] ?? 'Arquivo')); ?></div>
                      <input class="form-check-input" type="checkbox" name="remove_asset_ids[]" value="<?php echo (int) $asset['id']; ?>" title="Marque para remover">
                    </div>

                    <?php if (($asset['asset_type'] ?? '') === 'image' && !empty($asset['file_path'])): ?>
                      <img class="materials-preview-image" src="<?php echo $appUrl; ?>/<?php echo htmlspecialchars((string) $asset['file_path']); ?>" alt="Imagem">
                    <?php elseif (($asset['asset_type'] ?? '') === 'video' && !empty($asset['file_path'])): ?>
                      <video class="materials-preview-video" src="<?php echo $appUrl; ?>/<?php echo htmlspecialchars((string) $asset['file_path']); ?>" controls></video>
                    <?php elseif (($asset['asset_type'] ?? '') === 'pdf' && !empty($asset['file_path'])): ?>
                      <div class="materials-preview-file"><i class="fa-solid fa-file-pdf"></i> PDF</div>
                    <?php elseif (($asset['asset_type'] ?? '') === 'url' && !empty($asset['file_url'])): ?>
                      <div class="materials-preview-file"><i class="fa-solid fa-link"></i> Link</div>
                    <?php else: ?>
                      <div class="materials-preview-file"><i class="fa-solid fa-file"></i> Arquivo</div>
                    <?php endif; ?>
                  </label>
                <?php endforeach; ?>
              </div>
              <div class="form-text">Marque os itens que deseja remover.</div>
            <?php endif; ?>
          </div>

          <div class="col-12">
            <label class="form-label">Adicionar novos arquivos (PDF, imagem e vídeo)</label>
            <input class="form-control" type="file" name="material_files[]" id="materialFilesInput" accept=".pdf,image/*,video/*" multiple>
            <div id="materialFilesPreview" class="materials-preview-grid mt-2"></div>
          </div>

          <div class="col-12">
            <label class="form-label">Adicionar links URL (um por linha)</label>
            <textarea class="form-control" name="material_links" rows="3" placeholder="https://...\nhttps://..."></textarea>
          </div>

          <div class="col-12">
            <label class="form-label">Código HTML do material (opcional)</label>
            <textarea class="form-control" name="custom_html" rows="8"><?php echo htmlspecialchars((string) ($material['custom_html'] ?? '')); ?></textarea>
          </div>
        </div>

        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary" type="submit">Salvar alterações</button>
          <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials-show&id=<?php echo (int) $material['id']; ?>">Visualizar</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var materialForm = document.getElementById('materialEditForm');
  var descriptionHtmlInput = document.getElementById('descriptionHtmlInput');
  var filesInput = document.getElementById('materialFilesInput');
  var filesPreview = document.getElementById('materialFilesPreview');

  var quill = new Quill('#materialDescriptionEditor', {
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

  quill.root.innerHTML = descriptionHtmlInput.value || '';

  var renderFilePreview = function (files) {
    if (!filesPreview) {
      return;
    }

    filesPreview.innerHTML = '';
    if (!files || files.length === 0) {
      return;
    }

    Array.from(files).forEach(function (file) {
      var item = document.createElement('div');
      item.className = 'materials-preview-item';

      var head = document.createElement('div');
      head.className = 'materials-preview-name';
      head.textContent = file.name;
      item.appendChild(head);

      if (file.type.indexOf('image/') === 0) {
        var img = document.createElement('img');
        img.className = 'materials-preview-image';
        img.src = URL.createObjectURL(file);
        item.appendChild(img);
      } else if (file.type.indexOf('video/') === 0) {
        var video = document.createElement('video');
        video.className = 'materials-preview-video';
        video.src = URL.createObjectURL(file);
        video.controls = true;
        item.appendChild(video);
      } else if (file.type.indexOf('pdf') !== -1 || file.name.toLowerCase().endsWith('.pdf')) {
        var pdf = document.createElement('div');
        pdf.className = 'materials-preview-file';
        pdf.innerHTML = '<i class="fa-solid fa-file-pdf"></i> PDF';
        item.appendChild(pdf);
      } else {
        var generic = document.createElement('div');
        generic.className = 'materials-preview-file';
        generic.innerHTML = '<i class="fa-solid fa-file"></i> Arquivo';
        item.appendChild(generic);
      }

      filesPreview.appendChild(item);
    });
  };

  if (filesInput) {
    filesInput.addEventListener('change', function () {
      renderFilePreview(filesInput.files);
    });
  }

  if (materialForm) {
    materialForm.addEventListener('submit', function (e) {
      if (!window.FormSubmitGuard.lock(materialForm, 'Salvando...')) {
        e.preventDefault();
        return;
      }

      var plain = quill.getText().trim();
      if (plain === '') {
        e.preventDefault();
        window.FormSubmitGuard.unlock(materialForm);
        if (typeof Swal !== 'undefined') {
          Swal.fire('Campo obrigatório', 'Preencha a descrição do material.', 'warning');
        } else {
          alert('Preencha a descrição do material.');
        }
        return;
      }

      descriptionHtmlInput.value = quill.root.innerHTML;
    });
  }
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
