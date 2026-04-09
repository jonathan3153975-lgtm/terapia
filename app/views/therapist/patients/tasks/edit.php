<?php $title = 'Editar Tarefa'; include __DIR__ . '/../../../partials/header.php'; include __DIR__ . '/../../../partials/nav.php'; ?>
<div class="container page-wrap">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="card">
        <div class="card-body">
          <?php include __DIR__ . '/../../../partials/flash-alert.php'; ?>

          <h4 class="mb-3">Editar tarefa</h4>
          <form id="taskEditForm" method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-tasks-update" enctype="multipart/form-data">
            <input type="hidden" name="patient_id" value="<?php echo (int) $patient['id']; ?>">
            <input type="hidden" name="id" value="<?php echo (int) $task['id']; ?>">
            <input type="hidden" name="description" id="taskDescriptionInput" value="<?php echo htmlspecialchars((string) ($task['description'] ?? '')); ?>">
            <input type="hidden" name="material_id" id="linkedMaterialIdInput" value="<?php echo (int) ($task['material_id'] ?? 0); ?>">

            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label">Data</label>
                <input class="form-control" type="date" name="due_date" required value="<?php echo htmlspecialchars((string) ($task['due_date'] ?? '')); ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Título</label>
                <input class="form-control" name="title" required value="<?php echo htmlspecialchars((string) ($task['title'] ?? '')); ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                  <option value="pending" <?php echo (($task['status'] ?? 'pending') === 'pending') ? 'selected' : ''; ?>>Pendente</option>
                  <option value="done" <?php echo (($task['status'] ?? '') === 'done') ? 'selected' : ''; ?>>Finalizado</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Descrição</label>
                <div id="taskDescriptionEditor" style="height: 260px;"></div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Anexos (PDF e imagens)</label>
                <input class="form-control" type="file" name="task_attachments[]" accept=".pdf,image/*" multiple>
              </div>
              <div class="col-md-6">
                <label class="form-label">Link</label>
                <input class="form-control" type="url" name="attachment_link" placeholder="https://...">
              </div>
              <div class="col-12">
                <label class="form-label d-flex justify-content-between align-items-center gap-2">
                  <span>Material vinculado</span>
                  <span class="text-muted small">A tarefa enviará apenas o link do material ao paciente</span>
                </label>
                <div class="card border-0 bg-light-subtle">
                  <div class="card-body p-3">
                    <div class="row g-3 align-items-start">
                      <div class="col-lg-5">
                        <input class="form-control mb-2" type="search" id="materialSearchInput" placeholder="Pesquisar material por título, tipo ou conteúdo...">
                        <div id="materialPickerList" class="material-picker-list">
                          <?php foreach (($materials ?? []) as $material): ?>
                            <?php
                              $materialTypeLabel = (($material['type'] ?? '') === 'exercise') ? 'Exercício' : 'Material de apoio';
                              $materialSearch = strtolower(trim((string) ($material['title'] ?? '') . ' ' . $materialTypeLabel . ' ' . strip_tags((string) ($material['description_html'] ?? ''))));
                            ?>
                            <button
                              class="material-picker-item <?php echo ((int) ($task['material_id'] ?? 0) === (int) $material['id']) ? 'is-selected' : ''; ?>"
                              type="button"
                              data-material-id="<?php echo (int) $material['id']; ?>"
                              data-material-title="<?php echo htmlspecialchars((string) ($material['title'] ?? ''), ENT_QUOTES); ?>"
                              data-material-type="<?php echo htmlspecialchars((string) ($material['type'] ?? ''), ENT_QUOTES); ?>"
                              data-material-description="<?php echo htmlspecialchars((string) ($material['description_html'] ?? ''), ENT_QUOTES); ?>"
                              data-material-custom-html="<?php echo htmlspecialchars((string) ($material['custom_html'] ?? ''), ENT_QUOTES); ?>"
                              data-material-assets="<?php echo htmlspecialchars((string) ($material['asset_types'] ?? ''), ENT_QUOTES); ?>"
                              data-search="<?php echo htmlspecialchars($materialSearch); ?>"
                            >
                              <span class="fw-semibold"><?php echo htmlspecialchars((string) ($material['title'] ?? '')); ?></span>
                              <span class="text-muted small"><?php echo htmlspecialchars($materialTypeLabel); ?></span>
                            </button>
                          <?php endforeach; ?>
                        </div>
                      </div>
                      <div class="col-lg-7">
                        <div class="material-picker-preview card h-100">
                          <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                              <div>
                                <div id="selectedMaterialTitle" class="fw-semibold"><?php echo !empty($linkedMaterial['title']) ? htmlspecialchars((string) $linkedMaterial['title']) : 'Nenhum material selecionado'; ?></div>
                                <div id="selectedMaterialType" class="text-muted small"><?php echo !empty($linkedMaterial) ? ((($linkedMaterial['type'] ?? '') === 'exercise') ? 'Exercício' : 'Material de apoio') : 'Pesquise e selecione um material'; ?></div>
                              </div>
                              <div class="d-flex gap-2">
                                <a id="selectedMaterialOpenBtn" class="btn btn-sm btn-outline-primary <?php echo empty($linkedMaterial) ? 'd-none' : ''; ?>" target="_blank" rel="noopener noreferrer" href="<?php echo !empty($linkedMaterial) ? ($appUrl . '/dashboard.php?action=therapist-materials-show&id=' . (int) $linkedMaterial['id']) : '#'; ?>">Abrir</a>
                                <button id="clearSelectedMaterialBtn" class="btn btn-sm btn-light <?php echo empty($linkedMaterial) ? 'd-none' : ''; ?>" type="button">Remover</button>
                              </div>
                            </div>
                            <div id="selectedMaterialPreview" class="material-picker-preview-content"><?php echo !empty($linkedMaterial['description_html']) ? (string) $linkedMaterial['description_html'] : '<div class="text-muted">Selecione um material para visualizar antes de vincular.</div>'; ?></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="send_to_patient" id="send_to_patient" value="1" <?php echo !empty($task['send_to_patient']) ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="send_to_patient">Enviar para o paciente</label>
                </div>
              </div>
            </div>

            <div class="mt-3 d-flex gap-2">
              <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-history&id=<?php echo (int) $patient['id']; ?>">Voltar</a>
              <button class="btn btn-primary" type="submit">Salvar alterações</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
