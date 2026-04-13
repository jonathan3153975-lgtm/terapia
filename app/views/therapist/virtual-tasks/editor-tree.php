<?php
$title = 'Editor Arvore da Vida | Tera-Tech';
include __DIR__ . '/../../partials/header.php';
?>
<?php include __DIR__ . '/../../partials/nav.php'; ?>

<div class="container page-wrap" style="max-width: 1220px;">
  <section class="editor-sky">
    <span class="cloud cloud-a"></span>
    <span class="cloud cloud-b"></span>
    <span class="cloud cloud-c"></span>

    <header class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <div>
        <h1 class="h4 text-white mb-1">Editor da Arvore da Vida</h1>
        <p class="text-white-50 mb-0">Personalize a experiencia, selecione as secoes e envie ao paciente com um clique.</p>
      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-light btn-sm" href="<?php echo $appUrl; ?>/dashboard.php?action=virtual-tasks">
          <i class="fa-solid fa-arrow-left me-1"></i>Voltar
        </a>
        <button class="btn btn-outline-light btn-sm" type="button" onclick="testTask()">
          <i class="fa-solid fa-vial me-1"></i>Testar fluxo
        </button>
      </div>
    </header>

    <div class="row g-3 align-items-start">
      <div class="col-lg-7">
        <article class="card border-0 shadow-sm editor-card reveal-up" style="--stagger-delay: 0ms;">
          <div class="card-body p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h2 class="h6 mb-0 text-secondary">Visual da jornada</h2>
              <span class="badge text-bg-light" id="previewBadge">0 / 7 secoes</span>
            </div>

            <div class="editor-tree-box mb-3">
              <svg id="editorTree" viewBox="0 0 360 390" aria-label="Preview da arvore">
                <defs>
                  <linearGradient id="editorSky" x1="0" x2="0" y1="0" y2="1">
                    <stop offset="0%" stop-color="#ecf9ff"/>
                    <stop offset="100%" stop-color="#daf1ff"/>
                  </linearGradient>
                  <linearGradient id="editorTrunk" x1="0" x2="0" y1="0" y2="1">
                    <stop offset="0%" stop-color="#8a5a33"/>
                    <stop offset="100%" stop-color="#6a4225"/>
                  </linearGradient>
                </defs>

                <rect x="0" y="0" width="360" height="390" fill="url(#editorSky)" rx="18"/>
                <circle cx="300" cy="56" r="30" fill="#ffe6ae"/>

                <g id="edRoots" class="ed-stage">
                  <path d="M180 332 C146 360, 112 378, 84 386"/>
                  <path d="M180 332 C216 360, 248 378, 278 386"/>
                  <path d="M180 332 C180 360, 181 376, 182 388"/>
                </g>

                <g id="edTrunk" class="ed-stage">
                  <rect x="163" y="190" width="34" height="144" rx="12" fill="url(#editorTrunk)"/>
                </g>

                <g id="edBranches" class="ed-stage">
                  <path d="M180 208 C144 176, 122 150, 102 124"/>
                  <path d="M180 204 C216 176, 236 150, 258 124"/>
                  <path d="M180 236 C146 212, 114 194, 90 178"/>
                  <path d="M180 236 C214 212, 246 194, 272 178"/>
                </g>

                <g id="edLeaves" class="ed-stage">
                  <circle cx="98" cy="118" r="30"/>
                  <circle cx="146" cy="88" r="32"/>
                  <circle cx="214" cy="86" r="34"/>
                  <circle cx="264" cy="120" r="29"/>
                  <circle cx="84" cy="175" r="22"/>
                  <circle cx="278" cy="174" r="22"/>
                </g>

                <g id="edFruits" class="ed-stage">
                  <circle cx="144" cy="120" r="10"/>
                  <circle cx="186" cy="108" r="11"/>
                  <circle cx="228" cy="126" r="9"/>
                  <circle cx="164" cy="156" r="8"/>
                </g>
              </svg>
            </div>

            <div class="progress editor-progress mb-2">
              <div class="progress-bar" id="editorProgress" style="width:0%"></div>
            </div>
            <small class="text-muted" id="editorProgressText">Selecione as secoes para montar a tarefa.</small>
          </div>
        </article>

        <article class="card border-0 shadow-sm editor-card mt-3 reveal-up" style="--stagger-delay: 120ms;">
          <div class="card-body p-3 p-md-4">
            <h3 class="h6 text-secondary mb-3">Resumo das secoes ativas</h3>
            <div id="activeSectionCards" class="active-sections"></div>
          </div>
        </article>
      </div>

      <div class="col-lg-5">
        <article class="card border-0 shadow-sm editor-card sticky-lg-top reveal-up" style="top:18px; --stagger-delay: 220ms;">
          <div class="card-body p-3 p-md-4">
            <h2 class="h6 text-secondary mb-3">Configuracao de envio</h2>

            <div class="mb-3">
              <label class="form-label">Titulo da tarefa</label>
              <input type="text" class="form-control" id="taskTitle" value="Arvore da Vida" placeholder="Nome da tarefa">
            </div>

            <div class="mb-3">
              <label class="form-label">Paciente</label>
              <select class="form-select" id="patientSelect">
                <option value="">-- Selecione um paciente --</option>
                <?php foreach ($patients as $patient): ?>
                  <option value="<?php echo (int) $patient['id']; ?>"><?php echo htmlspecialchars($patient['name'] ?? ''); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Descricao para o paciente</label>
              <textarea class="form-control" id="taskDescription" rows="3" placeholder="Contextualize o objetivo da tarefa..."></textarea>
            </div>

            <div class="mb-4">
              <label class="form-label d-block">Secoes da Arvore</label>
              <div class="section-checklist" id="sectionChecklist">
                <?php foreach ($structure['sections'] as $section): ?>
                  <label class="section-check-item" for="section_<?php echo $section['key']; ?>">
                    <input
                      class="section-toggle"
                      type="checkbox"
                      id="section_<?php echo $section['key']; ?>"
                      value="<?php echo $section['key']; ?>"
                      checked
                      data-title="<?php echo htmlspecialchars($section['title']); ?>"
                      data-color="<?php echo htmlspecialchars($section['color']); ?>"
                      data-qcount="<?php echo (int) count($section['questions']); ?>"
                    >
                    <span class="section-marker" style="background: <?php echo $section['color']; ?>;"></span>
                    <span class="section-content">
                      <strong><?php echo htmlspecialchars($section['title']); ?></strong>
                      <small><?php echo (int) count($section['questions']); ?> perguntas</small>
                    </span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="d-grid gap-2">
              <button class="btn btn-primary" id="sendBtn" type="button" onclick="sendTaskToPatient()">
                <i class="fa-solid fa-paper-plane me-2"></i>Enviar tarefa dinamica
              </button>
              <button class="btn btn-outline-secondary" type="button" onclick="testTask()">
                <i class="fa-solid fa-vial me-2"></i>Testar em nova aba
              </button>
            </div>
          </div>
        </article>
      </div>
    </div>
  </section>
