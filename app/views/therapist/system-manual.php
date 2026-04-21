<?php $title = 'Manual do sistema'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<style>
.system-manual-layout {
  display: grid;
  grid-template-columns: minmax(0, 320px) minmax(0, 1fr);
  gap: 1.5rem;
}

.system-manual-sidebar {
  position: sticky;
  top: 1.5rem;
  align-self: start;
}

.system-manual-search,
.system-manual-index,
.system-manual-card,
.system-manual-stat {
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 14px 32px rgba(15, 23, 42, 0.06);
}

.system-manual-search,
.system-manual-index {
  background: #fff;
  border-radius: 1rem;
}

.system-manual-search {
  padding: 1rem;
  margin-bottom: 1rem;
}

.system-manual-search-label {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.system-manual-search-label strong {
  font-size: 0.95rem;
}

.system-manual-index {
  padding: 1rem;
}

.system-manual-index a {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.625rem 0.75rem;
  border-radius: 0.85rem;
  color: #334155;
  text-decoration: none;
  transition: background-color .2s ease, color .2s ease, transform .2s ease;
}

.system-manual-index a:hover,
.system-manual-index a:focus {
  background: #eff6ff;
  color: #0f172a;
  transform: translateX(2px);
}

.system-manual-stat {
  background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  border-radius: 1rem;
  padding: 1rem;
  height: 100%;
}

.system-manual-stat small {
  color: #64748b;
}

.system-manual-stat strong {
  display: block;
  margin-top: 0.35rem;
  font-size: 1.75rem;
  color: #0f172a;
}

.system-manual-group + .system-manual-group {
  margin-top: 1.5rem;
}

.system-manual-group-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.85rem;
}

.system-manual-card {
  background: #fff;
  border-radius: 1rem;
  padding: 1.25rem;
}

.system-manual-card + .system-manual-card {
  margin-top: 1rem;
}

.system-manual-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1rem;
}

.system-manual-card-title {
  display: flex;
  align-items: flex-start;
  gap: 0.85rem;
}

.system-manual-card-title i {
  width: 2.35rem;
  height: 2.35rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.8rem;
  background: #eff6ff;
  color: #1d4ed8;
}

.system-manual-summary {
  color: #475569;
  margin-bottom: 1rem;
}

.system-manual-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.system-manual-panel {
  background: #f8fafc;
  border-radius: 0.9rem;
  padding: 0.95rem;
}

.system-manual-panel h6 {
  margin-bottom: 0.65rem;
}

.system-manual-panel ul {
  margin-bottom: 0;
  padding-left: 1.1rem;
}

.system-manual-panel li + li {
  margin-top: 0.45rem;
}

.system-manual-routes {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 1rem;
}

.system-manual-route {
  border-radius: 999px;
  background: #f1f5f9;
  color: #334155;
  font-size: 0.85rem;
  padding: 0.45rem 0.8rem;
}

.system-manual-hidden {
  display: none !important;
}

@media (max-width: 1199.98px) {
  .system-manual-layout {
    grid-template-columns: 1fr;
  }

  .system-manual-sidebar {
    position: static;
  }
}

