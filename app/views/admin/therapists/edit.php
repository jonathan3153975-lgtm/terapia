<?php $title = 'Editar Terapeuta'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="container page-wrap">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="card">
        <div class="card-body">
          <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

          <h4 class="mb-3">Editar terapeuta</h4>
          <form id="therapistEditForm" action="<?php echo $appUrl; ?>/dashboard.php?action=therapists-update" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo (int) $therapist['id']; ?>">
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label">Nome</label><input class="form-control" name="name" required value="<?php echo htmlspecialchars((string) $therapist['name']); ?>"></div>
              <div class="col-md-6"><label class="form-label">CPF</label><input class="form-control mask-cpf" name="cpf" required value="<?php echo htmlspecialchars((string) ($therapist['cpf'] ?? '')); ?>"></div>
              <div class="col-md-6"><label class="form-label">Telefone</label><input class="form-control mask-phone" name="phone" required value="<?php echo htmlspecialchars((string) ($therapist['phone'] ?? '')); ?>"></div>
              <div class="col-md-6"><label class="form-label">E-mail</label><input class="form-control" type="email" name="email" required value="<?php echo htmlspecialchars((string) ($therapist['email'] ?? '')); ?>"></div>
              <div class="col-md-6">
                <label class="form-label">Plano</label>
                <select class="form-select" name="plan_type">
                  <option value="mensal" <?php echo ($therapist['plan_type'] ?? 'mensal') === 'mensal' ? 'selected' : ''; ?>>Mensal</option>
                  <option value="anual" <?php echo ($therapist['plan_type'] ?? '') === 'anual' ? 'selected' : ''; ?>>Anual</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                  <option value="active" <?php echo ($therapist['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Ativo</option>
                  <option value="inactive" <?php echo ($therapist['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inativo</option>
                </select>
              </div>
              <div class="col-md-12">
                <label class="form-label">Logo da empresa (opcional)</label>
                <input class="form-control" type="file" name="company_logo" id="companyLogoEditInput" accept="image/*,.svg">
                <div class="form-text">Envie um novo logo para substituir o atual.</div>
              </div>
              <div class="col-md-12">
                <div class="guided-image-preview <?php echo !empty($therapist['company_logo_path']) ? 'has-image' : ''; ?>" id="companyLogoEditPreviewBox">
                  <img
                    id="companyLogoEditPreview"
                    class="<?php echo !empty($therapist['company_logo_path']) ? 'is-visible' : ''; ?>"
                    src="<?php echo !empty($therapist['company_logo_path']) ? ($appUrl . '/' . ltrim((string) $therapist['company_logo_path'], '/')) : ''; ?>"
                    alt="Pré-visualização do logo"
                  >
                  <span id="companyLogoEditPreviewHint" class="<?php echo !empty($therapist['company_logo_path']) ? 'd-none' : ''; ?>">Pré-visualização do logo</span>
                </div>
              </div>
            </div>
            <div class="mt-3 d-flex gap-2">
              <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapists">Voltar</a>
              <button class="btn btn-primary" type="submit">Salvar alterações</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
window.addEventListener('load', function() {
  const logoInput = document.getElementById('companyLogoEditInput');
  const logoPreview = document.getElementById('companyLogoEditPreview');
  const logoPreviewHint = document.getElementById('companyLogoEditPreviewHint');
  const logoPreviewBox = document.getElementById('companyLogoEditPreviewBox');

  if (logoInput && logoPreview && logoPreviewHint && logoPreviewBox) {
    logoInput.addEventListener('change', function() {
      const file = logoInput.files && logoInput.files[0] ? logoInput.files[0] : null;
      if (!file) {
        return;
      }

      const reader = new FileReader();
      reader.onload = function(event) {
        logoPreview.setAttribute('src', String((event.target && event.target.result) || ''));
        logoPreview.classList.add('is-visible');
        logoPreviewHint.classList.add('d-none');
        logoPreviewBox.classList.add('has-image');
      };
      reader.readAsDataURL(file);
    });
  }

  $('#therapistEditForm').on('submit', function(e){
    e.preventDefault();
    const form = this;
    if (!window.FormSubmitGuard.lock(form, 'Salvando...')) {
      return;
    }
    $.ajax({
      url: form.action,
      method: 'POST',
      data: new FormData(form),
      processData: false,
      contentType: false,
      headers: {'X-Requested-With':'XMLHttpRequest'},
      success: function(res){
        if (res.success) { window.location.href = res.redirect; return; }
        window.FormSubmitGuard.unlock(form);
        Swal.fire('Erro', res.message || 'Falha ao atualizar', 'error');
      },
      error: function(xhr){
        window.FormSubmitGuard.unlock(form);
        Swal.fire('Erro', xhr.responseJSON?.message || 'Falha ao atualizar', 'error');
      }
    });
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>