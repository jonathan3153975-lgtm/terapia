<?php
$title = 'Preview - Árvore da Vida | Tera-Tech';
include __DIR__ . '/../../partials/header.php';
?>

<div style="padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
  <div class="container" style="max-width: 900px;">
    <div class="mb-4">
      <h1 class="h2 text-white mb-1"><i class="fa-solid fa-tree me-2"></i>Árvore da Vida</h1>
      <p class="text-white-50">Explore sua história a partir de diferentes perspectivas da sua vida</p>
    </div>

    <div class="card border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
      <div class="card-body p-4">
        <div id="treeProgressContainer" class="mb-4">
          <div class="progress" style="height: 8px;">
            <div id="treeProgress" class="progress-bar" role="progressbar" style="width: 0%;"></div>
          </div>
          <small class="text-muted mt-2 d-block">
            Seção <span id="currentSection">1</span> de <span id="totalSections">8</span>
          </small>
        </div>

        <!-- Árvore SVG Animada -->
        <div style="text-align: center; margin: 30px 0;">
          <svg id="previewTree" width="250" height="350" viewBox="0 0 250 350" style="max-width: 100%;">
            <!-- Raízes -->
            <g id="previewRoots" opacity="0">
              <path d="M125 300 Q115 330 105 350" stroke="#8B4513" stroke-width="3" fill="none"/>
              <path d="M125 300 Q135 330 145 350" stroke="#8B4513" stroke-width="3" fill="none"/>
            </g>
            <!-- Tronco -->
            <g id="previewTrunk" opacity="0">
              <rect x="115" y="200" width="20" height="100" fill="#a0522d"/>
            </g>
            <!-- Galhos -->
            <g id="previewBranches" opacity="0">
              <line x1="125" y1="220" x2="80" y2="150" stroke="#8B4513" stroke-width="2"/>
              <line x1="125" y1="220" x2="170" y2="150" stroke="#8B4513" stroke-width="2"/>
            </g>
            <!-- Folhas -->
            <g id="previewLeaves" opacity="0">
              <circle cx="70" cy="130" r="20" fill="#27ae60"/>
              <circle cx="125" cy="100" r="20" fill="#27ae60"/>
              <circle cx="180" cy="130" r="20" fill="#27ae60"/>
            </g>
            <!-- Frutos -->
            <g id="previewFruits" opacity="0">
              <circle cx="125" cy="50" r="18" fill="#f1c40f"/>
            </g>
          </svg>
        </div>

        <!-- Questões -->
        <div id="questionsContainer"></div>

        <!-- Botões de navegação -->
        <div class="d-flex gap-2 mt-4" id="navigationButtons">
          <button class="btn btn-outline-secondary" id="prevBtn" onclick="previousSection()" style="display: none;">
            <i class="fa-solid fa-arrow-left me-2"></i>Anterior
          </button>
          <button class="btn btn-primary flex-grow-1" id="nextBtn" onclick="nextSection()">
            Próxima <i class="fa-solid fa-arrow-right ms-2"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Informação de teste -->
    <div class="alert alert-info mt-3 mb-0">
      <i class="fa-solid fa-info-circle me-2"></i>
      <strong>Modo Teste:</strong> Isso é uma visualização. Para enviar ao paciente, volte e clique em "Enviar Tarefa"
    </div>
  </div>
</div>

<style>
.form-group-preview {
  margin-bottom: 20px;
}

.form-group-preview label {
  font-weight: 600;
  margin-bottom: 10px;
  display: block;
  color: #333;
}

.form-group-preview textarea {
  width: 100%;
  padding: 12px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-family: inherit;
  font-size: 14px;
  resize: vertical;
  min-height: 80px;
  transition: border-color 0.3s ease;
}

.form-group-preview textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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

.section-questions {
  animation: fadeInUp 0.5s ease;
}
</style>

<script>
const structure = <?php echo json_encode($structure); ?>;
let currentSectionIndex = 0;
const sections = structure.sections;
const answers = {};

