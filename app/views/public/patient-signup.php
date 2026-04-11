<?php $title = 'Ficha de cadastro'; include __DIR__ . '/../partials/header.php'; ?>
<div class="container py-5" style="max-width:780px;">
  <div class="card shadow-sm border-0">
    <div class="card-body p-4 p-md-5">
      <h3 class="mb-1">Ficha de cadastro do paciente</h3>
      <p class="text-muted mb-4">Preencha seus dados para iniciar seu acesso ao sistema.</p>

      <?php if (isset($_GET['status']) && isset($_GET['msg'])): ?>
        <div class="alert <?php echo (string) $_GET['status'] === 'success' ? 'alert-success' : 'alert-danger'; ?> mb-3"><?php echo htmlspecialchars((string) $_GET['msg']); ?></div>
      <?php endif; ?>

      <?php if (empty($linkData)): ?>
        <div class="alert alert-danger mb-0">Este link está inválido, expirado ou indisponível.</div>
      <?php else: ?>
        <form method="POST" action="<?php echo $appUrl; ?>/index.php?action=patient-signup-submit" class="row g-3">
          <input type="hidden" name="token" value="<?php echo htmlspecialchars((string) $token); ?>">
          <div class="col-md-8"><label class="form-label">Nome completo</label><input class="form-control" name="name" required></div>
          <div class="col-md-4"><label class="form-label">Data de nascimento</label><input class="form-control" type="date" name="birth_date"></div>
          <div class="col-md-4"><label class="form-label">CPF</label><input class="form-control mask-cpf" name="cpf" required></div>
          <div class="col-md-4"><label class="form-label">Telefone</label><input class="form-control mask-phone" name="phone" required></div>
          <div class="col-md-4"><label class="form-label">E-mail</label><input class="form-control" type="email" name="email" required></div>
          <div class="col-md-6"><label class="form-label">Estado civil</label><input class="form-control" name="marital_status"></div>
          <div class="col-md-6"><label class="form-label">Filhos</label><input class="form-control" name="children"></div>
          <div class="col-12"><label class="form-label">Queixa principal</label><textarea class="form-control" name="main_complaint" rows="4"></textarea></div>
          <div class="col-12"><button class="btn btn-primary w-100" type="submit">Enviar cadastro</button></div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
