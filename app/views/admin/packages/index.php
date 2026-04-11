<?php $title = 'Pacotes de Pacientes'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Pacotes de assinatura (paciente)</h3>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patient-packages-store" class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Nome do pacote</label>
          <input type="text" class="form-control" name="name" required maxlength="100" placeholder="Ex.: Acompanhamento Essencial">
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
          <select class="form-select" name="billing_cycle" required>
            <option value="mensal">Mensal</option>
            <option value="semestral">Semestral</option>
            <option value="anual">Anual</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Valor (R$)</label>
          <input type="number" class="form-control" name="price" min="0.01" step="0.01" required>
        </div>
        <div class="col-12">
          <label class="form-label">Descrição</label>
          <textarea class="form-control" rows="3" name="description_text" maxlength="1000" placeholder="Descrição do que o paciente terá acesso."></textarea>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Cadastrar pacote</button>
        </div>
      </form>
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
                  <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patient-packages-toggle-status" class="m-0">
                    <input type="hidden" name="id" value="<?php echo (int) ($package['id'] ?? 0); ?>">
                    <button class="btn btn-sm <?php echo $isActive ? 'btn-outline-danger' : 'btn-outline-success'; ?>" type="submit">
                      <?php echo $isActive ? 'Desativar' : 'Ativar'; ?>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
