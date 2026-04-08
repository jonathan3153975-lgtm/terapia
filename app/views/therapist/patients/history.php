<?php $title = 'Histórico do Paciente'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Histórico: <?php echo htmlspecialchars((string) $patient['name']); ?></h3>
    <a class="btn btn-outline-dark" href="<?php echo $appUrl; ?>/dashboard.php?action=patients"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
  </div>

  <div class="card mb-3">
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
    <div class="col-lg-8">
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
                    <td><?php echo htmlspecialchars((string) ($appointment['session_date'] ?? '-')); ?></td>
                    <td><?php echo htmlspecialchars((string) ($appointment['description'] ?? '-')); ?></td>
                    <td class="align-middle">
                      <div class="d-flex align-items-center gap-1 flex-nowrap">
                        <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-appointments-show&patient_id=<?php echo (int) $patient['id']; ?>&id=<?php echo (int) $appointment['id']; ?>" title="Visualizar"><i class="fa-solid fa-eye"></i></a>
                        <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-appointments-edit&patient_id=<?php echo (int) $patient['id']; ?>&id=<?php echo (int) $appointment['id']; ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
                        <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-appointments-delete" class="d-flex m-0 js-delete-appointment-form" data-session-date="<?php echo htmlspecialchars((string) ($appointment['session_date'] ?? '')); ?>">
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

    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header bg-transparent"><strong>Tarefas</strong></div>
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
<script>
window.addEventListener('load', function() {
  const quill = new Quill('#historyEditor', {
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
    const html = quill.root.innerHTML;
    const plain = quill.getText().trim();
    if (!plain) {
      e.preventDefault();
      Swal.fire('Campo obrigatório', 'Preencha o histórico do atendimento.', 'warning');
      return;
    }
    $('#historyInput').val(html);
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
