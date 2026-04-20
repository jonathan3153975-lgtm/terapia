<?php $title = 'Novo Devocional'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Novo devocional</h3>
    <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals">Voltar</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-store">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Mês</label>
            <select class="form-select" name="month_number" required>
              <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>" <?php echo $m === (int) date('n') ? 'selected' : ''; ?>><?php echo htmlspecialchars($monthLabel($m)); ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Ano</label>
            <input class="form-control" type="number" name="year_number" min="2000" max="2100" value="<?php echo (int) date('Y'); ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Tema</label>
            <input class="form-control" type="text" name="theme" maxlength="255" required placeholder="Ex.: Renovação da fé">
          </div>
        </div>

        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary" type="submit">Salvar</button>
          <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
