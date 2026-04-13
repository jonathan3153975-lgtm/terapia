<?php
$title = 'Árvore da Vida - Minha Jornada | Tera-Tech';
include __DIR__ . '/../../partials/header.php';
?>

<div style="padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
  <div class="container" style="max-width: 1000px;">
    <!-- Cabeçalho -->
    <div class="mb-4">
      <button class="btn btn-light btn-sm mb-3" onclick="window.history.back()">
        <i class="fa-solid fa-arrow-left me-1"></i>Voltar
      </button>
      <h1 class="h2 text-white mb-1"><i class="fa-solid fa-tree me-2"></i>Árvore da Vida</h1>
      <p class="text-white-50">Explore sua história a partir de diferentes perspectivas da sua vida</p>
    </div>

    <div class="row gap-3">
      <!-- Árvore Animada (lado esquerdo) -->
      <div class="col-lg-4 order-lg-2">
        <div class="card border-0 shadow-lg" style="border-radius: 16px; overflow: hidden; position: sticky; top: 20px;">
          <div class="card-body p-4 text-center">
            <h6 class="text-muted mb-3">Sua Árvore</h6>
            <svg id="patientTree" width="100%" height="400" viewBox="0 0 300 400" style="max-width: 100%; display: block; margin: 0 auto;">
              <!-- Raízes -->
              <g id="patientRoots" opacity="0" style="transition: opacity 0.5s ease;">
                <path d="M150 350 Q130 375 110 400" stroke="#8B4513" stroke-width="4" fill="none"/>
                <path d="M150 350 Q170 375 190 400" stroke="#8B4513" stroke-width="4" fill="none"/>
                <path d="M150 350 Q150 380 150 400" stroke="#8B4513" stroke-width="3" fill="none"/>
              </g>

              <!-- Tronco -->
              <g id="patientTrunk" opacity="0" style="transition: opacity 0.5s ease;">
                <rect x="135" y="220" width="30" height="130" fill="#a0522d" rx="8"/>
                <ellipse cx="150" cy="220" rx="15" ry="8" fill="#8B4513"/>
              </g>

              <!-- Galhos menores -->
              <g id="patientBranches" opacity="0" style="transition: opacity 0.5s ease;">
                <line x1="150" y1="240" x2="90" y2="140" stroke="#8B4513" stroke-width="3"/>
                <line x1="150" y1="240" x2="210" y2="140" stroke="#8B4513" stroke-width="3"/>
                <line x1="150" y1="260" x2="80" y2="180" stroke="#8B4513" stroke-width="2"/>
                <line x1="150" y1="260" x2="220" y2="180" stroke="#8B4513" stroke-width="2"/>
              </g>

              <!-- Folhas -->
              <g id="patientLeaves" opacity="0" style="transition: opacity 0.5s ease;">
                <circle cx="80" cy="120" r="22" fill="#27ae60"/>
                <circle cx="150" cy="80" r="22" fill="#27ae60"/>
                <circle cx="220" cy="120" r="22" fill="#27ae60"/>
                <circle cx="70" cy="160" r="18" fill="#52b788"/>
                <circle cx="230" cy="160" r="18" fill="#52b788"/>
              </g>

              <!-- Frutos/Flores -->
              <g id="patientFruits" opacity="0" style="transition: opacity 0.5s ease;">
                <circle cx="150" cy="40" r="20" fill="#f1c40f"/>
                <circle cx="120" cy="60" r="15" fill="#f39c12"/>
                <circle cx="180" cy="60" r="15" fill="#f39c12"/>
              </g>

              <!-- sol de fundo -->
              <circle cx="280" cy="30" r="40" fill="#ffe082" opacity="0.3"/>
            </svg>

            <!-- Progresso -->
            <div class="progress mt-4" style="height: 6px;">
              <div id="treeProgressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%;"></div>
            </div>
            <small class="text-muted mt-2 d-block">
              Seção <span id="sectionCounter">1</span> de <span id="totalCounter">8</span>
            </small>
          </div>
        </div>
      </div>

      <!-- Formulário (lado direito) -->
      <div class="col-lg-8 order-lg-1">
        <div class="card border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
          <div class="card-body p-4">
            <form id="treeForm">
              <div id="sectionsContainer"></div>

              <!-- Navegação -->
              <div class="d-flex gap-2 mt-4" id="navButtons">
                <button type="button" class="btn btn-outline-secondary" id="prevSectionBtn" onclick="previousPatientSection()" style="display: none;">
                  <i class="fa-solid fa-arrow-left me-2"></i>Anterior
                </button>
                <button type="button" class="btn btn-primary flex-grow-1" id="nextSectionBtn" onclick="nextPatientSection()">
                  Próxima <i class="fa-solid fa-arrow-right ms-2"></i>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.answer-textarea {
  width: 100%;
  padding: 12px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-family: 'Segoe UI', sans-serif;
  font-size: 14px;
  resize: vertical;
  min-height: 100px;
  transition: all 0.3s ease;
  margin-bottom: 12px;
}

