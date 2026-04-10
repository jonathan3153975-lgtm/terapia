<?php $title = 'Mensagens diárias'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0">Mensagens diárias</h3>
    <span class="badge text-bg-light border"><?php echo count($messages ?? []); ?> mensagem(ns)</span>
  </div>

  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="row g-3 mb-3">
    <div class="col-12 col-xl-5">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title mb-2">Nova mensagem</h5>
          <p class="text-muted small mb-3">Crie mensagens para sorteio no baú do paciente.</p>
          <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-messages-store">
            <div class="mb-2">
              <label class="form-label">Categoria</label>
              <select class="form-select" name="category" required>
                <option value="dores">Dores</option>
                <option value="reflexivas">Reflexivas</option>
                <option value="cura">Cura</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Mensagem</label>
              <textarea class="form-control" name="message_text" rows="5" required placeholder="Escreva uma mensagem inspiradora..."></textarea>
            </div>
            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar mensagem</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-7">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title mb-2">Importação em massa (JSON)</h5>
          <p class="text-muted small mb-3">Envie um arquivo JSON com uma lista de mensagens. Formatos aceitos: array direto ou objeto com chave <code>messages</code>.</p>
          <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-messages-bulk" enctype="multipart/form-data" class="mb-3">
            <div class="mb-3">
              <label class="form-label">Arquivo JSON</label>
              <input class="form-control" type="file" name="messages_json" accept="application/json,.json" required>
            </div>
            <button class="btn btn-outline-primary" type="submit"><i class="fa-solid fa-file-import me-1"></i>Importar mensagens</button>
          </form>
          <div class="small text-muted mb-1">Exemplo de JSON:</div>
          <pre class="messenger-json-example mb-0">[
  {"category":"dores","text":"Respire fundo e nomeie sua dor com gentileza."},
  {"category":"reflexivas","text":"O que você gostaria de acolher em si hoje?"},
  {"category":"cura","text":"A cura também acontece nos pequenos passos."}
]</pre>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body pb-0">
      <form method="GET" action="<?php echo $appUrl; ?>/dashboard.php" class="row g-2 align-items-end">
        <input type="hidden" name="action" value="therapist-messages">
        <div class="col-12 col-md-3">
          <label class="form-label mb-1">Categoria</label>
          <select class="form-select" name="category">
            <option value="" <?php echo (($filters['category'] ?? '') === '') ? 'selected' : ''; ?>>Todas</option>
            <option value="dores" <?php echo (($filters['category'] ?? '') === 'dores') ? 'selected' : ''; ?>>Dores</option>
            <option value="reflexivas" <?php echo (($filters['category'] ?? '') === 'reflexivas') ? 'selected' : ''; ?>>Reflexivas</option>
            <option value="cura" <?php echo (($filters['category'] ?? '') === 'cura') ? 'selected' : ''; ?>>Cura</option>
          </select>
        </div>
        <div class="col-12 col-md-7">
          <label class="form-label mb-1">Buscar texto</label>
          <input class="form-control" type="search" name="q" value="<?php echo htmlspecialchars((string) ($filters['q'] ?? '')); ?>" placeholder="Digite parte da mensagem...">
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
              <th>Mensagem</th>
              <th style="width: 180px;">Criada em</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($messages)): ?>
              <tr>
                <td colspan="3" class="text-center text-muted py-4">Nenhuma mensagem encontrada.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($messages as $message): ?>
                <tr>
                  <td>
                    <?php
                      $cat = (string) ($message['category'] ?? 'dores');
                      $label = $cat === 'reflexivas' ? 'Reflexivas' : ($cat === 'cura' ? 'Cura' : 'Dores');
                    ?>
                    <span class="badge rounded-pill text-bg-secondary"><?php echo htmlspecialchars($label); ?></span>
                  </td>
                  <td><?php echo nl2br(htmlspecialchars((string) ($message['message_text'] ?? ''))); ?></td>
                  <td><?php echo !empty($message['created_at']) ? date('d/m/Y H:i', strtotime((string) $message['created_at'])) : '-'; ?></td>
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
      <h5 class="card-title mb-3">Reflexões compartilhadas por pacientes</h5>
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
                <div class="small text-muted mb-1">Mensagem sorteada</div>
                <p class="mb-2"><?php echo nl2br(htmlspecialchars((string) ($entry['message_text'] ?? ''))); ?></p>
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
<?php include __DIR__ . '/../../partials/footer.php'; ?>
