<?php $title = 'Login - Tera-Tech'; include __DIR__ . '/../partials/header.php'; ?>
<div class="login-shell">
  <div class="login-backdrop"></div>
  <div class="login-orb login-orb-a"></div>
  <div class="login-orb login-orb-b"></div>
  <div class="login-card-wrap">
    <div class="login-brand-panel">
      <h1>Tera-Tech</h1>
      <p>Seu ambiente de terapia e autoconhecimento diário</p>
      <div class="login-system-info">
        <article>
          <i class="fa-solid fa-calendar-check"></i>
          <div>
            <h6>Agenda organizada</h6>
            <small>Visualize suas sessões e acompanhe seus próximos atendimentos.</small>
          </div>
        </article>
        <article>
          <i class="fa-solid fa-list-check"></i>
          <div>
            <h6>Tarefas terapêuticas</h6>
            <small>Receba atividades práticas e marque seu progresso de forma simples.</small>
          </div>
        </article>
        <article>
          <i class="fa-solid fa-book-open"></i>
          <div>
            <h6>Materiais de apoio</h6>
            <small>Acesse conteúdos enviados pelo terapeuta para fortalecer sua jornada.</small>
          </div>
        </article>
        <article>
          <i class="fa-solid fa-shield-heart"></i>
          <div>
            <h6>Ambiente seguro</h6>
            <small>Seus dados ficam protegidos para um acompanhamento com confiança.</small>
          </div>
        </article>
      </div>
    </div>
    <div class="card shadow-sm login-card login-glass-card">
      <div class="card-body p-4 p-md-5">
        <div class="mb-4">
          <h4 class="mb-1">Acessar plataforma</h4>
          <small class="text-muted">Entre com seu e-mail e senha</small>
        </div>
        <?php if (isset($_GET['status']) && isset($_GET['msg'])): ?>
          <div class="alert <?php echo (string) $_GET['status'] === 'success' ? 'alert-success' : 'alert-danger'; ?>">
            <?php echo htmlspecialchars((string) $_GET['msg']); ?>
          </div>
        <?php elseif (isset($_GET['error'])): ?>
          <div class="alert alert-danger">Credenciais inválidas. Verifique seu e-mail e senha.</div>
        <?php endif; ?>
        <form id="loginForm" method="POST" action="<?php echo $appUrl; ?>/index.php?action=process-login">
          <div class="mb-3 login-input-wrap">
            <label class="form-label">E-mail</label>
            <input class="form-control" name="email" type="email" required>
          </div>
          <div class="mb-2 login-input-wrap">
            <label class="form-label">Senha</label>
            <input class="form-control" name="password" type="password" required>
          </div>
          <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-link p-0 login-link" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Esqueci minha senha</button>
          </div>
          <button class="btn btn-primary w-100 login-btn" type="submit">Entrar</button>
        </form>
        <?php if (!empty($signupUrl)): ?>
          <div class="login-signup-cta mt-4 pt-3">
            <span class="text-muted small d-block mb-2">Primeiro acesso como paciente?</span>
            <a class="btn btn-outline-light w-100 login-signup-btn" href="<?php echo htmlspecialchars((string) $signupUrl); ?>">Fazer cadastro básico</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header">
        <h5 class="modal-title">Redefinir senha</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">Informe seu e-mail de acesso para receber uma nova senha.</p>
        <form id="forgotPasswordForm" action="<?php echo $appUrl; ?>/index.php?action=forgot-password" method="POST">
          <label class="form-label">E-mail</label>
          <input class="form-control" type="email" name="email" required>
          <button class="btn btn-primary w-100 mt-3" type="submit">Enviar nova senha</button>
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

  $('#forgotPasswordForm').on('submit', function(e){
    e.preventDefault();
    const form = this;
    if (!window.FormSubmitGuard.lock(form, 'Enviando...')) {
      return;
    }

    $.ajax({
      url: form.action,
      method: 'POST',
      data: $(form).serialize(),
      headers: {'X-Requested-With':'XMLHttpRequest'},
      success: function(res){
        window.FormSubmitGuard.unlock(form);
        const modalEl = document.getElementById('forgotPasswordModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
          modal.hide();
        }
        Swal.fire('Pronto', res.message || 'Verifique seu e-mail.', 'success');
        form.reset();
      },
      error: function(xhr){
        window.FormSubmitGuard.unlock(form);
        const msg = xhr.responseJSON?.message || 'Falha ao enviar nova senha';
        Swal.fire('Erro', msg, 'error');
      }
    });
  });
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
