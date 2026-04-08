<?php $title = 'Visualizar Atendimento'; include __DIR__ . '/../../../partials/header.php'; include __DIR__ . '/../../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Atendimento de <?php echo htmlspecialchars((string) $patient['name']); ?></h3>
    <a class="btn btn-outline-dark" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-history&id=<?php echo (int) $patient['id']; ?>"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4"><strong>Data:</strong> <?php echo htmlspecialchars((string) ($appointment['session_date'] ?? '-')); ?></div>
        <div class="col-md-8"><strong>Descrição:</strong> <?php echo htmlspecialchars((string) ($appointment['description'] ?? '-')); ?></div>
        <div class="col-12">
          <strong>Histórico:</strong>
          <div class="mt-2 border rounded p-3 bg-light-subtle">
            <?php echo (string) ($appointment['history'] ?? ''); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../partials/footer.php'; ?>