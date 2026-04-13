<?php
$title = 'Arvore da Vida | Jornada do Paciente';
include __DIR__ . '/../../partials/header.php';
?>
<?php include __DIR__ . '/../../partials/nav.php'; ?>

<div class="container page-wrap" style="max-width: 1160px;">
  <section class="tree-sky" id="treeSky">
    <span class="cloud cloud-a"></span>
    <span class="cloud cloud-b"></span>
    <span class="cloud cloud-c"></span>

    <header class="tree-hero mb-4">
      <button class="btn btn-light btn-sm" type="button" onclick="window.history.back()">
        <i class="fa-solid fa-arrow-left me-1"></i>Voltar
      </button>
      <div>
        <h1 class="h3 mb-1 text-white">Arvore da Vida</h1>
        <p class="mb-0 text-white-50">Responda cada etapa para construir uma visao completa da sua historia.</p>
      </div>
    </header>

    <div class="row g-3 align-items-start">
      <div class="col-lg-4 order-lg-2">
        <aside class="card border-0 shadow-sm tree-card tree-card-sticky">
          <div class="card-body p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <strong class="text-secondary">Evolucao da arvore</strong>
              <span class="badge text-bg-light" id="stepBadge">1 / 8</span>
            </div>

            <div class="tree-canvas-wrap mb-3">
              <svg id="patientTree" viewBox="0 0 320 360" aria-label="Arvore da vida">
                <defs>
                  <linearGradient id="skyGlow" x1="0" x2="0" y1="0" y2="1">
                    <stop offset="0%" stop-color="#e8f7ff"/>
                    <stop offset="100%" stop-color="#d8f0ff"/>
                  </linearGradient>
                  <linearGradient id="trunkGrad" x1="0" x2="0" y1="0" y2="1">
                    <stop offset="0%" stop-color="#8a5a33"/>
                    <stop offset="100%" stop-color="#6e4525"/>
                  </linearGradient>
                </defs>

                <rect x="0" y="0" width="320" height="360" fill="url(#skyGlow)" rx="18"/>
                <circle cx="270" cy="52" r="26" fill="#ffe4a8" opacity="0.9"/>

                <g id="stageRoots" class="tree-stage">
                  <path d="M160 302 C130 327, 105 345, 76 355" />
                  <path d="M160 302 C192 328, 220 344, 248 355" />
                  <path d="M160 302 C159 332, 160 347, 161 358" />
                </g>

                <g id="stageTrunk" class="tree-stage">
                  <rect x="145" y="175" width="30" height="130" rx="10" fill="url(#trunkGrad)"/>
                </g>

                <g id="stageBranches" class="tree-stage">
                  <path d="M160 194 C126 164, 108 140, 90 120" />
                  <path d="M160 190 C196 162, 214 140, 231 118" />
                  <path d="M160 220 C126 198, 96 180, 78 168" />
                  <path d="M160 218 C194 198, 224 180, 244 166" />
                </g>

                <g id="stageLeaves" class="tree-stage">
                  <circle cx="88" cy="114" r="27" />
                  <circle cx="132" cy="88" r="29" />
                  <circle cx="183" cy="84" r="30" />
                  <circle cx="232" cy="112" r="26" />
                  <circle cx="74" cy="168" r="22" />
                  <circle cx="248" cy="166" r="20" />
                </g>

                <g id="stageFruits" class="tree-stage">
                  <circle cx="125" cy="112" r="9" />
                  <circle cx="168" cy="104" r="10" />
                  <circle cx="208" cy="124" r="8" />
                  <circle cx="148" cy="146" r="8" />
                </g>
              </svg>
            </div>

            <div class="progress tree-progress mb-2">
              <div class="progress-bar" id="treeProgressBar" role="progressbar" style="width: 0%"></div>
            </div>
            <small class="text-muted d-block" id="progressText">Etapa 1 de 8</small>
          </div>
        </aside>
      </div>

      <div class="col-lg-8 order-lg-1">
        <section class="card border-0 shadow-sm tree-card">
          <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
              <div>
                <small class="text-uppercase text-muted fw-semibold" id="sectionTag">Etapa 1</small>
                <h2 class="h4 mb-0" id="sectionTitle">Carregando...</h2>
              </div>
              <span class="badge rounded-pill text-bg-primary" id="questionCount">0 perguntas</span>
            </div>

            <div id="sectionsContainer"></div>

            <div class="d-flex gap-2 mt-4" id="navButtons">
              <button type="button" class="btn btn-outline-secondary" id="prevSectionBtn">Anterior</button>
              <button type="button" class="btn btn-primary flex-grow-1" id="nextSectionBtn">Proxima</button>
            </div>
          </div>
        </section>
      </div>
    </div>
  </section>
