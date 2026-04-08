<?php $title = 'Login - Terapia SaaS'; include __DIR__ . '/../partials/header.php'; ?>
<div class="login-shell">
  <div class="login-backdrop"></div>
  <div class="login-card-wrap">
    <div class="login-brand-panel">
      <h1>Terapia SaaS</h1>
      <p>Gestao clinica simples, segura e elegante para terapeutas e pacientes.</p>
      <ul>
        <li><i class="fa-solid fa-check"></i> Agenda e historico integrados</li>
        <li><i class="fa-solid fa-check"></i> Portal dedicado ao paciente</li>
        <li><i class="fa-solid fa-check"></i> Controle financeiro e tarefas</li>
      </ul>
    </div>
    <div class="card shadow-sm login-card">
      <div class="card-body p-4 p-md-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="mb-0">Entrar no sistema</h4>
          <button id="themeToggle" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-circle-half-stroke"></i></button>
        </div>
        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger">Credenciais invalidas.</div>
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
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
