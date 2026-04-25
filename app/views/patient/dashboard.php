<?php $title = 'Portal do Paciente'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container-fluid page-wrap dashboard-page">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <?php
  $nextLabel = 'Sem sessão agendada';
  if (!empty($nextAppointment['session_date'])) {
    $nextLabel = date('d/m/Y H:i', strtotime((string) $nextAppointment['session_date']));
  }
  ?>

  <section class="dashboard-hero dashboard-hero--patient">
    <div class="dashboard-hero-grid">
      <div>
        <span class="dashboard-kicker">Minha jornada</span>
        <h1 class="dashboard-title">Seu espaço para acompanhar a terapia com mais clareza e acolhimento.</h1>
        <p class="dashboard-copy">Veja sua evolução, acompanhe a próxima sessão e retome rapidamente os conteúdos que ajudam no seu processo.</p>
        <div class="dashboard-stat-chips">
          <span class="dashboard-stat-chip"><i class="fa-solid fa-calendar-day"></i><?php echo (int) $daysSinceRegister; ?> dias na jornada</span>
          <span class="dashboard-stat-chip"><i class="fa-solid fa-heart-pulse"></i><?php echo (int) $sessionsDone; ?> sessões realizadas</span>
          <span class="dashboard-stat-chip"><i class="fa-solid fa-list-check"></i><?php echo (int) $receivedTasks; ?> tarefas pendentes</span>
        </div>
        <div class="dashboard-quick-actions">
          <a class="btn btn-primary" href="<?php echo $appUrl; ?>/patient.php?action=materials"><i class="fa-solid fa-book me-2"></i>Meus materiais</a>
          <a class="btn btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=tasks"><i class="fa-solid fa-list-check me-2"></i>Minhas tarefas</a>
        </div>
      </div>
      <aside class="dashboard-highlight-card">
        <span class="dashboard-highlight-kicker">Próximo passo</span>
        <div class="dashboard-highlight-row is-emphasis">
          <strong><?php echo htmlspecialchars($nextLabel); ?></strong>
          <span>próxima sessão prevista</span>
        </div>
        <div class="dashboard-highlight-row">
          <strong><?php echo (int) ($completionRate ?? 0); ?>%</strong>
          <span>taxa de conclusão das suas tarefas</span>
        </div>
        <div class="dashboard-highlight-row">
          <strong><?php echo (int) $receivedMaterials; ?></strong>
          <span>materiais disponíveis para consulta</span>
        </div>
        <p class="dashboard-highlight-note mb-0">Ritmo constante também é cuidado. Siga no seu tempo, com apoio e organização.</p>
      </aside>
    </div>
  </section>

  <?php if (!empty($activeSubscription)): ?>
    <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
      <span><i class="fa-solid fa-crown me-1"></i>Assinatura ativa: <strong><?php echo htmlspecialchars((string) ($activeSubscription['plan_name'] ?? 'Plano')); ?></strong></span>
      <?php if (!empty($activeSubscription['ends_at'])): ?><small>Válida até <?php echo date('d/m/Y', strtotime((string) $activeSubscription['ends_at'])); ?></small><?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="dashboard-metric-grid dashboard-metric-grid--compact">
    <article class="dashboard-kpi-card dashboard-kpi-card--peach">
      <span class="dashboard-kpi-label"><i class="fa-solid fa-calendar-day"></i>Dias desde o cadastro</span>
      <h2><?php echo (int) $daysSinceRegister; ?></h2>
      <p>Tempo de caminhada dentro da plataforma.</p>
    </article>
    <article class="dashboard-kpi-card dashboard-kpi-card--mint">
      <span class="dashboard-kpi-label"><i class="fa-solid fa-heart-pulse"></i>Sessões realizadas</span>
      <h2><?php echo (int) $sessionsDone; ?></h2>
      <p>Encontros terapêuticos já vividos por você.</p>
    </article>
    <article class="dashboard-kpi-card dashboard-kpi-card--sky">
      <span class="dashboard-kpi-label"><i class="fa-solid fa-bell"></i>Próxima sessão</span>
      <h2 class="dashboard-kpi-text"><?php echo htmlspecialchars($nextLabel); ?></h2>
      <p>Mantenha esse momento no radar.</p>
    </article>
    <article class="dashboard-kpi-card dashboard-kpi-card--sun">
      <span class="dashboard-kpi-label"><i class="fa-solid fa-list-check"></i>Tarefas pendentes</span>
      <h2><?php echo (int) $receivedTasks; ?></h2>
      <p>Concluídas: <?php echo (int) ($doneTasks ?? 0); ?> | Materiais: <?php echo (int) $receivedMaterials; ?></p>
    </article>
  </div>

  <div class="row g-4 align-items-stretch">
    <div class="col-12 col-xxl-8">
      <section class="card dashboard-chart-card h-100">
        <div class="card-body p-4">
          <div class="dashboard-section-head mb-3">
            <div>
              <span class="dashboard-section-kicker">Minha evolução</span>
              <h5 class="mb-1">Sessões e tarefas ao longo do tempo</h5>
              <p class="mb-0 text-muted">Uma leitura simples para enxergar sua consistência e seu ritmo.</p>
            </div>
          </div>
          <canvas id="patientTrendChart" height="110"></canvas>
        </div>
      </section>
    </div>
    <div class="col-12 col-xxl-4">
      <section class="card dashboard-section-card h-100">
        <div class="card-body p-4 d-grid gap-3">
          <div>
            <span class="dashboard-section-kicker">Constância</span>
            <h5 class="mb-1">Taxa de conclusão</h5>
            <p class="mb-0 text-muted">Cada pequeno passo conta no seu processo.</p>
          </div>
          <div class="dashboard-progress-card">
            <div class="dashboard-progress-value"><?php echo (int) ($completionRate ?? 0); ?>%</div>
            <div class="progress dashboard-progress-bar" role="progressbar" aria-valuenow="<?php echo (int) ($completionRate ?? 0); ?>" aria-valuemin="0" aria-valuemax="100">
              <div class="progress-bar" style="width: <?php echo (int) ($completionRate ?? 0); ?>%;"></div>
            </div>
          </div>
          <div class="dashboard-actions-grid">
            <a class="dashboard-action-tile" href="<?php echo $appUrl; ?>/patient.php?action=my-contents">
              <i class="fa-solid fa-bookmark"></i>
              <strong>Meus conteúdos</strong>
              <span>Retome materiais, vídeos e leituras salvos.</span>
            </a>
            <a class="dashboard-action-tile" href="<?php echo $appUrl; ?>/patient.php?action=devotionals">
              <i class="fa-solid fa-sun"></i>
              <strong>Devocional</strong>
              <span>Acesse reflexões e registros do seu dia.</span>
            </a>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
<script>
window.addEventListener('load', function () {
  if (typeof Chart === 'undefined') return;
  var labels = <?php echo json_encode($chartLabels ?? []); ?>;
  var sessions = <?php echo json_encode($chartSessions ?? []); ?>;
  var tasksDone = <?php echo json_encode($chartTasksDone ?? []); ?>;
  var el = document.getElementById('patientTrendChart');
  if (!el) return;

  new Chart(el, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        { label: 'Sessões', data: sessions, borderColor: '#4d908e', backgroundColor: 'rgba(77, 144, 142, .14)', fill: true, tension: .35 },
        { label: 'Tarefas concluídas', data: tasksDone, borderColor: '#f9844a', backgroundColor: 'rgba(249, 132, 74, .12)', fill: true, tension: .35 }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          labels: {
            usePointStyle: true,
            boxWidth: 10
          }
        }
      },
      scales: {
        y: { beginAtZero: true, grid: { color: 'rgba(38, 70, 83, .08)' } },
        x: { grid: { display: false } }
      }
    }
  });
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
