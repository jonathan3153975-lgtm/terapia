function maskCpf(v) {
  return v.replace(/\D/g, '').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
}

function maskPhone(v) {
  v = v.replace(/\D/g, '');
  if (v.length > 10) return v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
  return v.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
}

$(document).on('input', '.mask-cpf', function() { this.value = maskCpf(this.value); });
$(document).on('input', '.mask-phone', function() { this.value = maskPhone(this.value); });

function generatePassword(length = 12) {
  const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
  const lower = 'abcdefghijkmnopqrstuvwxyz';
  const nums = '23456789';
  const sym = '!@#$%*?';
  const all = upper + lower + nums + sym;
  let p = upper[Math.floor(Math.random()*upper.length)] + lower[Math.floor(Math.random()*lower.length)] + nums[Math.floor(Math.random()*nums.length)] + sym[Math.floor(Math.random()*sym.length)];
  for (let i = p.length; i < length; i++) p += all[Math.floor(Math.random()*all.length)];
  return p.split('').sort(() => Math.random()-0.5).join('');
}

const FormSubmitGuard = {
  lock(form, submittingText = 'Salvando...') {
    if (!form || form.dataset.submitting === '1') {
      return false;
    }

    form.dataset.submitting = '1';
    const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
    buttons.forEach((btn) => {
      if (!btn.dataset.originalLabel) {
        btn.dataset.originalLabel = btn.tagName.toLowerCase() === 'button' ? btn.innerHTML : btn.value;
      }
      btn.disabled = true;
      if (btn.tagName.toLowerCase() === 'button') {
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + submittingText;
      } else {
        btn.value = submittingText;
      }
    });

    return true;
  },

  unlock(form) {
    if (!form) {
      return;
    }

    form.dataset.submitting = '0';
    const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
    buttons.forEach((btn) => {
      btn.disabled = false;
      if (btn.dataset.originalLabel) {
        if (btn.tagName.toLowerCase() === 'button') {
          btn.innerHTML = btn.dataset.originalLabel;
        } else {
          btn.value = btn.dataset.originalLabel;
        }
      }
    });
  }
};

window.FormSubmitGuard = FormSubmitGuard;

