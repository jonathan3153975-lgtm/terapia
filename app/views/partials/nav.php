<?php $withSidebarLayout = true; ?>
<div class="app-layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
  <main class="app-content">
    <div class="mobile-topbar d-lg-none mb-3">
      <button class="btn btn-outline-secondary" id="sidebarToggle" type="button" aria-label="Abrir menu">
        <i class="fa-solid fa-bars"></i>
      </button>
      <span class="mobile-topbar-title">Terapia SaaS</span>
    </div>
