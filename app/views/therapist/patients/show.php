<?php $title = 'Paciente'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Ficha do paciente</h3>
    <a class="btn btn-outline-dark" href="<?php echo $appUrl; ?>/dashboard.php?action=patients"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6"><strong>Nome:</strong> <?php echo htmlspecialchars((string) $patient['name']); ?></div>
        <div class="col-md-3"><strong>CPF:</strong> <?php echo htmlspecialchars((string) $patient['cpf']); ?></div>
        <div class="col-md-3"><strong>Telefone:</strong> <?php echo htmlspecialchars((string) $patient['phone']); ?></div>
        <div class="col-md-6"><strong>E-mail:</strong> <?php echo htmlspecialchars((string) ($patient['email'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>Estado civil:</strong> <?php echo htmlspecialchars((string) ($patient['marital_status'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>Filhos:</strong> <?php echo htmlspecialchars((string) ($patient['children'] ?? '-')); ?></div>
        <div class="col-md-6"><strong>Endereco:</strong> <?php echo htmlspecialchars((string) ($patient['address'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>Cidade:</strong> <?php echo htmlspecialchars((string) ($patient['city'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>UF:</strong> <?php echo htmlspecialchars((string) ($patient['state'] ?? '-')); ?></div>
        <div class="col-12"><strong>Queixa principal:</strong><br><?php echo nl2br(htmlspecialchars((string) ($patient['main_complaint'] ?? '-'))); ?></div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
