<?php $title = 'Livros'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0">Livros</h3>
    <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books-create"><i class="fa-solid fa-plus"></i> Novo livro</a>
  </div>

  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="card">
    <div class="card-body p-3 pb-0">
      <div class="row g-2 align-items-center mb-3">
        <div class="col-12 col-lg-6">
          <label class="form-label mb-1" for="booksSearchInput">Buscar livros</label>
          <input id="booksSearchInput" class="form-control" type="search" placeholder="Digite o título do livro..." value="<?php echo htmlspecialchars((string) ($search ?? '')); ?>">
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Título</th>
              <th>Status</th>
              <th>Favoritos</th>
              <th>Atualizado em</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody id="booksTableBody">
            <?php if (empty($books)): ?>
              <tr id="booksEmptyRow">
                <td colspan="5" class="text-center text-muted py-4">Nenhum livro cadastrado.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($books as $book): ?>
                <?php $isPublished = (int) ($book['is_published'] ?? 0) === 1; ?>
                <tr class="books-data-row" data-search="<?php echo htmlspecialchars(strtolower((string) ($book['title'] ?? ''))); ?>">
                  <td><?php echo htmlspecialchars((string) ($book['title'] ?? '-')); ?></td>
                  <td>
                    <?php if ($isPublished): ?>
                      <span class="badge text-bg-success">Liberado</span>
                    <?php else: ?>
                      <span class="badge text-bg-secondary">Rascunho</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo (int) ($book['favorite_count'] ?? 0); ?></td>
                  <td><?php echo !empty($book['updated_at']) ? date('d/m/Y H:i', strtotime((string) $book['updated_at'])) : '-'; ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-1 flex-nowrap">
                      <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books-show&id=<?php echo (int) $book['id']; ?>" title="Visualizar"><i class="fa-solid fa-eye"></i></a>
                      <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books-edit&id=<?php echo (int) $book['id']; ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
                      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books-toggle-publish" class="d-flex m-0">
                        <input type="hidden" name="id" value="<?php echo (int) $book['id']; ?>">
                        <button class="btn btn-sm <?php echo $isPublished ? 'btn-outline-warning' : 'btn-outline-success'; ?>" style="width:32px;padding:0;line-height:1.8;" type="submit" title="<?php echo $isPublished ? 'Bloquear acesso' : 'Liberar acesso'; ?>"><i class="fa-solid <?php echo $isPublished ? 'fa-lock-open' : 'fa-unlock'; ?>"></i></button>
                      </form>
                      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books-delete" class="d-flex m-0 js-delete-book-form" data-book-title="<?php echo htmlspecialchars((string) ($book['title'] ?? '')); ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $book['id']; ?>">
                        <button class="btn btn-sm btn-outline-danger" style="width:32px;padding:0;line-height:1.8;" type="submit" title="Excluir"><i class="fa-solid fa-trash"></i></button>
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

    <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2" id="booksPaginationWrapper">
      <small class="text-muted mb-0" id="booksPaginationInfo"></small>
      <div class="btn-group" role="group" id="booksPaginationControls"></div>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var searchInput = document.getElementById('booksSearchInput');
  var dataRows = Array.prototype.slice.call(document.querySelectorAll('.books-data-row'));
  var tableBody = document.getElementById('booksTableBody');
  var emptyRow = document.getElementById('booksEmptyRow');
  var paginationInfo = document.getElementById('booksPaginationInfo');
  var paginationControls = document.getElementById('booksPaginationControls');
  var paginationWrapper = document.getElementById('booksPaginationWrapper');
  var rowsPerPage = 10;
  var currentPage = 1;

  var clearDynamicEmptyRow = function () {
    var existingDynamicEmpty = document.getElementById('booksNoSearchMatchRow');
    if (existingDynamicEmpty) {
      existingDynamicEmpty.remove();
    }
  };

  var renderPagination = function (totalItems, totalPages) {
    if (!paginationControls || !paginationInfo || !paginationWrapper) {
      return;
    }

    paginationControls.innerHTML = '';
    if (totalItems === 0 || totalPages <= 1) {
      paginationWrapper.style.display = 'none';
      paginationInfo.textContent = totalItems === 0 ? 'Nenhum registro para exibir.' : '1 página.';
      return;
    }

    paginationWrapper.style.display = '';
    paginationInfo.textContent = 'Página ' + currentPage + ' de ' + totalPages + ' (' + totalItems + ' registros)';

    var prevButton = document.createElement('button');
    prevButton.type = 'button';
    prevButton.className = 'btn btn-sm btn-outline-secondary';
    prevButton.textContent = 'Anterior';
    prevButton.disabled = currentPage <= 1;
    prevButton.addEventListener('click', function () {
      if (currentPage > 1) {
        currentPage--;
        applyFiltersAndPagination();
      }
    });
    paginationControls.appendChild(prevButton);

    var nextButton = document.createElement('button');
    nextButton.type = 'button';
    nextButton.className = 'btn btn-sm btn-outline-secondary';
    nextButton.textContent = 'Próxima';
    nextButton.disabled = currentPage >= totalPages;
    nextButton.addEventListener('click', function () {
      if (currentPage < totalPages) {
        currentPage++;
        applyFiltersAndPagination();
      }
    });
    paginationControls.appendChild(nextButton);
  };

  var applyFiltersAndPagination = function () {
    var term = searchInput ? searchInput.value.toLowerCase().trim() : '';
    var filteredRows = dataRows.filter(function (row) {
      var text = (row.getAttribute('data-search') || '').toLowerCase();
      return term === '' || text.indexOf(term) !== -1;
    });

    clearDynamicEmptyRow();

    var totalItems = filteredRows.length;
    var totalPages = Math.max(1, Math.ceil(totalItems / rowsPerPage));
    if (currentPage > totalPages) {
      currentPage = totalPages;
    }

    dataRows.forEach(function (row) {
      row.style.display = 'none';
    });

    if (dataRows.length === 0) {
      if (emptyRow) {
        emptyRow.style.display = '';
      }
      renderPagination(0, 1);
      return;
    }

    if (totalItems === 0 && tableBody) {
      var tr = document.createElement('tr');
      tr.id = 'booksNoSearchMatchRow';
      tr.innerHTML = '<td colspan="5" class="text-center text-muted py-4">Nenhum livro encontrado para a busca.</td>';
      tableBody.appendChild(tr);
      if (emptyRow) {
        emptyRow.style.display = 'none';
      }
      renderPagination(0, 1);
      return;
    }

    var start = (currentPage - 1) * rowsPerPage;
    var end = start + rowsPerPage;
    filteredRows.slice(start, end).forEach(function (row) {
      row.style.display = '';
    });

    if (emptyRow) {
      emptyRow.style.display = 'none';
    }

    renderPagination(totalItems, totalPages);
  };

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      currentPage = 1;
      applyFiltersAndPagination();
    });
  }

  document.querySelectorAll('.js-delete-book-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      var title = form.getAttribute('data-book-title') || 'este livro';
      if (!window.confirm('Excluir ' + title + '?')) {
        event.preventDefault();
      }
    });
  });

  applyFiltersAndPagination();
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>