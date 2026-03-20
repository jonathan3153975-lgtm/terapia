<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Agendamento - <?php echo \Config\Config::APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?php echo \Config\Config::APP_URL; ?>/public/css/dashboard.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-container">
    <?php $activeMenu = 'appointments'; include __DIR__ . '/../../partials/sidebar.php'; ?>

    <div class="main-content">
        <header class="topbar">
            <button class="btn-menu" id="menuBtn"><i class="fas fa-bars"></i></button>
            <div class="topbar-actions">
                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=show&id=<?php echo $appointment['id']; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </header>

        <div class="page-content">
            <div class="page-header">
                <h1>Editar Agendamento</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=calendar">Agenda</a></li>
                        <li class="breadcrumb-item active">Editar #<?php echo $appointment['id']; ?></li>
                    </ol>
                </nav>
            </div>

            <div class="card" style="max-width:700px">
                <form id="editAppointmentForm" method="POST"
                      action="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=update">
                    <input type="hidden" name="id" value="<?php echo $appointment['id']; ?>">

                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-edit me-2"></i>Dados do Agendamento
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="patient_id" class="form-label">Paciente *</label>
                                <select id="patient_id" name="patient_id" class="form-select" required>
                                    <option value="">-- Selecione --</option>
                                    <?php foreach ($patients as $p): ?>
                                    <option value="<?php echo $p['id']; ?>"
                                        <?php echo $p['id'] == $appointment['patient_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['name']); ?> (<?php echo htmlspecialchars($p['cpf']); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="appointment_date" class="form-label">Data *</label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date"
                                       value="<?php echo date('Y-m-d', strtotime($appointment['appointment_date'])); ?>"
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="appointment_time" class="form-label">Horário *</label>
                                <input type="time" class="form-control" id="appointment_time" name="appointment_time"
                                       value="<?php echo date('H:i', strtotime($appointment['appointment_date'])); ?>"
                                       required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select id="status" name="status" class="form-select" required>
                                    <?php
                                    $statuses = [
                                        'confirmed' => 'Confirmado',
                                        'pending'   => 'Pendente',
                                        'completed' => 'Concluído',
                                        'cancelled' => 'Cancelado',
                                    ];
                                    foreach ($statuses as $val => $lbl): ?>
                                    <option value="<?php echo $val; ?>" <?php echo $appointment['status'] === $val ? 'selected' : ''; ?>>
                                        <?php echo $lbl; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Observações</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"
                                      placeholder="Informações adicionais..."><?php echo htmlspecialchars($appointment['notes'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="card-footer d-flex gap-2 justify-content-end">
                        <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=calendar" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo \Config\Config::APP_URL; ?>/public/js/main.js"></script>
<script>
    document.getElementById('editAppointmentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitFormAjax(this);
    });
</script>
</body>
</html>
