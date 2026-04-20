<?php $title = 'Devocional'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <section class="messenger-hero-image mb-4" style="background-image: url('<?php echo $appUrl; ?>/app/images/devocional.png');">
    <div class="messenger-hero-overlay">
      <div class="messenger-hero-content">
        <h3 class="messenger-hero-image-title">Devocional</h3>
        <p class="messenger-hero-image-copy">Acesse o devocional do dia e registre sua reflexão pessoal com qualidade.</p>
      </div>
      <a class="btn btn-dark messenger-draw-btn" href="<?php echo $appUrl; ?>/patient.php?action=devotional-today"><i class="fa-solid fa-sun me-1"></i>Acessar devocional do dia</a>
    </div>
  </section>

  <?php if (empty($todayEntry)): ?>
    <div class="alert alert-warning">Ainda não há devocional cadastrado para hoje.</div>
  <?php endif; ?>

  <section class="card">
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="card-title mb-0">Meus registros devocionais</h5>
        <span class="badge text-bg-light border"><?php echo count($records ?? []); ?> registro(s)</span>
      </div>

      <?php if (empty($records)): ?>
        <div class="messenger-empty-state">
          <i class="fa-regular fa-bookmark"></i>
          <p class="mb-0">Você ainda não salvou reflexões de devocional.</p>
        </div>
      <?php else: ?>
        <div class="table-responsive d-none d-lg-block">
          <table class="table table-sm align-middle mb-0">
            <thead>
              <tr>
                <th>Data</th>
                <th>Título</th>
                <th>Palavra de Deus</th>
                <th>Salvo em</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($records as $record): ?>
                <tr>
                  <td><?php echo !empty($record['entry_date']) ? date('d/m/Y', strtotime((string) $record['entry_date'])) : '-'; ?></td>
                  <td><?php echo htmlspecialchars((string) ($record['title'] ?? '-')); ?></td>
                  <td><?php echo htmlspecialchars((string) ($record['word_of_god'] ?? '-')); ?></td>
                  <td><?php echo !empty($record['created_at']) ? date('d/m/Y H:i', strtotime((string) $record['created_at'])) : '-'; ?></td>
                  <td class="d-flex gap-1">
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=devotional-record-show&id=<?php echo (int) ($record['id'] ?? 0); ?>"><i class="fa-solid fa-eye me-1"></i>Visualizar</a>
                    <a class="btn btn-sm btn-outline-secondary" href="<?php echo $appUrl; ?>/patient.php?action=devotional-record-download&id=<?php echo (int) ($record['id'] ?? 0); ?>" title="Baixar HTML"><i class="fa-solid fa-download"></i></a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="d-lg-none d-grid gap-2">
          <?php foreach ($records as $record): ?>
            <article class="card border">
              <div class="card-body">
                <div class="fw-semibold mb-1"><?php echo htmlspecialchars((string) ($record['title'] ?? '-')); ?></div>
                <div class="small text-muted mb-1"><?php echo !empty($record['entry_date']) ? date('d/m/Y', strtotime((string) $record['entry_date'])) : '-'; ?></div>
                <div class="small mb-2"><strong>Palavra:</strong> <?php echo htmlspecialchars((string) ($record['word_of_god'] ?? '-')); ?></div>
                <div class="d-flex gap-2">
                  <a class="btn btn-sm btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=devotional-record-show&id=<?php echo (int) ($record['id'] ?? 0); ?>"><i class="fa-solid fa-eye me-1"></i>Visualizar</a>
                  <a class="btn btn-sm btn-outline-secondary" href="<?php echo $appUrl; ?>/patient.php?action=devotional-record-download&id=<?php echo (int) ($record['id'] ?? 0); ?>" title="Baixar HTML"><i class="fa-solid fa-download me-1"></i>Baixar</a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
