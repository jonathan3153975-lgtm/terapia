<?php $title = 'Pacotes de Pacientes'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Pacotes de assinatura (paciente)</h3>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-lg-8">
      <div class="card h-100">
    <div class="card-body">
      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patient-packages-store" class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Nome do pacote</label>
          <input type="text" class="form-control" name="name" id="pkgNameInput" required maxlength="100" placeholder="Ex.: Acompanhamento Essencial">
        </div>
        <div class="col-md-3">
          <label class="form-label">Terapeuta vinculado</label>
          <select class="form-select" name="therapist_id" required>
            <option value="">Selecione</option>
            <?php foreach (($therapists ?? []) as $therapist): ?>
              <option value="<?php echo (int) ($therapist['id'] ?? 0); ?>"><?php echo htmlspecialchars((string) ($therapist['name'] ?? '')); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Categoria</label>
          <select class="form-select" name="billing_cycle" id="pkgCycleInput" required>
            <option value="mensal">Mensal</option>
            <option value="semestral">Semestral</option>
            <option value="anual">Anual</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Valor (R$)</label>
          <input type="number" class="form-control" name="price" id="pkgPriceInput" min="0.01" step="0.01" required>
        </div>
        <div class="col-12">
          <label class="form-label">Descrição</label>
          <textarea class="form-control" rows="3" name="description_text" id="pkgDescInput" maxlength="1000" placeholder="Descrição do que o paciente terá acesso."></textarea>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Cadastrar pacote</button>
        </div>
      </form>
    </div>
  </div>
    </div>

    <div class="col-lg-4">
      <article class="card h-100 patient-subscription-card" id="packagePreviewCard">
        <div class="card-body d-flex flex-column">
          <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
            <h5 class="mb-0" id="previewName">Pacote de exemplo</h5>
            <span class="badge text-bg-light border" id="previewCycle">Mensal</span>
          </div>
          <div class="patient-subscription-price mb-2" id="previewPrice">R$ 0,00</div>
          <p class="text-muted small mb-3" id="previewDescription">Pré-visualização do card que será exibido ao paciente.</p>
          <ul class="small text-muted mb-0 ps-3">
            <li>Conteúdo completo liberado</li>
            <li>Vinculado ao terapeuta selecionado</li>
            <li>Vigência calculada automaticamente</li>
          </ul>
        </div>
      </article>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>Pacote</th>
            <th>Terapeuta</th>
            <th>Categoria</th>
            <th>Valor</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($packages)): ?>
            <tr><td colspan="6" class="text-center py-4 text-muted">Nenhum pacote cadastrado.</td></tr>
          <?php else: ?>
            <?php foreach ($packages as $package): ?>
              <?php $isActive = (int) ($package['is_active'] ?? 0) === 1; ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?php echo htmlspecialchars((string) ($package['name'] ?? '')); ?></div>
                  <?php if (!empty($package['description_text'])): ?>
                    <small class="text-muted"><?php echo nl2br(htmlspecialchars((string) $package['description_text'])); ?></small>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars((string) ($package['therapist_name'] ?? '-')); ?></td>
                <td><?php echo htmlspecialchars(ucfirst((string) ($package['billing_cycle'] ?? 'mensal'))); ?></td>
                <td>R$ <?php echo number_format((float) ($package['price'] ?? 0), 2, ',', '.'); ?></td>
                <td>
                  <span class="badge <?php echo $isActive ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                    <?php echo $isActive ? 'Ativo' : 'Inativo'; ?>
                  </span>
                </td>
                <td>
                  <div class="d-flex gap-1">
                    <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patient-packages-toggle-status" class="m-0">
                    <input type="hidden" name="id" value="<?php echo (int) ($package['id'] ?? 0); ?>">
                    <button class="btn btn-sm <?php echo $isActive ? 'btn-outline-danger' : 'btn-outline-success'; ?>" type="submit">
                      <?php echo $isActive ? 'Desativar' : 'Ativar'; ?>
                    </button>
                  </form>
                    <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patient-packages-delete" class="m-0">
                      <input type="hidden" name="id" value="<?php echo (int) ($package['id'] ?? 0); ?>">
                      <button class="btn btn-sm btn-outline-dark" type="submit">Excluir</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
window.addEventListener('load', function () {
  var nameInput = document.getElementById('pkgNameInput');
  var cycleInput = document.getElementById('pkgCycleInput');
  var priceInput = document.getElementById('pkgPriceInput');
  var descInput = document.getElementById('pkgDescInput');
  var previewName = document.getElementById('previewName');
  var previewCycle = document.getElementById('previewCycle');
  var previewPrice = document.getElementById('previewPrice');
  var previewDescription = document.getElementById('previewDescription');

  var update = function () {
    previewName.textContent = (nameInput && nameInput.value.trim()) ? nameInput.value.trim() : 'Pacote de exemplo';
    previewCycle.textContent = (cycleInput && cycleInput.value) ? (cycleInput.value.charAt(0).toUpperCase() + cycleInput.value.slice(1)) : 'Mensal';

    var rawPrice = priceInput && priceInput.value ? Number(priceInput.value) : 0;
    previewPrice.textContent = 'R$ ' + rawPrice.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    previewDescription.textContent = (descInput && descInput.value.trim()) ? descInput.value.trim() : 'Pré-visualização do card que será exibido ao paciente.';
  };

  [nameInput, cycleInput, priceInput, descInput].forEach(function (el) {
    if (el) {
      el.addEventListener('input', update);
      el.addEventListener('change', update);
    }
  });

  update();
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
