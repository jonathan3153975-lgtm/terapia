<?php $title = 'Material da Tarefa'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
    <div>
      <h3 class="mb-0"><?php echo htmlspecialchars((string) ($material['title'] ?? 'Material')); ?></h3>
      <div class="text-muted small">Vinculado a tarefa: <?php echo htmlspecialchars((string) ($task['title'] ?? '-')); ?></div>
    </div>
    <a class="btn btn-outline-dark" href="<?php echo $appUrl; ?>/patient.php?action=tasks"><i class="fa-solid fa-arrow-left me-1"></i>Voltar</a>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="mb-2"><strong>Tipo:</strong> <?php echo (($material['type'] ?? '') === 'exercise') ? 'Exercício' : 'Material de apoio'; ?></div>
      <div class="border rounded p-3 bg-light-subtle"><?php echo (string) ($material['description_html'] ?? ''); ?></div>
      <?php if (!empty($material['custom_html'])): ?>
        <div class="mt-3 border rounded p-3"><?php echo (string) $material['custom_html']; ?></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header bg-transparent"><strong>Arquivos e links do material</strong></div>
    <div class="card-body">
      <?php if (empty($assets)): ?>
        <div class="text-muted">Nenhum arquivo ou link anexado.</div>
      <?php else: ?>
        <div class="material-asset-view-list">
          <?php foreach ($assets as $asset): ?>
            <?php
              $assetType = (string) ($asset['asset_type'] ?? '');
              $filePath = !empty($asset['file_path']) ? ($appUrl . '/' . ltrim((string) $asset['file_path'], '/')) : '';
              $fileUrl = !empty($asset['file_url']) ? (string) $asset['file_url'] : '';
              $viewUrl = $fileUrl !== '' ? $fileUrl : $filePath;
            ?>
            <div class="material-asset-view-card">
              <div class="material-asset-view-header">
                <div class="material-asset-view-title">
                  <?php if ($assetType === 'pdf'): ?>
                    <i class="fa-solid fa-file-pdf text-danger"></i>
                  <?php elseif ($assetType === 'image'): ?>
                    <i class="fa-solid fa-file-image text-primary"></i>
                  <?php elseif ($assetType === 'video'): ?>
                    <i class="fa-solid fa-file-video text-warning"></i>
                  <?php elseif ($assetType === 'url'): ?>
                    <i class="fa-solid fa-link text-info"></i>
                  <?php else: ?>
                    <i class="fa-solid fa-file text-secondary"></i>
                  <?php endif; ?>
                  <span><?php echo htmlspecialchars((string) ($asset['file_name'] ?? 'Arquivo')); ?></span>
                </div>
                <?php if ($viewUrl !== ''): ?>
                  <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer" href="<?php echo htmlspecialchars($viewUrl); ?>">Abrir</a>
                <?php endif; ?>
              </div>
              <div class="material-asset-view-body">
                <?php if ($assetType === 'image' && $filePath !== ''): ?>
                  <a class="material-asset-open-link" target="_blank" rel="noopener noreferrer" href="<?php echo htmlspecialchars($filePath); ?>"><img class="material-view-image" src="<?php echo htmlspecialchars($filePath); ?>" alt="Imagem"></a>
                <?php elseif ($assetType === 'video' && $filePath !== ''): ?>
                  <video class="material-view-video" src="<?php echo htmlspecialchars($filePath); ?>" controls preload="metadata"></video>
                <?php elseif ($assetType === 'pdf' && $filePath !== ''): ?>
                  <iframe class="material-view-pdf" src="<?php echo htmlspecialchars($filePath); ?>#toolbar=0&navpanes=0"></iframe>
                <?php elseif ($assetType === 'url' && $fileUrl !== ''): ?>
                  <div class="material-view-link-box"><a target="_blank" rel="noopener noreferrer" href="<?php echo htmlspecialchars($fileUrl); ?>"><?php echo htmlspecialchars($fileUrl); ?></a></div>
                <?php else: ?>
                  <div class="material-view-link-box text-muted">Visualização indisponível.</div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>