<?php $title = 'Pacientes'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Pacientes</h3>
    <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-create"><i class="fa-solid fa-user-plus"></i> Novo paciente</a>
  </div>
  <div class="card mb-3"><div class="card-body">
    <input id="patientSearch" class="form-control" placeholder="Digite para filtrar dinamicamente" value="<?php echo htmlspecialchars($search); ?>">
  </div></div>
  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>Nome</th><th>CPF</th><th>Telefone</th><th>E-mail</th><th>Ações</th></tr></thead>
        <tbody>
          <?php if (empty($patients)): ?>
            <tr><td colspan="5" class="text-center py-4 text-muted">Nenhum paciente cadastrado.</td></tr>
          <?php else: foreach ($patients as $patient): ?>
            <tr class="row-patient" data-search="<?php echo strtolower(htmlspecialchars(($patient['name'] ?? '') . ' ' . ($patient['cpf'] ?? '') . ' ' . ($patient['email'] ?? ''))); ?>">
              <td><?php echo htmlspecialchars($patient['name']); ?></td>
              <td><?php echo htmlspecialchars($patient['cpf']); ?></td>
              <td><?php echo htmlspecialchars($patient['phone']); ?></td>
              <td><?php echo htmlspecialchars($patient['email'] ?? '-'); ?></td>
              <td class="align-middle">
                <div class="d-flex align-items-center gap-1 flex-nowrap">
                  <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-show&id=<?php echo (int) $patient['id']; ?>" title="Visualizar"><i class="fa-solid fa-eye"></i></a>
                  <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-history&id=<?php echo (int) $patient['id']; ?>" title="Histórico"><i class="fa-solid fa-book-medical"></i></a>
                  <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-edit&id=<?php echo (int) $patient['id']; ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
                  <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-delete" class="d-flex m-0 js-delete-patient-form" data-patient-name="<?php echo htmlspecialchars((string) $patient['name']); ?>">
                    <input type="hidden" name="id" value="<?php echo (int) $patient['id']; ?>">
                    <button class="btn btn-sm btn-outline-danger" style="width:32px;padding:0;line-height:1.8;" type="submit" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
window.addEventListener('load', function(){
  $('#patientSearch').on('input', function(){
    const t = ($(this).val() || '').toLowerCase();
    $('.row-patient').each(function(){
      const h = ($(this).data('search') || '').toString();
      $(this).toggle(t === '' || h.includes(t));
    });
  });

  $('.js-delete-patient-form').on('submit', function(e) {
    const form = this;
    if (form.dataset.confirmed === '1') {
      return;
    }

    e.preventDefault();
    const patientName = form.getAttribute('data-patient-name') || 'este paciente';

    if (typeof Swal === 'undefined') {
      if (confirm('Excluir ' + patientName + '?')) {
        form.dataset.confirmed = '1';
        form.submit();
      }
      return;
    }

    Swal.fire({
      title: 'Confirmar exclusao',
      text: 'Deseja realmente excluir ' + patientName + '?',
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
