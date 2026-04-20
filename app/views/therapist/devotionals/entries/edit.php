<?php $title = 'Editar Registro Devocional'; include __DIR__ . '/../../../partials/header.php'; include __DIR__ . '/../../../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../../../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Editar registro devocional</h3>
    <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-show&id=<?php echo (int) ($devotional['id'] ?? 0); ?>">Voltar</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-entries-update">
        <input type="hidden" name="devotional_id" value="<?php echo (int) ($devotional['id'] ?? 0); ?>">
        <input type="hidden" name="id" value="<?php echo (int) ($entry['id'] ?? 0); ?>">

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Data</label>
            <input class="form-control" type="date" name="entry_date" value="<?php echo htmlspecialchars((string) ($entry['entry_date'] ?? '')); ?>" required>
          </div>
          <div class="col-md-8">
            <label class="form-label">Título</label>
            <input class="form-control" type="text" name="title" maxlength="255" value="<?php echo htmlspecialchars((string) ($entry['title'] ?? '')); ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Palavra de Deus</label>
            <input class="form-control" type="text" name="word_of_god" maxlength="255" value="<?php echo htmlspecialchars((string) ($entry['word_of_god'] ?? '')); ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Texto</label>
            <textarea class="form-control" name="text_content" rows="8" required><?php echo htmlspecialchars((string) ($entry['text_content'] ?? '')); ?></textarea>
          </div>
        </div>

        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary" type="submit">Salvar alterações</button>
          <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals-show&id=<?php echo (int) ($devotional['id'] ?? 0); ?>">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../partials/footer.php'; ?>
