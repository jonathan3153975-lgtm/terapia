<?php $title = 'Visualizar gratidão'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <div class="mb-3">
    <a class="btn btn-sm btn-outline-secondary" href="<?php echo $appUrl; ?>/patient.php?action=gratitude"><i class="fa-solid fa-arrow-left me-1"></i>Voltar</a>
  </div>

  <section class="card">
    <div class="card-body p-4">
      <h4 class="mb-1">Diário da gratidão</h4>
      <p class="text-muted mb-3">Ciclo <?php echo (int) ($entry['cycle_number'] ?? 0); ?> - Dia <?php echo (int) ($entry['day_number'] ?? 0); ?></p>
      <div class="border rounded p-3 bg-light-subtle">
        <?php echo (string) ($entry['content_html'] ?? ''); ?>
      </div>
    </div>
  </section>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
