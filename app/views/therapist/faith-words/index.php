<?php $title = 'Pai, fala comigo'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0">Pai, fala comigo</h3>
  </div>

  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="row g-3 mb-3">
    <div class="col-12 col-xl-5">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title mb-2">Cadastrar palavra</h5>
          <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-faith-words-store" id="faithWordCreateForm">
            <div class="mb-2">
              <label class="form-label">Referência bíblica</label>
              <input class="form-control" name="reference_text" required maxlength="120" placeholder="Ex.: Salmos 23:1">
            </div>
            <div class="mb-3">
              <label class="form-label">Texto do versículo</label>
              <textarea class="form-control" name="verse_text" rows="6" required placeholder="Digite o texto..."></textarea>
            </div>
            <button class="btn btn-primary" type="submit" id="faithWordCreateBtn"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar palavra</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-7">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title mb-2">Importação em massa (JSON)</h5>
          <p class="text-muted small mb-3">Envie um arquivo JSON com uma lista de palavras. Formatos aceitos: array direto ou objeto com chave <code>words</code>.</p>
          <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-faith-words-bulk" enctype="multipart/form-data" id="faithWordBulkForm" class="mb-3">
            <div class="mb-3">
              <label class="form-label">Arquivo JSON</label>
              <input class="form-control" type="file" name="words_json" accept="application/json,.json" required>
            </div>
            <button class="btn btn-outline-primary" type="submit" id="faithWordBulkBtn"><i class="fa-solid fa-file-import me-1"></i>Importar palavras</button>
          </form>

          <div class="small text-muted mb-1">Exemplo de JSON:</div>
          <pre class="messenger-json-example mb-0">[
  {"reference":"João 3:16","text":"Porque Deus amou o mundo de tal maneira..."},
  {"reference":"Salmos 23:1","text":"O Senhor é o meu pastor; nada me faltará."}
]</pre>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body pb-0">
      <form method="GET" action="<?php echo $appUrl; ?>/dashboard.php" class="row g-2 align-items-end">
        <input type="hidden" name="action" value="therapist-faith-words">
        <div class="col-12 col-md-10">
          <label class="form-label mb-1">Buscar</label>
          <input class="form-control" type="search" name="q" value="<?php echo htmlspecialchars((string) ($filters['q'] ?? '')); ?>" placeholder="Referência ou trecho do versículo...">
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
              <th style="width:170px;">Referência</th>
              <th>Texto</th>
              <th style="width:120px;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($words)): ?>
              <tr><td colspan="3" class="text-center text-muted py-4">Nenhuma palavra cadastrada.</td></tr>
            <?php else: ?>
              <?php foreach ($words as $word): ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars((string) ($word['reference_text'] ?? '')); ?></strong></td>
                  <td><?php echo nl2br(htmlspecialchars((string) ($word['verse_text'] ?? ''))); ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-1 flex-nowrap">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary js-edit-faith-word-btn"
                        data-id="<?php echo (int) ($word['id'] ?? 0); ?>"
                        data-reference="<?php echo htmlspecialchars((string) ($word['reference_text'] ?? '')); ?>"
                        data-text="<?php echo htmlspecialchars((string) ($word['verse_text'] ?? '')); ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#editFaithWordModal"
                        title="Editar"
                      >
                        <i class="fa-solid fa-pen"></i>
                      </button>

                      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-faith-words-delete" class="m-0 js-delete-faith-word-form">
                        <input type="hidden" name="id" value="<?php echo (int) ($word['id'] ?? 0); ?>">
                        <button class="btn btn-sm btn-outline-danger js-delete-faith-word-btn" type="submit" title="Excluir">
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
      <h5 class="card-title mb-3">Reflexões compartilhadas pelos pacientes</h5>
      <?php if (empty($sharedEntries)): ?>
        <p class="text-muted mb-0">Nenhuma reflexão compartilhada até o momento.</p>
      <?php else: ?>
        <div class="row g-3 reflection-stack">
          <?php foreach ($sharedEntries as $entry): ?>
            <div class="col-12">
              <article class="messenger-shared-card h-100">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                  <strong><?php echo htmlspecialchars((string) ($entry['patient_name'] ?? 'Paciente')); ?></strong>
                  <span class="small text-muted"><?php echo !empty($entry['created_at']) ? date('d/m/Y H:i', strtotime((string) $entry['created_at'])) : '-'; ?></span>
                </div>
                <div class="small text-muted mb-1"><?php echo htmlspecialchars((string) ($entry['word_reference'] ?? '')); ?></div>
                <p class="mb-2"><?php echo nl2br(htmlspecialchars((string) ($entry['word_text'] ?? ''))); ?></p>
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

<div class="modal fade" id="editFaithWordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar palavra</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-faith-words-update" id="editFaithWordForm">
        <div class="modal-body">
          <input type="hidden" name="id" id="editFaithWordId" value="">
          <div class="mb-2">
            <label class="form-label">Referência bíblica</label>
            <input class="form-control" name="reference_text" id="editFaithWordReference" required maxlength="120">
          </div>
          <div class="mb-0">
            <label class="form-label">Texto do versículo</label>
            <textarea class="form-control" name="verse_text" id="editFaithWordText" rows="6" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="editFaithWordSaveBtn"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar alterações</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var createForm = document.getElementById('faithWordCreateForm');
  var createBtn = document.getElementById('faithWordCreateBtn');
  var bulkForm = document.getElementById('faithWordBulkForm');
  var bulkBtn = document.getElementById('faithWordBulkBtn');
  var editForm = document.getElementById('editFaithWordForm');
  var editSaveBtn = document.getElementById('editFaithWordSaveBtn');
  var editIdInput = document.getElementById('editFaithWordId');
  var editReferenceInput = document.getElementById('editFaithWordReference');
  var editTextInput = document.getElementById('editFaithWordText');

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

  if (bulkForm && bulkBtn) {
    bulkForm.addEventListener('submit', function () {
      if (bulkBtn.disabled) {
        return false;
      }
      bulkBtn.disabled = true;
      bulkBtn.innerHTML = '<i class="fa-solid fa-hourglass-half me-1"></i>Importando...';
      return true;
    });
  }

  document.querySelectorAll('.js-edit-faith-word-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!editIdInput || !editReferenceInput || !editTextInput) {
        return;
      }

      editIdInput.value = btn.getAttribute('data-id') || '';
      editReferenceInput.value = btn.getAttribute('data-reference') || '';
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

  document.querySelectorAll('.js-delete-faith-word-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      var ok = window.confirm('Deseja realmente excluir esta palavra?');
      if (!ok) {
        event.preventDefault();
        return;
      }
      var btn = form.querySelector('.js-delete-faith-word-btn');
      if (btn) {
        btn.disabled = true;
      }
    });
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