</div>

<style>
.editor-sky {
  position: relative;
  overflow: hidden;
  border-radius: 20px;
  padding: 1.25rem;
  background: linear-gradient(180deg, #4eaeea 0%, #9ad9ff 52%, #caefff 100%);
}

.cloud {
  position: absolute;
  display: block;
  background: rgba(255, 255, 255, 0.5);
  border-radius: 999px;
  filter: blur(1px);
  animation: floatCloud 14s ease-in-out infinite;
  pointer-events: none;
}

.cloud-a { width: 105px; height: 32px; top: 26px; left: 10%; }
.cloud-b { width: 130px; height: 36px; top: 70px; right: 14%; animation-delay: 1.2s; }
.cloud-c { width: 86px; height: 26px; top: 118px; left: 47%; animation-delay: 2.1s; }

.editor-card {
  border-radius: 16px;
}

.reveal-up {
  opacity: 0;
  transform: translateY(14px);
  transition: opacity 440ms cubic-bezier(0.16, 1, 0.3, 1), transform 440ms cubic-bezier(0.16, 1, 0.3, 1);
  transition-delay: var(--stagger-delay, 0ms);
}

.ui-ready .reveal-up {
  opacity: 1;
  transform: translateY(0);
}

.editor-tree-box {
  border: 1px solid #d8eaf7;
  border-radius: 14px;
  background: #f8fdff;
  padding: 10px;
}

#editorTree {
  width: 100%;
  height: 330px;
}

