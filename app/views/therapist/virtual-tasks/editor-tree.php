<?php
$title = 'Editor - Árvore da Vida | Tera-Tech';
include __DIR__ . '/../../partials/header.php';
?>
<?php include __DIR__ . '/../../partials/nav.php'; ?>

<div class="page-wrap">
  <div class="container-fluid">
    <a class="btn btn-outline-secondary btn-sm mb-4" href="<?php echo $appUrl; ?>/dashboard.php?action=virtual-tasks">
      <i class="fa-solid fa-arrow-left me-1"></i>Voltar
    </a>

    <div class="row">
      <!-- Painel de Visualização -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0"><i class="fa-solid fa-tree me-2"></i>Árvore da Vida</h5>
            <div class="btn-group btn-group-sm" role="group">
              <button type="button" class="btn btn-outline-secondary active" data-view="preview">
                <i class="fa-solid fa-eye me-1"></i>Visualizar
              </button>
              <button type="button" class="btn btn-outline-secondary" data-view="test">
                <i class="fa-solid fa-play me-1"></i>Testar
              </button>
            </div>
          </div>
          <div class="card-body p-4">
            <div id="treeVisualization" style="text-align: center; padding: 40px 0;">
              <svg id="treeCanvas" width="300" height="400" viewBox="0 0 300 400" style="max-width: 100%;">
                <!-- Raízes -->
                <g id="roots" opacity="0">
                  <path d="M150 350 Q140 380 130 400" stroke="#8B4513" stroke-width="3" fill="none"/>
                  <path d="M150 350 Q160 380 170 400" stroke="#8B4513" stroke-width="3" fill="none"/>
                  <path d="M150 350 Q150 380 150 400" stroke="#8B4513" stroke-width="2" fill="none"/>
                </g>

                <!-- Tronco -->
                <g id="trunk" opacity="0">
                  <rect x="140" y="250" width="20" height="100" fill="#a0522d"/>
                </g>

                <!-- Galhos -->
                <g id="branches" opacity="0">
                  <line x1="150" y1="270" x2="100" y2="200" stroke="#8B4513" stroke-width="3"/>
                  <line x1="150" y1="270" x2="200" y2="200" stroke="#8B4513" stroke-width="3"/>
                </g>

                <!-- Folhas -->
                <g id="leaves" opacity="0">
                  <circle cx="90" cy="180" r="25" fill="#27ae60" opacity="0.8"/>
                  <circle cx="150" cy="150" r="25" fill="#27ae60" opacity="0.8"/>
                  <circle cx="210" cy="180" r="25" fill="#27ae60" opacity="0.8"/>
                </g>

                <!-- Sol/Frutos no topo -->
                <g id="fruits" opacity="0">
                  <circle cx="150" cy="80" r="20" fill="#f1c40f"/>
                </g>
              </svg>
            </div>

            <!-- Áreas de exibição das seções -->
            <div id="sectionsDisplay" style="display: none;">
              <div class="sections-list">
                <?php foreach ($structure['sections'] as $section): ?>
                  <div class="section-card mb-3 p-3 border rounded" style="border-left: 4px solid <?php echo $section['color']; ?>">
                    <h6 class="mb-2"><?php echo $section['title']; ?></h6>
                    <small class="text-muted"><?php echo count($section['questions']); ?> perguntas</small>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Painel de Controles -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-light border-0 py-3">
            <h6 class="mb-0"><i class="fa-solid fa-sliders me-2"></i>Configuração</h6>
          </div>
          <div class="card-body">
            <!-- Título -->
            <div class="mb-3">
              <label class="form-label">Título da Tarefa</label>
              <input type="text" class="form-control" id="taskTitle" value="Árvore da Vida" placeholder="Nome da tarefa">
            </div>

            <!-- Seleção de Paciente -->
            <div class="mb-3">
              <label class="form-label">Enviar para Paciente</label>
              <select class="form-select" id="patientSelect">
                <option value="">-- Selecionar Paciente --</option>
                <?php foreach ($patients as $patient): ?>
                  <option value="<?php echo (int) $patient['id']; ?>">
                    <?php echo htmlspecialchars($patient['name'] ?? ''); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Seções com checkboxes -->
            <div class="mb-4">
              <label class="form-label">Seções a Incluir</label>
              <div class="sections-checkboxes">
                <?php foreach ($structure['sections'] as $section): ?>
                  <div class="form-check">
                    <input class="form-check-input section-toggle" type="checkbox" id="section_<?php echo $section['key']; ?>" checked value="<?php echo $section['key']; ?>" data-color="<?php echo $section['color']; ?>">
                    <label class="form-check-label" for="section_<?php echo $section['key']; ?>">
                      <?php echo $section['title']; ?>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Descrição -->
            <div class="mb-4">
              <label class="form-label">Descrição (opcional)</label>
              <textarea class="form-control" id="taskDescription" rows="3" placeholder="Instruções adicionais para o paciente"></textarea>
            </div>

            <!-- Botões de Ação -->
            <div class="d-grid gap-2">
              <button class="btn btn-primary" onclick="sendTaskToPatient()" id="sendBtn">
                <i class="fa-solid fa-paper-plane me-2"></i>Enviar Tarefa
              </button>
              <button class="btn btn-outline-secondary" onclick="testTask()">
                <i class="fa-solid fa-flask me-2"></i>Testar Primeiro
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal para teste -->
<div class="modal fade" id="testModal" tabindex="-1">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Teste - Árvore da Vida</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="testContent">
        <!-- Conteúdo do teste será carregado aqui -->
      </div>
    </div>
  </div>
