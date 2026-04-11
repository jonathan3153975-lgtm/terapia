<?php $title = 'Cartas de cura'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0">Cartas de cura</h3>
    <span class="badge text-bg-light border"><?php echo count($letters ?? []); ?> carta(s)</span>
  </div>

  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="row g-3 mb-3">
    <div class="col-12 col-xl-5">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title mb-2">Nova carta</h5>
          <p class="text-muted small mb-3">Cadastro manual de carta de cura.</p>
          <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-healing-letters-store" id="healingLetterCreateForm">
            <div class="mb-2">
              <label class="form-label">Categoria</label>
              <select class="form-select" name="category" required>
                <option value="dores">Dores</option>
                <option value="reflexivas">Reflexivas</option>
                <option value="cura">Cura</option>
                <option value="motivacionais">Motivacionais</option>
                <option value="conflitos">Conflitos</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Carta</label>
              <textarea class="form-control" name="message_text" rows="5" required placeholder="Escreva uma mensagem de cura..."></textarea>
            </div>
            <button class="btn btn-primary" type="submit" id="healingLetterCreateBtn"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar carta</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-7">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title mb-2">Importação em massa (JSON)</h5>
          <p class="text-muted small mb-3">Envie um arquivo JSON com array de cartas ou objeto com chave <code>letters</code>.</p>
          <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-healing-letters-bulk" enctype="multipart/form-data" class="mb-3">
            <div class="mb-3">
              <label class="form-label">Arquivo JSON</label>
              <input class="form-control" type="file" name="letters_json" accept="application/json,.json" required>
            </div>
            <button class="btn btn-outline-primary" type="submit"><i class="fa-solid fa-file-import me-1"></i>Importar cartas</button>
          </form>
          <div class="small text-muted mb-1">Exemplo de JSON:</div>
          <pre class="messenger-json-example mb-0">[
  {"category":"cura","text":"Voce pode acolher sua historia com gentileza hoje."},
  {"category":"motivacionais","text":"O proximo passo pode ser pequeno e ainda assim poderoso."}
]</pre>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body pb-0">
      <form method="GET" action="<?php echo $appUrl; ?>/dashboard.php" class="row g-2 align-items-end">
        <input type="hidden" name="action" value="therapist-healing-letters">
        <div class="col-12 col-md-3">
          <label class="form-label mb-1">Categoria</label>
          <select class="form-select" name="category">
            <option value="" <?php echo (($filters['category'] ?? '') === '') ? 'selected' : ''; ?>>Todas</option>
            <option value="dores" <?php echo (($filters['category'] ?? '') === 'dores') ? 'selected' : ''; ?>>Dores</option>
            <option value="reflexivas" <?php echo (($filters['category'] ?? '') === 'reflexivas') ? 'selected' : ''; ?>>Reflexivas</option>
            <option value="cura" <?php echo (($filters['category'] ?? '') === 'cura') ? 'selected' : ''; ?>>Cura</option>
            <option value="motivacionais" <?php echo (($filters['category'] ?? '') === 'motivacionais') ? 'selected' : ''; ?>>Motivacionais</option>
            <option value="conflitos" <?php echo (($filters['category'] ?? '') === 'conflitos') ? 'selected' : ''; ?>>Conflitos</option>
          </select>
        </div>
        <div class="col-12 col-md-7">
          <label class="form-label mb-1">Buscar texto</label>
          <input class="form-control" type="search" name="q" value="<?php echo htmlspecialchars((string) ($filters['q'] ?? '')); ?>" placeholder="Digite parte da carta...">
        </div>
        <div class="col-12 col-md-2 d-grid">
          <button class="btn btn-dark" type="submit"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th style="width: 140px;">Categoria</th>
              <th>Carta</th>
              <th style="width: 180px;">Criada em</th>
              <th style="width: 140px;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($letters)): ?>
              <tr>
                <td colspan="4" class="text-center text-muted py-4">Nenhuma carta encontrada.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($letters as $letter): ?>
                <tr>
                  <td>
                    <?php
                      $cat = (string) ($letter['category'] ?? 'dores');
                      $label = $cat === 'reflexivas'
                        ? 'Reflexivas'
                        : ($cat === 'cura'
                          ? 'Cura'
                          : ($cat === 'motivacionais'
                            ? 'Motivacionais'
                            : ($cat === 'conflitos' ? 'Conflitos' : 'Dores')));
                    ?>
                    <span class="badge rounded-pill text-bg-secondary"><?php echo htmlspecialchars($label); ?></span>
                  </td>
                  <td><?php echo nl2br(htmlspecialchars((string) ($letter['message_text'] ?? ''))); ?></td>
                  <td><?php echo !empty($letter['created_at']) ? date('d/m/Y H:i', strtotime((string) $letter['created_at'])) : '-'; ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-1 flex-nowrap">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary js-edit-letter-btn"
                        data-id="<?php echo (int) ($letter['id'] ?? 0); ?>"
                        data-category="<?php echo htmlspecialchars((string) ($letter['category'] ?? 'dores')); ?>"
                        data-text="<?php echo htmlspecialchars((string) ($letter['message_text'] ?? '')); ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#editHealingLetterModal"
                        title="Editar"
                      >
                        <i class="fa-solid fa-pen"></i>
                      </button>

                      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-healing-letters-delete" class="m-0 js-delete-letter-form">
                        <input type="hidden" name="id" value="<?php echo (int) ($letter['id'] ?? 0); ?>">
                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Excluir">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h5 class="card-title mb-3">Reflexões compartilhadas no baralho</h5>
      <?php if (empty($sharedEntries)): ?>
        <p class="text-muted mb-0">Nenhuma reflexão compartilhada até o momento.</p>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($sharedEntries as $entry): ?>
            <div class="col-12 col-lg-6">
              <article class="messenger-shared-card h-100">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                  <strong><?php echo htmlspecialchars((string) ($entry['patient_name'] ?? 'Paciente')); ?></strong>
                  <span class="small text-muted"><?php echo !empty($entry['created_at']) ? date('d/m/Y H:i', strtotime((string) $entry['created_at'])) : '-'; ?></span>
                </div>
                <div class="small text-muted mb-1"><?php echo htmlspecialchars((string) ($entry['meditation_title'] ?? 'Meditação')); ?></div>
                <p class="mb-2"><?php echo nl2br(htmlspecialchars((string) ($entry['letter_text'] ?? ''))); ?></p>
                <div class="small text-muted mb-1">Reflexão</div>
                <p class="mb-0"><?php echo nl2br(htmlspecialchars((string) ($entry['patient_note'] ?? ''))); ?></p>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="modal fade" id="editHealingLetterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar carta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-healing-letters-update" id="editHealingLetterForm">
        <div class="modal-body">
          <input type="hidden" name="id" id="editHealingLetterId" value="">

          <div class="mb-2">
            <label class="form-label">Categoria</label>
            <select class="form-select" name="category" id="editHealingLetterCategory" required>
              <option value="dores">Dores</option>
              <option value="reflexivas">Reflexivas</option>
              <option value="cura">Cura</option>
              <option value="motivacionais">Motivacionais</option>
              <option value="conflitos">Conflitos</option>
            </select>
          </div>

          <div class="mb-0">
            <label class="form-label">Carta</label>
            <textarea class="form-control" name="message_text" id="editHealingLetterText" rows="6" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="editHealingLetterSaveBtn"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar alterações</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var createForm = document.getElementById('healingLetterCreateForm');
  var createBtn = document.getElementById('healingLetterCreateBtn');
  var editForm = document.getElementById('editHealingLetterForm');
  var editSaveBtn = document.getElementById('editHealingLetterSaveBtn');
  var editIdInput = document.getElementById('editHealingLetterId');
  var editCategoryInput = document.getElementById('editHealingLetterCategory');
  var editTextInput = document.getElementById('editHealingLetterText');

  if (createForm && createBtn) {
    createForm.addEventListener('submit', function () {
      if (createBtn.disabled) {
        return false;
      }

      createBtn.disabled = true;
      createBtn.innerHTML = '<i class="fa-solid fa-hourglass-half me-1"></i>Salvando...';
      return true;
    });
  }

  document.querySelectorAll('.js-edit-letter-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!editIdInput || !editCategoryInput || !editTextInput) {
        return;
      }

      editIdInput.value = btn.getAttribute('data-id') || '';
      editCategoryInput.value = btn.getAttribute('data-category') || 'dores';
      editTextInput.value = btn.getAttribute('data-text') || '';

      if (editSaveBtn) {
        editSaveBtn.disabled = false;
        editSaveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i>Salvar alterações';
      }
    });
  });

  if (editForm && editSaveBtn) {
    editForm.addEventListener('submit', function () {
      if (editSaveBtn.disabled) {
        return false;
      }

      editSaveBtn.disabled = true;
      editSaveBtn.innerHTML = '<i class="fa-solid fa-hourglass-half me-1"></i>Salvando...';
      return true;
    });
  }

  document.querySelectorAll('.js-delete-letter-form').forEach(function (deleteForm) {
    deleteForm.addEventListener('submit', function (event) {
      var ok = window.confirm('Deseja realmente excluir esta carta de cura?');
      if (!ok) {
        event.preventDefault();
      }
    });
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
