// ========== CONFIGURAÇÕES GLOBAIS ==========
$(document).ready(function() {
    initializeApp();
});

function initializeApp() {
    // Menu toggle
    $('#menuBtn').on('click', function() {
        $('.sidebar').toggleClass('active');
    });

    $('#menuToggle').on('click', function() {
        $('.sidebar').removeClass('active');
    });

    // Fecha sidebar ao clicar fora em mobile
    $(document).on('click', function(e) {
        if ($(window).width() < 768) {
            if (!$(e.target).closest('.sidebar').length && !$(e.target).closest('#menuBtn').length) {
                $('.sidebar').removeClass('active');
            }
        }
    });

    // Highlight menu ativo
    highlightActiveMenu();

    // Inicializa tooltips e popovers do Bootstrap
    initializeBootstrapComponents();

    // Listeners para forms
    setupFormListeners();

    // Mascaras de campos
    initializeMasks();

    // Busca automatica de CEP quando completo
    setupAutoCepLookup();
}

// ========== MENU ATIVO ==========
function highlightActiveMenu() {
    const currentUrl = window.location.href;
    $('.nav-link').each(function() {
        const href = $(this).attr('href');
        if (currentUrl.includes(href.split('?')[1])) {
            $(this).addClass('active');
        }
    });
}

// ========== BOOTSTRAP COMPONENTS ==========
function initializeBootstrapComponents() {
    // Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

// ========== FORMS ==========
function setupFormListeners() {
    // Submit forms via AJAX
    $('.form-submit').on('submit', function(e) {
        e.preventDefault();
        submitFormAjax(this);
    });
}

function submitFormAjax(form) {
    const $form = $(form);
    const url = $form.attr('action');
    const method = $form.attr('method') || 'POST';
    const formData = new FormData(form);

    $(form).find('button[type="submit"]').prop('disabled', true);

    $.ajax({
        url: url,
        method: method,
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: response.message || 'Operação realizada com sucesso',
                    confirmButtonColor: '#2563eb'
                }).then(() => {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        location.reload();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: response.message || 'Erro ao processar requisição',
                    confirmButtonColor: '#ef4444'
                });
                $(form).find('button[type="submit"]').prop('disabled', false);
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro ao processar requisição',
                confirmButtonColor: '#ef4444'
            });
            $(form).find('button[type="submit"]').prop('disabled', false);
        }
    });
}

