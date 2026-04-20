<?php $title = 'Editar Vídeo - teraTube'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Editar vídeo no teraTube</h4>
    <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-teratube">Voltar</a>
  </div>

  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="card">
    <div class="card-body">
      <form id="teraTubeEditForm" method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-teratube-update" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo (int) ($video['id'] ?? 0); ?>">

        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Título</label>
            <input class="form-control" name="title" required maxlength="180" value="<?php echo htmlspecialchars((string) ($video['title'] ?? '')); ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Publicar para pacientes?</label>
            <select class="form-select" name="is_published" required>
              <option value="1" <?php echo (int) ($video['is_published'] ?? 0) === 1 ? 'selected' : ''; ?>>Sim, liberar acesso</option>
              <option value="0" <?php echo (int) ($video['is_published'] ?? 0) === 0 ? 'selected' : ''; ?>>Não, manter como rascunho</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Fonte do vídeo</label>
            <select class="form-select" id="sourceType" name="source_type" required>
              <option value="upload" <?php echo (string) ($video['source_type'] ?? '') === 'upload' ? 'selected' : ''; ?>>Upload de arquivo</option>
              <option value="youtube" <?php echo (string) ($video['source_type'] ?? '') === 'youtube' ? 'selected' : ''; ?>>Link do YouTube</option>
            </select>
          </div>

          <div class="col-md-8" id="youtubeField">
            <label class="form-label">Link do YouTube</label>
            <input class="form-control" type="url" name="youtube_url" value="<?php echo htmlspecialchars((string) ($video['youtube_url'] ?? '')); ?>" placeholder="https://www.youtube.com/watch?v=...">
          </div>

          <div class="col-12" id="uploadField">
            <?php if (!empty($video['video_original_name'])): ?>
              <label class="form-label">Arquivo atual</label>
              <div class="border rounded p-3 d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <div>
                  <div class="fw-semibold"><?php echo htmlspecialchars((string) ($video['video_original_name'] ?? 'Vídeo atual')); ?></div>
                  <small class="text-muted"><?php echo !empty($video['video_size']) ? number_format(((int) $video['video_size']) / (1024 * 1024), 2, ',', '.') . ' MB' : 'Tamanho indisponível'; ?></small>
                </div>
                <a class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-teratube-file&id=<?php echo (int) ($video['id'] ?? 0); ?>">Abrir vídeo</a>
              </div>
            <?php endif; ?>

            <label class="form-label">Substituir arquivo de vídeo</label>
            <input class="form-control" type="file" name="video_file" accept="video/mp4,video/webm,video/ogg,video/quicktime,.mp4,.webm,.ogg,.mov,.m4v">
            <div class="form-text">Envie um novo vídeo apenas se desejar substituir o atual.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Descrição</label>
            <textarea class="form-control" name="description_text" rows="4" placeholder="Descreva o conteúdo terapêutico do vídeo..."><?php echo htmlspecialchars((string) ($video['description_text'] ?? '')); ?></textarea>
          </div>

          <div class="col-12">
            <label class="form-label">Palavras-chave</label>
            <input class="form-control" name="keywords" maxlength="500" value="<?php echo htmlspecialchars((string) ($video['keywords'] ?? '')); ?>" placeholder="ex: ansiedade, respiração, autoestima">
          </div>
        </div>

        <div class="mt-4 d-flex gap-2">
          <button class="btn btn-primary" type="submit">Salvar alterações</button>
          <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-teratube-show&id=<?php echo (int) ($video['id'] ?? 0); ?>">Visualizar</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var form = document.getElementById('teraTubeEditForm');
  var sourceType = document.getElementById('sourceType');
  var uploadField = document.getElementById('uploadField');
  var youtubeField = document.getElementById('youtubeField');
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