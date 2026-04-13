<?php
$title = 'Preview Arvore da Vida | Terapeuta';
include __DIR__ . '/../../partials/header.php';
?>
<?php include __DIR__ . '/../../partials/nav.php'; ?>

<div class="container page-wrap" style="max-width: 1120px;">
  <section class="preview-sky">
    <header class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
      <div>
        <h1 class="h4 text-white mb-1">Preview da Arvore da Vida</h1>
        <p class="text-white-50 mb-0">Ambiente de teste para validar a experiencia antes do envio ao paciente.</p>
      </div>
      <button class="btn btn-light btn-sm" type="button" onclick="window.close()">
        <i class="fa-solid fa-xmark me-1"></i>Fechar teste
      </button>
    </header>

    <div class="row g-3 align-items-start">
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm preview-card sticky-lg-top" style="top:18px;">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <strong class="text-secondary">Progresso</strong>
              <span class="badge text-bg-light" id="previewStep">1 / 8</span>
            </div>
            <div class="preview-tree-box mb-2">
              <svg id="previewTree" viewBox="0 0 320 360" aria-label="Arvore preview">
                <rect x="0" y="0" width="320" height="360" fill="#eff9ff" rx="18"/>
                <g id="pvRoots" class="pv-stage">
                  <path d="M160 305 C130 328, 105 346, 80 356"/>
                  <path d="M160 305 C192 328, 218 344, 244 356"/>
                </g>
                <g id="pvTrunk" class="pv-stage">
                  <rect x="146" y="177" width="28" height="128" rx="8" fill="#7c4f2d"/>
                </g>
                <g id="pvBranches" class="pv-stage">
                  <path d="M160 198 C126 166, 108 144, 90 123"/>
                  <path d="M160 198 C194 166, 212 144, 230 123"/>
                </g>
                <g id="pvLeaves" class="pv-stage">
                  <circle cx="95" cy="120" r="26"/>
                  <circle cx="158" cy="86" r="30"/>
                  <circle cx="225" cy="121" r="25"/>
                </g>
                <g id="pvFruits" class="pv-stage">
                  <circle cx="130" cy="120" r="8"/>
                  <circle cx="170" cy="107" r="9"/>
                  <circle cx="198" cy="129" r="7"/>
                </g>
              </svg>
            </div>
            <div class="progress" style="height:8px;">
              <div class="progress-bar" id="previewProgress" style="width:0%"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="card border-0 shadow-sm preview-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <small class="text-uppercase text-muted fw-semibold" id="previewTag">Etapa 1</small>
                <h2 class="h5 mb-0" id="previewTitle">Carregando...</h2>
              </div>
              <span class="badge rounded-pill text-bg-primary" id="previewCount">0 perguntas</span>
            </div>

            <div id="questionsContainer"></div>

            <div class="d-flex gap-2 mt-4">
              <button class="btn btn-outline-secondary" id="prevBtn" type="button">Anterior</button>
              <button class="btn btn-primary flex-grow-1" id="nextBtn" type="button">Proxima</button>
            </div>
          </div>
        </div>

        <div class="alert alert-info mt-3 mb-0">
          <i class="fa-solid fa-circle-info me-1"></i>
          Modo de teste: os dados preenchidos aqui nao sao gravados no banco.
        </div>
      </div>
    </div>
  </section>
</div>

