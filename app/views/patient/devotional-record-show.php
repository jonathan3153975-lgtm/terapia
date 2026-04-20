<?php $title = 'Registro Devocional'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <div class="mb-3">
    <a class="btn btn-sm btn-outline-secondary" href="<?php echo $appUrl; ?>/patient.php?action=devotionals"><i class="fa-solid fa-arrow-left me-1"></i>Voltar</a>
  </div>

  <section class="card">
    <div class="card-body p-4">
      <h4 class="mb-1"><?php echo htmlspecialchars((string) ($record['title'] ?? 'Registro devocional')); ?></h4>
      <p class="text-muted mb-3">
        <?php echo !empty($record['entry_date']) ? date('d/m/Y', strtotime((string) $record['entry_date'])) : '-'; ?>
        <?php if (!empty($record['theme'])): ?>
          | Tema: <?php echo htmlspecialchars((string) $record['theme']); ?>
        <?php endif; ?>
      </p>
      <div class="border rounded overflow-hidden">
        <iframe
          title="Registro devocional completo"
          style="width:100%;min-height:740px;border:0;background:#fff;"
          srcdoc="<?php echo htmlspecialchars((string) ($record['compiled_html'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
        ></iframe>
      </div>
    </div>
  </section>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
