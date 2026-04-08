<?php $title = 'Novo Terapeuta'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="container page-wrap">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-body">
          <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

          <h4 class="mb-3">Cadastrar terapeuta</h4>
          <form id="therapistForm" action="<?php echo $appUrl; ?>/dashboard.php?action=therapists-store" method="POST">
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label">Nome</label><input class="form-control" name="name" required></div>
              <div class="col-md-6"><label class="form-label">CPF</label><input class="form-control mask-cpf" name="cpf" required></div>
              <div class="col-md-6"><label class="form-label">Telefone</label><input class="form-control mask-phone" name="phone" required></div>
              <div class="col-md-6"><label class="form-label">E-mail</label><input class="form-control" type="email" name="email" required></div>
              <div class="col-md-12">
                <label class="form-label">Senha</label>
                <div class="input-group">
                  <input class="form-control" id="pwd" name="password" required>
                  <button class="btn btn-outline-secondary" type="button" id="btnPwd">Gerar senha</button>
                </div>
              </div>
            </div>
            <div class="mt-3 d-flex gap-2">
              <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapists">Voltar</a>
              <button class="btn btn-primary" type="submit">Salvar</button>
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
  $('#therapistForm').on('submit', function(e){
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
        Swal.fire('Erro', res.message || 'Falha ao salvar', 'error');
      },
      error: function(xhr){
        window.FormSubmitGuard.unlock(form);
        Swal.fire('Erro', xhr.responseJSON?.message || 'Falha ao salvar', 'error');
      }
    });
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
