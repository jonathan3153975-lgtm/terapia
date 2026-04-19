<?php $title = 'Visualizar Livro'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">Livro: <?php echo htmlspecialchars((string) ($book['title'] ?? '')); ?></h4>
    <div class="d-flex gap-2">
      <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books">Voltar</a>
      <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books-edit&id=<?php echo (int) ($book['id'] ?? 0); ?>">Editar</a>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <div><strong>Status:</strong> <?php echo (int) ($book['is_published'] ?? 0) === 1 ? 'Liberado para pacientes' : 'Rascunho'; ?></div>
        </div>
        <div class="col-md-4">
          <div><strong>Favoritos:</strong> <?php echo (int) ($book['favorite_count'] ?? 0); ?></div>
        </div>
        <div class="col-md-4">
          <div><strong>Arquivo:</strong> <?php echo htmlspecialchars((string) ($book['pdf_original_name'] ?? '-')); ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
      <strong>Visualização do PDF</strong>
      <a class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books-file&id=<?php echo (int) ($book['id'] ?? 0); ?>">Abrir em nova aba</a>
    </div>
    <div class="card-body">
      <iframe title="Visualização do livro" src="<?php echo $appUrl; ?>/dashboard.php?action=therapist-books-file&id=<?php echo (int) ($book['id'] ?? 0); ?>#toolbar=0&navpanes=0&scrollbar=0" style="width:100%;min-height:72vh;border:1px solid #dee2e6;border-radius:.75rem;"></iframe>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../partials/footer.php'; ?>