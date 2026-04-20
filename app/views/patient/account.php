<?php $title = 'Minha conta'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
    <h3 class="mb-0">Minha conta</h3>
    <span class="text-muted small">Atualize seus dados de acesso e contato.</span>
  </div>

  <div class="row g-3">
    <div class="col-12 col-xl-7">
      <div class="card h-100">
        <div class="card-body p-4">
          <h5 class="mb-1">Dados básicos</h5>
          <p class="text-muted small mb-4">Essas informações são usadas no seu acesso à plataforma.</p>

          <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=account-update" class="row g-3">
            <div class="col-12">
              <label class="form-label">Nome</label>
              <input class="form-control" name="name" value="<?php echo htmlspecialchars((string) ($accountUser['name'] ?? '')); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">E-mail</label>
              <input class="form-control" type="email" name="email" value="<?php echo htmlspecialchars((string) ($accountUser['email'] ?? '')); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Telefone</label>
              <input class="form-control mask-phone" name="phone" value="<?php echo htmlspecialchars((string) ($accountUser['phone'] ?? '')); ?>" required>
            </div>
            <div class="col-12 d-flex justify-content-end">
              <button class="btn btn-primary" type="submit">Salvar dados</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-5">
      <div class="card h-100">
        <div class="card-body p-4">
          <h5 class="mb-1">Alterar senha</h5>
          <p class="text-muted small mb-4">Use sua senha atual para definir uma nova senha de acesso.</p>

          <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=account-password-update" class="row g-3">
            <div class="col-12">
              <label class="form-label">Senha atual</label>
              <input class="form-control" type="password" name="current_password" required>
            </div>
            <div class="col-12">
              <label class="form-label">Nova senha</label>
              <input class="form-control" type="password" name="new_password" minlength="6" required>
            </div>
            <div class="col-12">
              <label class="form-label">Confirmar nova senha</label>
              <input class="form-control" type="password" name="confirm_password" minlength="6" required>
            </div>
            <div class="col-12">
              <div class="alert alert-light border small mb-0">
                A nova senha deve ter pelo menos 6 caracteres.
              </div>
            </div>
            <div class="col-12 d-flex justify-content-end">
              <button class="btn btn-dark" type="submit">Atualizar senha</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
