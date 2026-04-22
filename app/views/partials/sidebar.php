<?php
use App\Models\User;
use Helpers\Auth;

$currentAction = $_GET['action'] ?? '';
$role = (string) Auth::role();
$isPatientPreview = Auth::isPatientPreviewActive();
$displayRole = $isPatientPreview ? 'patient' : $role;
$displayName = $isPatientPreview ? (Auth::patientPreviewName() ?? Auth::name()) : Auth::name();
$therapistBrand = null;
$therapistBrandId = (int) (Auth::therapistId() ?? 0);
if ($therapistBrandId > 0) {
    $therapistBrand = (new User())->findTherapistById($therapistBrandId);
}

$sidebarBrandName = 'Tera-Tech';
$sidebarLogoPath = trim((string) ($therapistBrand['company_logo_path'] ?? ''));
$sidebarLogoUrl = $sidebarLogoPath !== '' ? ($appUrl . '/' . ltrim($sidebarLogoPath, '/')) : '';
$patientFreeTier = $displayRole === 'patient' && !$isPatientPreview && !($_SESSION['patient_has_active_plan'] ?? true);

$roleLabels = [
    'super_admin' => 'Administrador',
    'therapist' => 'Terapeuta',
    'patient' => 'Paciente',
];

$roleDescriptions = [
    'super_admin' => 'Gestão integral da plataforma e da rede terapêutica.',
    'therapist' => 'Organize atendimentos, conteúdos e acompanhamento clínico.',
    'patient' => 'Acesse práticas, materiais e sua jornada de cuidado.',
];

$makeLink = static function (string $label, string $description, string $icon, string $href, bool $active, bool $locked = false, string $badge = ''): array {
    return [
        'label' => $label,
        'description' => $description,
        'icon' => $icon,
        'href' => $href,
        'active' => $active,
        'locked' => $locked,
        'badge' => $badge,
    ];
};

$navGroups = [];

