<?php $title = 'Login - Terapia SaaS'; include __DIR__ . '/../partials/header.php'; ?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h4 class="mb-3">Entrar no sistema</h4>
          <form id="loginForm" method="POST" action="<?php echo $appUrl; ?>/index.php?action=process-login">
            <div class="mb-3">
              <label class="form-label">E-mail</label>
              <input class="form-control" name="email" type="email" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Senha</label>
              <input class="form-control" name="password" type="password" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Entrar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$('#loginForm').on('submit', function(e){
  e.preventDefault();
  const form = this;
  $.ajax({
    url: form.action,
    method: 'POST',
    data: $(form).serialize(),
    headers: {'X-Requested-With':'XMLHttpRequest'},
    success: function(res){
      if (res.success) {
        window.location.href = res.redirect;
        return;
      }
      Swal.fire('Erro', res.message || 'Falha no login', 'error');
    },
    error: function(xhr){
      const msg = xhr.responseJSON?.message || 'Falha no login';
      Swal.fire('Erro', msg, 'error');
    }
  });
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
