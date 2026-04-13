<?php
$title = 'Criar Tarefa Dinâmica - Tera-Tech';
include __DIR__ . '/../../partials/header.php';
?>
<?php include __DIR__ . '/../../partials/nav.php'; ?>

<div class="page-wrap">
  <div class="container-fluid">
    <a class="btn btn-outline-secondary btn-sm mb-4" href="<?php echo $appUrl; ?>/dashboard.php?action=virtual-tasks">
      <i class="fa-solid fa-arrow-left me-1"></i>Voltar
    </a>

    <div class="mb-4">
      <h1 class="h3 mb-1"><i class="fa-solid fa-plus me-2"></i>Criar Nova Tarefa Dinâmica</h1>
      <small class="text-muted">Escolha um tipo de tarefa para começar</small>
    </div>

    <div class="row g-3">
      <?php foreach ($templates as $key => $template): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card border-0 shadow-sm cursor-pointer task-template-card h-100" data-type="<?php echo $key; ?>" onclick="selectTemplate('<?php echo $key; ?>')">
            <div class="card-body text-center py-5">
              <div class="fs-1 mb-3"><?php echo $template['icon']; ?></div>
              <h5 class="card-title mb-2"><?php echo $template['name']; ?></h5>
              <p class="text-muted small"><?php echo $template['description']; ?></p>
              <button class="btn btn-primary btn-sm mt-3">
                <i class="fa-solid fa-arrow-right me-1"></i>Usar Modelo
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Seção oculta para edição -->
    <div id="editorContainer" style="display: none;" class="mt-5 pt-5 border-top">
      <iframe id="editorFrame" style="width: 100%; height: 800px; border: none; border-radius: 8px;"></iframe>
    </div>
  </div>
</div>

<style>
.task-template-card {
  transition: all 0.3s ease;
  cursor: pointer;
}

.task-template-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12) !important;
}
</style>

<script>
function selectTemplate(templateType) {
  // Redireciona para o editor
  window.location.href = '<?php echo $appUrl; ?>/dashboard.php?action=virtual-tasks-editor&type=' + templateType;
}
</script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
