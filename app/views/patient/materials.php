<?php $title = 'Meus Materiais'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Meus materiais</h3>
    <a class="btn btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=tasks"><i class="fa-solid fa-list-check me-1"></i>Minhas tarefas</a>
  </div>

  <div class="row g-3">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-transparent"><strong>Materiais enviados por tarefa</strong></div>
        <div class="card-body">
          <?php if (empty($materialTasks)): ?>
            <p class="text-muted mb-0">Nenhum material recebido por tarefa.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead>
                  <tr>
                    <th>Data</th>
                    <th>Tarefa</th>
                    <th>Materiais</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($materialTasks as $task): ?>
                    <?php $linkedMaterials = $taskLinkedMaterials[(int) $task['id']] ?? []; ?>
                    <tr>
                      <td><?php echo htmlspecialchars((string) date('d/m/Y', strtotime((string) ($task['due_date'] ?? 'now')))); ?></td>
                      <td><?php echo htmlspecialchars((string) ($task['title'] ?? '-')); ?></td>
                      <td>
                        <?php if (empty($linkedMaterials)): ?>
                          <span class="text-muted">-</span>
                        <?php else: ?>
                          <a class="btn btn-sm btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=task-material&id=<?php echo (int) $task['id']; ?>">
                            <i class="fa-solid fa-book-open me-1"></i>Acessar (<?php echo count($linkedMaterials); ?>)
                          </a>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card">
        <div class="card-header bg-transparent"><strong>Materiais encaminhados diretamente</strong></div>
        <div class="card-body">
          <?php if (empty($deliveries)): ?>
            <p class="text-muted mb-0">Nenhum material encaminhado diretamente.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead>
                  <tr>
                    <th>Data envio</th>
                    <th>Material</th>
                    <th>Mensagem</th>
                    <th>Acessos</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($deliveries as $delivery): ?>
                    <?php $assets = $assetsByMaterial[(int) ($delivery['material_id'] ?? 0)] ?? []; ?>
                    <tr>
                      <td><?php echo htmlspecialchars((string) date('d/m/Y H:i', strtotime((string) ($delivery['sent_at'] ?? 'now')))); ?></td>
                      <td><?php echo htmlspecialchars((string) ($delivery['material_title'] ?? '-')); ?></td>
                      <td><?php echo htmlspecialchars((string) ($delivery['message'] ?? '-')); ?></td>
                      <td>
                        <?php if (empty($assets)): ?>
                          <span class="text-muted">Sem anexos</span>
                        <?php else: ?>
                          <div class="d-flex gap-2 flex-wrap">
                            <?php foreach ($assets as $asset): ?>
                              <?php if (($asset['asset_type'] ?? '') === 'url'): ?>
                                <a class="btn btn-sm btn-outline-info" href="<?php echo htmlspecialchars((string) ($asset['file_url'] ?? '#')); ?>" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-link me-1"></i>Link</a>
                              <?php else: ?>
                                <?php $path = (string) ($asset['file_path'] ?? ''); ?>
                                <?php if ($path !== ''): ?>
                                  <a class="btn btn-sm btn-outline-secondary" href="<?php echo $appUrl; ?>/<?php echo htmlspecialchars($path); ?>" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-file-arrow-down me-1"></i>Abrir</a>
                                <?php endif; ?>
                              <?php endif; ?>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
