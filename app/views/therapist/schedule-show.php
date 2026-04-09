<?php $title = 'Detalhes do compromisso'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Detalhes do compromisso</h4>
    <a class="btn btn-light" href="<?php echo $backUrl; ?>">Voltar para agenda</a>
  </div>

  <div class="card">
    <div class="card-body">
      <dl class="row mb-0">
        <dt class="col-sm-3">Paciente</dt>
        <dd class="col-sm-9"><?php echo htmlspecialchars((string) ($appointment['display_name'] ?? '')); ?></dd>

        <dt class="col-sm-3">Data e hora</dt>
        <dd class="col-sm-9"><?php echo date('d/m/Y H:i', strtotime((string) $appointment['session_date'])); ?></dd>

        <dt class="col-sm-3">Descrição</dt>
        <dd class="col-sm-9"><?php echo htmlspecialchars((string) ($appointment['description'] ?? '')); ?></dd>

        <dt class="col-sm-3">Histórico</dt>
        <dd class="col-sm-9"><?php echo !empty($appointment['history']) ? $appointment['history'] : '<span class="text-muted">Sem histórico</span>'; ?></dd>

        <dt class="col-sm-3">Criado em</dt>
        <dd class="col-sm-9"><?php echo !empty($appointment['created_at']) ? date('d/m/Y H:i', strtotime((string) $appointment['created_at'])) : '-'; ?></dd>
      </dl>
    </div>
  </div>

  <div class="mt-3 d-flex gap-2">
    <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-schedule-edit&id=<?php echo (int) $appointment['id']; ?>&view=<?php echo urlencode((string) $viewMode); ?>&date=<?php echo urlencode((string) $date); ?>">Editar</a>
    <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-schedule-delete" onsubmit="return confirm('Excluir este compromisso?');">
      <input type="hidden" name="id" value="<?php echo (int) $appointment['id']; ?>">
      <input type="hidden" name="view_mode" value="<?php echo htmlspecialchars((string) $viewMode); ?>">
      <input type="hidden" name="date" value="<?php echo htmlspecialchars((string) $date); ?>">
      <button class="btn btn-outline-danger" type="submit">Excluir</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
