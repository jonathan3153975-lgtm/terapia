<?php $title = 'Minha assinatura'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
    <h3 class="mb-0">Escolha seu plano</h3>
    <?php if (!empty($latestSubscription) && ($latestSubscription['status'] ?? '') === 'active'): ?>
      <a class="btn btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=dashboard">Acessar conteúdo</a>
    <?php endif; ?>
  </div>

  <?php if (!($mercadoPagoConfigured ?? false)): ?>
    <div class="alert alert-warning">
      O checkout está temporariamente indisponível. Contate o suporte para ativar sua assinatura.
    </div>
  <?php endif; ?>

  <?php if (!empty($latestSubscription)): ?>
    <div class="card mb-3">
      <div class="card-body">
        <h6 class="mb-2">Última assinatura</h6>
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <span class="badge text-bg-secondary"><?php echo htmlspecialchars((string) ($latestSubscription['plan_name'] ?? '-')); ?></span>
          <span class="badge text-bg-light border"><?php echo htmlspecialchars((string) ucfirst((string) ($latestSubscription['billing_cycle'] ?? 'mensal'))); ?></span>
          <span class="badge <?php echo (($latestSubscription['status'] ?? '') === 'active') ? 'text-bg-success' : 'text-bg-warning'; ?>">
            <?php echo htmlspecialchars((string) ucfirst((string) ($latestSubscription['status'] ?? 'pending'))); ?>
          </span>
          <?php if (!empty($latestSubscription['ends_at'])): ?>
            <span class="small text-muted">Válida até <?php echo date('d/m/Y', strtotime((string) $latestSubscription['ends_at'])); ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <?php if (empty($plans)): ?>
      <div class="col-12">
        <div class="card">
          <div class="card-body text-center text-muted py-5">
            Nenhum plano disponível para o seu terapeuta no momento.
          </div>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($plans as $plan): ?>
        <div class="col-12 col-lg-4">
          <article class="card h-100 patient-subscription-card">
            <div class="card-body d-flex flex-column">
              <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <h5 class="mb-0"><?php echo htmlspecialchars((string) ($plan['name'] ?? 'Plano')); ?></h5>
                <span class="badge text-bg-light border"><?php echo htmlspecialchars(ucfirst((string) ($plan['billing_cycle'] ?? 'mensal'))); ?></span>
              </div>

              <div class="patient-subscription-price mb-2">
                R$ <?php echo number_format((float) ($plan['price'] ?? 0), 2, ',', '.'); ?>
              </div>

              <?php if (!empty($plan['description_text'])): ?>
                <p class="text-muted small mb-3"><?php echo nl2br(htmlspecialchars((string) $plan['description_text'])); ?></p>
              <?php else: ?>
                <p class="text-muted small mb-3">Acesso completo aos conteúdos, materiais e tarefas com seu terapeuta.</p>
              <?php endif; ?>

              <ul class="small text-muted mb-3 ps-3">
                <li>Conteúdo completo liberado</li>
                <li>Vinculado ao terapeuta <?php echo htmlspecialchars((string) ($plan['therapist_name'] ?? '')); ?></li>
                <li>Vigência calculada automaticamente</li>
              </ul>

              <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=subscription-checkout" class="mt-auto">
                <input type="hidden" name="plan_id" value="<?php echo (int) ($plan['id'] ?? 0); ?>">
                <button class="btn btn-primary w-100" type="submit" <?php echo ($mercadoPagoConfigured ?? false) ? '' : 'disabled'; ?>>
                  Assinar com Mercado Pago
                </button>
              </form>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