if ($displayRole === 'super_admin') {
    $navGroups = [
        [
            'title' => 'Gestão da plataforma',
            'description' => 'Visão macro da operação e dos acessos.',
            'icon' => 'fa-solid fa-sitemap',
            'items' => [
                $makeLink('Dashboard', 'Indicadores e panorama geral', 'fa-solid fa-chart-pie', $appUrl . '/dashboard.php?action=admin-dashboard', $currentAction === 'admin-dashboard'),
                $makeLink('Terapeutas', 'Cadastros e acompanhamento da rede', 'fa-solid fa-user-doctor', $appUrl . '/dashboard.php?action=therapists', str_starts_with($currentAction, 'therapists')),
                $makeLink('Pacotes pacientes', 'Planos e ofertas disponíveis', 'fa-solid fa-box-open', $appUrl . '/dashboard.php?action=patient-packages', str_starts_with($currentAction, 'patient-packages')),
            ],
        ],
    ];
} elseif ($displayRole === 'therapist') {
    $navGroups = [
        [
            'title' => 'Panorama clínico',
            'description' => 'Acompanhe agenda, indicadores e fluxo do consultório.',
            'icon' => 'fa-solid fa-compass',
            'items' => [
                $makeLink('Dashboard', 'Resumo da rotina terapêutica', 'fa-solid fa-chart-line', $appUrl . '/dashboard.php?action=therapist-dashboard', $currentAction === 'therapist-dashboard'),
                $makeLink('Agenda', 'Sessões, horários e compromissos', 'fa-solid fa-calendar-days', $appUrl . '/dashboard.php?action=therapist-schedule', str_starts_with($currentAction, 'therapist-schedule')),
                $makeLink('Financeiro', 'Recebimentos e indicadores', 'fa-solid fa-wallet', $appUrl . '/dashboard.php?action=therapist-financial', str_starts_with($currentAction, 'therapist-financial')),
            ],
        ],
        [
            'title' => 'Pacientes e jornadas',
            'description' => 'Cadastros, acompanhamento e experiência do paciente.',
            'icon' => 'fa-solid fa-users-viewfinder',
            'items' => [
                $makeLink('Pacientes', 'Prontuários, histórico e tarefas', 'fa-solid fa-users', $appUrl . '/dashboard.php?action=patients', str_starts_with($currentAction, 'patients')),
                $makeLink('Acessar como paciente', 'Valide a experiência do portal', 'fa-solid fa-right-to-bracket', $appUrl . '/dashboard.php?action=patients-preview-menu', $currentAction === 'patients-preview-menu'),
            ],
        ],
        [
            'title' => 'Conteúdos terapêuticos',
            'description' => 'Materiais, práticas e recursos de acolhimento.',
            'icon' => 'fa-solid fa-seedling',
            'items' => [
                $makeLink('Materiais', 'Arquivos, links e conteúdos de apoio', 'fa-solid fa-book-open', $appUrl . '/dashboard.php?action=therapist-materials', str_starts_with($currentAction, 'therapist-materials')),
                $makeLink('Livros', 'Biblioteca de leituras recomendadas', 'fa-solid fa-book', $appUrl . '/dashboard.php?action=therapist-books', str_starts_with($currentAction, 'therapist-books')),
                $makeLink('teraTube', 'Vídeos e conteúdos audiovisuais', 'fa-solid fa-circle-play', $appUrl . '/dashboard.php?action=therapist-teratube', str_starts_with($currentAction, 'therapist-teratube')),
                $makeLink('Mensagens diárias', 'Mensagens breves e recorrentes', 'fa-solid fa-envelope-open-text', $appUrl . '/dashboard.php?action=therapist-messages', str_starts_with($currentAction, 'therapist-messages')),
                $makeLink('Devocional', 'Reflexões e registros guiados', 'fa-solid fa-sun', $appUrl . '/dashboard.php?action=therapist-devotionals', str_starts_with($currentAction, 'therapist-devotionals')),
                $makeLink('Pai, fala comigo', 'Palavras de fé e reflexão', 'fa-solid fa-cross', $appUrl . '/dashboard.php?action=therapist-faith-words', str_starts_with($currentAction, 'therapist-faith-words')),
                $makeLink('Meditação guiada', 'Áudios, cartas e respiração', 'fa-solid fa-headphones', $appUrl . '/dashboard.php?action=therapist-guided-meditations', str_starts_with($currentAction, 'therapist-guided-meditations')),
                $makeLink('Orações', 'Práticas contemplativas e apoio espiritual', 'fa-solid fa-hands-praying', $appUrl . '/dashboard.php?action=therapist-prayers', str_starts_with($currentAction, 'therapist-prayers')),
                $makeLink('Cartas de cura', 'Conteúdos restaurativos personalizados', 'fa-solid fa-clover', $appUrl . '/dashboard.php?action=therapist-healing-letters', str_starts_with($currentAction, 'therapist-healing-letters')),
            ],
        ],
        [
            'title' => 'Ferramentas de apoio',
            'description' => 'Automação, repertório e suporte operacional.',
            'icon' => 'fa-solid fa-sliders',
            'items' => [
                $makeLink('Tarefas pré-definidas', 'Modelos prontos para reutilizar', 'fa-solid fa-list-check', $appUrl . '/dashboard.php?action=therapist-predefined-tasks', str_starts_with($currentAction, 'therapist-predefined-tasks')),
                $makeLink('Tarefas dinâmicas', 'Fluxos interativos para pacientes', 'fa-solid fa-star', $appUrl . '/dashboard.php?action=virtual-tasks', str_starts_with($currentAction, 'virtual-tasks')),
                $makeLink('Manual do sistema', 'Referência rápida da plataforma', 'fa-solid fa-book-atlas', $appUrl . '/dashboard.php?action=therapist-system-manual', str_starts_with($currentAction, 'therapist-system-manual')),
            ],
        ],
    ];
} elseif ($displayRole === 'patient') {
    $premiumBadge = $patientFreeTier ? 'Premium' : '';
  $patientBaseItems = [
    $makeLink('Minha assinatura', 'Plano atual e opções de upgrade', 'fa-solid fa-crown', $appUrl . '/patient.php?action=subscription-plans', str_starts_with($currentAction, 'subscription-')),
    $makeLink('Dashboard', 'Resumo da sua jornada terapêutica', 'fa-solid fa-gauge', $appUrl . '/patient.php?action=dashboard', $currentAction === 'dashboard'),
  ];

  if (!$isPatientPreview) {
    $patientBaseItems[] = $makeLink('Minha conta', 'Dados pessoais e acesso', 'fa-solid fa-circle-user', $appUrl . '/patient.php?action=my-account', $currentAction === 'my-account');
  }

    $navGroups = [
        [
            'title' => 'Sua base',
            'description' => 'Resumo do acesso, plano e informações pessoais.',
            'icon' => 'fa-solid fa-house-heart',
      'items' => $patientBaseItems,
        ],
        [
            'title' => 'Práticas do dia',
            'description' => 'Atividades para presença, reflexão e equilíbrio.',
            'icon' => 'fa-solid fa-spa',
            'items' => [
                $makeLink('Minhas tarefas', 'Atividades enviadas pelo terapeuta', 'fa-solid fa-list-check', $appUrl . '/patient.php?action=tasks', $currentAction === 'tasks'),
                $makeLink('Devocional', 'Leituras e registros do dia', 'fa-solid fa-sun', $appUrl . '/patient.php?action=devotionals', str_starts_with($currentAction, 'devotional') || $currentAction === 'devotionals', $patientFreeTier, $premiumBadge),
                $makeLink('Diário da gratidão', 'Escreva e acompanhe reflexões', 'fa-solid fa-heart', $appUrl . '/patient.php?action=gratitude', str_starts_with($currentAction, 'gratitude'), $patientFreeTier, $premiumBadge),
                $makeLink('Pai, fala comigo', 'Mensagem de fé e acolhimento', 'fa-solid fa-book-bible', $appUrl . '/patient.php?action=father-word', str_starts_with($currentAction, 'father-word'), $patientFreeTier, $premiumBadge),
                $makeLink('Meditação guiada', 'Respire e desacelere com apoio guiado', 'fa-solid fa-compact-disc', $appUrl . '/patient.php?action=guided-meditations', str_starts_with($currentAction, 'guided-meditation') || $currentAction === 'guided-meditations', $patientFreeTier, $premiumBadge),
                $makeLink('Exercício de respiração', 'Prática rápida de presença', 'fa-solid fa-lungs', $appUrl . '/patient.php?action=breathing-game', $currentAction === 'breathing-game', $patientFreeTier, $premiumBadge),
                $makeLink('Orações', 'Momentos de contemplação e apoio', 'fa-solid fa-hands-praying', $appUrl . '/patient.php?action=prayers', str_starts_with($currentAction, 'prayer') || $currentAction === 'prayers', $patientFreeTier, $premiumBadge),
            ],
        ],
        [
            'title' => 'Conteúdos e recursos',
            'description' => 'Materiais para aprofundar a jornada terapêutica.',
            'icon' => 'fa-solid fa-book-open-reader',
            'items' => [
                $makeLink('Meus materiais', 'Arquivos e conteúdos compartilhados', 'fa-solid fa-book', $appUrl . '/patient.php?action=materials', $currentAction === 'materials', $patientFreeTier, $premiumBadge),
                $makeLink('Livros', 'Biblioteca de leituras sugeridas', 'fa-solid fa-book-open-reader', $appUrl . '/patient.php?action=books', str_starts_with($currentAction, 'book') || $currentAction === 'books', $patientFreeTier, $premiumBadge),
                $makeLink('teraTube', 'Vídeos de apoio e inspiração', 'fa-solid fa-circle-play', $appUrl . '/patient.php?action=teratube', str_starts_with($currentAction, 'teratube'), $patientFreeTier, $premiumBadge),
                $makeLink('Meus conteúdos', 'Itens salvos e preferidos', 'fa-solid fa-bookmark', $appUrl . '/patient.php?action=my-contents', $currentAction === 'my-contents', $patientFreeTier, $premiumBadge),
                $makeLink('Mensageiro', 'Mensagens especiais para reflexão', 'fa-solid fa-box-open', $appUrl . '/patient.php?action=messenger', str_starts_with($currentAction, 'messenger'), $patientFreeTier, $premiumBadge),
            ],
        ],
    ];
}