@media (max-width: 767.98px) {
  .system-manual-grid {
    grid-template-columns: 1fr;
  }

  .system-manual-card-header,
  .system-manual-group-header,
  .system-manual-search-label {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

<?php
$indexGroups = [];
foreach ($groupedSections as $groupName => $items) {
    foreach ($items as $item) {
        $indexGroups[$groupName][] = [
            'id' => (string) ($item['id'] ?? ''),
            'title' => (string) ($item['title'] ?? ''),
            'icon' => (string) ($item['icon'] ?? 'fa-solid fa-circle'),
        ];
    }
}
?>

<div class="container-fluid page-wrap">
  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
      <h3 class="mb-1">Manual do sistema</h3>
      <p class="text-muted mb-0">Consulta rápida para o terapeuta entender onde cadastrar, editar, visualizar e como cada bloco repercute no portal do paciente.</p>
    </div>
    <span class="badge text-bg-light border px-3 py-2" id="systemManualVisibleBadge"><?php echo (int) $totalBlocks; ?> blocos visíveis</span>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
      <div class="system-manual-stat">
        <small>Blocos documentados</small>
        <strong><?php echo (int) $totalBlocks; ?></strong>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="system-manual-stat">
        <small>Com reflexo no paciente</small>
        <strong><?php echo (int) $patientFacingCount; ?></strong>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="system-manual-stat">
        <small>Somente internos</small>
        <strong><?php echo (int) $internalOnlyCount; ?></strong>
      </div>
    </div>
  </div>

  <div class="system-manual-layout">
    <aside class="system-manual-sidebar">
      <section class="system-manual-search">
        <div class="system-manual-search-label">
          <strong>Pesquisar no manual</strong>
          <small class="text-muted" id="systemManualSearchFeedback">Busque por módulo, ação ou impacto</small>
        </div>
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
          <input class="form-control" type="search" id="systemManualSearchInput" placeholder="Ex.: assinatura, meditação, editar paciente" value="<?php echo htmlspecialchars((string) $initialSearch); ?>">
          <button class="btn btn-outline-secondary" type="button" id="systemManualClearBtn">Limpar</button>
        </div>
      </section>

      <nav class="system-manual-index">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <strong>Índice rápido</strong>
          <small class="text-muted"><?php echo (int) $totalBlocks; ?> blocos</small>
        </div>
        <?php foreach ($indexGroups as $groupName => $items): ?>
          <div class="mb-3 system-manual-index-group" data-manual-group-name="<?php echo htmlspecialchars((string) $groupName); ?>">
            <div class="small text-uppercase text-muted fw-semibold mb-2"><?php echo htmlspecialchars((string) $groupName); ?></div>
            <div class="d-grid gap-1">
              <?php foreach ($items as $item): ?>
                <a href="#<?php echo htmlspecialchars((string) $item['id']); ?>" data-manual-index-item>
                  <i class="<?php echo htmlspecialchars((string) $item['icon']); ?>"></i>
                  <span><?php echo htmlspecialchars((string) $item['title']); ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </nav>
    </aside>

    <div>
      <div class="alert alert-light border mb-4" id="systemManualEmptyState" style="display:none;">
        Nenhum bloco encontrado para essa busca. Tente pesquisar pelo nome do módulo, pela ação desejada ou por palavras como paciente, assinatura, tarefa, editar ou visualizar.
      </div>

      <?php foreach ($groupedSections as $groupName => $items): ?>
        <section class="system-manual-group" data-manual-group>
          <div class="system-manual-group-header">
            <h4 class="h5 mb-0"><?php echo htmlspecialchars((string) $groupName); ?></h4>
            <span class="badge rounded-pill text-bg-light border" data-manual-group-count><?php echo count($items); ?> bloco(s)</span>
          </div>

          <?php foreach ($items as $section): ?>
            <?php
              $searchParts = [
                  (string) ($section['title'] ?? ''),
                  (string) ($section['summary'] ?? ''),
                  (string) ($section['patient_label'] ?? ''),
                  (string) ($section['patient_delivery'] ?? ''),
              ];

              foreach ((array) ($section['operations'] ?? []) as $operation) {
                  $searchParts[] = (string) ($operation['label'] ?? '');
                  $searchParts[] = (string) ($operation['text'] ?? '');
              }

              foreach ((array) ($section['impacts'] ?? []) as $impact) {
                  $searchParts[] = (string) $impact;
              }

              foreach ((array) ($section['keywords'] ?? []) as $keyword) {
                  $searchParts[] = (string) $keyword;
              }

              foreach ((array) ($section['routes'] ?? []) as $route) {
                  $searchParts[] = (string) $route;
              }

              $searchPayload = strtolower(implode(' ', $searchParts));
            ?>
            <article
              class="system-manual-card"
              id="<?php echo htmlspecialchars((string) ($section['id'] ?? '')); ?>"
              data-manual-card
              data-manual-search="<?php echo htmlspecialchars($searchPayload); ?>"
            >
              <div class="system-manual-card-header">
                <div class="system-manual-card-title">
                  <i class="<?php echo htmlspecialchars((string) ($section['icon'] ?? 'fa-solid fa-circle')); ?>"></i>
                  <div>
                    <h5 class="mb-1"><?php echo htmlspecialchars((string) ($section['title'] ?? '')); ?></h5>
                    <div class="d-flex flex-wrap gap-2">
                      <span class="badge text-bg-primary-subtle border border-primary-subtle text-primary-emphasis"><?php echo htmlspecialchars((string) ($section['group'] ?? '')); ?></span>
                      <span class="badge text-bg-light border"><?php echo htmlspecialchars((string) ($section['patient_label'] ?? '')); ?></span>
                    </div>
                  </div>
                </div>
              </div>

              <p class="system-manual-summary"><?php echo htmlspecialchars((string) ($section['summary'] ?? '')); ?></p>

              <div class="system-manual-grid">
                <div class="system-manual-panel">
                  <h6>Operações do terapeuta</h6>
                  <ul>
                    <?php foreach ((array) ($section['operations'] ?? []) as $operation): ?>
                      <li>
                        <strong><?php echo htmlspecialchars((string) ($operation['label'] ?? '')); ?>:</strong>
                        <?php echo htmlspecialchars((string) ($operation['text'] ?? '')); ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>

                <div class="system-manual-panel">
                  <h6>Como chega ao paciente</h6>
                  <p class="mb-0"><?php echo htmlspecialchars((string) ($section['patient_delivery'] ?? '')); ?></p>
                </div>

                <div class="system-manual-panel" style="grid-column: 1 / -1;">
                  <h6>Impacto no restante do sistema</h6>
                  <ul>
                    <?php foreach ((array) ($section['impacts'] ?? []) as $impact): ?>
                      <li><?php echo htmlspecialchars((string) $impact); ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>

              <?php if (!empty($section['routes'])): ?>
                <div class="system-manual-routes">
                  <?php foreach ((array) $section['routes'] as $route): ?>
                    <span class="system-manual-route"><?php echo htmlspecialchars((string) $route); ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </section>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
  var searchInput = document.getElementById('systemManualSearchInput');
  var clearBtn = document.getElementById('systemManualClearBtn');
  var searchFeedback = document.getElementById('systemManualSearchFeedback');
  var visibleBadge = document.getElementById('systemManualVisibleBadge');
  var emptyState = document.getElementById('systemManualEmptyState');
  var groups = Array.prototype.slice.call(document.querySelectorAll('[data-manual-group]'));
  var cards = Array.prototype.slice.call(document.querySelectorAll('[data-manual-card]'));
  var indexItems = Array.prototype.slice.call(document.querySelectorAll('[data-manual-index-item]'));
  var totalCards = cards.length;

  var normalize = function (value) {
    return (value || '').toString().toLowerCase().trim();
  };

  var updateView = function () {
    var term = normalize(searchInput ? searchInput.value : '');
    var visibleCount = 0;

    cards.forEach(function (card) {
      var payload = normalize(card.getAttribute('data-manual-search'));
      var isVisible = term === '' || payload.indexOf(term) !== -1;
      card.classList.toggle('system-manual-hidden', !isVisible);
      if (isVisible) {
        visibleCount++;
      }
    });

    groups.forEach(function (group) {
      var groupCards = Array.prototype.slice.call(group.querySelectorAll('[data-manual-card]'));
      var groupVisibleCount = groupCards.filter(function (card) {
        return !card.classList.contains('system-manual-hidden');
      }).length;
      var countBadge = group.querySelector('[data-manual-group-count]');
      group.classList.toggle('system-manual-hidden', groupVisibleCount === 0);
      if (countBadge) {
        countBadge.textContent = groupVisibleCount + ' bloco(s)';
      }
    });

    indexItems.forEach(function (item) {
      var targetId = (item.getAttribute('href') || '').replace('#', '');
      var target = targetId ? document.getElementById(targetId) : null;
      var hide = !target || target.classList.contains('system-manual-hidden');
      item.classList.toggle('system-manual-hidden', hide);
    });

    if (visibleBadge) {
      visibleBadge.textContent = visibleCount + ' bloco(s) visíveis';
    }

    if (searchFeedback) {
      searchFeedback.textContent = term === ''
        ? 'Busque por módulo, ação ou impacto'
        : 'Pesquisa ativa: ' + visibleCount + ' de ' + totalCards + ' bloco(s)';
    }

    if (emptyState) {
      emptyState.style.display = visibleCount === 0 ? '' : 'none';
    }
  };

  if (searchInput) {
    searchInput.addEventListener('input', updateView);
  }

  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      if (!searchInput) {
        return;
      }

      searchInput.value = '';
      searchInput.focus();
      updateView();
    });
  }

  updateView();
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>