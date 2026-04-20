<?php $title = 'Devocional'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <h3 class="mb-0">Devocional: <?php echo htmlspecialchars($monthLabel((int) ($devotional['month_number'] ?? 0)) . '/' . (int) ($devotional['year_number'] ?? 0)); ?></h3>
      <div class="text-muted small">Tema: <?php echo htmlspecialchars((string) ($devotional['theme'] ?? '-')); ?></div>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-edit&id=<?php echo (int) ($devotional['id'] ?? 0); ?>"><i class="fa-solid fa-pen me-1"></i>Editar</a>
      <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals">Voltar</a>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title mb-3">Novo registro devocional diário</h5>
      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-entries-store">
        <input type="hidden" name="devotional_id" value="<?php echo (int) ($devotional['id'] ?? 0); ?>">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Data</label>
            <input class="form-control" type="date" name="entry_date" required>
          </div>
          <div class="col-md-8">
            <label class="form-label">Título</label>
            <input class="form-control" type="text" name="title" maxlength="255" required>
          </div>
          <div class="col-12">
            <label class="form-label">Palavra de Deus</label>
            <input class="form-control" type="text" name="word_of_god" maxlength="255" required placeholder="Ex.: Salmo 23:1">
          </div>
          <div class="col-12">
            <label class="form-label">Texto</label>
            <textarea class="form-control" name="text_content" rows="6" required></textarea>
          </div>
        </div>
        <div class="mt-3">
          <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar registro</button>
        </div>
      </form>
    </div>
  </div>

  <div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0">Registros do período</h5>
    <span class="badge text-bg-light border"><?php echo count($entries ?? []); ?> registro(s)</span>
  </div>

  <div class="card d-none d-lg-block">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Data</th>
            <th>Título</th>
            <th>Palavra de Deus</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($entries)): ?>
            <tr>
              <td colspan="4" class="text-center text-muted py-4">Nenhum registro devocional cadastrado para este período.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($entries as $entry): ?>
              <tr>
                <td><?php echo !empty($entry['entry_date']) ? date('d/m/Y', strtotime((string) $entry['entry_date'])) : '-'; ?></td>
                <td><?php echo htmlspecialchars((string) ($entry['title'] ?? '-')); ?></td>
                <td><?php echo htmlspecialchars((string) ($entry['word_of_god'] ?? '-')); ?></td>
                <td>
                  <div class="d-flex align-items-center gap-1 flex-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-entries-show&devotional_id=<?php echo (int) ($devotional['id'] ?? 0); ?>&id=<?php echo (int) $entry['id']; ?>" title="Visualizar"><i class="fa-solid fa-eye"></i></a>
                    <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-entries-edit&devotional_id=<?php echo (int) ($devotional['id'] ?? 0); ?>&id=<?php echo (int) $entry['id']; ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
                    <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-entries-delete" class="d-flex m-0 js-delete-devotional-entry-form" data-entry-label="<?php echo htmlspecialchars((string) ($entry['title'] ?? 'este registro')); ?>">
                      <input type="hidden" name="devotional_id" value="<?php echo (int) ($devotional['id'] ?? 0); ?>">
                      <input type="hidden" name="id" value="<?php echo (int) $entry['id']; ?>">
                      <button class="btn btn-sm btn-outline-danger" style="width:32px;padding:0;line-height:1.8;" type="submit" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="d-lg-none d-grid gap-2">
    <?php if (empty($entries)): ?>
      <div class="card"><div class="card-body text-center text-muted">Nenhum registro devocional cadastrado para este período.</div></div>
    <?php else: ?>
      <?php foreach ($entries as $entry): ?>
        <div class="card">
          <div class="card-body">
            <div class="fw-semibold"><?php echo htmlspecialchars((string) ($entry['title'] ?? '-')); ?></div>
            <div class="small text-muted mb-1"><?php echo !empty($entry['entry_date']) ? date('d/m/Y', strtotime((string) $entry['entry_date'])) : '-'; ?></div>
            <div class="small mb-2"><strong>Palavra de Deus:</strong> <?php echo htmlspecialchars((string) ($entry['word_of_god'] ?? '-')); ?></div>
            <div class="d-flex align-items-center gap-1 flex-nowrap">
              <a class="btn btn-sm btn-outline-secondary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-entries-show&devotional_id=<?php echo (int) ($devotional['id'] ?? 0); ?>&id=<?php echo (int) $entry['id']; ?>"><i class="fa-solid fa-eye"></i></a>
              <a class="btn btn-sm btn-outline-secondary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-entries-edit&devotional_id=<?php echo (int) ($devotional['id'] ?? 0); ?>&id=<?php echo (int) $entry['id']; ?>"><i class="fa-solid fa-pen"></i></a>
              <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-entries-delete" class="d-flex m-0 js-delete-devotional-entry-form" data-entry-label="<?php echo htmlspecialchars((string) ($entry['title'] ?? 'este registro')); ?>">
                <input type="hidden" name="devotional_id" value="<?php echo (int) ($devotional['id'] ?? 0); ?>">
                <input type="hidden" name="id" value="<?php echo (int) $entry['id']; ?>">
                <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  document.querySelectorAll('.js-delete-devotional-entry-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      var label = form.getAttribute('data-entry-label') || 'este registro';
      if (!window.confirm('Deseja excluir ' + label + '?')) {
        event.preventDefault();
      }
    });
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
