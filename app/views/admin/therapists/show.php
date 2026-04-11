<?php $title = 'Terapeuta'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Ficha do terapeuta</h3>
    <a class="btn btn-outline-dark" href="<?php echo $appUrl; ?>/dashboard.php?action=therapists"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-12">
          <strong>Logo da empresa:</strong>
          <?php if (!empty($therapist['company_logo_path'])): ?>
            <div class="mt-2">
              <img src="<?php echo $appUrl . '/' . ltrim((string) $therapist['company_logo_path'], '/'); ?>" alt="Logo da empresa" class="guided-table-thumb" style="width: 120px; height: 120px; border-radius: 14px;">
            </div>
          <?php else: ?>
            <span class="text-muted">Não informado</span>
          <?php endif; ?>
        </div>
        <div class="col-md-6"><strong>Nome:</strong> <?php echo htmlspecialchars((string) $therapist['name']); ?></div>
        <div class="col-md-3"><strong>CPF:</strong> <?php echo htmlspecialchars((string) ($therapist['cpf'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>Telefone:</strong> <?php echo htmlspecialchars((string) ($therapist['phone'] ?? '-')); ?></div>
        <div class="col-md-6"><strong>E-mail:</strong> <?php echo htmlspecialchars((string) ($therapist['email'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>Plano:</strong> <?php echo htmlspecialchars((string) ($therapist['plan_type'] ?? 'mensal')); ?></div>
        <div class="col-md-3"><strong>Status:</strong> <?php echo htmlspecialchars((string) ($therapist['status'] ?? 'active')); ?></div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../partials/footer.php'; ?>