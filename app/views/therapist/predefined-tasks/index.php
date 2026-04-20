<?php $title = 'Tarefas pré-definidas'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0">Tarefas pré-definidas</h3>
    <span class="badge text-bg-light border"><?php echo count($tasks ?? []); ?> cadastro(s)</span>
  </div>

  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="row g-3 mb-3">
    <div class="col-12 col-xl-5">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title mb-2">Nova tarefa pré-definida</h5>
          <p class="text-muted small mb-3">Crie modelos para agilizar o cadastro de tarefas dos pacientes.</p>
          <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-predefined-tasks-store" id="predefinedTaskCreateForm" enctype="multipart/form-data">
            <div class="mb-2">
              <label class="form-label">Título</label>
              <input class="form-control" name="title" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Tipo de envio</label>
              <select class="form-select" name="delivery_kind" required>
                <option value="task" selected>Envio de tarefa (com devolutiva)</option>
                <option value="material">Envio de material (consulta do paciente)</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Status padrão</label>
              <select class="form-select" name="status" required>
                <option value="pending" selected>Pendente</option>
                <option value="done">Finalizado</option>
              </select>
            </div>
            <div class="mb-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="send_to_patient" id="predefined_send_to_patient" value="1" checked>
                <label class="form-check-label" for="predefined_send_to_patient">Encaminhar para o paciente por padrão</label>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Descrição</label>
              <textarea class="form-control" name="description" rows="5" required placeholder="Descreva a tarefa pré-definida..."></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Imagem de capa (opcional)</label>
              <input class="form-control" type="file" name="cover_image" accept="image/*">
            </div>
            <button class="btn btn-primary" type="submit" id="predefinedTaskCreateBtn"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar modelo</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-7">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title mb-2">Buscar modelos</h5>
          <form method="GET" action="<?php echo $appUrl; ?>/dashboard.php" class="row g-2 align-items-end">
            <input type="hidden" name="action" value="therapist-predefined-tasks">
            <div class="col-12 col-md-9">
              <label class="form-label mb-1">Buscar por título ou descrição</label>
              <input class="form-control" type="search" name="q" value="<?php echo htmlspecialchars((string) ($filters['q'] ?? '')); ?>" placeholder="Digite parte do título ou conteúdo...">
            </div>
            <div class="col-12 col-md-3 d-grid">
              <button class="btn btn-dark" type="submit"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th style="width: 80px;">Capa</th>
              <th style="width: 22%;">Título</th>
              <th>Descrição</th>
              <th style="width: 120px;">Tipo</th>
              <th style="width: 110px;">Status</th>
              <th style="width: 120px;">Encaminhar</th>
              <th style="width: 150px;">Ações</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($tasks)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-4">Nenhuma tarefa pré-definida cadastrada.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($tasks as $task): ?>
              <tr>
                <td>
                  <?php if (!empty($task['cover_image_path'])): ?>
                    <a href="<?php echo $appUrl; ?>/<?php echo htmlspecialchars((string) $task['cover_image_path']); ?>" target="_blank" rel="noopener noreferrer">
                      <img src="<?php echo $appUrl; ?>/<?php echo htmlspecialchars((string) $task['cover_image_path']); ?>" alt="Capa" style="width: 56px; height: 40px; object-fit: cover; border-radius: .35rem; border: 1px solid #dee2e6;">
                    </a>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars((string) ($task['title'] ?? '')); ?></td>
                <td><?php echo nl2br(htmlspecialchars((string) strip_tags((string) ($task['description'] ?? '')))); ?></td>
                <td>
                  <span class="badge text-bg-light border"><?php echo (($task['delivery_kind'] ?? 'task') === 'material') ? 'Material' : 'Tarefa'; ?></span>
                </td>
                <td>
                  <span class="badge <?php echo (($task['status'] ?? 'pending') === 'done') ? 'text-bg-success' : 'text-bg-warning'; ?>"><?php echo (($task['status'] ?? 'pending') === 'done') ? 'Finalizado' : 'Pendente'; ?></span>
                </td>
                <td><?php echo ((int) ($task['send_to_patient'] ?? 0) === 1) ? 'Sim' : 'Não'; ?></td>
                <td>
                  <div class="d-flex align-items-center gap-1 flex-nowrap">
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-secondary js-edit-predefined-task-btn"
                      data-id="<?php echo (int) ($task['id'] ?? 0); ?>"
                      data-title="<?php echo htmlspecialchars((string) ($task['title'] ?? ''), ENT_QUOTES); ?>"
                      data-description="<?php echo htmlspecialchars((string) ($task['description'] ?? ''), ENT_QUOTES); ?>"
                      data-delivery-kind="<?php echo htmlspecialchars((string) ($task['delivery_kind'] ?? 'task')); ?>"
                      data-status="<?php echo htmlspecialchars((string) ($task['status'] ?? 'pending')); ?>"
                      data-send-to-patient="<?php echo (int) ($task['send_to_patient'] ?? 0); ?>"
                      data-cover-image-path="<?php echo htmlspecialchars((string) ($task['cover_image_path'] ?? ''), ENT_QUOTES); ?>"
                      data-cover-image-name="<?php echo htmlspecialchars((string) ($task['cover_image_name'] ?? ''), ENT_QUOTES); ?>"
                      data-bs-toggle="modal"
                      data-bs-target="#editPredefinedTaskModal"
                      title="Editar"
                    >
                      <i class="fa-solid fa-pen"></i>
                    </button>

                    <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-predefined-tasks-delete" class="m-0 js-delete-predefined-task-form" data-task-title="<?php echo htmlspecialchars((string) ($task['title'] ?? '')); ?>">
                      <input type="hidden" name="id" value="<?php echo (int) ($task['id'] ?? 0); ?>">
                      <button class="btn btn-sm btn-outline-danger" type="submit" title="Excluir">
                        <i class="fa-solid fa-trash"></i>
                      </button>
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

