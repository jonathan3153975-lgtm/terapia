<?php $title = 'Terapeutas'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="container page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Terapeutas</h3>
    <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapists-create">Novo terapeuta</a>
  </div>
  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>Nome</th><th>CPF</th><th>Telefone</th><th>E-mail</th><th>Plano</th><th>Status</th></tr></thead>
        <tbody>
          <?php if (empty($therapists)): ?>
            <tr><td colspan="6" class="text-center py-4 text-muted">Nao ha terapeutas cadastrados.</td></tr>
          <?php else: foreach ($therapists as $therapist): ?>
            <tr>
              <td><?php echo htmlspecialchars($therapist['name']); ?></td>
              <td><?php echo htmlspecialchars($therapist['cpf'] ?? '-'); ?></td>
              <td><?php echo htmlspecialchars($therapist['phone'] ?? '-'); ?></td>
              <td><?php echo htmlspecialchars($therapist['email']); ?></td>
              <td><?php echo htmlspecialchars($therapist['plan_type'] ?? 'mensal'); ?></td>
              <td><?php echo htmlspecialchars($therapist['status'] ?? 'active'); ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
