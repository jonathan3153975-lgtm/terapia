<?php $title = 'teraTube'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">teraTube</h3>
    <a class="btn btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=my-contents"><i class="fa-solid fa-bookmark me-1"></i>Meus conteúdos</a>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-lg-8">
          <label class="form-label mb-1" for="teraTubePatientSearchInput">Pesquisar vídeos</label>
          <input id="teraTubePatientSearchInput" class="form-control" type="search" placeholder="Título, descrição ou palavras-chave..." value="<?php echo htmlspecialchars((string) ($search ?? '')); ?>">
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3" id="teraTubePatientGrid">
    <?php if (empty($videos)): ?>
      <div class="col-12" id="teraTubePatientEmptyRow">
        <div class="card">
          <div class="card-body text-center text-muted py-4">Ainda não há vídeos disponíveis para você.</div>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($videos as $video): ?>
        <?php $videoId = (int) ($video['id'] ?? 0); ?>
        <?php $isFavorite = !empty($favoriteMap[$videoId]); ?>
        <div class="col-12 col-md-6 col-xl-4 teratube-patient-card" data-search="<?php echo htmlspecialchars(strtolower((string) (($video['title'] ?? '') . ' ' . ($video['description_text'] ?? '') . ' ' . ($video['keywords'] ?? '')))); ?>">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body d-flex flex-column">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge <?php echo (string) ($video['source_type'] ?? '') === 'youtube' ? 'text-bg-danger' : 'text-bg-primary'; ?>"><?php echo (string) ($video['source_type'] ?? '') === 'youtube' ? 'YouTube' : 'Arquivo'; ?></span>
                <small class="text-muted"><i class="fa-solid fa-star text-warning"></i> <?php echo number_format((float) ($video['average_rating'] ?? 0), 1, ',', '.'); ?></small>
              </div>

              <h5 class="card-title mb-2"><?php echo htmlspecialchars((string) ($video['title'] ?? '-')); ?></h5>
              <?php $descriptionPreview = (string) ($video['description_text'] ?? ''); ?>
              <?php if (strlen($descriptionPreview) > 160) { $descriptionPreview = substr($descriptionPreview, 0, 160) . '...'; } ?>
              <p class="text-muted small mb-3"><?php echo htmlspecialchars($descriptionPreview); ?></p>

              <?php if (!empty($video['keywords'])): ?>
                <div class="small text-secondary mb-3"># <?php echo htmlspecialchars((string) $video['keywords']); ?></div>
              <?php endif; ?>

              <div class="mt-auto d-flex gap-2 flex-wrap">
                <a class="btn btn-sm btn-primary" href="<?php echo $appUrl; ?>/patient.php?action=teratube-watch&id=<?php echo $videoId; ?>"><i class="fa-solid fa-play me-1"></i>Assistir</a>
                <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=teratube-toggle-favorite" class="m-0">
                  <input type="hidden" name="video_id" value="<?php echo $videoId; ?>">
                  <input type="hidden" name="redirect_action" value="teratube">
                  <button class="btn btn-sm <?php echo $isFavorite ? 'btn-outline-warning' : 'btn-outline-secondary'; ?>" type="submit"><i class="fa-solid <?php echo $isFavorite ? 'fa-bookmark-slash' : 'fa-bookmark'; ?> me-1"></i><?php echo $isFavorite ? 'Remover' : 'Salvar'; ?></button>
                </form>
              </div>
            </div>
            <div class="card-footer bg-white border-0 pt-0">
              <small class="text-muted"><?php echo (int) ($video['comment_count'] ?? 0); ?> comentário(s)</small>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var searchInput = document.getElementById('teraTubePatientSearchInput');
  var cards = Array.prototype.slice.call(document.querySelectorAll('.teratube-patient-card'));
  var emptyRow = document.getElementById('teraTubePatientEmptyRow');
  var grid = document.getElementById('teraTubePatientGrid');

  var removeDynamicEmpty = function () {
    var existing = document.getElementById('teraTubePatientNoMatch');
    if (existing) {
      existing.remove();
    }
  };

  var applyFilter = function () {
    var term = searchInput ? searchInput.value.toLowerCase().trim() : '';
    var visibleCount = 0;

    removeDynamicEmpty();

    cards.forEach(function (card) {
      var text = (card.getAttribute('data-search') || '').toLowerCase();
      var show = term === '' || text.indexOf(term) !== -1;
      card.style.display = show ? '' : 'none';
      if (show) {
        visibleCount += 1;
      }
    });

    if (emptyRow) {
      emptyRow.style.display = cards.length === 0 ? '' : 'none';
    }

    if (cards.length > 0 && visibleCount === 0 && grid) {
      var col = document.createElement('div');
      col.className = 'col-12';
      col.id = 'teraTubePatientNoMatch';
      col.innerHTML = '<div class="card"><div class="card-body text-center text-muted py-4">Nenhum vídeo encontrado para essa busca.</div></div>';
      grid.appendChild(col);
    }
  };

  if (searchInput) {
    searchInput.addEventListener('input', applyFilter);
  }

  applyFilter();
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>