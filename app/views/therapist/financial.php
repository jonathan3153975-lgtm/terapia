<?php $title = 'Financeiro'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0">Financeiro do terapeuta</h3>
    <form method="GET" action="<?php echo $appUrl; ?>/dashboard.php" class="d-flex gap-2 align-items-center">
      <input type="hidden" name="action" value="therapist-financial">
      <select class="form-select" name="month" style="max-width: 130px;">
        <?php for ($m = 1; $m <= 12; $m++): ?>
          <option value="<?php echo $m; ?>" <?php echo ((int) $month === $m) ? 'selected' : ''; ?>><?php echo str_pad((string) $m, 2, '0', STR_PAD_LEFT); ?></option>
        <?php endfor; ?>
      </select>
      <select class="form-select" name="year" style="max-width: 120px;">
        <?php for ($y = ((int) date('Y') - 3); $y <= ((int) date('Y') + 3); $y++): ?>
          <option value="<?php echo $y; ?>" <?php echo ((int) $year === $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
        <?php endfor; ?>
      </select>
      <button class="btn btn-primary" type="submit">Filtrar</button>
    </form>
  </div>

  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="mb-2 text-muted">Referência: <strong><?php echo htmlspecialchars((string) $monthLabel); ?></strong></div>

  <div class="row g-3 mb-3">
    <div class="col-md-6 col-xl-3">
      <div class="card card-kpi"><div class="card-body"><small>Valor recebido</small><h4 class="mb-0">R$ <?php echo number_format((float) $receivedTotal, 2, ',', '.'); ?></h4></div></div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card card-kpi"><div class="card-body"><small>Valor pendente</small><h4 class="mb-0">R$ <?php echo number_format((float) $pendingTotal, 2, ',', '.'); ?></h4></div></div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card card-kpi"><div class="card-body"><small>Consultas marcadas</small><h4 class="mb-0"><?php echo (int) $appointmentsCount; ?></h4></div></div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card card-kpi"><div class="card-body"><small>Prévia de receita</small><h4 class="mb-0">R$ <?php echo number_format((float) $estimatedRevenue, 2, ',', '.'); ?></h4><small class="text-muted">Média: R$ <?php echo number_format((float) $averageTicket, 2, ',', '.'); ?></small></div></div>
    </div>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Data/Hora</th>
              <th>Paciente</th>
              <th>Descrição</th>
              <th>Status</th>
              <th>Valor</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($financialRows)): ?>
              <tr>
                <td colspan="6" class="text-center text-muted py-4">Nenhuma consulta encontrada para o período.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($financialRows as $row): ?>
                <?php
                  $status = (string) ($row['payment_status'] ?? 'pending');
                  $badge = $status === 'paid' ? 'bg-success-subtle text-success-emphasis' : 'bg-warning-subtle text-warning-emphasis';
                  $patientName = trim((string) ($row['patient_name'] ?? ''));
                  if ($patientName === '') {
                      $patientName = trim((string) ($row['guest_patient_name'] ?? ''));
                  }
                  if ($patientName === '') {
                      $patientName = 'Paciente sem cadastro';
                  }
                ?>
                <tr>
                  <td><?php echo date('d/m/Y H:i', strtotime((string) $row['session_date'])); ?></td>
                  <td><?php echo htmlspecialchars($patientName); ?></td>
                  <td><?php echo htmlspecialchars((string) ($row['description'] ?? '-')); ?></td>
                  <td><span class="badge rounded-pill <?php echo $badge; ?>"><?php echo $status === 'paid' ? 'Pago' : 'Pendente'; ?></span></td>
                  <td>R$ <?php echo number_format((float) ($row['amount'] ?? 0), 2, ',', '.'); ?></td>
                  <td>
                    <div class="d-grid gap-2">
                      <form class="row g-2" method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-financial-update">
                        <input type="hidden" name="month" value="<?php echo (int) $month; ?>">
                        <input type="hidden" name="year" value="<?php echo (int) $year; ?>">
                        <input type="hidden" name="appointment_id" value="<?php echo (int) $row['appointment_id']; ?>">
                        <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars((string) ($row['patient_id'] ?? '')); ?>">
                        <div class="col-12 col-xl-4">
                          <input class="form-control form-control-sm" type="number" step="0.01" min="0" name="amount" value="<?php echo number_format((float) ($row['amount'] ?? 0), 2, '.', ''); ?>" required>
                        </div>
                        <div class="col-12 col-xl-4">
                          <select class="form-select form-select-sm" name="status">
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pendente</option>
                            <option value="paid" <?php echo $status === 'paid' ? 'selected' : ''; ?>>Pago</option>
                          </select>
                        </div>
                        <div class="col-12 col-xl-4">
                          <button class="btn btn-sm btn-outline-primary w-100" type="submit">Salvar</button>
                        </div>
                      </form>

                      <?php if ($status === 'pending' && (float) ($row['amount'] ?? 0) > 0): ?>
                        <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-financial-confirm">
                          <input type="hidden" name="month" value="<?php echo (int) $month; ?>">
                          <input type="hidden" name="year" value="<?php echo (int) $year; ?>">
                          <input type="hidden" name="appointment_id" value="<?php echo (int) $row['appointment_id']; ?>">
                          <button class="btn btn-sm btn-success" type="submit">Confirmar pagamento</button>
                        </form>
                      <?php endif; ?>
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
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
