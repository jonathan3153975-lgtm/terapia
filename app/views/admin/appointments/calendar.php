<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda - <?php echo \Config\Config::APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
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
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Pacientes</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=calendar" class="nav-link active">
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
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Novo Agendamento
                    </a>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <div class="page-content">
                <div class="page-header">
                    <h1>Agenda de Consultas</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=dashboard">Dashboard</a></li>
                            <li class="breadcrumb-item active">Agenda</li>
                        </ol>
                    </nav>
                </div>

                <!-- Calendar Card -->
                <div class="card">
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="<?php echo \Config\Config::APP_URL; ?>/public/js/main.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var baseUrl = '<?php echo \Config\Config::APP_URL; ?>';

            var statusLabel = {
                confirmed:  'Confirmado',
                pending:    'Pendente',
                cancelled:  'Cancelado',
                completed:  'Concluído'
            };

            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                buttonText: {
                    today:    'Hoje',
                    month:    'Mês',
                    week:     'Semana',
                    day:      'Dia',
                    listMonth:'Lista'
                },
                allDayText: 'O dia todo',
                moreLinkText: 'mais',
                noEventsText: 'Nenhuma consulta neste período',
                events: {
                    url: baseUrl + '/dashboard.php?action=appointments&subaction=get-by-range',
                    failure: function() {
                        Swal.fire({icon:'error', title:'Erro', text:'Não foi possível carregar os agendamentos.'});
                    }
                },
                eventTimeFormat: {
                    hour:   '2-digit',
                    minute: '2-digit',
                    hour12: false
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault(); // evita seguir a URL diretamente
                    var p = info.event.extendedProps;
                    var hora = info.event.start
                        ? info.event.start.toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'})
                        : '';
                    var data = info.event.start
                        ? info.event.start.toLocaleDateString('pt-BR')
                        : '';

                    Swal.fire({
                        title: info.event.title,
                        html: '<p class="mb-1"><strong>Data:</strong> ' + data + ' às ' + hora + '</p>' +
                              '<p class="mb-1"><strong>Status:</strong> ' + (statusLabel[p.status] || p.status) + '</p>' +
                              (p.notes ? '<p class="mb-0"><strong>Obs:</strong> ' + p.notes + '</p>' : ''),
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-eye"></i> Ver detalhes',
                        cancelButtonText: 'Fechar',
                        confirmButtonColor: '#2563eb'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            window.location.href = info.event.url;
                        }
                    });
                },
                dateClick: function(info) {
                    window.location.href = baseUrl + '/dashboard.php?action=appointments&subaction=create';
                }
            });
            calendar.render();
        });
    </script>
</body>
</html>