</div>

<style>
.tree-sky {
  position: relative;
  overflow: hidden;
  border-radius: 20px;
  padding: 1.25rem;
  background: linear-gradient(180deg, #6dc8ff 0%, #b3e5ff 48%, #d8f6ff 100%);
}

.tree-hero {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.tree-card {
  border-radius: 18px;
  backdrop-filter: blur(1px);
}

.tree-card-sticky {
  position: sticky;
  top: 18px;
}

.tree-canvas-wrap {
  background: linear-gradient(180deg, #f7fcff, #eef8ff);
  border: 1px solid #d7ebf8;
  border-radius: 16px;
  padding: 10px;
}

#patientTree {
  width: 100%;
  height: 330px;
}

.tree-stage {
  opacity: 0.18;
  transition: opacity 320ms ease;
}

.tree-stage.active {
  opacity: 1;
}

#stageRoots path,
#stageBranches path {
  fill: none;
  stroke: #7a4d2b;
  stroke-width: 4;
  stroke-linecap: round;
}

#stageLeaves circle {
  fill: #41a85f;
  opacity: 0.9;
}

#stageFruits circle {
  fill: #ffb347;
}

.tree-progress {
  height: 8px;
  border-radius: 999px;
  background: #deedf8;
}

.tree-progress .progress-bar {
  background: linear-gradient(90deg, #2ca7ff, #42cd8d);
}

.cloud {
  position: absolute;
  display: block;
  background: rgba(255, 255, 255, 0.55);
  border-radius: 999px;
  filter: blur(1px);
}

.cloud-a { width: 110px; height: 34px; top: 28px; left: 12%; }
.cloud-b { width: 140px; height: 38px; top: 72px; right: 16%; }
.cloud-c { width: 90px; height: 28px; top: 124px; left: 48%; }

.question-item {
  border: 1px solid #e0edf5;
  background: #fafdff;
  border-radius: 14px;
  padding: 14px;
  margin-bottom: 10px;
}

.question-label {
  display: block;
  font-weight: 600;
  color: #123;
  margin-bottom: 8px;
}

.answer-textarea {
  width: 100%;
  border: 1px solid #cfe1ed;
  border-radius: 10px;
  min-height: 96px;
  padding: 10px 12px;
  resize: vertical;
  background: #fff;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.answer-textarea:focus {
  border-color: #45a9e6;
  box-shadow: 0 0 0 0.2rem rgba(69, 169, 230, 0.2);
  outline: none;
}

.final-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

.final-block {
  border: 1px solid #d8ebf7;
  background: #f7fcff;
  border-radius: 12px;
  padding: 10px;
}

.final-block h6 {
  margin-bottom: 8px;
  color: #215a8b;
}

#editor {
  height: 220px;
  background: #fff;
  border-radius: 0 0 10px 10px;
}

@media (max-width: 992px) {
  .tree-card-sticky { position: static; }
  .tree-hero { flex-direction: column; align-items: flex-start; }
  .final-grid { grid-template-columns: 1fr; }
}
</style>

