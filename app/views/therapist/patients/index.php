<?php $title = 'Pacientes'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Pacientes</h3>
    <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-create"><i class="fa-solid fa-user-plus"></i> Novo paciente</a>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <h6 class="mb-0">Ficha de cadastro por link</h6>
        <small class="text-muted">Envie por e-mail ou copie o link manualmente.</small>
      </div>
      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-signup-link-create" class="row g-2 align-items-end">
        <div class="col-md-8">
          <label class="form-label">E-mail do paciente (opcional)</label>
          <input type="email" class="form-control" name="recipient_email" placeholder="Se informado, enviaremos automaticamente o link.">
        </div>
        <div class="col-md-4">
          <button class="btn btn-outline-primary w-100" type="submit"><i class="fa-solid fa-link me-1"></i>Gerar link</button>
        </div>
      </form>

      <?php if (!empty($generatedLink)): ?>
        <div class="mt-3">
          <label class="form-label">Link gerado</label>
          <div class="input-group">
            <input id="generatedSignupLink" type="text" class="form-control" value="<?php echo htmlspecialchars((string) $generatedLink); ?>" readonly>
            <button class="btn btn-outline-secondary" type="button" id="copySignupLinkBtn"><i class="fa-solid fa-copy"></i> Copiar</button>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($signupLinks)): ?>
        <div class="mt-3 small text-muted">
          Últimos links:
          <?php foreach ($signupLinks as $link): ?>
            <div>#<?php echo (int) ($link['id'] ?? 0); ?> | expira em <?php echo !empty($link['expires_at']) ? date('d/m/Y H:i', strtotime((string) $link['expires_at'])) : '-'; ?> | uso <?php echo (int) ($link['used_count'] ?? 0); ?>/<?php echo (int) ($link['max_uses'] ?? 0); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card mb-3"><div class="card-body">
    <input id="patientSearch" class="form-control" placeholder="Digite para filtrar dinamicamente" value="<?php echo htmlspecialchars($search); ?>">
  </div></div>
  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>Nome</th><th>Status</th><th>Plano</th><th>Ações</th></tr></thead>
        <tbody>
          <?php if (empty($patients)): ?>
            <tr><td colspan="4" class="text-center py-4 text-muted">Nenhum paciente cadastrado.</td></tr>
          <?php else: foreach ($patients as $patient): ?>
            <tr class="row-patient" data-search="<?php echo strtolower(htmlspecialchars(($patient['name'] ?? '') . ' ' . ($patient['cpf'] ?? '') . ' ' . ($patient['email'] ?? ''))); ?>">
              <td><?php echo htmlspecialchars($patient['name']); ?></td>
              <td>
                <?php $isPendingReview = (($patient['review_status'] ?? 'approved') === 'pending_review'); ?>
                <span class="badge <?php echo $isPendingReview ? 'text-bg-warning' : 'text-bg-success'; ?>"><?php echo $isPendingReview ? 'Pendente revisão' : 'Aprovado'; ?></span>
              </td>
              <td>
                <?php $sub = $patientSubscriptions[(int) ($patient['id'] ?? 0)] ?? null; ?>
                <div class="small mb-1">
                  <?php if ($sub): ?>
                    <strong><?php echo htmlspecialchars((string) ($sub['plan_name'] ?? 'Plano')); ?></strong>
                    <span class="badge <?php echo (string) ($sub['status'] ?? '') === 'active' ? 'text-bg-success' : 'text-bg-secondary'; ?>"><?php echo htmlspecialchars((string) ($sub['status'] ?? '')); ?></span>
                  <?php else: ?>
                    <span class="text-muted">Sem plano</span>
                  <?php endif; ?>
                </div>
                <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-plan-assign" class="d-flex gap-1">
                  <input type="hidden" name="patient_id" value="<?php echo (int) ($patient['id'] ?? 0); ?>">
                  <select class="form-select form-select-sm" name="plan_id" required>
                    <option value="">Plano...</option>
                    <?php foreach (($availablePlans ?? []) as $plan): ?>
                      <option value="<?php echo (int) ($plan['id'] ?? 0); ?>">
                        <?php echo htmlspecialchars((string) ($plan['name'] ?? 'Plano')); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <button class="btn btn-sm btn-outline-primary" type="submit" title="Atribuir plano"><i class="fa-solid fa-check"></i></button>
                </form>
              </td>
              <td class="align-middle">
                <div class="d-flex align-items-center gap-1 flex-nowrap">
                  <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-plan-toggle" class="d-flex m-0">
                    <input type="hidden" name="patient_id" value="<?php echo (int) ($patient['id'] ?? 0); ?>">
                    <button class="btn btn-sm btn-outline-warning" style="width:32px;padding:0;line-height:1.8;" type="submit" title="Ativar/Desativar plano"><i class="fa-solid fa-power-off"></i></button>
                  </form>
                  <?php if ($isPendingReview): ?>
                    <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-approve-review" class="d-flex m-0">
                      <input type="hidden" name="id" value="<?php echo (int) $patient['id']; ?>">
                      <button class="btn btn-sm btn-outline-success" style="width:32px;padding:0;line-height:1.8;" type="submit" title="Aprovar cadastro"><i class="fa-solid fa-check"></i></button>
                    </form>
                  <?php endif; ?>
                  <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-show&id=<?php echo (int) $patient['id']; ?>" title="Visualizar"><i class="fa-solid fa-eye"></i></a>
                  <a class="btn btn-sm btn-outline-primary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-preview-start&id=<?php echo (int) $patient['id']; ?>" title="Abrir ambiente do paciente"><i class="fa-solid fa-right-to-bracket"></i></a>
                  <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-history&id=<?php echo (int) $patient['id']; ?>" title="Histórico"><i class="fa-solid fa-book-medical"></i></a>
                  <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-password&id=<?php echo (int) $patient['id']; ?>" title="Redefinir senha"><i class="fa-solid fa-key"></i></a>
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
  const copyBtn = document.getElementById('copySignupLinkBtn');
  const copyInput = document.getElementById('generatedSignupLink');
  if (copyBtn && copyInput) {
    copyBtn.addEventListener('click', function() {
      copyInput.select();
      copyInput.setSelectionRange(0, 99999);
      document.execCommand('copy');
      if (typeof Swal !== 'undefined') {
        Swal.fire('Copiado', 'Link copiado para a área de transferência.', 'success');
      }
    });
  }

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
