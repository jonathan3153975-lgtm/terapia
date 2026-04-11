<?php $title = 'Mensageiro'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap messenger-page">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <section class="messenger-hero-image mb-4" style="background-image: url('<?php echo $appUrl; ?>/app/images/mensageiro.png');">
    <div class="messenger-hero-overlay">
      <button id="drawMessageBtn" class="btn btn-dark messenger-draw-btn" type="button" <?php echo ((int) ($totalMessages ?? 0) <= 0) ? 'disabled' : ''; ?>>
        <i class="fa-solid fa-box-open me-1"></i>Abrir mensagem
      </button>
    </div>
  </section>

  <section id="drawResultCard" class="messenger-reveal card mb-4 d-none" aria-live="polite">
    <div class="card-body p-4 p-lg-5">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <span id="drawCycleHint" class="small text-muted"></span>
      </div>
      <h4 class="messenger-reveal-title">A mensagem se abriu</h4>
      <p id="drawMessageText" class="messenger-draw-text mb-0"></p>
    </div>
  </section>

  <section id="messengerReflectionSection" class="card mb-4 d-none messenger-reflection-card">
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3"></div>

      <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=messenger-save" id="messengerSaveForm">
        <input type="hidden" name="message_id" id="messageIdInput" value="">
        <input type="hidden" name="message_category" id="messageCategoryInput" value="">
        <input type="hidden" name="message_text" id="messageTextInput" value="">

        <div class="mb-3">
          <textarea class="form-control messenger-reflection-input" name="patient_note" id="patientNoteInput" rows="6" placeholder="O que essa mensagem tocou em você hoje?" required></textarea>
        </div>

        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="share_with_therapist" id="shareWithTherapistCheckbox" value="1">
          <label class="form-check-label" for="shareWithTherapistCheckbox">
            Encaminhar esta reflexão para meu terapeuta
          </label>
        </div>

        <button class="btn btn-primary" type="submit" id="messengerSaveBtn"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar reflexão</button>
      </form>
    </div>
  </section>

  <section class="card">
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h5 class="card-title mb-0">Meu baú de reflexões</h5>
      </div>

      <?php if (empty($entries)): ?>
        <div class="messenger-empty-state">
          <i class="fa-regular fa-bookmark"></i>
          <p class="mb-0">Você ainda não salvou nenhuma reflexão. Abra sua primeira mensagem para começar.</p>
        </div>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($entries as $entry): ?>
            <div class="col-12 col-xl-6">
              <article class="messenger-entry-card h-100">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                  <span class="small text-muted"><?php echo !empty($entry['drawn_at']) ? date('d/m/Y H:i', strtotime((string) $entry['drawn_at'])) : '-'; ?></span>
                </div>
                <div class="small text-muted mb-1">Mensagem aberta</div>
                <p class="mb-3"><?php echo nl2br(htmlspecialchars((string) ($entry['message_text'] ?? ''))); ?></p>
                <div class="small text-muted mb-1">Reflexão registrada</div>
                <p class="mb-3"><?php echo nl2br(htmlspecialchars((string) ($entry['patient_note'] ?? ''))); ?></p>
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
  var drawBtn = document.getElementById('drawMessageBtn');
  var drawResultCard = document.getElementById('drawResultCard');
  var drawMessageText = document.getElementById('drawMessageText');
  var drawCycleHint = document.getElementById('drawCycleHint');
  var reflectionSection = document.getElementById('messengerReflectionSection');
  var patientNoteInput = document.getElementById('patientNoteInput');

  var messageIdInput = document.getElementById('messageIdInput');
  var messageCategoryInput = document.getElementById('messageCategoryInput');
  var messageTextInput = document.getElementById('messageTextInput');
  var saveForm = document.getElementById('messengerSaveForm');
  var saveBtn = document.getElementById('messengerSaveBtn');
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
    var chunk = fullText.length > 240 ? 3 : 2;
    var speed = fullText.length > 240 ? 10 : 14;

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

  var setDrawButtonIdle = function () {
    if (!drawBtn) {
      return;
    }

    drawBtn.disabled = false;
    drawBtn.innerHTML = '<i class="fa-solid fa-box-open me-1"></i>Abrir mensagem';
  };

  var setDrawButtonBusy = function () {
    if (!drawBtn) {
      return;
    }

    drawBtn.disabled = true;
    drawBtn.innerHTML = '<i class="fa-solid fa-hourglass-half me-1"></i>Sorteando...';
  };

  if (saveForm && saveBtn) {
    saveForm.addEventListener('submit', function (event) {
      if (!messageTextInput || !messageTextInput.value.trim()) {
        event.preventDefault();
        window.alert('Abra uma mensagem antes de salvar sua reflexão.');
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
    if (messageTextInput && messageTextInput.value.trim() && patientNoteInput && patientNoteInput.value.trim()) {
      var proceed = window.confirm('Você já tem uma reflexão preenchida. Abrir outra mensagem agora vai substituir a mensagem atual no formulário. Deseja continuar?');
      if (!proceed) {
        return;
      }
    }

    setDrawButtonBusy();

    if (drawResultCard) {
      drawResultCard.classList.remove('is-revealed');
      drawResultCard.classList.remove('d-none');
    }
    if (reflectionSection) {
      reflectionSection.classList.add('d-none');
    }
    if (drawMessageText) {
      drawMessageText.textContent = 'As mensagens estão se movendo dentro do baú...';
    }
    if (drawCycleHint) {
      drawCycleHint.textContent = 'Preparando uma abertura aleatória para você.';
    }

    var request = fetch('<?php echo $appUrl; ?>/patient.php?action=messenger-draw', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (response) {
      return response.json().catch(function () { return {}; }).then(function (payload) {
        if (!response.ok || !payload.success) {
          throw new Error(payload.message || 'Não foi possível abrir uma mensagem agora.');
        }
        return payload;
      });
    });

    var animationDelay = new Promise(function (resolve) {
      window.setTimeout(resolve, 1700);
    });

    Promise.all([request, animationDelay])
      .then(function (results) {
        var payload = results[0] || {};
        var message = payload.data && payload.data.message ? payload.data.message : null;
        var cycle = payload.data && payload.data.cycle ? payload.data.cycle : null;

        if (!message) {
          throw new Error('Resposta inválida ao abrir mensagem.');
        }

        if (drawResultCard) {
          drawResultCard.classList.add('is-revealed');
        }
        if (drawCycleHint && cycle) {
          drawCycleHint.textContent = cycle.restarted ? 'Novo ciclo iniciado para você.' : '';
        }

        if (messageIdInput) {
          messageIdInput.value = message.id || '';
        }
        if (messageCategoryInput) {
          messageCategoryInput.value = message.category || 'dores';
        }
        if (messageTextInput) {
          messageTextInput.value = message.text || '';
        }

        if (patientNoteInput) {
          patientNoteInput.value = '';
        }
        if (saveBtn) {
          saveBtn.disabled = false;
          saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i>Salvar reflexão';
        }
        if (reflectionSection) {
          reflectionSection.classList.remove('d-none');
        }

        revealTextWithTyping(drawMessageText, message.text || '', function () {
          if (drawResultCard) {
            var y = drawResultCard.getBoundingClientRect().top + window.scrollY - 18;
            window.scrollTo({ top: y, behavior: 'smooth' });
          }
        });
      })
      .catch(function (error) {
        if (drawMessageText) {
          drawMessageText.textContent = error.message || 'Erro ao abrir o baú.';
        }
        if (drawCycleHint) {
          drawCycleHint.textContent = 'Tente novamente em instantes.';
        }
        window.alert(error.message || 'Erro ao abrir o baú.');
      })
      .finally(function () {
        setDrawButtonIdle();
      });
  });
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
