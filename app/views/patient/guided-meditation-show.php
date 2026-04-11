<?php $title = 'Meditação guiada'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap guided-page">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
    <h3 class="mb-0"><?php echo htmlspecialchars((string) ($meditation['title'] ?? 'Meditação guiada')); ?></h3>
    <a class="btn btn-outline-secondary" href="<?php echo $appUrl; ?>/patient.php?action=guided-meditations"><i class="fa-solid fa-arrow-left me-1"></i>Voltar para lista</a>
  </div>

  <section class="guided-hero-image" style="<?php echo !empty($meditation['reference_image_path']) ? ('background-image: url(' . htmlspecialchars($appUrl . '/' . ltrim((string) $meditation['reference_image_path'], '/')) . ');') : ''; ?>">
    <div class="guided-hero-overlay">
      <div class="guided-hero-copy">
        <h4 class="mb-2">Feche os olhos por alguns segundos e se permita respirar.</h4>
        <p class="mb-0">Ouça a meditação completa para liberar a sua carta de cura.</p>
      </div>
    </div>
  </section>

  <section class="card guided-audio-card">
    <div class="card-body p-4">
      <h5 class="card-title mb-3">Audio da meditação</h5>
      <audio id="guidedMeditationAudio" controls preload="metadata" class="w-100">
        <source src="<?php echo $appUrl . '/' . ltrim((string) ($meditation['audio_path'] ?? ''), '/'); ?>">
      </audio>
      <div class="small text-muted mt-2">Ao finalizar o áudio, o baralho será liberado automaticamente.</div>
    </div>
  </section>

  <section id="guidedDeckSection" class="card guided-deck-card d-none" style="<?php echo !empty($therapist['company_logo_path']) ? ('--therapist-logo-bg: url(' . htmlspecialchars($appUrl . '/' . ltrim((string) $therapist['company_logo_path'], '/')) . ');') : ''; ?>">
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h5 class="mb-0">Escolha uma carta de cura</h5>
        <span id="guidedDeckHint" class="small text-muted">Clique em uma carta para revelar sua mensagem.</span>
      </div>

      <div id="guidedDeckGrid" class="guided-deck-grid" aria-label="Baralho de cartas de cura">
        <?php for ($i = 1; $i <= 6; $i++): ?>
          <button type="button" class="guided-card-slot" aria-label="Carta <?php echo $i; ?>">
            <span class="guided-card-inner">
              <span class="guided-card-face guided-card-front">
                <span class="guided-card-logo-mark">Logo do terapeuta</span>
              </span>
              <span class="guided-card-face guided-card-back">
                <small class="guided-card-back-category"></small>
                <strong class="guided-card-back-text"></strong>
              </span>
            </span>
          </button>
        <?php endfor; ?>
      </div>
    </div>
  </section>

  <section id="guidedReflectionSection" class="card d-none guided-reflection-card">
    <div class="card-body p-4">
      <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=guided-meditation-save" id="guidedSaveForm">
        <input type="hidden" name="meditation_id" value="<?php echo (int) ($meditation['id'] ?? 0); ?>">
        <input type="hidden" name="letter_id" id="guidedLetterIdInput" value="">
        <input type="hidden" name="letter_category" id="guidedLetterCategoryInput" value="">
        <input type="hidden" name="letter_text" id="guidedLetterTextInput" value="">

        <div class="mb-3">
          <textarea class="form-control guided-reflection-input" name="patient_note" id="guidedPatientNoteInput" rows="6" placeholder="Escreva sua reflexão sobre este momento..." required></textarea>
        </div>

        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="share_with_therapist" id="guidedShareCheckbox" value="1">
          <label class="form-check-label" for="guidedShareCheckbox">
            Encaminhar esta reflexão para meu terapeuta
          </label>
        </div>

        <button class="btn btn-primary" type="submit" id="guidedSaveBtn"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar reflexão</button>
      </form>
    </div>
  </section>

  <section class="card">
    <div class="card-body p-4">
      <h5 class="card-title mb-3">Histórico desta meditação</h5>
      <?php if (empty($entries)): ?>
        <div class="messenger-empty-state">
          <i class="fa-regular fa-bookmark"></i>
          <p class="mb-0">Você ainda não salvou reflexões desta meditação.</p>
        </div>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($entries as $entry): ?>
            <div class="col-12 col-xl-6">
              <article class="messenger-entry-card h-100">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                  <span class="badge text-bg-secondary"><?php echo htmlspecialchars((string) ($entry['letter_category'] ?? 'cura')); ?></span>
                  <span class="small text-muted"><?php echo !empty($entry['created_at']) ? date('d/m/Y H:i', strtotime((string) $entry['created_at'])) : '-'; ?></span>
                </div>
                <p class="mb-3"><?php echo nl2br(htmlspecialchars((string) ($entry['letter_text'] ?? ''))); ?></p>
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
  var audio = document.getElementById('guidedMeditationAudio');
  var deckSection = document.getElementById('guidedDeckSection');
  var deckGrid = document.getElementById('guidedDeckGrid');
  var reflectionSection = document.getElementById('guidedReflectionSection');
  var deckHint = document.getElementById('guidedDeckHint');

  var letterIdInput = document.getElementById('guidedLetterIdInput');
  var letterCategoryInput = document.getElementById('guidedLetterCategoryInput');
  var letterTextInput = document.getElementById('guidedLetterTextInput');
  var noteInput = document.getElementById('guidedPatientNoteInput');
  var saveForm = document.getElementById('guidedSaveForm');
  var saveBtn = document.getElementById('guidedSaveBtn');

  var meditationId = <?php echo (int) ($meditation['id'] ?? 0); ?>;
  var drawInFlight = false;
  var letterRevealed = false;

  var formatCategory = function (raw) {
    var value = String(raw || '').toLowerCase();
    if (value === 'reflexivas') {
      return 'Reflexivas';
    }
    if (value === 'cura') {
      return 'Cura';
    }
    if (value === 'motivacionais') {
      return 'Motivacionais';
    }
    if (value === 'conflitos') {
      return 'Conflitos';
    }
    return 'Dores';
  };

  var unlockDeck = function () {
    if (deckSection) {
      deckSection.classList.remove('d-none');
      deckSection.classList.add('is-open');
    }
  };

  if (audio) {
    audio.addEventListener('ended', unlockDeck);
  }

  if (saveForm && saveBtn) {
    saveForm.addEventListener('submit', function (event) {
      if (!letterTextInput || !letterTextInput.value.trim()) {
        event.preventDefault();
        window.alert('Revele uma carta de cura antes de salvar sua reflexão.');
        return;
      }

      if (saveBtn.disabled) {
        event.preventDefault();
        return;
      }

      saveBtn.disabled = true;
      saveBtn.innerHTML = '<i class="fa-solid fa-hourglass-half me-1"></i>Salvando...';
    });
  }

  if (!deckGrid) {
    return;
  }

  var slots = Array.prototype.slice.call(deckGrid.querySelectorAll('.guided-card-slot'));
  slots.forEach(function (slot, index) {
    slot.style.setProperty('--slot-delay', (index * 70) + 'ms');

    slot.addEventListener('click', function () {
      if (drawInFlight || letterRevealed) {
        return;
      }

      drawInFlight = true;
      slot.classList.add('is-picked');
      slots.forEach(function (el) {
        if (el !== slot) {
          el.classList.add('is-faded');
        }
      });

      fetch('<?php echo $appUrl; ?>/patient.php?action=guided-meditation-draw-letter&meditation_id=' + encodeURIComponent(String(meditationId)), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (response) {
          return response.json().catch(function () { return {}; }).then(function (payload) {
            if (!response.ok || !payload.success) {
              throw new Error(payload.message || 'Não foi possível revelar carta agora.');
            }
            return payload;
          });
        })
        .then(function (payload) {
          var letter = payload.data && payload.data.letter ? payload.data.letter : null;
          if (!letter) {
            throw new Error('Resposta inválida ao revelar carta.');
          }

          letterRevealed = true;
          drawInFlight = false;
          if (slot) {
            slot.classList.add('is-spotlight');
          }
          if (deckHint) {
            deckHint.textContent = 'Carta revelada. Respire fundo e escreva sua reflexão.';
          }

          var slotCategoryEl = slot ? slot.querySelector('.guided-card-back-category') : null;
          var slotTextEl = slot ? slot.querySelector('.guided-card-back-text') : null;
          if (slotCategoryEl) {
            slotCategoryEl.textContent = formatCategory(letter.category);
          }
          if (slotTextEl) {
            slotTextEl.textContent = letter.text || '';
          }
          if (letterIdInput) {
            letterIdInput.value = letter.id || '';
          }
          if (letterCategoryInput) {
            letterCategoryInput.value = letter.category || '';
          }
          if (letterTextInput) {
            letterTextInput.value = letter.text || '';
          }

          window.setTimeout(function () {
            if (slot) {
              slot.classList.add('is-flipped');
            }

            if (reflectionSection) {
              reflectionSection.classList.remove('d-none');
            }
            if (noteInput) {
              noteInput.value = '';
              noteInput.focus();
            }

            window.setTimeout(function () {
              if (reflectionSection) {
                var y = reflectionSection.getBoundingClientRect().top + window.scrollY - 16;
                window.scrollTo({ top: y, behavior: 'smooth' });
              }
            }, 160);
          }, 420);
        })
        .catch(function (error) {
          drawInFlight = false;
          slot.classList.remove('is-picked');
          slot.classList.remove('is-spotlight');
          slot.classList.remove('is-flipped');
          slots.forEach(function (el) {
            el.classList.remove('is-faded');
          });
          window.alert(error.message || 'Erro ao revelar carta.');
        });
    });
  });
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
