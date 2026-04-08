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
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header bg-transparent"><strong>Atendimentos</strong></div>
        <div class="card-body">
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

    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
          <strong>Tarefas</strong>
          <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#newTaskModal"><i class="fa-solid fa-list-check"></i> Nova tarefa</button>
        </div>
        <div class="card-body">
          <?php if (empty($tasks)): ?>
            <p class="text-muted mb-0">Nenhuma tarefa registrada.</p>
          <?php else: ?>
            <ul class="list-group list-group-flush">
              <?php foreach ($tasks as $task): ?>
                <li class="list-group-item px-0">
                  <div class="d-flex justify-content-between">
                    <strong><?php echo htmlspecialchars((string) ($task['title'] ?? '-')); ?></strong>
                    <span class="badge text-bg-light border"><?php echo htmlspecialchars((string) ($task['status'] ?? '-')); ?></span>
                  </div>
                  <small class="text-muted">Entrega: <?php echo htmlspecialchars((string) ($task['due_date'] ?? '-')); ?></small>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="newTaskModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cadastrar nova tarefa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <form id="taskForm" method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-tasks-store" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="patient_id" value="<?php echo (int) $patient['id']; ?>">
          <input type="hidden" name="description" id="taskDescriptionInput">

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Data</label>
              <input class="form-control" type="date" name="due_date" required>
            </div>
            <div class="col-md-8">
              <label class="form-label">Título</label>
              <input class="form-control" name="title" required>
            </div>
            <div class="col-12">
              <label class="form-label">Descrição</label>
              <div id="taskDescriptionEditor" style="height: 220px;"></div>
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
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="send_to_patient" id="send_to_patient" value="1">
                <label class="form-check-label" for="send_to_patient">Enviar para o paciente</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar tarefa</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function() {
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

  $('#appointmentForm').on('submit', function(e) {
    const html = appointmentQuill.root.innerHTML;
    const plain = appointmentQuill.getText().trim();
    if (!plain) {
      e.preventDefault();
      Swal.fire('Campo obrigatório', 'Preencha o histórico do atendimento.', 'warning');
      return;
    }
    $('#historyInput').val(html);
  });

  $('#taskForm').on('submit', function(e) {
    const html = taskQuill.root.innerHTML;
    const plain = taskQuill.getText().trim();
    if (!plain) {
      e.preventDefault();
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
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
