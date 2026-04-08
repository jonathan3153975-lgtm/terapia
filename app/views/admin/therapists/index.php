<?php $title = 'Terapeutas'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Terapeutas</h3>
    <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapists-create"><i class="fa-solid fa-user-plus"></i> Novo terapeuta</a>
  </div>
  <div class="card mb-3"><div class="card-body">
    <input id="therapistSearch" class="form-control" placeholder="Digite para filtrar dinamicamente">
  </div></div>
  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>Nome</th><th>CPF</th><th>Telefone</th><th>E-mail</th><th>Plano</th><th>Status</th><th>Ações</th></tr></thead>
        <tbody>
          <?php if (empty($therapists)): ?>
            <tr><td colspan="7" class="text-center py-4 text-muted">Não há terapeutas cadastrados.</td></tr>
          <?php else: foreach ($therapists as $therapist): ?>
            <tr class="row-therapist" data-search="<?php echo strtolower(htmlspecialchars(($therapist['name'] ?? '') . ' ' . ($therapist['cpf'] ?? '') . ' ' . ($therapist['email'] ?? ''))); ?>">
              <td><?php echo htmlspecialchars((string) $therapist['name']); ?></td>
              <td><?php echo htmlspecialchars((string) ($therapist['cpf'] ?? '-')); ?></td>
              <td><?php echo htmlspecialchars((string) ($therapist['phone'] ?? '-')); ?></td>
              <td><?php echo htmlspecialchars((string) $therapist['email']); ?></td>
              <td><?php echo htmlspecialchars((string) ($therapist['plan_type'] ?? 'mensal')); ?></td>
              <td><?php echo htmlspecialchars((string) ($therapist['status'] ?? 'active')); ?></td>
              <td class="align-middle">
                <div class="d-flex align-items-center gap-1 flex-nowrap">
                  <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=therapists-show&id=<?php echo (int) $therapist['id']; ?>" title="Visualizar"><i class="fa-solid fa-eye"></i></a>
                  <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=therapists-edit&id=<?php echo (int) $therapist['id']; ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
                  <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=therapists-password&id=<?php echo (int) $therapist['id']; ?>" title="Alterar senha"><i class="fa-solid fa-key"></i></a>
                  <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapists-delete" class="d-flex m-0 js-delete-therapist-form" data-therapist-name="<?php echo htmlspecialchars((string) $therapist['name']); ?>">
                    <input type="hidden" name="id" value="<?php echo (int) $therapist['id']; ?>">
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
  $('#therapistSearch').on('input', function(){
    const t = ($(this).val() || '').toLowerCase();
    $('.row-therapist').each(function(){
      const h = ($(this).data('search') || '').toString();
      $(this).toggle(t === '' || h.includes(t));
    });
  });

  $('.js-delete-therapist-form').on('submit', function(e) {
    const form = this;
    if (form.dataset.confirmed === '1') {
      return;
    }

    e.preventDefault();
    const therapistName = form.getAttribute('data-therapist-name') || 'este terapeuta';

    if (typeof Swal === 'undefined') {
      if (confirm('Excluir ' + therapistName + '?')) {
        form.dataset.confirmed = '1';
        form.submit();
      }
      return;
    }

    Swal.fire({
      title: 'Confirmar exclusão',
      text: 'Deseja realmente excluir ' + therapistName + '?',
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
