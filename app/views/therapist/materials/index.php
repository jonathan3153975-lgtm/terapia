<?php $title = 'Materiais'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0">Materiais</h3>
    <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials-create"><i class="fa-solid fa-plus"></i> Novo material</a>
  </div>

  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="card">
    <div class="card-body p-3 pb-0">
      <div class="row g-2 align-items-center mb-3">
        <div class="col-12 col-lg-6">
          <label class="form-label mb-1" for="materialsSearchInput">Buscar materiais</label>
          <input id="materialsSearchInput" class="form-control" type="search" placeholder="Digite título, tipo ou descrição..." value="<?php echo htmlspecialchars((string) ($search ?? '')); ?>">
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="materialsTable">
          <thead>
            <tr>
              <th>Título</th>
              <th>Tipo</th>
              <th>Enviado</th>
              <th>Criado em</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody id="materialsTableBody">
            <?php if (empty($materials)): ?>
              <tr id="materialsEmptyRow">
                <td colspan="5" class="text-center text-muted py-4">Nenhum material cadastrado.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($materials as $material): ?>
                <?php
                  $typeLabel = ($material['type'] ?? '') === 'exercise' ? 'Exercício' : 'Material de apoio';
                  $searchBlob = strtolower(trim((string) ($material['title'] ?? '') . ' ' . $typeLabel . ' ' . strip_tags((string) ($material['description_html'] ?? ''))));
                ?>
                <tr class="materials-data-row" data-search="<?php echo htmlspecialchars($searchBlob); ?>">
                  <td><?php echo htmlspecialchars((string) ($material['title'] ?? '-')); ?></td>
                  <td><?php echo htmlspecialchars($typeLabel); ?></td>
                  <td><?php echo (int) ($material['sent_count'] ?? 0); ?> paciente(s)</td>
                  <td><?php echo !empty($material['created_at']) ? date('d/m/Y H:i', strtotime((string) $material['created_at'])) : '-'; ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-1 flex-nowrap">
                      <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials-show&id=<?php echo (int) $material['id']; ?>" title="Visualizar"><i class="fa-solid fa-eye"></i></a>
                      <button class="btn btn-sm btn-outline-primary js-send-material-btn" style="width:32px;padding:0;line-height:1.8;" type="button" title="Encaminhar" data-bs-toggle="modal" data-bs-target="#sendMaterialModal" data-material-id="<?php echo (int) $material['id']; ?>" data-material-title="<?php echo htmlspecialchars((string) ($material['title'] ?? '')); ?>"><i class="fa-solid fa-paper-plane"></i></button>
                      <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials-edit&id=<?php echo (int) $material['id']; ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
                      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials-delete" class="d-flex m-0 js-delete-material-form" data-material-title="<?php echo htmlspecialchars((string) ($material['title'] ?? '')); ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $material['id']; ?>">
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
  </div>
</div>