window.addEventListener('load', function() {
  var materialSearchInput = document.getElementById('materialSearchInput');
  var materialItems = Array.from(document.querySelectorAll('.material-picker-item'));
  var linkedMaterialIdInput = document.getElementById('linkedMaterialIdInput');
  var selectedMaterialTitle = document.getElementById('selectedMaterialTitle');
  var selectedMaterialType = document.getElementById('selectedMaterialType');
  var selectedMaterialPreview = document.getElementById('selectedMaterialPreview');
  var selectedMaterialOpenBtn = document.getElementById('selectedMaterialOpenBtn');
  var clearSelectedMaterialBtn = document.getElementById('clearSelectedMaterialBtn');

  const quill = new Quill('#taskDescriptionEditor', {
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

  quill.root.innerHTML = $('#taskDescriptionInput').val() || '';

  var updateMaterialSelection = function(item) {
    materialItems.forEach(function(node) { node.classList.remove('is-selected'); });

    if (!item) {
      linkedMaterialIdInput.value = '';
      selectedMaterialTitle.textContent = 'Nenhum material selecionado';
      selectedMaterialType.textContent = 'Pesquise e selecione um material';
      selectedMaterialPreview.innerHTML = '<div class="text-muted">Selecione um material para visualizar antes de vincular.</div>';
      selectedMaterialOpenBtn.classList.add('d-none');
      clearSelectedMaterialBtn.classList.add('d-none');
      return;
    }

    item.classList.add('is-selected');
    linkedMaterialIdInput.value = item.dataset.materialId || '';
    selectedMaterialTitle.textContent = item.dataset.materialTitle || 'Material';
    selectedMaterialType.textContent = item.dataset.materialType === 'exercise' ? 'Exercício' : 'Material de apoio';
    selectedMaterialPreview.innerHTML = item.dataset.materialDescription || '<div class="text-muted">Este material não possui descrição.</div>';
    selectedMaterialOpenBtn.href = '<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials-show&id=' + (item.dataset.materialId || '');
    selectedMaterialOpenBtn.classList.remove('d-none');
    clearSelectedMaterialBtn.classList.remove('d-none');
  };

  materialItems.forEach(function(item) {
    item.addEventListener('click', function() {
      updateMaterialSelection(item);
    });
  });

  if (materialSearchInput) {
    materialSearchInput.addEventListener('input', function() {
      var term = materialSearchInput.value.toLowerCase().trim();
      materialItems.forEach(function(item) {
        var match = term === '' || (item.dataset.search || '').indexOf(term) !== -1;
        item.style.display = match ? '' : 'none';
      });
    });
  }

  if (clearSelectedMaterialBtn) {
    clearSelectedMaterialBtn.addEventListener('click', function() {
      updateMaterialSelection(null);
    });
  }

  $('#taskEditForm').on('submit', function(e) {
    const form = this;
    if (!window.FormSubmitGuard.lock(form, 'Salvando...')) {
      e.preventDefault();
      return;
    }

    const html = quill.root.innerHTML;
    const plain = quill.getText().trim();
    if (!plain) {
      e.preventDefault();
      window.FormSubmitGuard.unlock(form);
      Swal.fire('Campo obrigatório', 'Preencha a descrição da tarefa.', 'warning');
      return;
    }

    $('#taskDescriptionInput').val(html);
  });
});
</script>
<?php include __DIR__ . '/../../../partials/footer.php'; ?>