<?php $title = 'Novo Vídeo - teraTube'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Cadastrar vídeo no teraTube</h4>
    <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-teratube">Voltar</a>
  </div>

  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="card">
    <div class="card-body">
      <form id="teraTubeCreateForm" method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-teratube-store" enctype="multipart/form-data">
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

          <div class="col-md-4">
            <label class="form-label">Fonte do vídeo</label>
            <select class="form-select" id="sourceType" name="source_type" required>
              <option value="upload" selected>Upload de arquivo</option>
              <option value="youtube">Link do YouTube</option>
            </select>
          </div>

          <div class="col-md-8" id="youtubeField" style="display:none;">
            <label class="form-label">Link do YouTube</label>
            <input class="form-control" type="url" name="youtube_url" placeholder="https://www.youtube.com/watch?v=...">
          </div>

          <div class="col-12" id="uploadField">
            <label class="form-label">Arquivo de vídeo</label>
            <input class="form-control" type="file" name="video_file" accept="video/mp4,video/webm,video/ogg,video/quicktime,.mp4,.webm,.ogg,.mov,.m4v">
            <div class="form-text">Formatos aceitos: MP4, WEBM, OGG, MOV e M4V.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Descrição</label>
            <textarea class="form-control" name="description_text" rows="4" placeholder="Descreva o conteúdo terapêutico do vídeo..."></textarea>
          </div>

          <div class="col-12">
            <label class="form-label">Palavras-chave</label>
            <input class="form-control" name="keywords" maxlength="500" placeholder="ex: ansiedade, respiração, autoestima">
            <div class="form-text">Separe por vírgula para facilitar a busca dos pacientes.</div>
          </div>
        </div>

        <div class="mt-4 d-flex gap-2">
          <button class="btn btn-primary" type="submit">Salvar vídeo</button>
          <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-teratube">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var form = document.getElementById('teraTubeCreateForm');
  var sourceType = document.getElementById('sourceType');
  var uploadField = document.getElementById('uploadField');
  var youtubeField = document.getElementById('youtubeField');
  var videoInput = form ? form.querySelector('input[name="video_file"]') : null;
  var youtubeInput = form ? form.querySelector('input[name="youtube_url"]') : null;

  var syncSourceFields = function () {
    var type = sourceType ? sourceType.value : 'upload';
    var isYouTube = type === 'youtube';

    if (uploadField) {
      uploadField.style.display = isYouTube ? 'none' : '';
    }
    if (youtubeField) {
      youtubeField.style.display = isYouTube ? '' : 'none';
    }

    if (videoInput) {
      videoInput.required = !isYouTube;
    }
    if (youtubeInput) {
      youtubeInput.required = isYouTube;
    }
  };

  if (sourceType) {
    sourceType.addEventListener('change', syncSourceFields);
  }

  syncSourceFields();

  if (form) {
    form.addEventListener('submit', function (event) {
      if (!window.FormSubmitGuard.lock(form, 'Salvando...')) {
        event.preventDefault();
      }
    });
  }
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>