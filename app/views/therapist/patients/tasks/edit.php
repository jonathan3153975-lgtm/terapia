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
            <?php $linkedMaterialIds = array_map(static fn (array $item): int => (int) ($item['id'] ?? 0), $linkedMaterials ?? []); ?>

            <div class="row g-3">
              <div class="col-lg-7 d-grid gap-3">
                <div class="card border-0 bg-light-subtle">
                  <div class="card-body p-3">
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label">Data</label>
                        <input class="form-control" type="date" name="due_date" required value="<?php echo htmlspecialchars((string) ($task['due_date'] ?? '')); ?>">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                          <option value="pending" <?php echo (($task['status'] ?? 'pending') === 'pending') ? 'selected' : ''; ?>>Pendente</option>
                          <option value="done" <?php echo (($task['status'] ?? '') === 'done') ? 'selected' : ''; ?>>Finalizado</option>
                        </select>
                      </div>
                      <div class="col-12">
                        <label class="form-label">Título</label>
                        <input class="form-control" name="title" required value="<?php echo htmlspecialchars((string) ($task['title'] ?? '')); ?>">
                      </div>
                      <div class="col-12">
                        <label class="form-label d-block mb-2">Tipo de envio</label>
                        <div class="d-flex gap-3 flex-wrap">
                          <div class="form-check">
                            <input class="form-check-input" type="radio" name="delivery_kind" id="delivery_kind_task" value="task" <?php echo (($task['delivery_kind'] ?? 'task') === 'task') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="delivery_kind_task">Envio de tarefa (com devolutiva)</label>
                          </div>
                          <div class="form-check">
                            <input class="form-check-input" type="radio" name="delivery_kind" id="delivery_kind_material" value="material" <?php echo (($task['delivery_kind'] ?? '') === 'material') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="delivery_kind_material">Envio de material (consulta do paciente)</label>
                          </div>
                        </div>
                      </div>
                      <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <div id="taskDescriptionEditor" class="task-description-editor"></div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="card border-0 bg-light-subtle">
                  <div class="card-body p-3">
                    <div class="row g-3">
                      <div class="col-12">
                        <label class="form-label">Anexos (PDF e imagens)</label>
                        <input class="form-control" type="file" name="task_attachments[]" accept=".pdf,image/*" multiple>
                      </div>
                      <div class="col-12">
                        <label class="form-label">Link adicional</label>
                        <input class="form-control" type="url" name="attachment_link" placeholder="https://...">
                      </div>
                      <div class="col-12">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="send_to_patient" id="send_to_patient" value="1" <?php echo !empty($task['send_to_patient']) ? 'checked' : ''; ?>>
                          <label class="form-check-label" for="send_to_patient">Encaminhar para o paciente</label>
                        </div>
                      </div>
                      <div class="col-12">
                        <label class="form-label d-block mb-2">Alertar paciente por</label>
                        <div class="d-flex gap-3 flex-wrap">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notify_channels[]" id="task_notify_email" value="email" checked>
                            <label class="form-check-label" for="task_notify_email">E-mail</label>
                          </div>
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notify_channels[]" id="task_notify_whatsapp" value="whatsapp" checked>
                            <label class="form-check-label" for="task_notify_whatsapp">WhatsApp</label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-5">
                <label class="form-label d-flex justify-content-between align-items-center gap-2">
                  <span>Materiais vinculados</span>
                  <span class="text-muted small">Selecione um ou mais materiais para encaminhar</span>
                </label>
                <div class="card border-0 bg-light-subtle task-material-panel">
                  <div class="card-body p-3">
                    <input class="form-control mb-2" type="search" id="materialSearchInput" placeholder="Pesquisar material por título, tipo ou conteúdo...">
                    <div id="materialPickerList" class="material-picker-list mb-3">
                      <?php foreach (($materials ?? []) as $material): ?>
                        <?php
                          $materialTypeLabel = (($material['type'] ?? '') === 'exercise') ? 'Exercício' : 'Material de apoio';
                          $materialSearch = strtolower(trim((string) ($material['title'] ?? '') . ' ' . $materialTypeLabel . ' ' . strip_tags((string) ($material['description_html'] ?? ''))));
                          $isChecked = in_array((int) $material['id'], $linkedMaterialIds, true);
                        ?>
                        <label class="material-picker-item material-picker-item--multi" data-search="<?php echo htmlspecialchars($materialSearch); ?>">
                          <div class="d-flex align-items-start gap-2 w-100">
                            <input class="form-check-input mt-1 js-material-checkbox" type="checkbox" name="material_ids[]" value="<?php echo (int) $material['id']; ?>" <?php echo $isChecked ? 'checked' : ''; ?>
                              data-material-id="<?php echo (int) $material['id']; ?>"
                              data-material-title="<?php echo htmlspecialchars((string) ($material['title'] ?? ''), ENT_QUOTES); ?>"
                              data-material-type="<?php echo htmlspecialchars((string) ($material['type'] ?? ''), ENT_QUOTES); ?>"
                              data-material-description="<?php echo htmlspecialchars((string) ($material['description_html'] ?? ''), ENT_QUOTES); ?>">
                            <button class="btn-reset text-start flex-grow-1 js-material-preview-btn" type="button"
                              data-material-id="<?php echo (int) $material['id']; ?>"
                              data-material-title="<?php echo htmlspecialchars((string) ($material['title'] ?? ''), ENT_QUOTES); ?>"
                              data-material-type="<?php echo htmlspecialchars((string) ($material['type'] ?? ''), ENT_QUOTES); ?>"
                              data-material-description="<?php echo htmlspecialchars((string) ($material['description_html'] ?? ''), ENT_QUOTES); ?>">
                              <span class="fw-semibold d-block"><?php echo htmlspecialchars((string) ($material['title'] ?? '')); ?></span>
                              <span class="text-muted small d-block"><?php echo htmlspecialchars($materialTypeLabel); ?></span>
                            </button>
                          </div>
                        </label>
                      <?php endforeach; ?>
                    </div>
                    <div class="material-picker-preview card h-100">
                      <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                          <div>
                            <div id="selectedMaterialTitle" class="fw-semibold">Nenhum material em foco</div>
                            <div id="selectedMaterialType" class="text-muted small">Clique em um material para visualizar</div>
                          </div>
                          <a id="selectedMaterialOpenBtn" class="btn btn-sm btn-outline-primary d-none" target="_blank" rel="noopener noreferrer" href="#">Abrir</a>
                        </div>
                        <div id="selectedMaterialPreview" class="material-picker-preview-content"><div class="text-muted">Clique em um material da lista para visualizar a descrição.</div></div>
                        <div id="selectedMaterialsSummary" class="task-selected-materials mt-3"></div>
                      </div>
                    </div>
                  </div>
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
  var materialCheckboxes = Array.from(document.querySelectorAll('.js-material-checkbox'));
  var materialPreviewButtons = Array.from(document.querySelectorAll('.js-material-preview-btn'));
  var selectedMaterialTitle = document.getElementById('selectedMaterialTitle');
  var selectedMaterialType = document.getElementById('selectedMaterialType');
  var selectedMaterialPreview = document.getElementById('selectedMaterialPreview');
  var selectedMaterialOpenBtn = document.getElementById('selectedMaterialOpenBtn');
  var selectedMaterialsSummary = document.getElementById('selectedMaterialsSummary');

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

  var renderSelectedMaterialsSummary = function() {
    var selected = materialCheckboxes.filter(function(checkbox) { return checkbox.checked; });
    if (selected.length === 0) {
      selectedMaterialsSummary.innerHTML = '<span class="text-muted small">Nenhum material selecionado.</span>';
      return;
    }

    selectedMaterialsSummary.innerHTML = selected.map(function(checkbox) {
      return '<span class="task-material-badge"><i class="fa-solid fa-link"></i><span>' + checkbox.dataset.materialTitle + '</span></span>';
    }).join('');
  };

  var updateMaterialPreview = function(source) {
    materialItems.forEach(function(node) { node.classList.remove('is-selected'); });
    var item = source ? source.closest('.material-picker-item') : null;
    if (item) {
      item.classList.add('is-selected');
    }

    if (!source) {
      selectedMaterialTitle.textContent = 'Nenhum material em foco';
      selectedMaterialType.textContent = 'Clique em um material para visualizar';
      selectedMaterialPreview.innerHTML = '<div class="text-muted">Clique em um material da lista para visualizar a descrição.</div>';
      selectedMaterialOpenBtn.classList.add('d-none');
      return;
    }

    selectedMaterialTitle.textContent = source.dataset.materialTitle || 'Material';
    selectedMaterialType.textContent = source.dataset.materialType === 'exercise' ? 'Exercício' : 'Material de apoio';
    selectedMaterialPreview.innerHTML = source.dataset.materialDescription || '<div class="text-muted">Este material não possui descrição.</div>';
    selectedMaterialOpenBtn.href = '<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials-show&id=' + (source.dataset.materialId || '');
    selectedMaterialOpenBtn.classList.remove('d-none');
  };

  materialPreviewButtons.forEach(function(button) {
    button.addEventListener('click', function() {
      updateMaterialPreview(button);
    });
  });

  materialCheckboxes.forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
      renderSelectedMaterialsSummary();
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

  renderSelectedMaterialsSummary();

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