<?php $title = 'Devocional'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0">Devocional</h3>
    <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-create"><i class="fa-solid fa-plus"></i> Novo devocional</a>
  </div>

  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="<?php echo $appUrl; ?>/dashboard.php" class="row g-2 align-items-end">
        <input type="hidden" name="action" value="therapist-devotionals">
        <div class="col-12 col-md-9">
          <label class="form-label mb-1">Buscar por tema, mês ou ano</label>
          <input class="form-control" type="search" name="q" value="<?php echo htmlspecialchars((string) ($filters['q'] ?? '')); ?>" placeholder="Ex.: Abril 2026, Gratidão...">
        </div>
        <div class="col-12 col-md-3 d-grid">
          <button class="btn btn-dark" type="submit"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card d-none d-lg-block">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Mês/Ano</th>
            <th>Tema</th>
            <th>Registros</th>
            <th>Atualizado em</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($devotionals)): ?>
            <tr>
              <td colspan="5" class="text-center text-muted py-4">Nenhum devocional cadastrado.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($devotionals as $devotional): ?>
              <tr>
                <td>
                  <?php
                    $monthNum = (int) ($devotional['month_number'] ?? 0);
                    $yearNum = (int) ($devotional['year_number'] ?? 0);
                    echo htmlspecialchars($monthLabel($monthNum) . '/' . $yearNum);
                  ?>
                </td>
                <td><?php echo htmlspecialchars((string) ($devotional['theme'] ?? '-')); ?></td>
                <td><?php echo (int) ($devotional['entries_count'] ?? 0); ?></td>
                <td><?php echo !empty($devotional['updated_at']) ? date('d/m/Y H:i', strtotime((string) $devotional['updated_at'])) : '-'; ?></td>
                <td>
                  <div class="d-flex align-items-center gap-1 flex-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-show&id=<?php echo (int) $devotional['id']; ?>" title="Visualizar"><i class="fa-solid fa-eye"></i></a>
                    <a class="btn btn-sm btn-outline-secondary" style="width:32px;padding:0;line-height:1.8;" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-edit&id=<?php echo (int) $devotional['id']; ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
                    <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-delete" class="d-flex m-0 js-delete-devotional-form" data-devotional-label="<?php echo htmlspecialchars((string) (($monthLabel((int) ($devotional['month_number'] ?? 0))) . '/' . ((int) ($devotional['year_number'] ?? 0)))); ?>">
                      <input type="hidden" name="id" value="<?php echo (int) $devotional['id']; ?>">
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
    <?php if (empty($devotionals)): ?>
      <div class="card"><div class="card-body text-center text-muted">Nenhum devocional cadastrado.</div></div>
    <?php else: ?>
      <?php foreach ($devotionals as $devotional): ?>
        <div class="card">
          <div class="card-body">
            <div class="fw-semibold mb-1"><?php echo htmlspecialchars($monthLabel((int) ($devotional['month_number'] ?? 0)) . '/' . (int) ($devotional['year_number'] ?? 0)); ?></div>
            <div class="mb-1"><?php echo htmlspecialchars((string) ($devotional['theme'] ?? '-')); ?></div>
            <div class="small text-muted mb-2">Registros: <?php echo (int) ($devotional['entries_count'] ?? 0); ?></div>
            <div class="d-flex align-items-center gap-1 flex-nowrap">
              <a class="btn btn-sm btn-outline-secondary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-show&id=<?php echo (int) $devotional['id']; ?>"><i class="fa-solid fa-eye"></i></a>
              <a class="btn btn-sm btn-outline-secondary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-edit&id=<?php echo (int) $devotional['id']; ?>"><i class="fa-solid fa-pen"></i></a>
              <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-delete" class="d-flex m-0 js-delete-devotional-form" data-devotional-label="<?php echo htmlspecialchars((string) (($monthLabel((int) ($devotional['month_number'] ?? 0))) . '/' . ((int) ($devotional['year_number'] ?? 0)))); ?>">
                <input type="hidden" name="id" value="<?php echo (int) $devotional['id']; ?>">
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
  document.querySelectorAll('.js-delete-devotional-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      var label = form.getAttribute('data-devotional-label') || 'este devocional';
      if (!window.confirm('Deseja excluir o devocional ' + label + '?')) {
        event.preventDefault();
      }
    });
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
