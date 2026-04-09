<?php $title = 'Novo Material'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Cadastrar material</h4>
    <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials">Voltar</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form id="materialForm" method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials-store" enctype="multipart/form-data">
        <input type="hidden" name="description_html" id="descriptionHtmlInput">

        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Título</label>
            <input class="form-control" name="title" required maxlength="180">
          </div>
          <div class="col-md-4">
            <label class="form-label">Tipo</label>
            <select class="form-select" name="type" required>
              <option value="support">Material de apoio</option>
              <option value="exercise">Exercício</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Descrição</label>
            <div id="materialDescriptionEditor" style="min-height: 220px;"></div>
          </div>

          <div class="col-12">
            <label class="form-label">Arquivos (PDF, imagem e vídeo)</label>
            <input class="form-control" type="file" name="material_files[]" id="materialFilesInput" accept=".pdf,image/*,video/*" multiple>
            <div class="form-text">Você pode carregar mais de um arquivo.</div>
            <div id="materialFilesPreview" class="materials-preview-grid mt-2"></div>
          </div>

          <div class="col-12">
            <label class="form-label">Links URL (um por linha)</label>
            <textarea class="form-control" name="material_links" rows="3" placeholder="https://...\nhttps://..."></textarea>
          </div>

          <div class="col-12">
            <label class="form-label">Código HTML do material (opcional)</label>
            <textarea class="form-control" name="custom_html" rows="8" placeholder="Insira aqui o código HTML para criar material direto no sistema."></textarea>
          </div>
        </div>

        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary" type="submit">Salvar material</button>
          <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var materialForm = document.getElementById('materialForm');
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
