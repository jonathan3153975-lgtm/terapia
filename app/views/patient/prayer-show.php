<?php
$title = 'Orações';
include __DIR__ . '/../partials/header.php';
include __DIR__ . '/../partials/nav.php';
?>
<div class="container page-wrap guided-page portal-stack">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
    <h3 class="mb-0"><?php echo htmlspecialchars((string) ($prayer['title'] ?? 'Oração')); ?></h3>
    <a class="btn btn-outline-secondary" href="<?php echo $appUrl; ?>/patient.php?action=prayers"><i class="fa-solid fa-arrow-left me-1"></i>Voltar para lista</a>
  </div>

  <section class="guided-hero-image" style="<?php echo !empty($prayer['reference_image_path']) ? ('background-image: url(' . htmlspecialchars($appUrl . '/' . ltrim((string) $prayer['reference_image_path'], '/')) . ');') : ''; ?>">
    <div class="guided-hero-overlay">
      <div class="guided-hero-copy guided-hero-copy--spotlight">
        <h4 class="mb-2">Reserve um momento de silêncio e conexão.</h4>
        <p class="mb-0">Escute a oração completa e escreva sua reflexão ao final.</p>
      </div>
    </div>
  </section>

  <section class="card guided-audio-card">
    <div class="card-body p-4">
      <h5 class="card-title mb-3">Áudio da oração</h5>
      <audio id="prayerAudio" controls controlsList="nodownload noremoteplayback" preload="metadata" class="w-100">
        <source src="<?php echo $appUrl; ?>/patient.php?action=prayer-audio&id=<?php echo (int) ($prayer['id'] ?? 0); ?>">
      </audio>
      <div class="small text-muted mt-2">Depois de ouvir, registre sua reflexão e, se desejar, compartilhe com seu terapeuta.</div>
    </div>
  </section>

  <section id="prayerReflectionSection" class="card guided-reflection-card">
    <div class="card-body p-4">
      <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=prayer-save" id="prayerSaveForm">
        <input type="hidden" name="prayer_id" value="<?php echo (int) ($prayer['id'] ?? 0); ?>">

        <div class="mb-3">
          <textarea class="form-control guided-reflection-input" name="patient_note" id="prayerPatientNoteInput" rows="6" placeholder="Escreva sua reflexão sobre este momento..." required></textarea>
        </div>

        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="share_with_therapist" id="prayerShareCheckbox" value="1">
          <label class="form-check-label" for="prayerShareCheckbox">
            Encaminhar esta reflexão para meu terapeuta
          </label>
        </div>

        <button class="btn btn-primary" type="submit" id="prayerSaveBtn"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar reflexão</button>
      </form>
    </div>
  </section>

  <section class="card">
    <div class="card-body p-4">
      <h5 class="card-title mb-3">Histórico desta oração</h5>
      <?php if (empty($entries)): ?>
        <div class="messenger-empty-state">
          <i class="fa-regular fa-bookmark"></i>
          <p class="mb-0">Você ainda não salvou reflexões desta oração.</p>
        </div>
      <?php else: ?>
        <div class="row g-3 reflection-stack">
          <?php foreach ($entries as $entry): ?>
            <div class="col-12">
              <article class="messenger-entry-card h-100">
                <div class="d-flex justify-content-end align-items-start gap-2 mb-2">
                  <span class="small text-muted"><?php echo !empty($entry['created_at']) ? date('d/m/Y H:i', strtotime((string) $entry['created_at'])) : '-'; ?></span>
                </div>
                <div class="small text-muted mb-1">Minha reflexão</div>
                <p class="mb-2"><?php echo nl2br(htmlspecialchars((string) ($entry['patient_note'] ?? ''))); ?></p>
                <?php if ((int) ($entry['share_with_therapist'] ?? 0) === 1): ?>
                  <span class="badge text-bg-success">Compartilhada com terapeuta</span>
                <?php endif; ?>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</div>

<script>
window.addEventListener('load', function () {
  var saveForm = document.getElementById('prayerSaveForm');
  var saveBtn = document.getElementById('prayerSaveBtn');

  if (saveForm && saveBtn) {
    saveForm.addEventListener('submit', function (event) {
      if (saveBtn.disabled) {
        event.preventDefault();
        return;
      }

      saveBtn.disabled = true;
      saveBtn.innerHTML = '<i class="fa-solid fa-hourglass-half me-1"></i>Salvando...';
    });
  }
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