<div class="modal fade" id="sendMaterialModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Encaminhar material</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <form id="sendMaterialForm" method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials-send">
        <div class="modal-body">
          <input type="hidden" name="material_id" id="sendMaterialIdInput" value="">

          <div class="mb-3">
            <div class="small text-muted">Material selecionado</div>
            <div id="sendMaterialTitle" class="fw-semibold">-</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Mensagem (opcional)</label>
            <textarea class="form-control" name="message" rows="3" placeholder="Escreva uma mensagem para acompanhar o material..."></textarea>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label mb-0">Selecione os pacientes</label>
            <button type="button" class="btn btn-sm btn-light" id="toggleAllPatientsBtn">Selecionar/Desmarcar todos</button>
          </div>

          <div class="row g-2" style="max-height: 38vh; overflow: auto;">
            <?php if (empty($patients)): ?>
              <div class="col-12 text-muted">Nenhum paciente encontrado.</div>
            <?php else: ?>
              <?php foreach ($patients as $patient): ?>
                <div class="col-md-6">
                  <label class="form-check border rounded p-2 d-flex align-items-center gap-2">
                    <input class="form-check-input js-patient-check" type="checkbox" name="patient_ids[]" value="<?php echo (int) $patient['id']; ?>">
                    <span><?php echo htmlspecialchars((string) $patient['name']); ?></span>
                  </label>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Encaminhar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var searchInput = document.getElementById('materialsSearchInput');
  var rows = document.querySelectorAll('.materials-data-row');
  var tableBody = document.getElementById('materialsTableBody');
  var emptyRow = document.getElementById('materialsEmptyRow');

  var sendModal = document.getElementById('sendMaterialModal');
  var sendMaterialIdInput = document.getElementById('sendMaterialIdInput');
  var sendMaterialTitle = document.getElementById('sendMaterialTitle');
  var toggleAllPatientsBtn = document.getElementById('toggleAllPatientsBtn');
  var patientChecks = document.querySelectorAll('.js-patient-check');

  if (searchInput && rows.length > 0) {
    searchInput.addEventListener('input', function () {
      var term = searchInput.value.toLowerCase().trim();
      var visibleCount = 0;

      rows.forEach(function (row) {
        var blob = (row.getAttribute('data-search') || '').toLowerCase();
        var match = term === '' || blob.indexOf(term) !== -1;
        row.style.display = match ? '' : 'none';
        if (match) {
          visibleCount++;
        }
      });

      var existingDynamicEmpty = document.getElementById('materialsNoSearchMatchRow');
      if (existingDynamicEmpty) {
        existingDynamicEmpty.remove();
      }

      if (visibleCount === 0 && tableBody) {
        var tr = document.createElement('tr');
        tr.id = 'materialsNoSearchMatchRow';
        tr.innerHTML = '<td colspan="5" class="text-center text-muted py-4">Nenhum material encontrado para a busca.</td>';
        tableBody.appendChild(tr);
      }

      if (emptyRow) {
        emptyRow.style.display = 'none';
      }
    });
  }

  document.querySelectorAll('.js-send-material-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var materialId = btn.getAttribute('data-material-id') || '';
      var materialTitle = btn.getAttribute('data-material-title') || '-';

      if (sendMaterialIdInput) {
        sendMaterialIdInput.value = materialId;
      }

      if (sendMaterialTitle) {
        sendMaterialTitle.textContent = materialTitle;
      }

      patientChecks.forEach(function (check) {
        check.checked = false;
      });
    });
  });

  if (toggleAllPatientsBtn) {
    toggleAllPatientsBtn.addEventListener('click', function () {
      var allChecked = true;
      patientChecks.forEach(function (check) {
        if (!check.checked) {
          allChecked = false;
        }
      });

      patientChecks.forEach(function (check) {
        check.checked = !allChecked;
      });
    });
  }

  var sendMaterialForm = document.getElementById('sendMaterialForm');
  if (sendMaterialForm) {
    sendMaterialForm.addEventListener('submit', function (e) {
      var selected = 0;
      patientChecks.forEach(function (check) {
        if (check.checked) {
          selected++;
        }
      });

      if (selected === 0) {
        e.preventDefault();
        if (typeof Swal !== 'undefined') {
          Swal.fire('Seleção obrigatória', 'Selecione ao menos um paciente.', 'warning');
        } else {
          alert('Selecione ao menos um paciente.');
        }
        return;
      }

      if (!window.FormSubmitGuard.lock(sendMaterialForm, 'Enviando...')) {
        e.preventDefault();
      }
    });
  }

  document.querySelectorAll('.js-delete-material-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (form.dataset.confirmed === '1') {
        return;
      }

      e.preventDefault();
      var materialTitle = form.getAttribute('data-material-title') || 'este material';

      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: 'Confirmar exclusão',
          text: 'Deseja realmente excluir o material "' + materialTitle + '"?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sim, excluir',
          cancelButtonText: 'Cancelar',
          confirmButtonColor: '#c0392b'
        }).then(function (result) {
          if (!result.isConfirmed) {
            return;
          }
          form.dataset.confirmed = '1';
          form.submit();
        });
        return;
      }

      if (confirm('Deseja realmente excluir o material "' + materialTitle + '"?')) {
        form.dataset.confirmed = '1';
        form.submit();
      }
    });
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
