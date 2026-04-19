<?php $title = 'Visualizar Livro'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
    <h3 class="mb-0"><?php echo htmlspecialchars((string) ($book['title'] ?? 'Livro')); ?></h3>
    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-outline-secondary" href="<?php echo $appUrl; ?>/patient.php?action=books"><i class="fa-solid fa-arrow-left me-1"></i>Voltar para livros</a>
      <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=book-toggle-favorite" class="m-0">
        <input type="hidden" name="book_id" value="<?php echo (int) ($book['id'] ?? 0); ?>">
        <input type="hidden" name="redirect_action" value="book-show">
        <button class="btn <?php echo !empty($isFavorite) ? 'btn-outline-warning' : 'btn-primary'; ?>" type="submit"><i class="fa-solid <?php echo !empty($isFavorite) ? 'fa-bookmark-slash' : 'fa-bookmark'; ?> me-1"></i><?php echo !empty($isFavorite) ? 'Remover dos salvos' : 'Salvar em Meus conteúdos'; ?></button>
      </form>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <div class="small text-muted">Arquivo</div>
        <div class="fw-semibold"><?php echo htmlspecialchars((string) ($book['pdf_original_name'] ?? 'PDF')); ?></div>
      </div>
      <div class="small text-muted">A visualização abaixo oculta os controles padrão do navegador, mas não existe bloqueio absoluto contra download.</div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <iframe title="Leitor do livro" src="<?php echo $appUrl; ?>/patient.php?action=book-view&id=<?php echo (int) ($book['id'] ?? 0); ?>#toolbar=0&navpanes=0&scrollbar=0&statusbar=0&messages=0" sandbox="allow-same-origin allow-scripts" style="width:100%;min-height:78vh;border:1px solid #dee2e6;border-radius:.75rem;background:#f8f9fa;"></iframe>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>