// ========== CONFIRMAÇÃO DE EXCLUSÃO ==========
function confirmDelete(url, itemName = 'item') {
    Swal.fire({
        title: 'Tem certeza?',
        text: `Você está prestes a deletar ${itemName}. Esta ação não pode ser desfeita!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sim, deletar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message || 'Registro removido com sucesso',
                            confirmButtonColor: '#2563eb'
                        }).then(() => {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: response.message || 'Erro ao remover registro',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: 'Erro ao processar a requisição',
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        }
    });
}

// ========== MASCARAS E VALIDAÇÕES ==========
function initializeMasks() {
    // CPF Mask
    $('.mask-cpf').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{2})$/, '$1-$2');
        $(this).val(value);
    });

    // Phone Mask
    $('.mask-phone').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length === 11) {
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else {
            value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        }
        $(this).val(value);
    });

    // CEP Mask
    $('.mask-cep').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
        $(this).val(value);
    });

    // Money Mask
    $('.mask-money').on('input', function() {
        let digits = $(this).val().replace(/\D/g, '') || '0';
        let value = (parseInt(digits) / 100).toFixed(2);
        let parts = value.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        $(this).val('R$ ' + parts[0] + ',' + parts[1]);
    });
}

// ========== BUSCA DE CEP ==========
function searchCEP(cep) {
    const cleanCep = cep.replace(/\D/g, '');

    if (cleanCep.length !== 8) {
        Swal.fire({
            icon: 'warning',
            title: 'CEP Inválido',
            text: 'O CEP deve conter 8 dígitos',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    $.ajax({
        url: getDashboardBaseUrl() + '?action=patients&subaction=search-cep',
        method: 'GET',
        data: { cep: cleanCep },
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                $('#address').val(response.address);
                $('#neighborhood').val(response.neighborhood);
                $('#city').val(response.city);
                $('#state').val(response.state);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: response.message || 'CEP não encontrado',
                    confirmButtonColor: '#ef4444'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Erro ao buscar CEP',
                confirmButtonColor: '#ef4444'
            });
        }
    });
}

function getDashboardBaseUrl() {
    const currentPath = window.location.pathname;
    const dashboardIndex = currentPath.indexOf('/dashboard.php');
    if (dashboardIndex > -1) {
        return currentPath.substring(0, dashboardIndex) + '/dashboard.php';
    }
    return '/dashboard.php';
}

function setupAutoCepLookup() {
    let lastSearchedCep = '';

    $(document).on('input', '#cep', function() {
        const cleanCep = ($(this).val() || '').replace(/\D/g, '');
        if (cleanCep.length !== 8 || cleanCep === lastSearchedCep) {
            return;
        }

        lastSearchedCep = cleanCep;
        searchCEP(cleanCep);
    });

    $(document).on('click', '#searchCepBtn', function() {
        const cep = ($('#cep').val() || '').replace(/\D/g, '');
        if (cep.length === 8) {
            lastSearchedCep = cep;
            searchCEP(cep);
        }
    });
}

// ========== INICIALIZAR QUILL EDITOR ==========
function initializeQuillEditor(elementId, options = {}) {
    const defaultOptions = {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],
                ['clean']
            ]
        }
    };

    const finalOptions = { ...defaultOptions, ...options };
    return new Quill('#' + elementId, finalOptions);
}

// ========== INICIALIZAR FULLCALENDAR ==========
function initializeFullCalendar(elementId, events = []) {
    const calendarEl = document.getElementById(elementId);
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        plugins: ['dayGrid', 'timeGrid', 'interaction'],
        locale: 'pt-br',
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: events,
        dateClick: function(info) {
            handleDateClick(info.dateStr);
        },
        eventClick: function(info) {
            handleEventClick(info.event.id);
        }
    });

    calendar.render();
    return calendar;
}

// ========== FORMATAÇÃO ==========
function formatMoney(value) {
    return 'R$ ' + parseFloat(value).toFixed(2).replace('.', ',');
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR');
}

// ========== NOTIFICAÇÕES ==========
function showNotification(type, title, message) {
    Swal.fire({
        icon: type, // 'success', 'error', 'warning', 'info', 'question'
        title: title,
        text: message,
        confirmButtonColor: '#2563eb'
    });
}

function showConfirm(title, message, confirmText = 'Sim', cancelText = 'Cancelar') {
    return new Promise((resolve) => {
        Swal.fire({
            title: title,
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280',
            confirmButtonText: confirmText,
            cancelButtonText: cancelText
        }).then((result) => {
            resolve(result.isConfirmed);
        });
    });
}

// ========== TABELAS COM SORTING E FILTROS ==========
function initializeDataTable(tableId) {
    // Implementar com DataTables (opcional)
    // Para agora, apenas adiciona classes de interação
    $(`#${tableId} tbody tr`).on('hover', function() {
        $(this).toggleClass('highlight');
    });
}

// ========== ACCORDION TABLES ==========
function setupAccordionTables() {
    $('.accordion-toggle').on('click', function() {
        $(this).closest('tr').next('.accordion-content').toggleClass('d-none');
        $(this).find('i').toggleClass('rotate');
    });
}

// ========== EXPORTAR PARA PDF ==========
function exportToPDF(elementId, filename) {
    const element = document.getElementById(elementId);
    const opt = {
        margin: 10,
        filename: filename + '.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
    };

    html2pdf().set(opt).from(element).save();
}

// ========== EXPORTAR PARA EXCEL ==========
function exportToExcel(elementId, filename) {
    const element = document.getElementById(elementId);
    const html = element.outerHTML;
    const url = 'data:application/vnd.ms-excel,' + escape(html);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename + '.xls';
    link.click();
}

// ========== LOADING STATE ==========
function setLoadingState(element, isLoading) {
    if (isLoading) {
        $(element).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Carregando...');
    } else {
        $(element).prop('disabled', false);
    }
}

// ========== VALIDAÇÃO DE FORMULÁRIO ==========
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
        return false;
    }
    return true;
}

// ========== INICIALIZAR TUDO ==========
$(window).on('load', function() {
    initializeMasks();
    setupAccordionTables();
    initializeBootstrapComponents();
});
