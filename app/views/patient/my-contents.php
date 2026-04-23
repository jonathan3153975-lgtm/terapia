<?php $title = 'Meus conteúdos'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap portal-stack">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Meus conteúdos</h3>
    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=books"><i class="fa-solid fa-book-open-reader me-1"></i>Ver livros</a>
      <a class="btn btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=teratube"><i class="fa-solid fa-circle-play me-1"></i>Ver teraTube</a>
    </div>
  </div>

  <section class="card portal-search-card">
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-lg-7">
          <label class="form-label mb-2" for="myContentsSearchInput">Buscar nos salvos</label>
          <div class="portal-search-field">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input id="myContentsSearchInput" class="form-control" type="search" placeholder="Digite título, descrição ou palavras-chave..." value="<?php echo htmlspecialchars((string) ($search ?? '')); ?>">
          </div>
        </div>
        <div class="col-12 col-lg-5">
          <p class="portal-inline-meta mb-0">A busca vale para livros e vídeos salvos nesta tela.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="card portal-list-card">
    <div class="portal-list-card__header">
      <div>
        <h5 class="card-title mb-1">Livros salvos</h5>
        <p class="text-muted mb-0">Sua seleção de leituras favoritas fica organizada aqui.</p>
      </div>
      <span class="small text-muted"><?php echo count($favoriteBooks ?? []); ?> item(ns)</span>
    </div>
    <div class="portal-list-card__body">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Título</th>
              <th>Salvo em</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody id="myContentsTableBody">
            <?php if (empty($favoriteBooks)): ?>
              <tr id="myContentsEmptyRow">
                <td colspan="3" class="text-center text-muted py-4">Você ainda não salvou nenhum livro.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($favoriteBooks as $book): ?>
                <tr class="my-contents-book-row" data-search="<?php echo htmlspecialchars(strtolower((string) ($book['title'] ?? ''))); ?>">
                  <td><?php echo htmlspecialchars((string) ($book['title'] ?? '-')); ?></td>
                  <td><?php echo !empty($book['favorited_at']) ? date('d/m/Y H:i', strtotime((string) $book['favorited_at'])) : '-'; ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <a class="btn btn-sm btn-outline-secondary" href="<?php echo $appUrl; ?>/patient.php?action=book-view&id=<?php echo (int) ($book['id'] ?? 0); ?>#toolbar=0&navpanes=0&scrollbar=0&statusbar=0&messages=0" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-eye me-1"></i>Visualizar</a>
                      <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=book-toggle-favorite" class="m-0">
                        <input type="hidden" name="book_id" value="<?php echo (int) ($book['id'] ?? 0); ?>">
                        <input type="hidden" name="redirect_action" value="my-contents">
                        <button class="btn btn-sm btn-outline-warning" type="submit"><i class="fa-solid fa-bookmark-slash me-1"></i>Remover</button>
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

  </section>

  <section class="card portal-list-card">
    <div class="portal-list-card__header">
      <div>
        <h5 class="card-title mb-1">Vídeos salvos (teraTube)</h5>
        <p class="text-muted mb-0">Seus vídeos favoritos continuam disponíveis em um só lugar.</p>
      </div>
      <span class="small text-muted"><?php echo count($favoriteVideos ?? []); ?> item(ns)</span>
    </div>
    <div class="portal-list-card__body">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Título</th>
              <th>Nota média</th>
              <th>Salvo em</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody id="myContentVideosTableBody">
            <?php if (empty($favoriteVideos)): ?>
              <tr id="myContentVideosEmptyRow">
                <td colspan="4" class="text-center text-muted py-4">Você ainda não salvou nenhum vídeo.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($favoriteVideos as $video): ?>
                <tr class="my-contents-video-row" data-search="<?php echo htmlspecialchars(strtolower((string) (($video['title'] ?? '') . ' ' . ($video['description_text'] ?? '') . ' ' . ($video['keywords'] ?? '')))); ?>">
                  <td><?php echo htmlspecialchars((string) ($video['title'] ?? '-')); ?></td>
                  <td><?php echo number_format((float) ($video['average_rating'] ?? 0), 1, ',', '.'); ?></td>
                  <td><?php echo !empty($video['favorited_at']) ? date('d/m/Y H:i', strtotime((string) $video['favorited_at'])) : '-'; ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <a class="btn btn-sm btn-outline-secondary" href="<?php echo $appUrl; ?>/patient.php?action=teratube-watch&id=<?php echo (int) ($video['id'] ?? 0); ?>"><i class="fa-solid fa-play me-1"></i>Assistir</a>
                      <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=teratube-toggle-favorite" class="m-0">
                        <input type="hidden" name="video_id" value="<?php echo (int) ($video['id'] ?? 0); ?>">
                        <input type="hidden" name="redirect_action" value="my-contents">
                        <button class="btn btn-sm btn-outline-warning" type="submit"><i class="fa-solid fa-bookmark-slash me-1"></i>Remover</button>
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
  </section>
</div>

<script>
window.addEventListener('load', function () {
  var searchInput = document.getElementById('myContentsSearchInput');
  var bookRows = Array.prototype.slice.call(document.querySelectorAll('.my-contents-book-row'));
  var videoRows = Array.prototype.slice.call(document.querySelectorAll('.my-contents-video-row'));
  var booksTableBody = document.getElementById('myContentsTableBody');
  var videosTableBody = document.getElementById('myContentVideosTableBody');
  var booksEmptyRow = document.getElementById('myContentsEmptyRow');
  var videosEmptyRow = document.getElementById('myContentVideosEmptyRow');

  var clearDynamicRow = function (id) {
    var row = document.getElementById(id);
    if (row) {
      row.remove();
    }
  };

  var filterTableRows = function (rows, options) {
    var term = options.term;
    var tableBody = options.tableBody;
    var emptyRow = options.emptyRow;
    var emptyDynamicId = options.emptyDynamicId;
    var emptyMessage = options.emptyMessage;
    var colspan = options.colspan;
    var visibleCount = 0;

    clearDynamicRow(emptyDynamicId);

    rows.forEach(function (row) {
      var text = (row.getAttribute('data-search') || '').toLowerCase();
      var match = term === '' || text.indexOf(term) !== -1;
      row.style.display = match ? '' : 'none';
      if (match) {
        visibleCount += 1;
      }
    });

    if (emptyRow) {
      emptyRow.style.display = rows.length === 0 ? '' : 'none';
    }

    if (rows.length > 0 && visibleCount === 0 && tableBody) {
      var tr = document.createElement('tr');
      tr.id = emptyDynamicId;
      tr.innerHTML = '<td colspan="' + colspan + '" class="text-center text-muted py-4">' + emptyMessage + '</td>';
      tableBody.appendChild(tr);
    }
  };

  var applySearch = function () {
    var term = searchInput ? searchInput.value.toLowerCase().trim() : '';

    filterTableRows(bookRows, {
      term: term,
      tableBody: booksTableBody,
      emptyRow: booksEmptyRow,
      emptyDynamicId: 'myContentsBooksNoMatchRow',
      emptyMessage: 'Nenhum livro salvo corresponde à busca.',
      colspan: 3
    });

    filterTableRows(videoRows, {
      term: term,
      tableBody: videosTableBody,
      emptyRow: videosEmptyRow,
      emptyDynamicId: 'myContentsVideosNoMatchRow',
      emptyMessage: 'Nenhum vídeo salvo corresponde à busca.',
      colspan: 4
    });
  };

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      applySearch();
    });
  }

  applySearch();
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>