<script>
(function() {
  const appUrl = '<?php echo $appUrl; ?>';
  const taskId = <?php echo (int) ($task['id'] ?? 0); ?>;
  const structure = <?php echo isset($structure) ? json_encode($structure) : 'null'; ?>;
  const sections = (structure && Array.isArray(structure.sections)) ? structure.sections : [];

  const state = {
    currentIndex: 0,
    answers: {},
    isFinal: false,
    quill: null,
  };

  const refs = {
    sectionTag: document.getElementById('sectionTag'),
    sectionTitle: document.getElementById('sectionTitle'),
    questionCount: document.getElementById('questionCount'),
    container: document.getElementById('sectionsContainer'),
    prevBtn: document.getElementById('prevSectionBtn'),
    nextBtn: document.getElementById('nextSectionBtn'),
    progressBar: document.getElementById('treeProgressBar'),
    progressText: document.getElementById('progressText'),
    stepBadge: document.getElementById('stepBadge'),
    sky: document.getElementById('treeSky')
  };

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/\"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function swal(icon, title, text) {
    if (window.Swal && typeof Swal.fire === 'function') {
      return Swal.fire({ icon, title, text, confirmButtonColor: '#2f93d8' });
    }
    alert(title + '\n' + text);
    return Promise.resolve();
  }

  function updateTreeVisual(progress) {
    const stages = [
      ['stageRoots', 0.12],
      ['stageTrunk', 0.28],
      ['stageBranches', 0.48],
      ['stageLeaves', 0.72],
      ['stageFruits', 0.9],
    ];

    stages.forEach(([id, threshold]) => {
      const el = document.getElementById(id);
      if (el) {
        el.classList.toggle('active', progress >= threshold);
      }
    });
  }

  function updateProgress() {
    const total = sections.length + 1;
    const current = state.isFinal ? total : state.currentIndex + 1;
    const progress = total > 0 ? current / total : 0;

    refs.progressBar.style.width = (progress * 100).toFixed(0) + '%';
    refs.progressText.textContent = 'Etapa ' + current + ' de ' + total;
    refs.stepBadge.textContent = current + ' / ' + total;
    updateTreeVisual(progress);
  }

  function persistCurrentAnswers() {
    if (state.isFinal) {
      const fields = ['passadoReflection', 'presenteReflection', 'futuroReflection'];
      fields.forEach((id) => {
        const el = document.getElementById(id);
        if (el) {
          state.answers[id] = el.value;
        }
      });
      return;
    }

    const section = sections[state.currentIndex];
    if (!section) {
      return;
    }

    const values = [];
    refs.container.querySelectorAll('.answer-textarea').forEach((ta) => {
      values.push(ta.value || '');
    });
    state.answers[section.key] = values;
  }

  function renderSection(index) {
    const section = sections[index];
    if (!section) {
      return;
    }

    state.isFinal = false;
    state.currentIndex = index;

    refs.sectionTag.textContent = 'Etapa ' + (index + 1);
    refs.sectionTitle.textContent = section.title || 'Secao';
    refs.questionCount.textContent = (section.questions || []).length + ' perguntas';

    const existing = Array.isArray(state.answers[section.key]) ? state.answers[section.key] : [];

    const html = (section.questions || []).map((q, i) => {
      const value = existing[i] || '';
      return '<article class="question-item">'
        + '<label class="question-label" for="q_' + i + '">' + (i + 1) + '. ' + escapeHtml(q) + '</label>'
        + '<textarea id="q_' + i + '" class="answer-textarea" placeholder="Escreva sua resposta com calma...">' + escapeHtml(value) + '</textarea>'
        + '</article>';
    }).join('');

    refs.container.innerHTML = html || '<div class="alert alert-warning">Nenhuma pergunta encontrada nesta secao.</div>';

    refs.prevBtn.style.display = index === 0 ? 'none' : 'inline-block';
    refs.nextBtn.innerHTML = (index === sections.length - 1)
      ? 'Ir para reflexao final <i class="fa-solid fa-arrow-right ms-2"></i>'
      : 'Proxima etapa <i class="fa-solid fa-arrow-right ms-2"></i>';

    updateProgress();
  }

  function renderFinal() {
    state.isFinal = true;
    refs.sectionTag.textContent = 'Etapa final';
    refs.sectionTitle.textContent = 'Reflexao Integrada';
    refs.questionCount.textContent = 'Sintese da sua jornada';

    const p = state.answers.passadoReflection || '';
    const pr = state.answers.presenteReflection || '';
    const f = state.answers.futuroReflection || '';

    refs.container.innerHTML = ''
      + '<div class="final-grid mb-3">'
      + '  <div class="final-block"><h6>Passado</h6><textarea id="passadoReflection" class="answer-textarea" placeholder="Eventos, desafios e forcas que surgiram...">' + escapeHtml(p) + '</textarea></div>'
      + '  <div class="final-block"><h6>Presente</h6><textarea id="presenteReflection" class="answer-textarea" placeholder="Como voce se percebe hoje...">' + escapeHtml(pr) + '</textarea></div>'
      + '  <div class="final-block"><h6>Futuro</h6><textarea id="futuroReflection" class="answer-textarea" placeholder="O que voce deseja cultivar adiante...">' + escapeHtml(f) + '</textarea></div>'
      + '</div>'
      + '<label class="form-label fw-semibold">Texto final para seu terapeuta</label>'
      + '<div id="editor"></div>';

    refs.prevBtn.style.display = 'inline-block';
    refs.nextBtn.innerHTML = '<i class="fa-solid fa-check me-2"></i>Concluir tarefa';

    state.quill = new Quill('#editor', {
      theme: 'snow',
      modules: {
        toolbar: [
          ['bold', 'italic', 'underline'],
          [{ list: 'ordered' }, { list: 'bullet' }],
          ['link']
        ]
      },
      placeholder: 'Escreva aqui sua sintese final...'
    });

    updateProgress();
  }

  function buildPayload() {
    const mappedAnswers = {};
    sections.forEach((section) => {
      mappedAnswers[section.key] = Array.isArray(state.answers[section.key]) ? state.answers[section.key] : [];
    });

    return {
      task_id: taskId,
      reflection: state.quill ? state.quill.root.innerHTML : '',
      answers: mappedAnswers,
      prompts: {
        passado: state.answers.passadoReflection || '',
        presente: state.answers.presenteReflection || '',
        futuro: state.answers.futuroReflection || ''
      }
    };
  }

  async function concludeTask() {
    persistCurrentAnswers();

    const payload = buildPayload();
    const plain = state.quill ? state.quill.getText().trim() : '';
    if (plain.length < 10) {
      await swal('warning', 'Reflexao incompleta', 'Escreva ao menos algumas linhas no texto final para concluir.');
      return;
    }

    refs.nextBtn.disabled = true;
    refs.nextBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Salvando';

    try {
      const response = await fetch(appUrl + '/patient.php?action=virtual-task-complete', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
      });

      const result = await response.json();
      if (!result.success) {
        throw new Error(result.message || 'Falha ao concluir tarefa.');
      }

      await swal('success', 'Jornada concluida', 'Sua Arvore da Vida foi enviada com sucesso para o terapeuta.');
      window.location.href = appUrl + '/patient.php?action=tasks';
    } catch (err) {
      await swal('error', 'Nao foi possivel salvar', err.message || 'Tente novamente em alguns instantes.');
      refs.nextBtn.disabled = false;
      refs.nextBtn.innerHTML = '<i class="fa-solid fa-check me-2"></i>Concluir tarefa';
    }
  }

  function handleNext() {
    persistCurrentAnswers();

    if (state.isFinal) {
      concludeTask();
      return;
    }

    if (state.currentIndex < sections.length - 1) {
      renderSection(state.currentIndex + 1);
      return;
    }

    renderFinal();
  }

  function handlePrev() {
    if (state.isFinal) {
      renderSection(sections.length - 1);
      return;
    }

    persistCurrentAnswers();
    if (state.currentIndex > 0) {
      renderSection(state.currentIndex - 1);
    }
  }

  function boot() {
    refs.prevBtn.addEventListener('click', handlePrev);
    refs.nextBtn.addEventListener('click', handleNext);

    if (!taskId || !Array.isArray(sections) || sections.length === 0) {
      refs.sectionTag.textContent = 'Indisponivel';
      refs.sectionTitle.textContent = 'Estrutura da tarefa nao encontrada';
      refs.questionCount.textContent = '0 perguntas';
      refs.container.innerHTML = '<div class="alert alert-warning mb-0">Nao foi possivel carregar a estrutura da Arvore da Vida. Solicite ao terapeuta o reenvio da tarefa.</div>';
      refs.prevBtn.style.display = 'none';
      refs.nextBtn.disabled = true;
      updateProgress();
      return;
    }

    renderSection(0);
  }

  window.addEventListener('load', boot);
})();
</script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
