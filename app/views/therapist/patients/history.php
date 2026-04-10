<?php $title = 'Histórico do Paciente'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>
  <?php
  $formatDateTimeBr = static function (?string $value): string {
    if (empty($value)) {
      return '-';
    }
    $date = date_create((string) $value);
    if (!$date) {
      return htmlspecialchars((string) $value);
    }
    return $date->format('d/m/Y H:i');
  };
  $formatDateBr = static function (?string $value): string {
    if (empty($value)) {
      return '-';
    }
    $date = date_create((string) $value);
    if (!$date) {
      return htmlspecialchars((string) $value);
    }
    return $date->format('d/m/Y');
  };
  $taskStatusInfo = static function (array $task): array {
    if (($task['status'] ?? '') === 'done') {
      return ['Finalizado', 'text-bg-success'];
    }
    if (!empty($task['send_to_patient'])) {
      return ['Enviado', 'text-bg-info'];
    }
    return ['Pendente', 'text-bg-warning'];
  };
  $renderMaterialTypeLabel = static function (?string $type): string {
    return $type === 'exercise' ? 'Exercício' : 'Material de apoio';
  };
  ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Histórico: <?php echo htmlspecialchars((string) $patient['name']); ?></h3>
    <div class="d-flex gap-2">
      <button id="btnToggleAppointmentForm" class="btn btn-primary" type="button"><i class="fa-solid fa-calendar-plus"></i> Novo atendimento</button>
      <a class="btn btn-outline-dark" href="<?php echo $appUrl; ?>/dashboard.php?action=patients"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
    </div>
  </div>

  <div id="appointmentFormCard" class="card mb-3 d-none">
    <div class="card-header bg-transparent"><strong>Novo atendimento</strong></div>
    <div class="card-body">
      <form id="appointmentForm" method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-appointments-store">
        <input type="hidden" name="patient_id" value="<?php echo (int) $patient['id']; ?>">
        <input type="hidden" name="history" id="historyInput">

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Data do atendimento</label>
            <input class="form-control" type="datetime-local" name="session_date" required>
          </div>
          <div class="col-md-8">
            <label class="form-label">Descrição</label>
            <input class="form-control" name="description" placeholder="Resumo breve do atendimento">
          </div>
          <div class="col-12">
            <label class="form-label">Histórico</label>
            <div id="historyEditor" style="height: 220px;"></div>
          </div>
        </div>

        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Salvar atendimento</button>
        </div>
      </form>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-transparent"><strong>Atendimentos</strong></div>
        <div class="card-body" style="max-height: 42vh; overflow: auto;">
          <?php if (empty($appointments)): ?>
            <p class="text-muted mb-0">Nenhum atendimento registrado.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead><tr><th>Data</th><th>Descrição</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($appointments as $appointment): ?>
                  <tr>
                    <td><?php echo $formatDateTimeBr($appointment['session_date'] ?? null); ?></td>
                    <td><?php echo htmlspecialchars((string) ($appointment['description'] ?? '-')); ?></td>
                    <td class="align-middle">
                      <div class="d-flex align-items-center gap-1 flex-nowrap">
                        <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-appointments-show&patient_id=<?php echo (int) $patient['id']; ?>&id=<?php echo (int) $appointment['id']; ?>" title="Visualizar"><i class="fa-solid fa-eye"></i></a>
                        <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-appointments-edit&patient_id=<?php echo (int) $patient['id']; ?>&id=<?php echo (int) $appointment['id']; ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
                        <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-appointments-delete" class="d-flex m-0 js-delete-appointment-form" data-session-date="<?php echo $formatDateTimeBr($appointment['session_date'] ?? null); ?>">
                          <input type="hidden" name="patient_id" value="<?php echo (int) $patient['id']; ?>">
                          <input type="hidden" name="id" value="<?php echo (int) $appointment['id']; ?>">
                          <button class="btn btn-sm btn-outline-danger" style="width:32px;padding:0;line-height:1.8;" type="submit" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
          <strong>Tarefas</strong>
          <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#newTaskModal"><i class="fa-solid fa-list-check"></i> Nova tarefa</button>
        </div>
        <div class="card-body" style="max-height: 42vh; overflow: auto;">
          <?php if (empty($tasks)): ?>
            <p class="text-muted mb-0">Nenhuma tarefa registrada.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead><tr><th>Data</th><th>Título</th><th>Tipo</th><th>Status</th><th>Anexos</th><th>Material</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($tasks as $task): ?>
                  <?php [$taskStatusLabel, $taskStatusClass] = $taskStatusInfo($task); ?>
                  <?php $taskAttachments = $taskFiles[(int) $task['id']] ?? []; ?>
                  <?php $taskMaterials = $taskLinkedMaterials[(int) $task['id']] ?? []; ?>
                  <tr>
                    <td><?php echo $formatDateBr($task['due_date'] ?? null); ?></td>
                    <td><?php echo htmlspecialchars((string) ($task['title'] ?? '-')); ?></td>
                    <td>
                      <?php if (($task['delivery_kind'] ?? 'task') === 'material'): ?>
                        <span class="badge text-bg-secondary">Envio de material</span>
                      <?php else: ?>
                        <span class="badge text-bg-primary">Envio de tarefa</span>
                      <?php endif; ?>
                    </td>
                    <td><span class="badge <?php echo $taskStatusClass; ?>"><?php echo $taskStatusLabel; ?></span></td>
                    <td>
                      <?php if (!empty($taskAttachments)): ?>
                        <div class="d-flex gap-1 flex-wrap">
                          <?php foreach ($taskAttachments as $fi): ?>
                            <?php if (($fi['file_type'] ?? '') === 'link'): ?>
                              <a href="<?php echo htmlspecialchars((string) $fi['file_path']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-info" style="padding:2px 6px;" title="<?php echo htmlspecialchars((string) $fi['file_name']); ?>"><i class="fa-solid fa-link"></i></a>
                            <?php elseif (($fi['file_type'] ?? '') === 'pdf'): ?>
                              <a href="<?php echo $appUrl; ?>/<?php echo htmlspecialchars((string) $fi['file_path']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-danger" style="padding:2px 6px;" title="<?php echo htmlspecialchars((string) $fi['file_name']); ?>"><i class="fa-solid fa-file-pdf"></i></a>
                            <?php else: ?>
                              <a href="<?php echo $appUrl; ?>/<?php echo htmlspecialchars((string) $fi['file_path']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary" style="padding:2px 6px;" title="<?php echo htmlspecialchars((string) $fi['file_name']); ?>"><i class="fa-solid fa-image"></i></a>
                            <?php endif; ?>
                          <?php endforeach; ?>
                        </div>
                      <?php else: ?>
                        <span class="text-muted small">-</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (!empty($taskMaterials)): ?>
                        <div class="task-material-badges">
                          <?php foreach ($taskMaterials as $linkedMaterial): ?>
                            <a class="task-material-badge" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials-show&id=<?php echo (int) $linkedMaterial['id']; ?>" target="_blank" rel="noopener noreferrer" title="Visualizar material vinculado">
                              <i class="fa-solid fa-link"></i>
                              <span><?php echo htmlspecialchars((string) ($linkedMaterial['title'] ?? 'Material')); ?></span>
                            </a>
                          <?php endforeach; ?>
                        </div>
                      <?php else: ?>
                        <span class="text-muted small">-</span>
                      <?php endif; ?>
                    </td>
                    <td class="align-middle">
                      <div class="d-flex align-items-center gap-1 flex-nowrap">
                        <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-tasks-show&patient_id=<?php echo (int) $patient['id']; ?>&id=<?php echo (int) $task['id']; ?>" title="Visualizar"><i class="fa-solid fa-eye"></i></a>
                        <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-tasks-edit&patient_id=<?php echo (int) $patient['id']; ?>&id=<?php echo (int) $task['id']; ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
                        <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-tasks-delete" class="d-flex m-0 js-delete-task-form" data-task-title="<?php echo htmlspecialchars((string) ($task['title'] ?? '')); ?>">
                          <input type="hidden" name="patient_id" value="<?php echo (int) $patient['id']; ?>">
                          <input type="hidden" name="id" value="<?php echo (int) $task['id']; ?>">
                          <button class="btn btn-sm btn-outline-danger" style="width:32px;padding:0;line-height:1.8;" type="submit" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="newTaskModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title">Cadastrar nova tarefa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <form id="taskForm" method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-tasks-store" enctype="multipart/form-data">
        <div class="modal-body pt-3 task-modal-body">
          <input type="hidden" name="patient_id" value="<?php echo (int) $patient['id']; ?>">
          <input type="hidden" name="description" id="taskDescriptionInput">

          <div class="row g-3">
            <div class="col-lg-7 d-grid gap-3">
              <div class="card border-0 bg-light-subtle">
                <div class="card-body p-3">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Data</label>
                      <input class="form-control" type="date" name="due_date" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Status</label>
                      <select class="form-select" name="status">
                        <option value="pending" selected>Pendente</option>
                        <option value="done">Finalizado</option>
                      </select>
                    </div>
                    <div class="col-12">
                      <label class="form-label">Título</label>
                      <input class="form-control" name="title" required>
                    </div>
                    <div class="col-12">
                      <label class="form-label d-block mb-2">Tipo de envio</label>
                      <div class="d-flex gap-3 flex-wrap">
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="delivery_kind" id="delivery_kind_task" value="task" checked>
                          <label class="form-check-label" for="delivery_kind_task">Envio de tarefa (com devolutiva)</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="delivery_kind" id="delivery_kind_material" value="material">
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
                        <input class="form-check-input" type="checkbox" name="send_to_patient" id="send_to_patient" value="1">
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
                <span class="text-muted small">Pesquise, visualize e selecione mais de um</span>
              </label>
              <div class="card border-0 bg-light-subtle task-material-panel">
                <div class="card-body p-3">
                  <input class="form-control mb-2" type="search" id="materialSearchInput" placeholder="Pesquisar material por título, tipo ou conteúdo...">
                  <div id="materialPickerList" class="material-picker-list mb-3">
                    <?php foreach (($materials ?? []) as $material): ?>
                      <?php
                        $materialTypeLabel = $renderMaterialTypeLabel((string) ($material['type'] ?? ''));
                        $materialSearch = strtolower(trim((string) ($material['title'] ?? '') . ' ' . $materialTypeLabel . ' ' . strip_tags((string) ($material['description_html'] ?? ''))));
                      ?>
                      <label class="material-picker-item material-picker-item--multi" data-search="<?php echo htmlspecialchars($materialSearch); ?>">
                        <div class="d-flex align-items-start gap-2 w-100">
                          <input class="form-check-input mt-1 js-material-checkbox" type="checkbox" name="material_ids[]" value="<?php echo (int) $material['id']; ?>"
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
                      <div id="selectedMaterialsSummary" class="task-selected-materials mt-3"><span class="text-muted small">Nenhum material selecionado.</span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar tarefa</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function() {
  const materialSearchInput = document.getElementById('materialSearchInput');
  const materialItems = Array.from(document.querySelectorAll('.material-picker-item'));
  const materialCheckboxes = Array.from(document.querySelectorAll('.js-material-checkbox'));
  const materialPreviewButtons = Array.from(document.querySelectorAll('.js-material-preview-btn'));
  const selectedMaterialTitle = document.getElementById('selectedMaterialTitle');
  const selectedMaterialType = document.getElementById('selectedMaterialType');
  const selectedMaterialPreview = document.getElementById('selectedMaterialPreview');
  const selectedMaterialOpenBtn = document.getElementById('selectedMaterialOpenBtn');
  const selectedMaterialsSummary = document.getElementById('selectedMaterialsSummary');

  const appointmentCard = document.getElementById('appointmentFormCard');
  const toggleAppointmentBtn = document.getElementById('btnToggleAppointmentForm');
  if (toggleAppointmentBtn && appointmentCard) {
    toggleAppointmentBtn.addEventListener('click', function() {
      appointmentCard.classList.toggle('d-none');
      toggleAppointmentBtn.innerHTML = appointmentCard.classList.contains('d-none')
        ? '<i class="fa-solid fa-calendar-plus"></i> Novo atendimento'
        : '<i class="fa-solid fa-xmark"></i> Fechar formulário';
    });
  }

  const appointmentQuill = new Quill('#historyEditor', {
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

  const taskQuill = new Quill('#taskDescriptionEditor', {
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

  const renderSelectedMaterialsSummary = function() {
    const selected = materialCheckboxes.filter(function(checkbox) { return checkbox.checked; });
    if (selected.length === 0) {
      selectedMaterialsSummary.innerHTML = '<span class="text-muted small">Nenhum material selecionado.</span>';
      return;
    }

    selectedMaterialsSummary.innerHTML = selected.map(function(checkbox) {
      return '<span class="task-material-badge"><i class="fa-solid fa-link"></i><span>' + checkbox.dataset.materialTitle + '</span></span>';
    }).join('');
  };

  const updateMaterialPreview = function(source) {
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
      const term = materialSearchInput.value.toLowerCase().trim();
      materialItems.forEach(function(item) {
        const match = term === '' || (item.dataset.search || '').indexOf(term) !== -1;
        item.style.display = match ? '' : 'none';
      });
    });
  }

  renderSelectedMaterialsSummary();

  $('#appointmentForm').on('submit', function(e) {
    const form = this;
    if (!window.FormSubmitGuard.lock(form, 'Salvando...')) {
      e.preventDefault();
      return;
    }

    const html = appointmentQuill.root.innerHTML;
    const plain = appointmentQuill.getText().trim();
    if (!plain) {
      e.preventDefault();
      window.FormSubmitGuard.unlock(form);
      Swal.fire('Campo obrigatório', 'Preencha o histórico do atendimento.', 'warning');
      return;
    }
    $('#historyInput').val(html);
  });

  $('#taskForm').on('submit', function(e) {
    const form = this;
    if (!window.FormSubmitGuard.lock(form, 'Salvando...')) {
      e.preventDefault();
      return;
    }

    const html = taskQuill.root.innerHTML;
    const plain = taskQuill.getText().trim();
    if (!plain) {
      e.preventDefault();
      window.FormSubmitGuard.unlock(form);
      Swal.fire('Campo obrigatório', 'Preencha a descrição da tarefa.', 'warning');
      return;
    }
    $('#taskDescriptionInput').val(html);
  });

  $('.js-delete-appointment-form').on('submit', function(e) {
    const form = this;
    if (form.dataset.confirmed === '1') {
      return;
    }

    e.preventDefault();
    const sessionDate = form.getAttribute('data-session-date') || 'este atendimento';

    if (typeof Swal === 'undefined') {
      if (confirm('Excluir atendimento de ' + sessionDate + '?')) {
        form.dataset.confirmed = '1';
        form.submit();
      }
      return;
    }

    Swal.fire({
      title: 'Confirmar exclusão',
      text: 'Deseja realmente excluir o atendimento de ' + sessionDate + '?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sim, excluir',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#c0392b'
    }).then(function(result) {
      if (!result.isConfirmed) {
        return;
      }

      form.dataset.confirmed = '1';
      form.submit();
    });
  });

  $('.js-delete-task-form').on('submit', function(e) {
    const form = this;
    if (form.dataset.confirmed === '1') {
      return;
    }

    e.preventDefault();
    const taskTitle = form.getAttribute('data-task-title') || 'esta tarefa';

    if (typeof Swal === 'undefined') {
      if (confirm('Excluir tarefa "' + taskTitle + '"?')) {
        form.dataset.confirmed = '1';
        form.submit();
      }
      return;
    }

    Swal.fire({
      title: 'Confirmar exclusão',
      text: 'Deseja realmente excluir a tarefa "' + taskTitle + '"?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sim, excluir',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#c0392b'
    }).then(function(result) {
      if (!result.isConfirmed) {
        return;
      }

      form.dataset.confirmed = '1';
      form.submit();
    });
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
