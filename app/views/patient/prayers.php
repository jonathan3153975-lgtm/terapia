<?php $title = 'Orações'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap guided-page">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <section class="card guided-list-card">
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h4 class="mb-0">Orações disponíveis</h4>
        <span class="small text-muted"><?php echo count($prayers ?? []); ?> disponível(eis)</span>
      </div>

      <?php if (empty($prayers)): ?>
        <div class="messenger-empty-state">
          <i class="fa-solid fa-hands-praying"></i>
          <p class="mb-0">Ainda não há orações disponíveis para você.</p>
        </div>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($prayers as $prayer): ?>
            <div class="col-12 col-lg-6">
              <article class="guided-list-item h-100">
                <div
                  class="guided-list-cover"
                  style="<?php echo !empty($prayer['reference_image_path']) ? ('background-image: url(' . htmlspecialchars($appUrl . '/' . ltrim((string) $prayer['reference_image_path'], '/')) . ');') : ''; ?>"
                >
                  <?php if (empty($prayer['reference_image_path'])): ?>
                    <span><i class="fa-regular fa-image me-1"></i>Sem imagem de referência</span>
                  <?php endif; ?>
                </div>

                <div class="guided-list-content">
                  <h5 class="mb-2"><?php echo htmlspecialchars((string) ($prayer['title'] ?? 'Oração')); ?></h5>
                  <a class="btn btn-dark" href="<?php echo $appUrl; ?>/patient.php?action=prayer-show&id=<?php echo (int) ($prayer['id'] ?? 0); ?>">
                    <i class="fa-solid fa-play me-1"></i>Ouvir oração
                  </a>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
