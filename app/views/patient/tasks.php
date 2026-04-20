<?php $title = 'Minhas Tarefas'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <?php if (!($_SESSION['patient_has_active_plan'] ?? true)): ?>
  <div class="free-tier-notice mb-4">
    <div class="free-tier-notice__icon"><i class="fa-solid fa-lock-open"></i></div>
    <div class="free-tier-notice__body">
      <strong>Você está no plano gratuito</strong>
      <p class="mb-0">Aqui você pode visualizar e responder as tarefas enviadas pelo seu terapeuta. Para acessar todos os recursos — materiais, mensageiro, diário da gratidão, meditação guiada e orações — ative um plano.</p>
    </div>
    <a class="btn btn-primary btn-sm free-tier-notice__cta" href="<?php echo $appUrl; ?>/patient.php?action=subscription-plans">
      <i class="fa-solid fa-crown me-1"></i>Ver planos
    </a>
  </div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Minhas tarefas</h3>
    <a class="btn btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=materials"><i class="fa-solid fa-book me-1"></i>Meus materiais</a>
  </div>
  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>Miniatura</th><th>Data</th><th>Título</th><th>Status</th><th>Material</th><th>Ação</th></tr></thead>
        <tbody>
          <?php if (empty($tasks)): ?>
          <tr><td colspan="6" class="text-center py-4 text-muted">Nenhuma tarefa recebida.</td></tr>
          <?php else: foreach ($tasks as $task): ?>
          <?php $linkedMaterials = $taskLinkedMaterials[(int) $task['id']] ?? []; ?>
          <?php
            $coverPath = trim((string) ($task['cover_image_path'] ?? ''));
            $therapistId = (int) ($task['therapist_id'] ?? 0);
            $therapistLogoPath = trim((string) (($therapistLogoMap[$therapistId] ?? '') ?: ''));
            $thumbPath = $coverPath !== '' ? $coverPath : $therapistLogoPath;
          ?>
          <tr>
            <td>
              <?php if ($thumbPath !== ''): ?>
                <img
                  src="<?php echo $appUrl; ?>/<?php echo htmlspecialchars(ltrim($thumbPath, '/')); ?>"
                  alt="Miniatura da tarefa"
                  style="width: 72px; height: 48px; object-fit: cover; border-radius: .45rem; border: 1px solid #dee2e6;"
                >
              <?php else: ?>
                <div class="d-inline-flex align-items-center justify-content-center" style="width:72px;height:48px;border-radius:.45rem;border:1px solid #dee2e6;background:#f8f9fa;color:#6c757d;">
                  <i class="fa-solid fa-image"></i>
                </div>
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars((string) date('d/m/Y', strtotime((string) $task['due_date']))); ?></td>
            <td><?php echo htmlspecialchars($task['title']); ?></td>
            <td>
              <?php if (($task['status'] ?? '') === 'done'): ?>
                <span class="badge text-bg-success">Respondida</span>
              <?php else: ?>
                <span class="badge text-bg-warning">Pendente</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($linkedMaterials) && !empty($task['send_to_patient'])): ?>
                <a class="btn btn-sm btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=task-material&id=<?php echo (int) $task['id']; ?>"><i class="fa-solid fa-link me-1"></i><?php echo count($linkedMaterials); ?> material(is)</a>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <td>
              <a class="btn btn-sm btn-primary" href="<?php echo $appUrl; ?>/patient.php?action=task-respond&id=<?php echo (int) $task['id']; ?>">
                <i class="fa-solid fa-eye me-1"></i>Acessar tarefa
              </a>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