function showSection(index) {
  if (index < 0 || index >= sections.length) return;

  currentSectionIndex = index;
  const section = sections[index];

  // Atualiza progresso
  document.getElementById('currentSection').textContent = index + 1;
  document.getElementById('totalSections').textContent = sections.length;
  document.getElementById('treeProgress').style.width = ((index + 1) / sections.length * 100) + '%';

  // Anima árvore gradualmente
  animateTreeProgress(index);

  // Mostra perguntas
  const html = `
    <div class="section-questions">
      <h5 style="color: ${section.color}; margin-bottom: 20px;">
        ${section.title}
      </h5>
      ${section.questions.map((q, i) => `
        <div class="form-group-preview">
          <label>${q}</label>
          <textarea placeholder="Sua resposta aqui..." data-section="${section.key}" data-question="${i}"></textarea>
        </div>
      `).join('')}
    </div>
  `;

  document.getElementById('questionsContainer').innerHTML = html;

  // Atualiza botões
  document.getElementById('prevBtn').style.display = index === 0 ? 'none' : 'block';
  document.getElementById('nextBtn').textContent = index === sections.length - 1 
    ? 'Finalizar <i class="fa-solid fa-check ms-2"></i>' 
    : 'Próxima <i class="fa-solid fa-arrow-right ms-2"></i>';

  // Salva respostas ao mudar
  document.querySelectorAll('textarea').forEach(ta => {
    ta.addEventListener('blur', function() {
      answers[this.dataset.section] = answers[this.dataset.section] || {};
      answers[this.dataset.section][this.dataset.question] = this.value;
    });
  });
}

function animateTreeProgress(progress) {
  const ratio = (progress + 1) / sections.length;

  // Anima opacidade das partes da árvore
  document.getElementById('previewRoots').style.opacity = ratio >= 0.2 ? '1' : '0';
  document.getElementById('previewTrunk').style.opacity = ratio >= 0.4 ? '1' : '0';
  document.getElementById('previewBranches').style.opacity = ratio >= 0.6 ? '1' : '0';
  document.getElementById('previewLeaves').style.opacity = ratio >= 0.8 ? '1' : '0';
  document.getElementById('previewFruits').style.opacity = ratio >= 1 ? '1' : '0';
}

function nextSection() {
  if (currentSectionIndex < sections.length - 1) {
    showSection(currentSectionIndex + 1);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } else {
    // Última seção - mostrar reflexão final
    showFinalReflection();
  }
}

function previousSection() {
  if (currentSectionIndex > 0) {
    showSection(currentSectionIndex - 1);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
}

function showFinalReflection() {
  const html = `
    <div style="text-align: center; padding: 40px 0;">
      <i class="fa-solid fa-check-circle" style="font-size: 60px; color: #27ae60; margin-bottom: 20px; display: block;"></i>
      <h4>Excelente! Você completou todas as seções</h4>
      <p class="text-muted mb-4">Agora complete sua reflexão final sobre sua história de vida</p>
    </div>
    <div class="form-group-preview">
      <label>✍️ Reflexão Final - Sua História de Vida</label>
      <textarea id="finalReflection" placeholder="Escreva aqui suas reflexões sobre sua história de vida, aprendizados e perspectivas..."></textarea>
    </div>
    <div class="d-flex gap-2 mt-4">
      <button class="btn btn-outline-secondary" onclick="previousSection()">
        <i class="fa-solid fa-arrow-left me-2"></i>Voltar
      </button>
      <button class="btn btn-success flex-grow-1" onclick="completePreview()">
        <i class="fa-solid fa-check me-2"></i>Finalizar Teste
      </button>
    </div>
  `;

  document.getElementById('questionsContainer').innerHTML = html;
  document.getElementById('currentSection').textContent = sections.length + 1;
  document.getElementById('totalSections').textContent = sections.length + 1;
  document.getElementById('treeProgress').style.width = '100%';
  document.getElementById('prevBtn').style.display = 'none';
  document.getElementById('nextBtn').style.display = 'none';

  // Anima completamente
  document.getElementById('previewRoots').style.opacity = '1';
  document.getElementById('previewTrunk').style.opacity = '1';
  document.getElementById('previewBranches').style.opacity = '1';
  document.getElementById('previewLeaves').style.opacity = '1';
  document.getElementById('previewFruits').style.opacity = '1';
}

function completePreview() {
  Swal.fire({
    icon: 'success',
    title: 'Teste Concluído!',
    text: 'A tarefa funcionou perfeitamente. Você pode enviar para o paciente agora.',
    confirmButtonText: 'Fechar'
  }).then(() => {
    window.close();
  });
}

// Inicializa
window.addEventListener('load', function() {
  if (!Array.isArray(sections) || sections.length === 0) {
    document.getElementById('questionsContainer').innerHTML = `
      <div class="alert alert-warning mb-0">
        <strong>Estrutura indisponível:</strong> nenhuma seção foi carregada para o preview.
      </div>
    `;
    document.getElementById('nextBtn').disabled = true;
    return;
  }

  showSection(0);
});
</script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