(function() {
  function createPageButton(label, page, currentPage, disabled) {
    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'records-tabulation-btn' + (page === currentPage ? ' is-active' : '');
    button.textContent = label;
    button.disabled = !!disabled;
    button.dataset.page = String(page);
    return button;
  }

  function initializeRecordsTabulation() {
    var tables = document.querySelectorAll('table.table');
    tables.forEach(function(table, index) {
      if (table.dataset.tabulationReady === '1' || table.hasAttribute('data-no-tabulation')) {
        return;
      }

      var tbody = table.querySelector('tbody');
      if (!tbody) {
        return;
      }

      var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
      if (rows.length === 0) {
        return;
      }

      var headerCount = table.querySelectorAll('thead th').length;
      var validRows = rows.filter(function(row) {
        var cells = row.querySelectorAll('td');
        if (cells.length === 0) {
          return false;
        }

        var colspanCell = row.querySelector('td[colspan]');
        var fullSpan = false;
        if (colspanCell) {
          var colspan = Number(colspanCell.getAttribute('colspan') || 0);
          fullSpan = headerCount > 0 ? colspan >= headerCount : colspan > 1;
        }
        return !fullSpan;
      });

      if (validRows.length === 0) {
        return;
      }

      table.dataset.tabulationReady = '1';
      var tableId = table.id || ('recordsTable' + index);
      table.id = tableId;

      var pageSize = 10;
      var currentPage = 1;
      var totalPages = Math.max(1, Math.ceil(validRows.length / pageSize));
      var rowsData = validRows.map(function(row) {
        return {
          row: row,
          text: (row.textContent || '').toLowerCase()
        };
      });
      var filteredRows = validRows.slice();

      var controls = document.createElement('div');
      controls.className = 'records-tabulation-controls';
      controls.innerHTML = '' +
        '<div class="records-tabulation-toolbar">' +
          '<div class="records-tabulation-search-wrap">' +
            '<input type="search" class="records-tabulation-search" placeholder="Buscar na lista..." aria-label="Buscar na lista">' +
          '</div>' +
          '<div class="records-tabulation-page-size-wrap">' +
            '<label class="records-tabulation-page-size-label">Itens por página</label>' +
            '<select class="records-tabulation-page-size" aria-label="Itens por página">' +
              '<option value="10" selected>10</option>' +
              '<option value="20">20</option>' +
              '<option value="50">50</option>' +
              '<option value="100">100</option>' +
            '</select>' +
          '</div>' +
        '</div>' +
        '<div class="records-tabulation-footer">' +
          '<div class="records-tabulation-summary"></div>' +
          '<div class="records-tabulation-pages"></div>' +
        '</div>';

      var wrapper = table.closest('.table-responsive') || table.parentElement;
      wrapper.insertAdjacentElement('afterend', controls);

      var summary = controls.querySelector('.records-tabulation-summary');
      var pages = controls.querySelector('.records-tabulation-pages');
      var searchInput = controls.querySelector('.records-tabulation-search');
      var pageSizeSelect = controls.querySelector('.records-tabulation-page-size');

      function applyFilter() {
        var term = ((searchInput && searchInput.value) || '').trim().toLowerCase();
        if (!term) {
          filteredRows = validRows.slice();
          return;
        }

        filteredRows = rowsData
          .filter(function(item) {
            return item.text.indexOf(term) !== -1;
          })
          .map(function(item) {
            return item.row;
          });
      }

      function render() {
        applyFilter();

        totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize));
        if (currentPage > totalPages) {
          currentPage = totalPages;
        }

        var start = (currentPage - 1) * pageSize;
        var end = start + pageSize;

        validRows.forEach(function(row, rowIndex) {
          var visible = filteredRows.indexOf(row) !== -1;
          if (!visible) {
            row.style.display = 'none';
            return;
          }

          var filteredIndex = filteredRows.indexOf(row);
          row.style.display = filteredIndex >= start && filteredIndex < end ? '' : 'none';
        });

        var initial = filteredRows.length === 0 ? 0 : start + 1;
        var final = Math.min(end, filteredRows.length);
        summary.textContent = 'Exibindo ' + initial + ' a ' + final + ' de ' + filteredRows.length + ' registros';
        if (filteredRows.length !== validRows.length) {
          summary.textContent += ' (filtrado de ' + validRows.length + ')';
        }

        pages.innerHTML = '';
        if (filteredRows.length <= pageSize) {
          pages.style.display = 'none';
          return;
        }

        pages.style.display = 'inline-flex';
        pages.appendChild(createPageButton('Anterior', Math.max(1, currentPage - 1), currentPage, currentPage === 1));

        var pageStart = Math.max(1, currentPage - 2);
        var pageEnd = Math.min(totalPages, currentPage + 2);
        for (var p = pageStart; p <= pageEnd; p += 1) {
          pages.appendChild(createPageButton(String(p), p, currentPage, false));
        }

        pages.appendChild(createPageButton('Próxima', Math.min(totalPages, currentPage + 1), currentPage, currentPage === totalPages));
      }

      controls.addEventListener('click', function(event) {
        var target = event.target;
        if (!(target instanceof HTMLElement) || !target.classList.contains('records-tabulation-btn')) {
          return;
        }

        var page = Number(target.dataset.page || currentPage);
        if (!Number.isFinite(page) || page < 1 || page > totalPages || page === currentPage) {
          return;
        }

        currentPage = page;
        render();
      });

      if (searchInput) {
        searchInput.addEventListener('input', function() {
          currentPage = 1;
          render();
        });
      }

      if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', function() {
          var selectedSize = Number(pageSizeSelect.value || '10');
          if (!Number.isFinite(selectedSize) || selectedSize <= 0) {
            selectedSize = 10;
          }

          pageSize = selectedSize;
          currentPage = 1;
          render();
        });
      }

      render();
    });
  }

  window.initializeRecordsTabulation = initializeRecordsTabulation;
  window.addEventListener('load', initializeRecordsTabulation);
})();