.answer-textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
  background: #f8f9ff;
}

.question-item {
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f0f0f0;
}

.question-item:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.question-text {
  font-weight: 600;
  margin-bottom: 10px;
  color: #333;
  font-size: 15px;
}

.section-title {
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 25px;
  display: flex;
  align-items: center;
  gap: 10px;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.section-content {
  animation: fadeInUp 0.5s ease;
}

@media (max-width: 992px) {
  #patientTree {
    max-height: 300px;
  }
}
</style>

<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<script>
const appUrl = '<?php echo $appUrl; ?>';
const taskId = new URLSearchParams(window.location.search).get('id');
const structure = <?php echo isset($structure) ? json_encode($structure) : '{}'; ?>;
const sections = structure.sections || [];

let currentSectionIndex = 0;
const sectionAnswers = {};

function showPatientSection(index) {
  if (index < 0 || index >= sections.length) return;

  currentSectionIndex = index;
  const section = sections[index];

  // Atualiza contador
  document.getElementById('sectionCounter').textContent = index + 1;
  
  // Anima árvore
  updateTreeProgress(index);

  // Renderiza seção
  const html = `
    <div class="section-content">
      <div class="section-title" style="color: ${section.color};">
        <span style="font-size: 24px;">${section.title.split(' ')[0]}</span>
        <span>${section.title}</span>
      </div>
      ${section.questions.map((q, i) => `
        <div class="question-item">
          <div class="question-text">${i + 1}. ${q}</div>
          <textarea class="answer-textarea" data-section="${section.key}" data-question="${i}" placeholder="Escreva sua resposta aqui..."></textarea>
        </div>
      `).join('')}
    </div>
  `;

  document.getElementById('sectionsContainer').innerHTML = html;

  // Restaura respostas anterior (se houver)
  if (sectionAnswers[section.key]) {
    document.querySelectorAll('.answer-textarea').forEach((ta, i) => {
      if (sectionAnswers[section.key][i]) {
        ta.value = sectionAnswers[section.key][i];
      }
    });
  }

  // Salva respostas ao sair
  document.querySelectorAll('.answer-textarea').forEach(ta => {
    ta.addEventListener('blur', function() {
      sectionAnswers[this.dataset.section] = sectionAnswers[this.dataset.section] || {};
      sectionAnswers[this.dataset.section][this.dataset.question] = this.value;
    });
  });

  // Atualiza botões
  document.getElementById('prevSectionBtn').style.display = index === 0 ? 'none' : 'block';
  document.getElementById('nextSectionBtn').textContent = index === sections.length - 1
    ? 'Próxima: Reflexão Final <i class="fa-solid fa-arrow-right ms-2"></i>'
    : 'Próxima <i class="fa-solid fa-arrow-right ms-2"></i>';

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateTreeProgress(sectionIndex) {
  const progress = (sectionIndex + 1) / sections.length;
  document.getElementById('treeProgressBar').style.width = (progress * 100) + '%';

  // Anima elementos da árvore
  if (progress >= 0.15) $('#patientRoots').css('opacity', 1);
  if (progress >= 0.3) $('#patientTrunk').css('opacity', 1);
  if (progress >= 0.5) $('#patientBranches').css('opacity', 1);
  if (progress >= 0.75) $('#patientLeaves').css('opacity', 1);
  if (progress >= 0.9) $('#patientFruits').css('opacity', 1);
}

function nextPatientSection() {
  if (currentSectionIndex < sections.length - 1) {
    showPatientSection(currentSectionIndex + 1);
  } else {
    showFinalReflection();
  }
}

function previousPatientSection() {
  if (currentSectionIndex > 0) {
    showPatientSection(currentSectionIndex - 1);
  }
}

function showFinalReflection() {
  const html = `
    <div class="section-content">
      <div class="section-title" style="color: #667eea;">
        <span style="font-size: 24px;">📖</span>
        <span>Reflexão Final - Sua História de Vida</span>
      </div>
      
      <div style="background: #f8f9ff; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
        <h6 class="mb-3">🔙 Passado</h6>
        <textarea class="answer-textarea" id="passadoReflection" placeholder="Qual é a história do seu passado? Quais desafios superou? Quais forças ganhou?"></textarea>
      </div>

      <div style="background: #f8f9ff; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
        <h6 class="mb-3">📍 Presente</h6>
        <textarea class="answer-textarea" id="presenteReflection" placeholder="Como descreveria sua vida atual? É diferente do passado? Enfrenta novos desafios?"></textarea>
      </div>

      <div style="background: #f8f9ff; padding: 20px; border-radius: 12px;">
        <h6 class="mb-3">🔮 Futuro</h6>
        <textarea class="answer-textarea" id="futuroReflection" placeholder="Como é seu futuro ideal? Seria diferente? Quem estará ao seu lado?"></textarea>
      </div>

      <div id="quillContainer" style="margin-top: 20px;">
        <label class="form-label">✍️ Reflexão Final Geral</label>
        <div id="editor" style="height: 250px; background: white;"></div>
      </div>
    </div>
  `;

  document.getElementById('sectionsContainer').innerHTML = html;
  document.getElementById('sectionCounter').textContent = sections.length + 1;
  document.getElementById('prevSectionBtn').style.display = 'block';
  document.getElementById('nextSectionBtn').innerHTML = '<i class="fa-solid fa-check me-2"></i>Finalizar';
  document.getElementById('nextSectionBtn').onclick = function() { completePatientTask(); };

  // Inicializa Quill
  const quill = new Quill('#editor', {
    theme: 'snow',
    modules: {
      toolbar: [
        ['bold', 'italic', 'underline'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        ['link']
      ]
    },
    placeholder: 'Escreva suas reflexões finais sobre sua história de vida...'
  });

  window.quillInstance = quill;
}

function completePatientTask() {
  const reflection = window.quillInstance ? window.quillInstance.root.innerHTML : '';

  if (!reflection || reflection.length < 10) {
    Swal.fire('Aviso', 'Por favor, escreva uma reflexão final', 'warning');
    return;
  }

  const btn = document.getElementById('nextSectionBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Salvando...';

  $.ajax({
    url: appUrl + '/patient.php?action=virtual-task-complete',
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({
      task_id: taskId,
      reflection: reflection,
      answers: sectionAnswers
    }),
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        Swal.fire({
          icon: 'success',
          title: 'Tarefa Concluída!',
          text: 'Sua árvore da vida foi salva com sucesso. O terapeuta verá em breve.',
          confirmButtonText: 'Voltar para Tarefas'
        }).then(() => {
          window.location.href = appUrl + '/patient.php?action=tasks';
        });
      } else {
        Swal.fire('Erro', res.message, 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check me-2"></i>Finalizar';
      }
    },
    error: function() {
      Swal.fire('Erro', 'Não foi possível salvar a tarefa', 'error');
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-check me-2"></i>Finalizar';
    }
  });
}

// Inicializa
$(document).ready(function() {
  document.getElementById('totalCounter').textContent = sections.length;
  showPatientSection(0);
});
</script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