</div>

<script>
const appUrl = '<?php echo $appUrl; ?>';
const structure = <?php echo json_encode($structure); ?>;
let selectedSections = <?php echo json_encode(array_column($structure['sections'], 'key')); ?>;

// Animação da árvore
function animateTree() {
  const timeline = [
    { element: '#roots', delay: 0 },
    { element: '#trunk', delay: 300 },
    { element: '#branches', delay: 600 },
    { element: '#leaves', delay: 900 },
    { element: '#fruits', delay: 1200 }
  ];

  timeline.forEach(item => {
    setTimeout(() => {
      const el = document.querySelector(item.element);
      if (el) {
        el.style.transition = 'opacity 0.5s ease';
        el.style.opacity = '1';
      }
    }, item.delay);
  });
}

// Inicializa animação ao carregar
window.addEventListener('load', function() {
  animateTree();

  // Toggle de seções
  document.querySelectorAll('.section-toggle').forEach(function(el) {
    el.addEventListener('change', function() {
      if (this.checked) {
        selectedSections.push(this.value);
      } else {
        selectedSections = selectedSections.filter(s => s !== this.value);
      }
      selectedSections = Array.from(new Set(selectedSections));
    });
  });
});

function safeSwal(title, text, icon) {
  if (window.Swal && typeof Swal.fire === 'function') {
    return Swal.fire(title, text, icon);
  }

  alert(title + ': ' + text);
  return Promise.resolve();
}

function getSelectedPatientId() {
  const select = document.getElementById('patientSelect');
  return parseInt(select ? select.value : '', 10) || 0;
}

function getTaskTitle() {
  const input = document.getElementById('taskTitle');
  return (input && input.value ? input.value : 'Árvore da Vida').trim();
}

function setSendButtonLoading(loading) {
  const btn = document.getElementById('sendBtn');
  if (!btn) {
    return;
  }

  if (loading) {
    btn.dataset.originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Enviando...';
  } else {
    btn.disabled = false;
    btn.innerHTML = btn.dataset.originalText || '<i class="fa-solid fa-paper-plane me-2"></i>Enviar Tarefa';
  }
}

function testTask() {
  const modalEl = document.getElementById('testModal');
  const target = document.getElementById('testContent');
  const modal = new bootstrap.Modal(modalEl);

  fetch(appUrl + '/dashboard.php?action=virtual-tasks-preview', { method: 'GET' })
    .then((res) => res.text())
    .then((html) => {
      if (target) {
        target.innerHTML = html;
      }
      modal.show();
    })
    .catch(() => {
      safeSwal('Erro', 'Não foi possível carregar o teste', 'error');
    });
}

function sendTaskToPatient() {
  const patientId = getSelectedPatientId();
  const taskTitle = getTaskTitle();

  if (!patientId) {
    safeSwal('Aviso', 'Selecione um paciente para enviar a tarefa', 'warning');
    return;
  }

  setSendButtonLoading(true);

  fetch(appUrl + '/dashboard.php?action=virtual-tasks-store', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({
      patient_id: patientId,
      title: taskTitle,
      task_type: 'virtual_tree_of_life',
      sections: selectedSections
    })
  })
    .then((res) => res.json())
    .then((res) => {
      if (res.success) {
        safeSwal('Sucesso!', res.message, 'success').then(() => {
          window.location.href = appUrl + '/dashboard.php?action=virtual-tasks';
        });
        return;
      }

      safeSwal('Erro', res.message || 'Erro ao enviar tarefa', 'error');
      setSendButtonLoading(false);
    })
    .catch(() => {
      safeSwal('Erro', 'Erro ao enviar tarefa', 'error');
      setSendButtonLoading(false);
    });
}
</script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
