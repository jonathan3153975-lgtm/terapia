<?php $title = 'Cadastro enviado'; include __DIR__ . '/../partials/header.php'; ?>
<div class="container py-5" style="max-width:680px;">
  <div class="card shadow-sm border-0">
    <div class="card-body p-5 text-center">
      <i class="fa-solid fa-circle-check text-success" style="font-size:3rem;"></i>
      <h3 class="mt-3">Cadastro enviado com sucesso</h3>
      <p class="text-muted mb-0">Seu cadastro ficou pendente de revisão pelo terapeuta. Assim que aprovado, você poderá acessar o sistema com os dados enviados por e-mail.</p>
      <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success mt-3 mb-0"><?php echo htmlspecialchars((string) $_GET['msg']); ?></div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