<style>
.preview-sky {
  border-radius: 20px;
  padding: 1.25rem;
  background: linear-gradient(180deg, #4eaeea 0%, #9cdbff 52%, #c9eeff 100%);
}

.preview-card { border-radius: 16px; }
.preview-tree-box {
  border: 1px solid #d8eaf7;
  border-radius: 12px;
  background: #f8fdff;
  padding: 8px;
}

#previewTree { width: 100%; height: 300px; }

.pv-stage { opacity: 0.16; transition: opacity .25s ease; }
.pv-stage.active { opacity: 1; }
#pvRoots path, #pvBranches path { fill: none; stroke: #7d502d; stroke-width: 4; stroke-linecap: round; }
#pvLeaves circle { fill: #3ea35d; }
#pvFruits circle { fill: #ffb84d; }

.preview-question {
  border: 1px solid #e1edf5;
  border-radius: 12px;
  background: #fbfeff;
  padding: 12px;
  margin-bottom: 10px;
}

.preview-question label {
  display: block;
  font-weight: 600;
  margin-bottom: 6px;
}

.preview-question textarea {
  width: 100%;
  min-height: 82px;
  border: 1px solid #d0e4f2;
  border-radius: 10px;
  padding: 8px 10px;
  resize: vertical;
}

.preview-question textarea:focus {
  outline: none;
  border-color: #4eaeea;
  box-shadow: 0 0 0 0.2rem rgba(78,174,234,.2);
}
</style>

<script>
(function() {
  const structure = <?php echo isset($structure) ? json_encode($structure) : 'null'; ?>;
  const sections = (structure && Array.isArray(structure.sections)) ? structure.sections : [];
  let current = 0;

  const refs = {
    tag: document.getElementById('previewTag'),
    title: document.getElementById('previewTitle'),
    count: document.getElementById('previewCount'),
    container: document.getElementById('questionsContainer'),
    step: document.getElementById('previewStep'),
    progress: document.getElementById('previewProgress'),
    prev: document.getElementById('prevBtn'),
    next: document.getElementById('nextBtn')
  };

  function setTree(progress) {
    const map = [
      ['pvRoots', 0.12],
      ['pvTrunk', 0.28],
      ['pvBranches', 0.48],
      ['pvLeaves', 0.72],
      ['pvFruits', 0.9]
    ];
    map.forEach(([id, min]) => {
      const el = document.getElementById(id);
      if (el) {
        el.classList.toggle('active', progress >= min);
      }
    });
  }

  function renderFinal() {
    refs.tag.textContent = 'Etapa final';
    refs.title.textContent = 'Reflexao de fechamento';
    refs.count.textContent = 'Texto livre';
    refs.container.innerHTML = ''
      + '<div class="preview-question">'
      + '  <label>Reflexao final</label>'
      + '  <textarea placeholder="Simulacao da etapa final...\"></textarea>'
      + '</div>';

    refs.prev.style.display = 'inline-block';
    refs.next.textContent = 'Concluir teste';

    const total = sections.length + 1;
    const step = total;
    refs.step.textContent = step + ' / ' + total;
    const pct = (step / total) * 100;
    refs.progress.style.width = pct.toFixed(0) + '%';
    setTree(1);
  }

  function renderSection(index) {
    const section = sections[index];
    if (!section) {
      return;
    }

    refs.tag.textContent = 'Etapa ' + (index + 1);
    refs.title.textContent = section.title || 'Secao';
    refs.count.textContent = ((section.questions || []).length) + ' perguntas';

    refs.container.innerHTML = (section.questions || []).map((q, i) => {
      return '<div class="preview-question">'
        + '<label>' + (i + 1) + '. ' + q + '</label>'
        + '<textarea placeholder="Resposta de teste..."></textarea>'
        + '</div>';
    }).join('');

    refs.prev.style.display = index === 0 ? 'none' : 'inline-block';
    refs.next.textContent = index === sections.length - 1 ? 'Ir para reflexao final' : 'Proxima';

    const total = sections.length + 1;
    const step = index + 1;
    refs.step.textContent = step + ' / ' + total;
    const pct = (step / total) * 100;
    refs.progress.style.width = pct.toFixed(0) + '%';
    setTree(step / total);
  }

  function onPrev() {
    if (current > 0) {
      current -= 1;
      renderSection(current);
    }
  }

  function onNext() {
    if (sections.length === 0) {
      return;
    }

    if (current < sections.length - 1) {
      current += 1;
      renderSection(current);
      return;
    }

    if (refs.next.textContent === 'Concluir teste') {
      if (window.Swal && typeof Swal.fire === 'function') {
        Swal.fire('Teste concluido', 'Fluxo validado com sucesso.', 'success').then(() => window.close());
      } else {
        alert('Teste concluido com sucesso.');
        window.close();
      }
      return;
    }

    renderFinal();
  }

  function boot() {
    refs.prev.addEventListener('click', onPrev);
    refs.next.addEventListener('click', onNext);

    if (!Array.isArray(sections) || sections.length === 0) {
      refs.tag.textContent = 'Indisponivel';
      refs.title.textContent = 'Estrutura nao carregada';
      refs.count.textContent = '0 perguntas';
      refs.container.innerHTML = '<div class="alert alert-warning mb-0">Nao foi possivel montar o preview. Recarregue ou volte ao editor.</div>';
      refs.next.disabled = true;
      refs.prev.style.display = 'none';
      return;
    }

    renderSection(0);
  }

  window.addEventListener('load', boot);
})();
</script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
