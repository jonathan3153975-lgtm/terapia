<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Paciente - <?php echo \Config\Config::APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?php echo \Config\Config::APP_URL; ?>/public/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span>Terapia</span>
                </div>
                <button class="btn-menu-toggle" id="menuToggle">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=dashboard" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients" class="nav-link active">
                        <i class="fas fa-users"></i>
                        <span>Pacientes</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=calendar" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Agenda</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=payments" class="nav-link">
                        <i class="fas fa-wallet"></i>
                        <span>Valores</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports" class="nav-link">
                        <i class="fas fa-file-chart-line"></i>
                        <span>Relatórios</span>
                    </a>
                </li>

                <li class="nav-divider"></li>

                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/index.php?action=logout" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Sair</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <p class="user-name"><?php echo \Helpers\Auth::userName(); ?></p>
                        <p class="user-role">Administrador</p>
                    </div>
                </div>
            </div>
        </nav>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- TOPBAR -->
            <header class="topbar">
                <button class="btn-menu" id="menuBtn">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="topbar-actions">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=edit&id=<?php echo $patient['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <div class="page-content">
                <div class="page-header">
                    <h1>Detalhes do Paciente</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=dashboard">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients">Pacientes</a></li>
                            <li class="breadcrumb-item active"><?php echo htmlspecialchars($patient['name']); ?></li>
                        </ol>
                    </nav>
                </div>

                <?php
                    $birthDate = $patient['birth_date'] ?? '';
                    $ageDisplay = '';
                    if ($birthDate) {
                        $today = new DateTime();
                        $born  = new DateTime($birthDate);
                        $ageDisplay = $today->diff($born)->y . ' anos';
                    }
                    $msLabels = [
                        'solteiro' => 'Solteiro(a)',
                        'casado' => 'Casado(a)',
                        'divorciado' => 'Divorciado(a)',
                        'viuvo' => 'Viúvo(a)',
                        'uniao_estavel' => 'União Estável',
                        'separado' => 'Separado(a)'
                    ];
                ?>
                <!-- Patient Details Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informações Pessoais</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nome Completo</label>
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($patient['name']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">CPF</label>
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($patient['cpf']); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Data de Nascimento</label>
                                    <p class="form-control-plaintext"><?php echo $birthDate ? date('d/m/Y', strtotime($birthDate)) : '—'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Idade</label>
                                    <p class="form-control-plaintext"><?php echo $ageDisplay ?: '—'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Telefone</label>
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($patient['phone']); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">E-mail</label>
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($patient['email'] ?: 'Não informado'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Estado Civil</label>
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($msLabels[$patient['marital_status'] ?? ''] ?? ($patient['marital_status'] ?: 'Não informado')); ?></p>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($patient['children'])): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Filhos</label>
                                    <p class="form-control-plaintext"><?php echo nl2br(htmlspecialchars($patient['children'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Endereço</label>
                            <p class="form-control-plaintext">
                                <?php echo htmlspecialchars($patient['address']); ?>, <?php echo htmlspecialchars($patient['number']); ?>
                                <?php if ($patient['complement']): ?>(<?php echo htmlspecialchars($patient['complement']); ?>)<?php endif; ?><br>
                                <?php echo htmlspecialchars($patient['neighborhood']); ?>, <?php echo htmlspecialchars($patient['city']); ?> - <?php echo htmlspecialchars($patient['state']); ?><br>
                                CEP: <?php echo htmlspecialchars($patient['cep']); ?>
                            </p>
                        </div>

                        <?php if ($patient['observations']): ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Observações</label>
                            <p class="form-control-plaintext"><?php echo nl2br(htmlspecialchars($patient['observations'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Saúde -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-heartbeat me-2"></i>Saúde</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Depressão</label>
                                    <p class="form-control-plaintext">
                                        <?php if (!empty($patient['depression'])): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-check"></i> Sim</span>
                                        <?php else: ?>
                                            <span class="text-muted">Não</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ansiedade</label>
                                    <p class="form-control-plaintext">
                                        <?php if (!empty($patient['anxiety'])): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-check"></i> Sim</span>
                                        <?php else: ?>
                                            <span class="text-muted">Não</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($patient['medications'])): ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Medicamentos</label>
                            <p class="form-control-plaintext"><?php echo nl2br(htmlspecialchars($patient['medications'])); ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="row">
                            <?php if (!empty($patient['bowel'])): ?>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Intestino</label>
                                    <p class="form-control-plaintext"><?php echo nl2br(htmlspecialchars($patient['bowel'])); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($patient['menstruation'])): ?>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Menstruação</label>
                                    <p class="form-control-plaintext"><?php echo nl2br(htmlspecialchars($patient['menstruation'])); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Histórico Terapêutico -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-brain me-2"></i>Histórico Terapêutico</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Já fez terapia?</label>
                                    <p class="form-control-plaintext">
                                        <?php if (!empty($patient['had_therapy'])): ?>
                                            <span class="badge bg-success"><i class="fas fa-check"></i> Sim</span>
                                        <?php else: ?>
                                            <span class="text-muted">Não</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <?php if (!empty($patient['had_therapy']) && !empty($patient['therapy_duration'])): ?>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Quanto tempo?</label>
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($patient['therapy_duration']); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($patient['therapy_reason'])): ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">O que fez buscar terapia?</label>
                            <p class="form-control-plaintext"><?php echo nl2br(htmlspecialchars($patient['therapy_reason'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Patient Records -->
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Registros do Paciente</h5>
                        <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=records&subaction=create&patient_id=<?php echo $patient['id']; ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Novo Registro
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($records)): ?>
                        <p class="text-muted">Nenhum registro encontrado.</p>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Resumo do Atendimento</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($records as $record): ?>
                                    <?php
                                        $notesText = strip_tags($record['notes'] ?? '');
                                        $preview = mb_strlen($notesText) > 80 ? mb_substr($notesText, 0, 80) . '...' : $notesText;
                                    ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($record['record_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($preview ?: '—'); ?></td>
                                        <td>
                                            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=records&subaction=show&id=<?php echo $record['id']; ?>" class="btn btn-sm btn-info" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" title="Excluir"
                                                onclick="deleteRecord(<?php echo $record['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo \Config\Config::APP_URL; ?>/public/js/main.js"></script>
    <script>
    function deleteRecord(id) {
        Swal.fire({
            title: 'Excluir registro?',
            text: 'Esta ação não pode ser desfeita.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=records&subaction=delete',
                method: 'POST',
                data: { id: id },
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: 'Excluído!', text: response.message, confirmButtonColor: '#2563eb' })
                            .then(function() { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Erro', text: response.message, confirmButtonColor: '#ef4444' });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Erro', text: 'Erro ao processar requisição.', confirmButtonColor: '#ef4444' });
                }
            });
        });
    }
    </script>
</body>
</html>