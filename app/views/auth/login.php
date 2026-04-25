<?php $title = 'Login - Tera-Tech'; include __DIR__ . '/../partials/header.php'; ?>
<div class="login-shell">
  <div class="login-backdrop"></div>
  <div class="login-orb login-orb-a"></div>
  <div class="login-orb login-orb-b"></div>
  <div class="login-card-wrap">
    <div class="login-brand-panel">
      <span class="login-kicker">Bem-estar com presença</span>
      <h1>Tera-Tech</h1>
      <p>Uma plataforma mais humana para organizar atendimentos, fortalecer a rotina terapêutica e deixar a jornada do paciente mais acolhedora.</p>
      <div class="login-mini-stats">
        <div class="login-mini-stat">
          <strong>Rotina leve</strong>
          <span>Agenda, tarefas e materiais em um fluxo simples.</span>
        </div>
        <div class="login-mini-stat">
          <strong>Cuidado contínuo</strong>
          <span>Experiências guiadas para manter constância entre as sessões.</span>
        </div>
      </div>
      <div class="login-feature-chip-list">
        <span class="login-feature-chip"><i class="fa-solid fa-sparkles"></i> Mais cor e clareza</span>
        <span class="login-feature-chip"><i class="fa-solid fa-heart"></i> Ambiente acolhedor</span>
        <span class="login-feature-chip"><i class="fa-solid fa-mobile-screen-button"></i> Navegação fluida no celular</span>
      </div>
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
        <div class="login-card-header mb-4">
          <div>
            <span class="login-form-badge">Acesso seguro</span>
            <h4 class="mb-1 mt-2">Entrar na plataforma</h4>
            <small class="text-muted">Use seu e-mail e sua senha para continuar.</small>
          </div>
          <div class="login-card-header-icon">
            <i class="fa-solid fa-sun"></i>
          </div>
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
            <input class="form-control" name="email" type="email" inputmode="email" autocomplete="username" autocapitalize="none" autocorrect="off" spellcheck="false" required>
          </div>
          <div class="mb-2 login-input-wrap">
            <label class="form-label">Senha</label>
            <input class="form-control" name="password" type="password" autocomplete="current-password" required>
          </div>
          <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-link p-0 login-link" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Esqueci minha senha</button>
          </div>
          <button class="btn btn-primary w-100 login-btn" type="submit">Entrar</button>
        </form>
        <div class="login-support-note mt-3">
          <i class="fa-solid fa-shield-heart"></i>
          <span>Seus dados ficam protegidos para uma experiência terapêutica mais tranquila.</span>
        </div>
        <?php if (!empty($signupUrl)): ?>
          <div class="login-signup-cta mt-4 pt-3">
            <span class="text-muted small d-block mb-2">Primeiro acesso como paciente?</span>
            <a class="btn btn-dark w-100 login-signup-btn" href="<?php echo htmlspecialchars((string) $signupUrl); ?>">Fazer cadastro básico</a>
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
    const form = this;
    if (!window.FormSubmitGuard.lock(form, 'Entrando...')) {
      e.preventDefault();
    }
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
