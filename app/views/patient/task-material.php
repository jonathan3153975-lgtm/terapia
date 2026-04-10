<?php $title = 'Material da Tarefa'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
    <div>
      <h3 class="mb-0">Materiais da tarefa</h3>
      <div class="text-muted small">Vinculados a tarefa: <?php echo htmlspecialchars((string) ($task['title'] ?? '-')); ?></div>
    </div>
    <a class="btn btn-outline-dark" href="<?php echo $appUrl; ?>/patient.php?action=tasks"><i class="fa-solid fa-arrow-left me-1"></i>Voltar</a>
  </div>

  <div class="d-grid gap-3">
    <?php foreach ($materials as $material): ?>
      <?php $assets = $assetsByMaterial[(int) $material['id']] ?? []; ?>
      <div class="card">
        <div class="card-body">
          <div class="material-task-layout">
            <aside class="material-task-aside">
              <div class="material-task-meta">
                <span class="material-task-label">Título</span>
                <div class="fw-semibold"><?php echo htmlspecialchars((string) ($material['title'] ?? 'Material')); ?></div>
              </div>
              <div class="material-task-meta">
                <span class="material-task-label">Tipo</span>
                <div><?php echo (($material['type'] ?? '') === 'exercise') ? 'Exercício' : 'Material de apoio'; ?></div>
              </div>
              <div class="material-task-meta">
                <span class="material-task-label">Observação</span>
                <div class="text-muted"><?php echo !empty($task['description']) ? trim(strip_tags((string) $task['description'])) : 'Sem observações adicionais'; ?></div>
              </div>
              <div class="material-task-meta">
                <span class="material-task-label">Total de anexos</span>
                <div><?php echo (int) count($assets); ?></div>
              </div>
            </aside>

            <section class="material-task-content">
              <div>
                <div class="material-task-label mb-2">Conteúdo principal</div>
                <div class="border rounded p-3 bg-light-subtle"><?php echo (string) ($material['description_html'] ?? ''); ?></div>
              </div>
              <?php if (!empty($material['custom_html'])): ?>
                <div>
                  <div class="material-task-label mb-2">Conteúdo complementar</div>
                  <div class="border rounded p-3"><?php echo (string) $material['custom_html']; ?></div>
                </div>
              <?php endif; ?>

              <div>
                <div class="material-task-label mb-2">Arquivos e links do material</div>
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
            </section>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>