<?php $title = 'Mensageiro'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="messenger-hero card mb-3">
    <div class="card-body p-4">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
          <h3 class="mb-1">Mensageiro</h3>
          <p class="mb-0 text-muted">Abra o baú, sorteie uma mensagem e registre sua reflexão do momento.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
          <select id="drawCategory" class="form-select messenger-hero-select">
            <option value="">Todas as categorias</option>
            <option value="dores">Dores</option>
            <option value="reflexivas">Reflexivas</option>
            <option value="cura">Cura</option>
            <option value="motivacionais">Motivacionais</option>
            <option value="conflitos">Conflitos</option>
          </select>
          <button id="drawMessageBtn" class="btn btn-dark" type="button">
            <i class="fa-solid fa-box-open me-1"></i>Abrir baú
          </button>
        </div>
      </div>
    </div>
  </div>

  <div id="drawResultCard" class="card mb-3 d-none">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <h5 class="mb-0">Mensagem sorteada</h5>
        <span id="drawCategoryBadge" class="badge rounded-pill text-bg-secondary">-</span>
      </div>
      <p id="drawMessageText" class="messenger-draw-text mb-0"></p>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title">Salvar no meu baú pessoal</h5>
      <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=messenger-save" id="messengerSaveForm">
        <input type="hidden" name="message_id" id="messageIdInput" value="">
        <input type="hidden" name="message_category" id="messageCategoryInput" value="">
        <input type="hidden" name="message_text" id="messageTextInput" value="">

        <div class="mb-3">
          <label class="form-label">Sua reflexão</label>
          <textarea class="form-control" name="patient_note" id="patientNoteInput" rows="5" placeholder="Escreva aqui o que sentiu ao receber essa mensagem..." required></textarea>
        </div>

        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="share_with_therapist" id="shareWithTherapistCheckbox" value="1">
          <label class="form-check-label" for="shareWithTherapistCheckbox">
            Encaminhar esta reflexão para meu terapeuta
          </label>
        </div>

        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar reflexão</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h5 class="card-title mb-3">Meu histórico</h5>
      <?php if (empty($entries)): ?>
        <p class="text-muted mb-0">Você ainda não salvou nenhuma mensagem no seu baú.</p>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($entries as $entry): ?>
            <div class="col-12 col-lg-6">
              <article class="messenger-entry-card h-100">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                  <span class="badge rounded-pill text-bg-light border"><?php echo htmlspecialchars(ucfirst((string) ($entry['message_category'] ?? ''))); ?></span>
                  <span class="small text-muted"><?php echo !empty($entry['drawn_at']) ? date('d/m/Y H:i', strtotime((string) $entry['drawn_at'])) : '-'; ?></span>
                </div>
                <div class="small text-muted mb-1">Mensagem</div>
                <p class="mb-2"><?php echo nl2br(htmlspecialchars((string) ($entry['message_text'] ?? ''))); ?></p>
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
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var drawBtn = document.getElementById('drawMessageBtn');
  var drawCategory = document.getElementById('drawCategory');
  var drawResultCard = document.getElementById('drawResultCard');
  var drawCategoryBadge = document.getElementById('drawCategoryBadge');
  var drawMessageText = document.getElementById('drawMessageText');

  var messageIdInput = document.getElementById('messageIdInput');
  var messageCategoryInput = document.getElementById('messageCategoryInput');
  var messageTextInput = document.getElementById('messageTextInput');
  var saveForm = document.getElementById('messengerSaveForm');

  var categoryLabel = function (cat) {
    if (cat === 'reflexivas') {
      return 'Reflexivas';
    }
    if (cat === 'cura') {
      return 'Cura';
    }
    if (cat === 'motivacionais') {
      return 'Motivacionais';
    }
    if (cat === 'conflitos') {
      return 'Conflitos';
    }
    return 'Dores';
  };

  if (saveForm) {
    saveForm.addEventListener('submit', function (event) {
      if (!messageTextInput || !messageTextInput.value.trim()) {
        event.preventDefault();
        window.alert('Sorteie uma mensagem no baú antes de salvar sua reflexão.');
      }
    });
  }

  if (!drawBtn) {
    return;
  }

  drawBtn.addEventListener('click', function () {
    drawBtn.disabled = true;
    drawBtn.innerHTML = '<i class="fa-solid fa-hourglass-half me-1"></i>Sorteando...';

    var selectedCategory = drawCategory ? (drawCategory.value || '') : '';
    var url = '<?php echo $appUrl; ?>/patient.php?action=messenger-draw';
    if (selectedCategory) {
      url += '&category=' + encodeURIComponent(selectedCategory);
    }

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (response) {
        return response.json().catch(function () { return {}; }).then(function (payload) {
          if (!response.ok || !payload.success) {
            var error = payload.message || 'Não foi possível sortear uma mensagem agora.';
            throw new Error(error);
          }
          return payload;
        });
      })
      .then(function (payload) {
        var message = payload.data && payload.data.message ? payload.data.message : null;
        if (!message) {
          throw new Error('Resposta inválida ao sortear mensagem.');
        }

        if (drawResultCard) {
          drawResultCard.classList.remove('d-none');
        }
        if (drawCategoryBadge) {
          drawCategoryBadge.textContent = categoryLabel(message.category || 'dores');
        }
        if (drawMessageText) {
          drawMessageText.textContent = message.text || '';
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
      })
      .catch(function (error) {
        window.alert(error.message || 'Erro ao abrir o baú.');
      })
      .finally(function () {
        drawBtn.disabled = false;
        drawBtn.innerHTML = '<i class="fa-solid fa-box-open me-1"></i>Abrir baú';
      });
  });
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
