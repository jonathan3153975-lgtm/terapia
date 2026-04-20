<?php $title = 'Ficha de cadastro'; include __DIR__ . '/../partials/header.php'; ?>
<div class="container py-5" style="max-width:780px;">
  <div class="card shadow-sm border-0">
    <div class="card-body p-4 p-md-5">
      <h3 class="mb-1">Cadastro básico do paciente</h3>
      <p class="text-muted mb-4">Informe seus dados iniciais. O restante do cadastro será concluído pelo terapeuta no momento da aprovação.</p>

      <?php if (isset($_GET['status']) && isset($_GET['msg'])): ?>
        <div class="alert <?php echo (string) $_GET['status'] === 'success' ? 'alert-success' : 'alert-danger'; ?> mb-3"><?php echo htmlspecialchars((string) $_GET['msg']); ?></div>
      <?php endif; ?>

      <?php if (empty($linkData)): ?>
        <div class="alert alert-danger mb-0">Este link está inválido, expirado ou indisponível.</div>
      <?php else: ?>
        <form method="POST" action="<?php echo $appUrl; ?>/index.php?action=patient-signup-submit" class="row g-3">
          <input type="hidden" name="token" value="<?php echo htmlspecialchars((string) $token); ?>">
          <div class="col-12"><label class="form-label">Nome completo</label><input class="form-control" name="name" required></div>
          <div class="col-md-4"><label class="form-label">CPF</label><input class="form-control mask-cpf" name="cpf" required></div>
          <div class="col-md-4"><label class="form-label">Telefone</label><input class="form-control mask-phone" name="phone" required></div>
          <div class="col-md-4"><label class="form-label">E-mail</label><input class="form-control" type="email" name="email" required></div>
          <div class="col-12">
            <div class="alert alert-light border mb-0">
              Após concluir o cadastro, você receberá por e-mail a senha gerada automaticamente para acessar a plataforma.
            </div>
          </div>

          <div class="col-12 d-flex flex-column flex-sm-row gap-2">
            <button class="btn btn-primary flex-fill" type="submit">Concluir cadastro</button>
            <a class="btn btn-outline-secondary flex-fill" href="<?php echo $appUrl; ?>/index.php?action=login">Voltar ao login</a>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
