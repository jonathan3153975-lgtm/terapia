<?php $title = 'Login - Tera-Tech'; include __DIR__ . '/../partials/header.php'; ?>
<div class="login-shell">
  <div class="login-backdrop"></div>
  <div class="login-card-wrap">
    <div class="login-brand-panel">
      <h1>Tera-Tech - Seu ambiente de terapia diária.</h1>
      <p>Para seu ambiente de conhecimento e autoajuda que te levará ao extraordinário!</p>
      <ul>
        <li><i class="fa-solid fa-check"></i> Agenda e histórico integrados</li>
        <li><i class="fa-solid fa-check"></i> Portal com assinatura e conteúdos</li>
        <li><i class="fa-solid fa-check"></i> Gestão completa de tarefas e materiais</li>
      </ul>
    </div>
    <div class="card shadow-sm login-card">
      <div class="card-body p-4 p-md-5">
        <div class="mb-3">
          <h4 class="mb-0">Acessar plataforma</h4>
        </div>
        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger">Credenciais inválidas ou acesso ainda não liberado.</div>
        <?php endif; ?>
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
<script>
window.addEventListener('load', function() {
  $('#loginForm').on('submit', function(e){
    e.preventDefault();
    const form = this;
    if (!window.FormSubmitGuard.lock(form, 'Entrando...')) {
      return;
    }
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
        window.FormSubmitGuard.unlock(form);
        Swal.fire('Erro', res.message || 'Falha no login', 'error');
      },
      error: function(xhr){
        window.FormSubmitGuard.unlock(form);
        const msg = xhr.responseJSON?.message || 'Falha no login';
        Swal.fire('Erro', msg, 'error');
      }
    });
  });
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
