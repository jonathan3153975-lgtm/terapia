<?php $title = 'Agenda'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0">Agenda do terapeuta</h3>
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <div class="btn-group" role="group" aria-label="Visualizacao da agenda">
        <a class="btn btn-outline-primary <?php echo ($viewMode === 'week') ? 'active' : ''; ?>" href="<?php echo $weekViewUrl; ?>">Semana</a>
        <a class="btn btn-outline-primary <?php echo ($viewMode === 'day') ? 'active' : ''; ?>" href="<?php echo $dayViewUrl; ?>">Dia</a>
      </div>
      <a class="btn btn-outline-secondary" href="<?php echo $previousUrl; ?>"><i class="fa-solid fa-chevron-left"></i></a>
      <button class="btn btn-light" type="button" disabled><?php echo htmlspecialchars((string) $monthLabel); ?></button>
      <a class="btn btn-outline-secondary" href="<?php echo $nextUrl; ?>"><i class="fa-solid fa-chevron-right"></i></a>
    </div>
  </div>

  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="row g-3">
    <div class="col-xl-8">
      <div class="card schedule-card">
        <div class="card-body p-2 p-md-3">
          <?php if ($viewMode === 'week'): ?>
            <div class="schedule-week-vertical">
              <?php foreach ($calendarWeekDays as $day): ?>
                <div class="schedule-day-card <?php echo !empty($day['isToday']) ? 'schedule-day-card-today' : ''; ?>">
                  <div class="schedule-day-card-head">
                    <div class="schedule-day-title"><?php echo htmlspecialchars((string) $day['dayLabel']); ?></div>
                    <div class="schedule-day-number"><?php echo (int) $day['day']; ?></div>
                    <div class="schedule-week-date-small"><?php echo date('d/m/Y', strtotime((string) $day['date'])); ?></div>
                  </div>
                  <div class="schedule-day-card-body">
                    <?php if (empty($day['appointments'])): ?>
                      <div class="text-muted small">Sem atendimentos</div>
                    <?php else: ?>
                      <?php foreach ($day['appointments'] as $appointment): ?>
                        <?php
                          $hour = date('H:i', strtotime((string) $appointment['session_date']));
                          $patientName = (string) ($appointment['patient_name'] ?? '');
                          if ($patientName === '') {
                              $patientName = (string) ($appointment['guest_patient_name'] ?? 'Paciente sem cadastro');
                          }
                          $showUrl = $appUrl . '/dashboard.php?action=therapist-schedule-show&id=' . (int) $appointment['id'] . '&view=' . urlencode((string) $viewMode) . '&date=' . urlencode((string) $selectedDate);
                          $editUrl = $appUrl . '/dashboard.php?action=therapist-schedule-edit&id=' . (int) $appointment['id'] . '&view=' . urlencode((string) $viewMode) . '&date=' . urlencode((string) $selectedDate);
                        ?>
                        <div class="schedule-line-item" title="<?php echo htmlspecialchars((string) ($appointment['description'] ?? '')); ?>">
                          <div class="schedule-line-main">
                            <span class="schedule-item-hour"><?php echo $hour; ?></span>
                            <span class="schedule-item-name"><?php echo htmlspecialchars($patientName); ?></span>
                          </div>
                          <div class="schedule-line-actions">
                            <a class="btn btn-sm btn-outline-secondary schedule-icon-btn" href="<?php echo $showUrl; ?>" title="Visualizar">
                              <i class="fa-solid fa-eye"></i>
                            </a>
                            <a class="btn btn-sm btn-outline-primary schedule-icon-btn" href="<?php echo $editUrl; ?>" title="Editar">
                              <i class="fa-solid fa-pen"></i>
                            </a>
                            <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-schedule-delete" onsubmit="return confirm('Excluir este compromisso?');">
                              <input type="hidden" name="id" value="<?php echo (int) $appointment['id']; ?>">
                              <input type="hidden" name="view_mode" value="<?php echo htmlspecialchars((string) $viewMode); ?>">
                              <input type="hidden" name="date" value="<?php echo htmlspecialchars((string) $selectedDate); ?>">
                              <button class="btn btn-sm btn-outline-danger schedule-icon-btn" type="submit" title="Excluir">
                                <i class="fa-solid fa-trash"></i>
                              </button>
                            </form>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="schedule-day-view">
              <?php $hasDayAppointments = false; ?>
              <?php foreach ($calendarDayHours as $hourSlot): ?>
                <?php if (empty($hourSlot['appointments'])) { continue; } ?>
                <?php $hasDayAppointments = true; ?>
                <div class="schedule-hour-row <?php echo !empty($hourSlot['isCurrentHour']) ? 'schedule-hour-row-now' : ''; ?>">
                  <div class="schedule-hour-label"><?php echo htmlspecialchars((string) $hourSlot['label']); ?></div>
                  <div>
                    <?php foreach ($hourSlot['appointments'] as $appointment): ?>
                      <?php
                        $patientName = (string) ($appointment['patient_name'] ?? '');
                        if ($patientName === '') {
                            $patientName = (string) ($appointment['guest_patient_name'] ?? 'Paciente sem cadastro');
                        }
                        $showUrl = $appUrl . '/dashboard.php?action=therapist-schedule-show&id=' . (int) $appointment['id'] . '&view=' . urlencode((string) $viewMode) . '&date=' . urlencode((string) $selectedDate);
                        $editUrl = $appUrl . '/dashboard.php?action=therapist-schedule-edit&id=' . (int) $appointment['id'] . '&view=' . urlencode((string) $viewMode) . '&date=' . urlencode((string) $selectedDate);
                      ?>
                      <div class="schedule-line-item mb-1" title="<?php echo htmlspecialchars((string) ($appointment['description'] ?? '')); ?>">
                        <div class="schedule-line-main">
                          <span class="schedule-item-name"><?php echo htmlspecialchars($patientName); ?></span>
                        </div>
                        <div class="schedule-line-actions">
                          <a class="btn btn-sm btn-outline-secondary schedule-icon-btn" href="<?php echo $showUrl; ?>" title="Visualizar">
                            <i class="fa-solid fa-eye"></i>
                          </a>
                          <a class="btn btn-sm btn-outline-primary schedule-icon-btn" href="<?php echo $editUrl; ?>" title="Editar">
                            <i class="fa-solid fa-pen"></i>
                          </a>
                          <form method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=therapist-schedule-delete" onsubmit="return confirm('Excluir este compromisso?');">
                            <input type="hidden" name="id" value="<?php echo (int) $appointment['id']; ?>">
                            <input type="hidden" name="view_mode" value="<?php echo htmlspecialchars((string) $viewMode); ?>">
                            <input type="hidden" name="date" value="<?php echo htmlspecialchars((string) $selectedDate); ?>">
                            <button class="btn btn-sm btn-outline-danger schedule-icon-btn" type="submit" title="Excluir">
                              <i class="fa-solid fa-trash"></i>
                            </button>
                          </form>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endforeach; ?>
              <?php if (!$hasDayAppointments): ?>
                <div class="alert alert-light border mb-0">Nenhum atendimento registrado neste dia.</div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
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
            <input type="hidden" name="view_mode" value="<?php echo htmlspecialchars((string) $viewMode); ?>">
            <input type="hidden" name="date" value="<?php echo htmlspecialchars((string) $selectedDate); ?>">

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

            <button class="btn btn-primary w-100" type="submit" id="scheduleSubmitButton">Salvar compromisso</button>
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
  var scheduleForm = document.getElementById('scheduleForm');
  var scheduleSubmitButton = document.getElementById('scheduleSubmitButton');
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

  if (scheduleForm && scheduleSubmitButton) {
    scheduleForm.addEventListener('submit', function () {
      scheduleSubmitButton.disabled = true;
      scheduleSubmitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Salvando...';
    });
  }
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
