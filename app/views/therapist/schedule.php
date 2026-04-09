<?php $title = 'Agenda'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0">Agenda do terapeuta</h3>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-schedule&month=<?php echo (int) $previousMonth; ?>&year=<?php echo (int) $previousYear; ?>">
        <i class="fa-solid fa-chevron-left"></i>
      </a>
      <button class="btn btn-light" type="button" disabled><?php echo htmlspecialchars((string) $monthLabel); ?></button>
      <a class="btn btn-outline-secondary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-schedule&month=<?php echo (int) $nextMonth; ?>&year=<?php echo (int) $nextYear; ?>">
        <i class="fa-solid fa-chevron-right"></i>
      </a>
    </div>
  </div>

  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="row g-3">
    <div class="col-xl-8">
      <div class="card schedule-card">
        <div class="card-body p-2 p-md-3">
          <div class="schedule-grid-head">
            <div>Seg</div>
            <div>Ter</div>
            <div>Qua</div>
            <div>Qui</div>
            <div>Sex</div>
            <div>Sáb</div>
            <div>Dom</div>
          </div>

          <div class="schedule-grid-body">
            <?php foreach ($calendarWeeks as $week): ?>
              <?php foreach ($week as $day): ?>
                <?php if ($day === null): ?>
                  <div class="schedule-day schedule-day-empty"></div>
                <?php else: ?>
                  <div class="schedule-day <?php echo !empty($day['isToday']) ? 'schedule-day-today' : ''; ?>">
                    <div class="schedule-day-number"><?php echo (int) $day['day']; ?></div>
                    <div class="schedule-day-list">
                      <?php foreach ($day['appointments'] as $appointment): ?>
                        <?php
                          $hour = date('H:i', strtotime((string) $appointment['session_date']));
                          $patientName = (string) ($appointment['patient_name'] ?? '');
                          if ($patientName === '') {
                              $patientName = (string) ($appointment['guest_patient_name'] ?? 'Paciente sem cadastro');
                          }
                        ?>
                        <div class="schedule-item" title="<?php echo htmlspecialchars((string) ($appointment['description'] ?? '')); ?>">
                          <span class="schedule-item-hour"><?php echo $hour; ?></span>
                          <span class="schedule-item-name"><?php echo htmlspecialchars($patientName); ?></span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="card schedule-form-card">
        <div class="card-body">
          <h5 class="mb-3">Novo compromisso</h5>
          <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-schedule-store" id="scheduleForm">
            <input type="hidden" name="month" value="<?php echo (int) $month; ?>">
            <input type="hidden" name="year" value="<?php echo (int) $year; ?>">

            <div class="mb-3">
              <label class="form-label">Data e hora</label>
              <input class="form-control" type="datetime-local" name="appointment_at" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Buscar paciente cadastrado</label>
              <input class="form-control" list="patientsList" id="patientSearchInput" placeholder="Digite o nome do paciente">
              <datalist id="patientsList">
                <?php foreach ($patients as $patient): ?>
                  <option data-id="<?php echo (int) $patient['id']; ?>" value="<?php echo htmlspecialchars((string) $patient['name']); ?>"></option>
                <?php endforeach; ?>
              </datalist>
              <input type="hidden" name="patient_id" id="patientIdInput" value="">
              <div class="form-text">Ao selecionar da lista, o campo abaixo fica opcional.</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Ou novo paciente (sem cadastro)</label>
              <input class="form-control" type="text" name="new_patient_name" id="newPatientInput" maxlength="150" placeholder="Nome do paciente sem cadastro">
            </div>

            <div class="mb-3">
              <label class="form-label">Descrição</label>
              <input class="form-control" type="text" name="description" maxlength="255" placeholder="Ex.: Sessão inicial">
            </div>

            <button class="btn btn-primary w-100" type="submit">Salvar compromisso</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var patientSearchInput = document.getElementById('patientSearchInput');
  var patientIdInput = document.getElementById('patientIdInput');
  var newPatientInput = document.getElementById('newPatientInput');
  var options = document.querySelectorAll('#patientsList option');

  if (!patientSearchInput || !patientIdInput || !newPatientInput) {
    return;
  }

  patientSearchInput.addEventListener('input', function () {
    var selectedId = '';
    var typedValue = patientSearchInput.value.trim();

    options.forEach(function (option) {
      if (option.value === typedValue) {
        selectedId = option.dataset.id || '';
      }
    });

    patientIdInput.value = selectedId;
    if (selectedId !== '') {
      newPatientInput.value = '';
    }
  });

  newPatientInput.addEventListener('input', function () {
    if (newPatientInput.value.trim() !== '') {
      patientSearchInput.value = '';
      patientIdInput.value = '';
    }
  });
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