<div class="modal fade" id="editPredefinedTaskModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar tarefa pré-definida</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-predefined-tasks-update" id="editPredefinedTaskForm" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="id" id="editPredefinedTaskId" value="">

          <div class="mb-2">
            <label class="form-label">Título</label>
            <input class="form-control" name="title" id="editPredefinedTaskTitle" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Tipo de envio</label>
            <select class="form-select" name="delivery_kind" id="editPredefinedTaskDeliveryKind" required>
              <option value="task">Envio de tarefa (com devolutiva)</option>
              <option value="material">Envio de material (consulta do paciente)</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Status padrão</label>
            <select class="form-select" name="status" id="editPredefinedTaskStatus" required>
              <option value="pending">Pendente</option>
              <option value="done">Finalizado</option>
            </select>
          </div>
          <div class="mb-2">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="send_to_patient" id="editPredefinedTaskSendToPatient" value="1">
              <label class="form-check-label" for="editPredefinedTaskSendToPatient">Encaminhar para o paciente por padrão</label>
            </div>
          </div>
          <div class="mb-0">
            <label class="form-label">Descrição</label>
            <textarea class="form-control" name="description" id="editPredefinedTaskDescription" rows="6" required></textarea>
          </div>
          <div class="mt-3">
            <label class="form-label">Imagem de capa (opcional)</label>
            <input class="form-control" type="file" name="cover_image" accept="image/*">
            <div id="editPredefinedTaskCoverWrap" class="mt-2 d-none">
              <a id="editPredefinedTaskCoverLink" href="#" target="_blank" rel="noopener noreferrer" class="d-inline-block">
                <img id="editPredefinedTaskCoverImg" src="" alt="Capa atual" style="max-width: 220px; max-height: 140px; border-radius: .6rem; border: 1px solid #dee2e6; object-fit: cover;">
              </a>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="remove_cover_image" id="editPredefinedTaskRemoveCover" value="1">
                <label class="form-check-label" for="editPredefinedTaskRemoveCover">Remover capa atual</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="editPredefinedTaskSaveBtn"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar alterações</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var createForm = document.getElementById('predefinedTaskCreateForm');
  var createBtn = document.getElementById('predefinedTaskCreateBtn');
  var editForm = document.getElementById('editPredefinedTaskForm');
  var editSaveBtn = document.getElementById('editPredefinedTaskSaveBtn');

  var editIdInput = document.getElementById('editPredefinedTaskId');
  var editTitleInput = document.getElementById('editPredefinedTaskTitle');
  var editDescriptionInput = document.getElementById('editPredefinedTaskDescription');
  var editDeliveryInput = document.getElementById('editPredefinedTaskDeliveryKind');
  var editStatusInput = document.getElementById('editPredefinedTaskStatus');
  var editSendInput = document.getElementById('editPredefinedTaskSendToPatient');
  var editCoverWrap = document.getElementById('editPredefinedTaskCoverWrap');
  var editCoverLink = document.getElementById('editPredefinedTaskCoverLink');
  var editCoverImg = document.getElementById('editPredefinedTaskCoverImg');
  var editRemoveCover = document.getElementById('editPredefinedTaskRemoveCover');

  if (createForm && createBtn) {
    createForm.addEventListener('submit', function () {
      if (createBtn.disabled) {
        return false;
      }

      createBtn.disabled = true;
      createBtn.innerHTML = '<i class="fa-solid fa-hourglass-half me-1"></i>Salvando...';
      createForm.setAttribute('data-submitted', '1');
      return true;
    });
  }

  document.querySelectorAll('.js-edit-predefined-task-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!editIdInput || !editTitleInput || !editDescriptionInput || !editDeliveryInput || !editStatusInput || !editSendInput) {
        return;
      }

      editIdInput.value = btn.getAttribute('data-id') || '';
      editTitleInput.value = btn.getAttribute('data-title') || '';
      editDescriptionInput.value = btn.getAttribute('data-description') || '';
      editDeliveryInput.value = btn.getAttribute('data-delivery-kind') || 'task';
      editStatusInput.value = btn.getAttribute('data-status') || 'pending';
      editSendInput.checked = (btn.getAttribute('data-send-to-patient') || '0') === '1';

      var coverPath = btn.getAttribute('data-cover-image-path') || '';
      if (editCoverWrap && editCoverLink && editCoverImg && editRemoveCover) {
        editRemoveCover.checked = false;
        if (coverPath !== '') {
          var fullCoverUrl = '<?php echo $appUrl; ?>/' + coverPath.replace(/^\/+/, '');
          editCoverLink.href = fullCoverUrl;
          editCoverImg.src = fullCoverUrl;
          editCoverWrap.classList.remove('d-none');
        } else {
          editCoverLink.href = '#';
          editCoverImg.src = '';
          editCoverWrap.classList.add('d-none');
        }
      }

      if (editSaveBtn) {
        editSaveBtn.disabled = false;
        editSaveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i>Salvar alterações';
      }
    });
  });

  if (editForm && editSaveBtn) {
    editForm.addEventListener('submit', function () {
      if (editSaveBtn.disabled) {
        return false;
      }

      editSaveBtn.disabled = true;
      editSaveBtn.innerHTML = '<i class="fa-solid fa-hourglass-half me-1"></i>Salvando...';
      editForm.setAttribute('data-submitted', '1');
      return true;
    });
  }

  document.querySelectorAll('.js-delete-predefined-task-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (form.dataset.confirmed === '1') {
        return;
      }

      e.preventDefault();
      var title = form.getAttribute('data-task-title') || 'esta tarefa pré-definida';

      if (typeof Swal === 'undefined') {
        if (confirm('Excluir tarefa pré-definida "' + title + '"?')) {
          form.dataset.confirmed = '1';
          form.submit();
        }
        return;
      }

      Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Deseja realmente excluir a tarefa pré-definida "' + title + '"?',
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
    });
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
