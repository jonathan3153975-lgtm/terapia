<?php $title = 'Alterar Senha do Terapeuta'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="container page-wrap">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-body">
          <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

          <h4 class="mb-1">Alterar senha</h4>
          <p class="text-muted mb-3">Terapeuta: <?php echo htmlspecialchars((string) $therapist['name']); ?></p>

          <form id="therapistPasswordForm" action="<?php echo $appUrl; ?>/dashboard.php?action=therapists-password-update" method="POST">
            <input type="hidden" name="id" value="<?php echo (int) $therapist['id']; ?>">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Nova senha</label>
                <div class="input-group">
                  <input class="form-control" id="pwd" name="password" required>
                  <button class="btn btn-outline-secondary" type="button" id="btnPwd">Gerar senha</button>
                </div>
              </div>
            </div>
            <div class="mt-3 d-flex gap-2">
              <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapists">Voltar</a>
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
  $('#therapistPasswordForm').on('submit', function(e){
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
        Swal.fire('Erro', res.message || 'Falha ao alterar senha', 'error');
      },
      error: function(xhr){
        window.FormSubmitGuard.unlock(form);
        Swal.fire('Erro', xhr.responseJSON?.message || 'Falha ao alterar senha', 'error');
      }
    });
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>