.ed-stage {
  opacity: 0.18;
  transition: opacity 320ms ease;
}

.ed-stage.active {
  opacity: 1;
}

#edRoots path,
#edBranches path {
  fill: none;
  stroke: #7d502d;
  stroke-width: 4;
  stroke-linecap: round;
}

#edLeaves circle {
  fill: #40a75e;
  opacity: .95;
}

#edFruits circle {
  fill: #ffb85d;
}

.editor-progress {
  height: 8px;
  border-radius: 999px;
  background: #deedf8;
}

.editor-progress .progress-bar {
  background: linear-gradient(90deg, #30a8ff, #39c488);
}

.section-checklist {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.section-check-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  border: 1px solid #deebf5;
  border-radius: 12px;
  padding: 10px;
  background: #fbfeff;
  cursor: pointer;
  transition: transform 240ms cubic-bezier(0.16, 1, 0.3, 1), box-shadow 240ms ease, border-color 240ms ease;
}

.section-check-item:hover {
  border-color: #bfdbef;
  transform: translateY(-1px);
  box-shadow: 0 8px 18px rgba(25, 86, 132, 0.08);
}

.section-check-item:focus-within {
  border-color: #45a9e6;
  box-shadow: 0 0 0 0.2rem rgba(69, 169, 230, 0.2);
}

.section-check-item input {
  margin-top: 2px;
}

.section-marker {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-top: 6px;
  flex-shrink: 0;
}

.section-content {
  display: flex;
  flex-direction: column;
}

.section-content strong {
  font-size: .92rem;
  color: #18314a;
}

.section-content small {
  color: #6f8598;
}

.active-sections {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
}

.active-section-card {
  border: 1px solid #d7eaf8;
  border-radius: 12px;
  padding: 10px;
  background: #f8fcff;
  opacity: 0;
  transform: translateY(8px);
  animation: cardIn 380ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.active-section-card strong {
  display: block;
  font-size: .88rem;
  color: #1d3f5f;
  line-height: 1.3;
}

.active-section-card small {
  color: #6c8295;
}

.btn:focus-visible,
.form-control:focus,
.form-select:focus {
  box-shadow: 0 0 0 0.22rem rgba(69, 169, 230, 0.24);
  border-color: #45a9e6;
}

@keyframes floatCloud {
  0% { transform: translate3d(0, 0, 0); }
  50% { transform: translate3d(0, -4px, 0); }
  100% { transform: translate3d(0, 0, 0); }
}

@keyframes cardIn {
  0% { opacity: 0; transform: translateY(8px); }
  100% { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
  .cloud,
  .reveal-up,
  .section-check-item,
  .active-section-card,
  .ed-stage {
    animation: none !important;
    transition: none !important;
  }

  .reveal-up,
  .active-section-card {
    opacity: 1;
    transform: none;
  }
}

@media (max-width: 992px) {
  .active-sections {
    grid-template-columns: 1fr;
  }
}
</style>

<script>
const appUrl = '<?php echo $appUrl; ?>';
const baseSections = <?php echo isset($structure['sections']) ? json_encode($structure['sections']) : '[]'; ?>;
let selectedSections = Array.isArray(baseSections) ? baseSections.map(s => s.key) : [];

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
  return (input && input.value ? input.value : 'Arvore da Vida').trim();
}

function getTaskDescription() {
  const textarea = document.getElementById('taskDescription');
  return textarea ? textarea.value.trim() : '';
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
    btn.innerHTML = btn.dataset.originalText || '<i class="fa-solid fa-paper-plane me-2"></i>Enviar tarefa dinamica';
  }
}

function updateTreeVisual(progress) {
  const map = [
    ['edRoots', 0.12],
    ['edTrunk', 0.28],
    ['edBranches', 0.48],
    ['edLeaves', 0.72],
    ['edFruits', 0.9],
  ];

  map.forEach(([id, min]) => {
    const el = document.getElementById(id);
    if (el) {
      el.classList.toggle('active', progress >= min);
    }
  });
}

function renderActiveSectionCards() {
  const container = document.getElementById('activeSectionCards');
  if (!container) {
    return;
  }

  const active = baseSections.filter((section) => selectedSections.includes(section.key));
  if (active.length === 0) {
    container.innerHTML = '<div class="alert alert-warning mb-0">Selecione ao menos uma secao para montar a tarefa.</div>';
    return;
  }

  container.innerHTML = active.map((section) => {
    const qCount = Array.isArray(section.questions) ? section.questions.length : 0;
    return ''
      + '<div class="active-section-card">'
      + '  <strong>' + section.title + '</strong>'
      + '  <small>' + qCount + ' perguntas</small>'
      + '</div>';
  }).join('');

  container.querySelectorAll('.active-section-card').forEach((card, index) => {
    card.style.animationDelay = (index * 60) + 'ms';
  });
}

function updatePreviewState() {
  const total = baseSections.length || 1;
  const activeCount = selectedSections.length;
  const progress = activeCount / total;

  const badge = document.getElementById('previewBadge');
  const progressBar = document.getElementById('editorProgress');
  const progressText = document.getElementById('editorProgressText');

  if (badge) {
    badge.textContent = activeCount + ' / ' + total + ' secoes';
  }

  if (progressBar) {
    progressBar.style.width = (progress * 100).toFixed(0) + '%';
  }

  if (progressText) {
    if (activeCount === 0) {
      progressText.textContent = 'Nenhuma secao ativa. Selecione ao menos uma para envio.';
    } else {
      progressText.textContent = 'A tarefa sera enviada com ' + activeCount + ' secao(oes).';
    }
  }

  updateTreeVisual(progress);
  renderActiveSectionCards();
}

function testTask() {
  const previewUrl = appUrl + '/dashboard.php?action=virtual-tasks-preview';
  const win = window.open(previewUrl, '_blank', 'noopener,noreferrer');
  if (!win) {
    safeSwal('Aviso', 'Nao foi possivel abrir a aba de teste. Verifique bloqueio de pop-up.', 'warning');
  }
}

function sendTaskToPatient() {
  const patientId = getSelectedPatientId();
  const taskTitle = getTaskTitle();
  const taskDescription = getTaskDescription();

  if (!patientId) {
    safeSwal('Aviso', 'Selecione um paciente para enviar a tarefa.', 'warning');
    return;
  }

  if (selectedSections.length === 0) {
    safeSwal('Aviso', 'Selecione ao menos uma secao da Arvore da Vida.', 'warning');
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
      description: taskDescription,
      task_type: 'virtual_tree_of_life',
      sections: selectedSections
    })
  })
    .then((res) => res.json())
    .then((res) => {
      if (res.success) {
        safeSwal('Sucesso', res.message || 'Tarefa enviada com sucesso!', 'success').then(() => {
          window.location.href = appUrl + '/dashboard.php?action=virtual-tasks';
        });
        return;
      }

      safeSwal('Erro', res.message || 'Falha ao enviar tarefa.', 'error');
      setSendButtonLoading(false);
    })
    .catch(() => {
      safeSwal('Erro', 'Falha de comunicacao ao enviar tarefa.', 'error');
      setSendButtonLoading(false);
    });
}

window.addEventListener('load', function() {
  document.body.classList.add('ui-ready');

  const toggles = document.querySelectorAll('.section-toggle');
  toggles.forEach((toggle) => {
    toggle.addEventListener('change', function() {
      if (this.checked) {
        selectedSections.push(this.value);
      } else {
        selectedSections = selectedSections.filter((key) => key !== this.value);
      }
      selectedSections = Array.from(new Set(selectedSections));
      updatePreviewState();
    });
  });

  const checkItems = document.querySelectorAll('.section-check-item');
  checkItems.forEach((item, index) => {
    item.classList.add('reveal-up');
    item.style.setProperty('--stagger-delay', (260 + index * 50) + 'ms');
  });

  updatePreviewState();
});
</script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
