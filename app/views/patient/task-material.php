<?php $title = 'Material da Tarefa'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
    <div>
      <h3 class="mb-0">Materiais da tarefa</h3>
      <div class="text-muted small">Vinculados a tarefa: <?php echo htmlspecialchars((string) ($task['title'] ?? '-')); ?></div>
    </div>
    <a class="btn btn-outline-dark" href="<?php echo $appUrl; ?>/patient.php?action=tasks"><i class="fa-solid fa-arrow-left me-1"></i>Voltar</a>
  </div>

  <?php if (!empty($task['cover_image_path'])): ?>
    <?php $coverUrl = $appUrl . '/' . ltrim((string) $task['cover_image_path'], '/'); ?>
    <div class="card mb-3">
      <div class="card-header bg-transparent"><strong>Capa da tarefa</strong></div>
      <div class="card-body">
        <button type="button" class="btn btn-link p-0 border-0" data-bs-toggle="modal" data-bs-target="#taskMaterialCoverModal" title="Clique para ampliar">
          <img src="<?php echo htmlspecialchars($coverUrl); ?>" alt="Capa da tarefa" style="width: 100%; max-height: 320px; object-fit: cover; border-radius: .8rem; border: 1px solid #dee2e6;">
        </button>
        <div class="small text-muted mt-2">Clique na imagem para visualizar em tamanho ampliado.</div>
      </div>
    </div>
  <?php endif; ?>

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
              $previewUrl = (string) ($asset['preview_url'] ?? '');
              $fileUrl = !empty($asset['file_url']) ? (string) $asset['file_url'] : '';
              $viewUrl = $fileUrl !== '' ? $fileUrl : (string) ($asset['view_url'] ?? '');
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
                <?php if ($assetType === 'image' && $previewUrl !== ''): ?>
                  <a class="material-asset-open-link" target="_blank" rel="noopener noreferrer" href="<?php echo htmlspecialchars($viewUrl); ?>"><img class="material-view-image" src="<?php echo htmlspecialchars($previewUrl); ?>" alt="Imagem"></a>
                <?php elseif ($assetType === 'video' && $previewUrl !== ''): ?>
                  <video class="material-view-video" src="<?php echo htmlspecialchars($previewUrl); ?>" controls controlsList="nodownload noremoteplayback" preload="metadata" disablepictureinpicture></video>
                <?php elseif ($assetType === 'pdf' && $previewUrl !== ''): ?>
                  <iframe class="material-view-pdf" src="<?php echo htmlspecialchars($previewUrl); ?>"></iframe>
                <?php elseif ($assetType === 'audio' && $previewUrl !== ''): ?>
                  <audio class="w-100" src="<?php echo htmlspecialchars($previewUrl); ?>" controls controlsList="nodownload noremoteplayback" preload="metadata"></audio>
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

<?php if (!empty($task['cover_image_path'])): ?>
  <div class="modal fade" id="taskMaterialCoverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Capa da tarefa</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body text-center">
          <img src="<?php echo htmlspecialchars($coverUrl); ?>" alt="Capa da tarefa" style="max-width: 100%; height: auto; border-radius: .7rem;">
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
<?php include __DIR__ . '/../partials/footer.php'; ?>