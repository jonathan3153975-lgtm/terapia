<?php $title = 'Livros'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Livros</h3>
    <a class="btn btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=my-contents"><i class="fa-solid fa-bookmark me-1"></i>Meus conteúdos</a>
  </div>

  <div class="card">
    <div class="card-body p-3 pb-0">
      <div class="row g-2 align-items-center mb-3">
        <div class="col-12 col-lg-6">
          <label class="form-label mb-1" for="patientBooksSearchInput">Buscar livros</label>
          <input id="patientBooksSearchInput" class="form-control" type="search" placeholder="Digite o título do livro..." value="<?php echo htmlspecialchars((string) ($search ?? '')); ?>">
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Título</th>
              <th>Salvo</th>
              <th>Disponibilizado em</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody id="patientBooksTableBody">
            <?php if (empty($books)): ?>
              <tr id="patientBooksEmptyRow">
                <td colspan="4" class="text-center text-muted py-4">Ainda não há livros disponíveis para você.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($books as $book): ?>
                <?php $isFavorite = !empty($favoriteMap[(int) ($book['id'] ?? 0)]); ?>
                <tr class="patient-books-data-row" data-search="<?php echo htmlspecialchars(strtolower((string) ($book['title'] ?? ''))); ?>">
                  <td><?php echo htmlspecialchars((string) ($book['title'] ?? '-')); ?></td>
                  <td>
                    <?php if ($isFavorite): ?>
                      <span class="badge text-bg-warning">Favorito</span>
                    <?php else: ?>
                      <span class="text-muted small">Não salvo</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo !empty($book['updated_at']) ? date('d/m/Y H:i', strtotime((string) $book['updated_at'])) : '-'; ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <a class="btn btn-sm btn-outline-secondary" href="<?php echo $appUrl; ?>/patient.php?action=book-view&id=<?php echo (int) ($book['id'] ?? 0); ?>#toolbar=0&navpanes=0&scrollbar=0&statusbar=0&messages=0" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-eye me-1"></i>Visualizar</a>
                      <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=book-toggle-favorite" class="m-0">
                        <input type="hidden" name="book_id" value="<?php echo (int) ($book['id'] ?? 0); ?>">
                        <input type="hidden" name="redirect_action" value="books">
                        <button class="btn btn-sm <?php echo $isFavorite ? 'btn-outline-warning' : 'btn-outline-primary'; ?>" type="submit"><i class="fa-solid <?php echo $isFavorite ? 'fa-bookmark-slash' : 'fa-bookmark'; ?> me-1"></i><?php echo $isFavorite ? 'Remover' : 'Salvar'; ?></button>
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

    <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2" id="patientBooksPaginationWrapper">
      <small class="text-muted mb-0" id="patientBooksPaginationInfo"></small>
      <div class="btn-group" role="group" id="patientBooksPaginationControls"></div>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var searchInput = document.getElementById('patientBooksSearchInput');
  var dataRows = Array.prototype.slice.call(document.querySelectorAll('.patient-books-data-row'));
  var tableBody = document.getElementById('patientBooksTableBody');
  var emptyRow = document.getElementById('patientBooksEmptyRow');
  var paginationInfo = document.getElementById('patientBooksPaginationInfo');
  var paginationControls = document.getElementById('patientBooksPaginationControls');
  var paginationWrapper = document.getElementById('patientBooksPaginationWrapper');
  var rowsPerPage = 10;
  var currentPage = 1;

  var clearDynamicEmptyRow = function () {
    var existingDynamicEmpty = document.getElementById('patientBooksNoSearchMatchRow');
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
      tr.id = 'patientBooksNoSearchMatchRow';
      tr.innerHTML = '<td colspan="4" class="text-center text-muted py-4">Nenhum livro encontrado para a busca.</td>';
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

  applyFiltersAndPagination();
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>