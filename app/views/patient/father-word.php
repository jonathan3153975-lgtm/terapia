<?php $title = 'Pai, fala comigo'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap father-word-page portal-stack">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <section class="father-word-hero-image mb-4" style="background-image: url('<?php echo $appUrl; ?>/app/images/fala-comigo.png');">
    <div class="father-word-hero-overlay">
      <div class="father-word-hero-content">
        <h3 class="father-word-hero-quote">Não importa o que você está vivendo, Deus sempre tem uma palavra para o seu coração.</h3>
      </div>

      <button id="fatherWordDrawBtn" class="btn btn-dark father-word-draw-btn" type="button">
        <i class="fa-solid fa-hands-praying me-2"></i>Pai, fala comigo
      </button>
    </div>
  </section>

  <section id="fatherWordResultCard" class="father-word-result messenger-reveal card mb-4 d-none" aria-live="polite">
    <div class="card-body p-4 p-lg-5">
      <div class="small text-muted mb-1" id="fatherWordReference"></div>
      <p id="fatherWordText" class="father-word-text mb-0"></p>
    </div>
  </section>

  <section id="fatherWordReflectionSection" class="card mb-4 d-none father-word-reflection-card">
    <div class="card-body p-4">
      <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=father-word-save" id="fatherWordSaveForm">
        <input type="hidden" name="word_id" id="fatherWordIdInput" value="">
        <input type="hidden" name="word_reference" id="fatherWordReferenceInput" value="">
        <input type="hidden" name="word_text" id="fatherWordTextInput" value="">

        <div class="mb-3">
          <textarea class="form-control father-word-reflection-input" name="patient_note" id="fatherWordNoteInput" rows="6" placeholder="Escreva sua reflexão sobre esta palavra..." required></textarea>
        </div>

        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="share_with_therapist" id="fatherWordShareCheckbox" value="1">
          <label class="form-check-label" for="fatherWordShareCheckbox">
            Encaminhar esta reflexão para meu terapeuta
          </label>
        </div>

        <button class="btn btn-primary" type="submit" id="fatherWordSaveBtn"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar reflexão</button>
      </form>
    </div>
  </section>

  <section class="card">
    <div class="card-body p-4">
      <h5 class="card-title mb-3">Meu histórico de palavras</h5>
      <?php if (empty($entries)): ?>
        <div class="messenger-empty-state">
          <i class="fa-regular fa-bookmark"></i>
          <p class="mb-0">Você ainda não salvou nenhuma reflexão deste módulo.</p>
        </div>
      <?php else: ?>
        <div class="row g-3 reflection-stack">
          <?php foreach ($entries as $entry): ?>
            <div class="col-12">
              <article class="messenger-entry-card h-100">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                  <strong><?php echo htmlspecialchars((string) ($entry['word_reference'] ?? '')); ?></strong>
                  <span class="small text-muted"><?php echo !empty($entry['drawn_at']) ? date('d/m/Y H:i', strtotime((string) $entry['drawn_at'])) : '-'; ?></span>
                </div>
                <p class="mb-3"><?php echo nl2br(htmlspecialchars((string) ($entry['word_text'] ?? ''))); ?></p>
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
  var drawBtn = document.getElementById('fatherWordDrawBtn');
  var resultCard = document.getElementById('fatherWordResultCard');
  var referenceEl = document.getElementById('fatherWordReference');
  var textEl = document.getElementById('fatherWordText');
  var reflectionSection = document.getElementById('fatherWordReflectionSection');

  var wordIdInput = document.getElementById('fatherWordIdInput');
  var wordReferenceInput = document.getElementById('fatherWordReferenceInput');
  var wordTextInput = document.getElementById('fatherWordTextInput');
  var noteInput = document.getElementById('fatherWordNoteInput');
  var saveForm = document.getElementById('fatherWordSaveForm');
  var saveBtn = document.getElementById('fatherWordSaveBtn');
  var typingToken = 0;

  var revealTextWithTyping = function (el, text, onDone) {
    if (!el) {
      if (typeof onDone === 'function') {
        onDone();
      }
      return;
    }

    var fullText = String(text || '');
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion || fullText.length <= 8) {
      el.classList.remove('is-typing');
      el.textContent = fullText;
      if (typeof onDone === 'function') {
        onDone();
      }
      return;
    }

    typingToken += 1;
    var localToken = typingToken;
    var idx = 0;
    var chunk = fullText.length > 220 ? 3 : 2;
    var speed = fullText.length > 220 ? 10 : 14;

    el.textContent = '';
    el.classList.add('is-typing');

    var tick = function () {
      if (localToken !== typingToken) {
        return;
      }

      idx = Math.min(fullText.length, idx + chunk);
      el.textContent = fullText.slice(0, idx);

      if (idx >= fullText.length) {
        el.classList.remove('is-typing');
        if (typeof onDone === 'function') {
          onDone();
        }
        return;
      }

      window.setTimeout(tick, speed);
    };

    window.setTimeout(tick, 40);
  };

  var scrollResultToTop = function (onDone) {
    if (!resultCard) {
      if (typeof onDone === 'function') {
        onDone();
      }
      return;
    }

    var targetTop = Math.max(resultCard.getBoundingClientRect().top + window.scrollY - 88, 0);
    window.scrollTo({ top: targetTop, behavior: 'smooth' });

    if (typeof onDone === 'function') {
      window.setTimeout(onDone, 420);
    }
  };

  var focusReflection = function () {
    if (!noteInput) {
      return;
    }

    try {
      noteInput.focus({ preventScroll: true });
    } catch (error) {
      noteInput.focus();
    }
  };

  if (saveForm && saveBtn) {
    saveForm.addEventListener('submit', function (event) {
      if (!wordTextInput || !wordTextInput.value.trim()) {
        event.preventDefault();
        window.alert('Clique em "Pai, fala comigo" para receber uma palavra antes de salvar.');
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

  if (!drawBtn) {
    return;
  }

  drawBtn.addEventListener('click', function () {
    if (drawBtn.disabled) {
      return;
    }

    drawBtn.disabled = true;
    drawBtn.innerHTML = '<i class="fa-solid fa-hourglass-half me-1"></i>Recebendo...';

    fetch('<?php echo $appUrl; ?>/patient.php?action=father-word-draw', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (response) {
        return response.json().catch(function () { return {}; }).then(function (payload) {
          if (!response.ok || !payload.success) {
            throw new Error(payload.message || 'Não foi possível receber a palavra agora.');
          }
          return payload;
        });
      })
      .then(function (payload) {
        var word = payload.data && payload.data.word ? payload.data.word : null;
        if (!word) {
          throw new Error('Resposta inválida ao receber palavra.');
        }

        if (drawBtn) {
          drawBtn.classList.add('d-none');
        }

        if (referenceEl) {
          referenceEl.textContent = word.reference || '';
        }
        if (textEl) {
          textEl.textContent = '';
        }

        if (wordIdInput) {
          wordIdInput.value = word.id || '';
        }
        if (wordReferenceInput) {
          wordReferenceInput.value = word.reference || '';
        }
        if (wordTextInput) {
          wordTextInput.value = word.text || '';
        }

        if (resultCard) {
          resultCard.classList.remove('d-none');
          resultCard.classList.add('is-visible');
          resultCard.classList.add('is-revealed');
        }
        if (reflectionSection) {
          reflectionSection.classList.remove('d-none');
        }
        if (noteInput) {
          noteInput.value = '';
        }

        revealTextWithTyping(textEl, word.text || '', function () {
          scrollResultToTop(function () {
            focusReflection();
          });
        });
      })
      .catch(function (error) {
        window.alert(error.message || 'Erro ao receber palavra.');
        drawBtn.disabled = false;
        drawBtn.innerHTML = '<i class="fa-solid fa-hands-praying me-2"></i>Pai, fala comigo';
        drawBtn.classList.remove('d-none');
      });
  });
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
