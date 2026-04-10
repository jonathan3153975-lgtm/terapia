<?php $title = 'Acesso do Paciente'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="container page-wrap">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-body">
          <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

          <h4 class="mb-1">Redefinir acesso do paciente</h4>
          <p class="text-muted mb-1">Paciente: <?php echo htmlspecialchars((string) $patient['name']); ?></p>
          <p class="text-muted mb-3">
            <?php if (!empty($patientAccess)): ?>
              Acesso já existente para o e-mail <?php echo htmlspecialchars((string) ($patientAccess['email'] ?? '-')); ?>.
            <?php else: ?>
              Este paciente ainda não possui acesso criado. Ao salvar a senha, o acesso será criado.
            <?php endif; ?>
          </p>

          <form id="patientPasswordForm" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-password-update" method="POST">
            <input type="hidden" name="id" value="<?php echo (int) $patient['id']; ?>">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">E-mail do acesso</label>
                <input class="form-control" value="<?php echo htmlspecialchars((string) ($patient['email'] ?? '')); ?>" disabled>
                <small class="text-muted">O login do paciente usa o e-mail cadastrado na ficha.</small>
              </div>
              <div class="col-12">
                <label class="form-label">Nova senha</label>
                <div class="input-group">
                  <input class="form-control" id="pwd" name="password" required>
                  <button class="btn btn-outline-secondary" type="button" id="btnPwd">Gerar senha</button>
                </div>
              </div>
            </div>
            <div class="mt-3 d-flex gap-2">
              <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=patients">Voltar</a>
              <button class="btn btn-primary" type="submit">Salvar senha</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
window.addEventListener('load', function() {
  $('#btnPwd').on('click', function(){ $('#pwd').val(generatePassword()); });
  $('#patientPasswordForm').on('submit', function(e){
    e.preventDefault();
    const form = this;
    if (!window.FormSubmitGuard.lock(form, 'Salvando...')) {
      return;
    }
    $.ajax({
      url: form.action,
      method: 'POST',
      data: $(form).serialize(),
      headers: {'X-Requested-With':'XMLHttpRequest'},
      success: function(res){
        if (res.success) { window.location.href = res.redirect; return; }
        window.FormSubmitGuard.unlock(form);
        Swal.fire('Erro', res.message || 'Falha ao salvar senha', 'error');
      },
      error: function(xhr){
        window.FormSubmitGuard.unlock(form);
        Swal.fire('Erro', xhr.responseJSON?.message || 'Falha ao salvar senha', 'error');
      }
    });
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
