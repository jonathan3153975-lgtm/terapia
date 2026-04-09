<?php $title = 'Editar compromisso'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Editar compromisso</h4>
    <a class="btn btn-light" href="<?php echo $backUrl; ?>">Voltar para agenda</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-schedule-update">
        <input type="hidden" name="id" value="<?php echo (int) $appointment['id']; ?>">
        <input type="hidden" name="view_mode" value="<?php echo htmlspecialchars((string) $viewMode); ?>">
        <input type="hidden" name="date" value="<?php echo htmlspecialchars((string) $date); ?>">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Data e hora</label>
            <input class="form-control" type="datetime-local" name="appointment_at" value="<?php echo date('Y-m-d\\TH:i', strtotime((string) $appointment['session_date'])); ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Paciente cadastrado</label>
            <select class="form-select" name="patient_id" id="patientSelectEdit">
              <option value="0">Sem paciente cadastrado</option>
              <?php foreach ($patients as $patient): ?>
                <option value="<?php echo (int) $patient['id']; ?>" <?php echo ((int) ($appointment['patient_id'] ?? 0) === (int) $patient['id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars((string) $patient['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Novo paciente (sem cadastro)</label>
            <input class="form-control" type="text" name="new_patient_name" id="newPatientEdit" maxlength="150" value="<?php echo htmlspecialchars((string) ($appointment['guest_patient_name'] ?? '')); ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Descrição</label>
            <input class="form-control" type="text" name="description" maxlength="255" value="<?php echo htmlspecialchars((string) ($appointment['description'] ?? '')); ?>">
          </div>

          <div class="col-12">
            <label class="form-label">Histórico</label>
            <textarea class="form-control" name="history" rows="6"><?php echo htmlspecialchars((string) ($appointment['history'] ?? '')); ?></textarea>
          </div>
        </div>

        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary" type="submit">Salvar alterações</button>
          <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-schedule-show&id=<?php echo (int) $appointment['id']; ?>&view=<?php echo urlencode((string) $viewMode); ?>&date=<?php echo urlencode((string) $date); ?>">Ver detalhes</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var patientSelect = document.getElementById('patientSelectEdit');
  var newPatientInput = document.getElementById('newPatientEdit');

  if (!patientSelect || !newPatientInput) {
    return;
  }

  patientSelect.addEventListener('change', function () {
    if (patientSelect.value !== '0') {
      newPatientInput.value = '';
    }
  });

  newPatientInput.addEventListener('input', function () {
    if (newPatientInput.value.trim() !== '') {
      patientSelect.value = '0';
    }
  });
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
