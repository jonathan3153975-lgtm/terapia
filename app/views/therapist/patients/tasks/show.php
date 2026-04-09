<?php $title = 'Visualizar Tarefa'; include __DIR__ . '/../../../partials/header.php'; include __DIR__ . '/../../../partials/nav.php'; ?>
<div class="page-wrap">
  <?php
  $taskStatusLabel = 'Pendente';
  $taskStatusClass = 'text-bg-warning';
  if (($task['status'] ?? '') === 'done') {
    $taskStatusLabel = 'Finalizado';
    $taskStatusClass = 'text-bg-success';
  } elseif (!empty($task['send_to_patient'])) {
    $taskStatusLabel = 'Enviado';
    $taskStatusClass = 'text-bg-info';
  }
  ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Tarefa de <?php echo htmlspecialchars((string) $patient['name']); ?></h3>
    <a class="btn btn-outline-dark" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-history&id=<?php echo (int) $patient['id']; ?>"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3"><strong>Data:</strong> <?php echo htmlspecialchars((string) date('d/m/Y', strtotime((string) ($task['due_date'] ?? 'now')))); ?></div>
        <div class="col-md-6"><strong>Título:</strong> <?php echo htmlspecialchars((string) ($task['title'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>Status:</strong> <span class="badge <?php echo $taskStatusClass; ?>"><?php echo $taskStatusLabel; ?></span></div>
        <div class="col-12">
          <strong>Descrição:</strong>
          <div class="mt-2 border rounded p-3 bg-light-subtle" style="max-height: 52vh; overflow: auto;">
            <?php echo (string) ($task['description'] ?? ''); ?>
          </div>
        </div>
        <?php if (!empty($linkedMaterial)): ?>
        <div class="col-12">
          <strong>Material vinculado:</strong>
          <div class="mt-2 border rounded p-3 bg-white">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
              <div>
                <div class="fw-semibold"><?php echo htmlspecialchars((string) ($linkedMaterial['title'] ?? '-')); ?></div>
                <div class="text-muted small"><?php echo (($linkedMaterial['type'] ?? '') === 'exercise') ? 'Exercício' : 'Material de apoio'; ?></div>
              </div>
              <a class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener noreferrer" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials-show&id=<?php echo (int) $linkedMaterial['id']; ?>"><i class="fa-solid fa-eye me-1"></i>Ver material</a>
            </div>
            <?php if (!empty($linkedMaterial['description_html'])): ?>
              <div class="mt-3 border rounded p-3 bg-light-subtle" style="max-height: 32vh; overflow:auto;">
                <?php echo (string) $linkedMaterial['description_html']; ?>
              </div>
            <?php endif; ?>
            <?php if (!empty($linkedMaterialAssets)): ?>
              <div class="mt-3 d-flex flex-wrap gap-2">
                <?php foreach ($linkedMaterialAssets as $asset): ?>
                  <?php if (($asset['asset_type'] ?? '') === 'pdf'): ?>
                    <span class="btn btn-sm btn-outline-danger disabled"><i class="fa-solid fa-file-pdf me-1"></i><?php echo htmlspecialchars((string) ($asset['file_name'] ?? 'PDF')); ?></span>
                  <?php elseif (($asset['asset_type'] ?? '') === 'image'): ?>
                    <span class="btn btn-sm btn-outline-primary disabled"><i class="fa-solid fa-file-image me-1"></i><?php echo htmlspecialchars((string) ($asset['file_name'] ?? 'Imagem')); ?></span>
                  <?php elseif (($asset['asset_type'] ?? '') === 'video'): ?>
                    <span class="btn btn-sm btn-outline-warning disabled"><i class="fa-solid fa-file-video me-1"></i><?php echo htmlspecialchars((string) ($asset['file_name'] ?? 'Vídeo')); ?></span>
                  <?php elseif (($asset['asset_type'] ?? '') === 'url'): ?>
                    <span class="btn btn-sm btn-outline-info disabled"><i class="fa-solid fa-link me-1"></i><?php echo htmlspecialchars((string) ($asset['file_name'] ?? 'Link')); ?></span>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($files)): ?>
        <div class="col-12">
          <strong>Anexos:</strong>
          <div class="mt-2 d-flex flex-wrap gap-2">
            <?php foreach ($files as $fi): ?>
              <?php if (($fi['file_type'] ?? '') === 'link'): ?>
                <a href="<?php echo htmlspecialchars((string) $fi['file_path']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-info btn-sm">
                  <i class="fa-solid fa-link me-1"></i><?php echo htmlspecialchars((string) $fi['file_name']); ?>
                </a>
              <?php elseif (($fi['file_type'] ?? '') === 'pdf'): ?>
                <a href="<?php echo $appUrl; ?>/<?php echo htmlspecialchars((string) $fi['file_path']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-danger btn-sm">
                  <i class="fa-solid fa-file-pdf me-1"></i><?php echo htmlspecialchars((string) $fi['file_name']); ?>
                </a>
              <?php else: ?>
                <a href="<?php echo $appUrl; ?>/<?php echo htmlspecialchars((string) $fi['file_path']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm">
                  <i class="fa-solid fa-image me-1"></i><?php echo htmlspecialchars((string) $fi['file_name']); ?>
                </a>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../partials/footer.php'; ?>