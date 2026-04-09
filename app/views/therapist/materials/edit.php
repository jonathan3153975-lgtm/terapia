<?php $title = 'Editar Material'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>

<!-- FilePond -->
<link rel="stylesheet" href="https://unpkg.com/filepond@^4/dist/filepond.min.css">
<link rel="stylesheet" href="https://unpkg.com/filepond-plugin-image-preview@^4/dist/filepond-plugin-image-preview.min.css">

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

          <?php if (!empty($assets)): ?>
          <div class="col-12">
            <label class="form-label">Arquivos existentes <span class="text-muted fw-normal">(marque para remover)</span></label>
            <div class="material-assets-existing-list">
              <?php foreach ($assets as $asset): ?>
                <?php
                  $assetType = $asset['asset_type'] ?? '';
                  $assetName = $asset['file_name'] ?? 'Arquivo';
                  $iconMap = ['pdf' => 'fa-file-pdf', 'image' => 'fa-file-image', 'video' => 'fa-file-video', 'url' => 'fa-link'];
                  $colorMap = ['pdf' => 'text-danger', 'image' => 'text-primary', 'video' => 'text-warning', 'url' => 'text-info'];
                  $icon = $iconMap[$assetType] ?? 'fa-file';
                  $color = $colorMap[$assetType] ?? 'text-secondary';
                ?>
                <label class="material-asset-chip" for="asset_<?php echo (int) $asset['id']; ?>">
                  <i class="fa-solid <?php echo $icon; ?> <?php echo $color; ?>"></i>
                  <span class="material-asset-chip-name"><?php echo htmlspecialchars((string) $assetName); ?></span>
                  <input class="form-check-input ms-auto flex-shrink-0 js-remove-asset-check" type="checkbox" name="remove_asset_ids[]" value="<?php echo (int) $asset['id']; ?>" id="asset_<?php echo (int) $asset['id']; ?>">
                  <span class="material-asset-chip-remove"><i class="fa-solid fa-trash-can"></i> Remover</span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <div class="col-12">
            <label class="form-label">Adicionar novos arquivos <span class="text-muted fw-normal">(PDF, imagem e vídeo — arraste ou clique)</span></label>
            <input class="filepond-input" type="file" name="material_files[]" id="materialFilesInput" accept=".pdf,image/*,video/*" multiple>
          </div>

          <div class="col-12">
            <label class="form-label">Adicionar links URL <span class="text-muted fw-normal">(um por linha)</span></label>
            <textarea class="form-control" name="material_links" rows="3" placeholder="https://exemplo.com&#10;https://outro.com"></textarea>
          </div>

          <div class="col-12">
            <label class="form-label d-flex align-items-center justify-content-between gap-2">
              Código HTML do material <span class="text-muted fw-normal">(opcional)</span>
              <button class="btn btn-sm btn-outline-secondary" type="button" id="toggleHtmlPreviewBtn"><i class="fa-solid fa-eye me-1"></i>Pré-visualizar</button>
            </label>
            <textarea class="form-control font-monospace" name="custom_html" id="customHtmlInput" rows="10" style="font-size: .85rem;"><?php echo htmlspecialchars((string) ($material['custom_html'] ?? '')); ?></textarea>
            <div id="htmlPreviewBox" class="html-preview-box d-none">
              <div class="html-preview-toolbar">
                <span class="html-preview-label"><i class="fa-solid fa-eye me-1"></i>Pré-visualização</span>
                <button class="btn btn-sm btn-outline-secondary py-0" type="button" id="closeHtmlPreviewBtn"><i class="fa-solid fa-xmark"></i></button>
              </div>
              <div id="htmlPreviewContent" class="html-preview-content"></div>
            </div>
          </div>
        </div>

        <div class="mt-4 d-flex gap-2">
          <button class="btn btn-primary" type="submit">Salvar alterações</button>
          <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials-show&id=<?php echo (int) $material['id']; ?>">Visualizar</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://unpkg.com/filepond-plugin-image-preview@^4/dist/filepond-plugin-image-preview.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-type@^1/dist/filepond-plugin-file-validate-type.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-size@^2/dist/filepond-plugin-file-validate-size.min.js"></script>
<script src="https://unpkg.com/filepond@^4/dist/filepond.min.js"></script>

<script>
window.addEventListener('load', function () {
  /* ---------- Quill ---------- */
  var descriptionHtmlInput = document.getElementById('descriptionHtmlInput');
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

  /* ---------- FilePond ---------- */
  FilePond.registerPlugin(
    FilePondPluginImagePreview,
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize
  );

  FilePond.create(document.getElementById('materialFilesInput'), {
    allowMultiple: true,
    allowReorder: true,
    storeAsFile: true,
    allowProcess: false,
    maxFileSize: '50MB',
    labelIdle: '<span class="filepond--label-action"><i class="fa-solid fa-cloud-arrow-up me-1"></i>Arraste arquivos aqui ou <u>clique para selecionar</u></span>',
    labelMaxFileSizeExceeded: 'Arquivo muito grande (máx 50 MB)',
    labelFileTypeNotAllowed: 'Tipo não permitido',
    acceptedFileTypes: ['.pdf', '.jpg', '.jpeg', '.png', '.webp', '.gif', '.bmp', '.mp4', '.mov', '.avi', '.mkv', '.webm'],
    server: null,
    instantUpload: false,
    credits: false
  });

  /* ---------- Chips de remoção ---------- */
  document.querySelectorAll('.js-remove-asset-check').forEach(function (check) {
    var chip = check.closest('.material-asset-chip');
    check.addEventListener('change', function () {
      if (chip) {
        chip.classList.toggle('material-asset-chip--marked', check.checked);
      }
    });
  });

  /* ---------- Preview HTML ---------- */
  var customHtmlInput = document.getElementById('customHtmlInput');
  var htmlPreviewBox = document.getElementById('htmlPreviewBox');
  var htmlPreviewContent = document.getElementById('htmlPreviewContent');
  var toggleHtmlPreviewBtn = document.getElementById('toggleHtmlPreviewBtn');
  var closeHtmlPreviewBtn = document.getElementById('closeHtmlPreviewBtn');

  var updateHtmlPreview = function () {
    if (htmlPreviewContent) {
      htmlPreviewContent.innerHTML = customHtmlInput ? customHtmlInput.value : '';
    }
  };

  if (toggleHtmlPreviewBtn) {
    toggleHtmlPreviewBtn.addEventListener('click', function () {
      if (htmlPreviewBox.classList.contains('d-none')) {
        updateHtmlPreview();
        htmlPreviewBox.classList.remove('d-none');
        toggleHtmlPreviewBtn.innerHTML = '<i class="fa-solid fa-eye-slash me-1"></i>Fechar prévia';
      } else {
        htmlPreviewBox.classList.add('d-none');
        toggleHtmlPreviewBtn.innerHTML = '<i class="fa-solid fa-eye me-1"></i>Pré-visualizar';
      }
    });
  }

  if (closeHtmlPreviewBtn) {
    closeHtmlPreviewBtn.addEventListener('click', function () {
      htmlPreviewBox.classList.add('d-none');
      toggleHtmlPreviewBtn.innerHTML = '<i class="fa-solid fa-eye me-1"></i>Pré-visualizar';
    });
  }

  if (customHtmlInput) {
    customHtmlInput.addEventListener('input', function () {
      if (!htmlPreviewBox.classList.contains('d-none')) {
        updateHtmlPreview();
      }
    });
  }

  /* ---------- Submit ---------- */
  var materialForm = document.getElementById('materialEditForm');
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
