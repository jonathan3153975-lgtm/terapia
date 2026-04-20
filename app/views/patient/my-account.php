<?php $title = 'Minha conta'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="mb-4">
    <h4 class="fw-bold mb-0"><i class="fa-solid fa-circle-user me-2 text-primary"></i>Minha conta</h4>
    <p class="text-muted small mb-0">Gerencie seus dados pessoais e senha de acesso.</p>
  </div>

  <div class="row g-4">

    <!-- ── Dados pessoais ─────────────────────────────────────────────── -->
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-body p-4">
          <h5 class="card-title mb-4"><i class="fa-solid fa-id-card me-2 text-secondary"></i>Dados pessoais</h5>
          <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=my-account-save" novalidate>
            <input type="hidden" name="section" value="profile">

            <div class="mb-3">
              <label for="name" class="form-label fw-semibold">Nome completo <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="name" name="name"
                     value="<?php echo htmlspecialchars((string) ($user['name'] ?? '')); ?>"
                     required maxlength="150">
            </div>

            <div class="mb-3">
              <label for="email" class="form-label fw-semibold">E-mail <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="email" name="email"
                     value="<?php echo htmlspecialchars((string) ($user['email'] ?? '')); ?>"
                     required maxlength="150">
            </div>

            <div class="mb-4">
              <label for="phone" class="form-label fw-semibold">Telefone <span class="text-danger">*</span></label>
              <input type="tel" class="form-control" id="phone" name="phone"
                     value="<?php echo htmlspecialchars((string) ($user['phone'] ?? '')); ?>"
                     required maxlength="20">
            </div>

            <button type="submit" class="btn btn-primary w-100">
              <i class="fa-solid fa-floppy-disk me-1"></i>Salvar dados
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- ── Alteração de senha ─────────────────────────────────────────── -->
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-body p-4">
          <h5 class="card-title mb-4"><i class="fa-solid fa-lock me-2 text-secondary"></i>Alterar senha</h5>
          <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=my-account-save" novalidate id="passwordForm">
            <input type="hidden" name="section" value="password">

            <div class="mb-3">
              <label for="current_password" class="form-label fw-semibold">Senha atual <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="password" class="form-control" id="current_password" name="current_password"
                       required autocomplete="current-password">
                <button type="button" class="btn btn-outline-secondary toggle-pw" data-target="current_password" tabindex="-1">
                  <i class="fa-solid fa-eye"></i>
                </button>
              </div>
            </div>

            <div class="mb-3">
              <label for="new_password" class="form-label fw-semibold">Nova senha <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="password" class="form-control" id="new_password" name="new_password"
                       required minlength="8" autocomplete="new-password">
                <button type="button" class="btn btn-outline-secondary toggle-pw" data-target="new_password" tabindex="-1">
                  <i class="fa-solid fa-eye"></i>
                </button>
              </div>
              <div class="form-text">Mínimo de 8 caracteres.</div>
            </div>

            <div class="mb-4">
              <label for="confirm_password" class="form-label fw-semibold">Confirmar nova senha <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                       required minlength="8" autocomplete="new-password">
                <button type="button" class="btn btn-outline-secondary toggle-pw" data-target="confirm_password" tabindex="-1">
                  <i class="fa-solid fa-eye"></i>
                </button>
              </div>
            </div>

            <button type="submit" class="btn btn-warning w-100 text-dark fw-semibold">
              <i class="fa-solid fa-key me-1"></i>Alterar senha
            </button>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
(function () {
  // Toggle visibilidade de senha
  document.querySelectorAll('.toggle-pw').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var targetId = btn.getAttribute('data-target');
      var input = document.getElementById(targetId);
      if (!input) return;
      var isText = input.type === 'text';
      input.type = isText ? 'password' : 'text';
      btn.querySelector('i').classList.toggle('fa-eye', isText);
      btn.querySelector('i').classList.toggle('fa-eye-slash', !isText);
    });
  });

  // Validação client-side de confirmação de senha
  var form = document.getElementById('passwordForm');
  if (form) {
    form.addEventListener('submit', function (e) {
      var np = document.getElementById('new_password').value;
      var cp = document.getElementById('confirm_password').value;
      if (np !== cp) {
        e.preventDefault();
        document.getElementById('confirm_password').setCustomValidity('As senhas não conferem.');
        form.classList.add('was-validated');
      } else {
        document.getElementById('confirm_password').setCustomValidity('');
      }
    });
    document.getElementById('confirm_password').addEventListener('input', function () {
      this.setCustomValidity('');
    });
  }
}());
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