$renderLink = static function (array $item): void {
    $classes = ['sidebar-link'];
    if (!empty($item['active'])) {
        $classes[] = 'active';
    }
    if (!empty($item['locked'])) {
        $classes[] = 'sidebar-link--locked';
    }
    ?>
    <a class="<?php echo htmlspecialchars(implode(' ', $classes)); ?>" href="<?php echo htmlspecialchars((string) $item['href']); ?>">
      <span class="sidebar-link-icon"><i class="<?php echo htmlspecialchars((string) $item['icon']); ?>"></i></span>
      <span class="sidebar-link-copy">
        <span class="sidebar-link-title"><?php echo htmlspecialchars((string) $item['label']); ?></span>
        <small class="sidebar-link-description"><?php echo htmlspecialchars((string) $item['description']); ?></small>
      </span>
      <?php if (!empty($item['badge'])): ?>
        <span class="sidebar-link-badge"><?php echo htmlspecialchars((string) $item['badge']); ?></span>
      <?php endif; ?>
    </a>
    <?php
};
?>
<aside class="app-sidebar">
  <div class="sidebar-shell">
    <div class="sidebar-brand-row">
      <div class="sidebar-brand">
        <div class="sidebar-brand-logo <?php echo $sidebarLogoUrl !== '' ? 'has-image' : ''; ?>">
          <?php if ($sidebarLogoUrl !== ''): ?>
            <img class="sidebar-brand-logo-image" src="<?php echo htmlspecialchars($sidebarLogoUrl); ?>" alt="Logo da empresa" width="46" height="46">
          <?php else: ?>
            <i class="fa-solid fa-wave-square"></i>
          <?php endif; ?>
        </div>
        <div class="sidebar-brand-copy">
          <span class="sidebar-brand-title"><?php echo htmlspecialchars($sidebarBrandName); ?></span>
          <small class="sidebar-brand-subtitle">Plataforma terapêutica</small>
        </div>
      </div>
      <button class="btn btn-sm sidebar-close-btn d-lg-none" type="button" aria-label="Fechar menu" data-sidebar-close>
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="sidebar-intro">
      <span class="sidebar-intro-kicker">Cuidado com leveza</span>
      <p class="sidebar-intro-text"><?php echo htmlspecialchars($roleDescriptions[$displayRole] ?? 'Navegação principal do sistema.'); ?></p>
    </div>

    <div class="sidebar-user-card">
      <span class="sidebar-user-label">Conectado como</span>
      <strong class="sidebar-user-name"><?php echo htmlspecialchars((string) $displayName); ?></strong>
      <span class="sidebar-user-role"><?php echo htmlspecialchars($roleLabels[$displayRole] ?? 'Usuário'); ?></span>
      <?php if ($isPatientPreview): ?>
        <div class="sidebar-user-preview-note">Modo visualização do paciente ativo</div>
      <?php endif; ?>
    </div>

    <nav class="sidebar-nav" id="sidebarAccordion" aria-label="Menu principal">
      <?php foreach ($navGroups as $groupIndex => $group): ?>
        <?php $hasActiveItem = array_reduce($group['items'], static fn(bool $carry, array $item): bool => $carry || !empty($item['active']), false); ?>
        <?php $panelId = 'sidebar-section-' . $displayRole . '-' . $groupIndex; ?>
        <section class="sidebar-section">
          <button class="sidebar-section-toggle <?php echo $hasActiveItem ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo htmlspecialchars($panelId); ?>" aria-expanded="<?php echo $hasActiveItem ? 'true' : 'false'; ?>" aria-controls="<?php echo htmlspecialchars($panelId); ?>">
            <span class="sidebar-section-toggle-copy">
              <span class="sidebar-section-icon"><i class="<?php echo htmlspecialchars((string) $group['icon']); ?>"></i></span>
              <span class="sidebar-section-headings">
                <span class="sidebar-section-title"><?php echo htmlspecialchars((string) $group['title']); ?></span>
                <small class="sidebar-section-description"><?php echo htmlspecialchars((string) $group['description']); ?></small>
              </span>
            </span>
            <i class="fa-solid fa-chevron-down sidebar-section-caret"></i>
          </button>
          <div class="collapse sidebar-section-panel <?php echo $hasActiveItem ? 'show' : ''; ?>" id="<?php echo htmlspecialchars($panelId); ?>">
            <div class="sidebar-section-links">
              <?php foreach ($group['items'] as $item): ?>
                <?php $renderLink($item); ?>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
      <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
      <?php if ($isPatientPreview): ?>
        <a class="btn btn-outline-warning w-100 mb-2" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-preview-stop"><i class="fa-solid fa-rotate-left"></i> Voltar ao terapeuta</a>
      <?php endif; ?>
      <a class="btn btn-outline-danger w-100" href="<?php echo $appUrl; ?>/index.php?action=logout"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
    </div>
  </div>
</aside>

