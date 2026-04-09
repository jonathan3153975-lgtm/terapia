<?php $title = 'Visualizar Material'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Material: <?php echo htmlspecialchars((string) ($material['title'] ?? '')); ?></h4>
    <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials">Voltar</a>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="mb-2"><strong>Tipo:</strong> <?php echo (($material['type'] ?? '') === 'exercise') ? 'Exercício' : 'Material de apoio'; ?></div>
      <div class="mb-2"><strong>Descrição:</strong></div>
      <div class="border rounded p-3 bg-light-subtle"><?php echo (string) ($material['description_html'] ?? ''); ?></div>

      <?php if (!empty($material['custom_html'])): ?>
        <div class="mt-3 mb-2"><strong>Material em HTML:</strong></div>
        <div class="border rounded p-3"><?php echo (string) $material['custom_html']; ?></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header bg-transparent"><strong>Arquivos e links</strong></div>
    <div class="card-body">
      <?php if (empty($assets)): ?>
        <div class="text-muted">Nenhum arquivo/link anexado.</div>
      <?php else: ?>
        <div class="materials-preview-grid">
          <?php foreach ($assets as $asset): ?>
            <div class="materials-preview-item">
              <div class="materials-preview-name"><?php echo htmlspecialchars((string) ($asset['file_name'] ?? 'Arquivo')); ?></div>
              <?php if (($asset['asset_type'] ?? '') === 'image' && !empty($asset['file_path'])): ?>
                <img class="materials-preview-image" src="<?php echo $appUrl; ?>/<?php echo htmlspecialchars((string) $asset['file_path']); ?>" alt="Imagem">
              <?php elseif (($asset['asset_type'] ?? '') === 'video' && !empty($asset['file_path'])): ?>
                <video class="materials-preview-video" src="<?php echo $appUrl; ?>/<?php echo htmlspecialchars((string) $asset['file_path']); ?>" controls></video>
              <?php elseif (($asset['asset_type'] ?? '') === 'pdf' && !empty($asset['file_path'])): ?>
                <div class="materials-preview-file"><i class="fa-solid fa-file-pdf"></i> PDF</div>
                <a class="btn btn-sm btn-outline-danger mt-2" target="_blank" rel="noopener noreferrer" href="<?php echo $appUrl; ?>/<?php echo htmlspecialchars((string) $asset['file_path']); ?>">Abrir PDF</a>
              <?php elseif (($asset['asset_type'] ?? '') === 'url' && !empty($asset['file_url'])): ?>
                <div class="materials-preview-file"><i class="fa-solid fa-link"></i> Link</div>
                <a class="btn btn-sm btn-outline-primary mt-2" target="_blank" rel="noopener noreferrer" href="<?php echo htmlspecialchars((string) $asset['file_url']); ?>">Abrir link</a>
              <?php else: ?>
                <div class="materials-preview-file"><i class="fa-solid fa-file"></i> Arquivo</div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header bg-transparent"><strong>Encaminhamentos</strong></div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead><tr><th>Paciente</th><th>Mensagem</th><th>Enviado em</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (empty($deliveries)): ?>
              <tr><td colspan="4" class="text-center text-muted py-3">Material ainda não foi encaminhado.</td></tr>
            <?php else: ?>
              <?php foreach ($deliveries as $delivery): ?>
                <tr>
                  <td><?php echo htmlspecialchars((string) ($delivery['patient_name'] ?? '-')); ?></td>
                  <td><?php echo htmlspecialchars((string) ($delivery['message'] ?? '-')); ?></td>
                  <td><?php echo !empty($delivery['sent_at']) ? date('d/m/Y H:i', strtotime((string) $delivery['sent_at'])) : '-'; ?></td>
                  <td><?php echo (($delivery['status'] ?? '') === 'viewed') ? 'Visualizado' : 'Enviado'; ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
