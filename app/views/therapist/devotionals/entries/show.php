<?php $title = 'Registro Devocional'; include __DIR__ . '/../../../partials/header.php'; include __DIR__ . '/../../../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../../../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Visualizar registro devocional</h3>
    <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-show&id=<?php echo (int) ($devotional['id'] ?? 0); ?>">Voltar</a>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row g-3 mb-2">
        <div class="col-md-4"><strong>Mês/Ano:</strong> <?php echo htmlspecialchars($monthLabel((int) ($devotional['month_number'] ?? 0)) . '/' . (int) ($devotional['year_number'] ?? 0)); ?></div>
        <div class="col-md-8"><strong>Tema:</strong> <?php echo htmlspecialchars((string) ($devotional['theme'] ?? '-')); ?></div>
        <div class="col-md-4"><strong>Data:</strong> <?php echo !empty($entry['entry_date']) ? date('d/m/Y', strtotime((string) $entry['entry_date'])) : '-'; ?></div>
        <div class="col-md-8"><strong>Título:</strong> <?php echo htmlspecialchars((string) ($entry['title'] ?? '-')); ?></div>
        <div class="col-12"><strong>Palavra de Deus:</strong> <?php echo htmlspecialchars((string) ($entry['word_of_god'] ?? '-')); ?></div>
      </div>
      <hr>
      <div>
        <strong>Texto</strong>
        <div class="border rounded p-3 mt-2 bg-light-subtle"><?php echo (string) ($entry['text_content'] ?? ''); ?></div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../partials/footer.php'; ?>
