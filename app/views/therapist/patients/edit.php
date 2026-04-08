<?php $title = 'Editar Paciente'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="row justify-content-center">
    <div class="col-xl-10">
      <div class="card">
        <div class="card-body">
          <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

          <h4 class="mb-3">Editar paciente</h4>
          <form id="patientEditForm" method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-update">
            <input type="hidden" name="id" value="<?php echo (int) $patient['id']; ?>">
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label">Nome</label><input class="form-control" name="name" required value="<?php echo htmlspecialchars((string) $patient['name']); ?>"></div>
              <div class="col-md-6"><label class="form-label">Telefone</label><input class="form-control mask-phone" name="phone" value="<?php echo htmlspecialchars((string) $patient['phone']); ?>"></div>
              <div class="col-md-6"><label class="form-label">E-mail</label><input class="form-control" type="email" name="email" value="<?php echo htmlspecialchars((string) ($patient['email'] ?? '')); ?>"></div>
              <div class="col-md-3"><label class="form-label">Estado civil</label><input class="form-control" name="marital_status" value="<?php echo htmlspecialchars((string) ($patient['marital_status'] ?? '')); ?>"></div>
              <div class="col-md-3"><label class="form-label">Filhos</label><input class="form-control" name="children" value="<?php echo htmlspecialchars((string) ($patient['children'] ?? '')); ?>"></div>
              <div class="col-12"><label class="form-label">Queixa principal</label><textarea class="form-control" name="main_complaint" rows="3"><?php echo htmlspecialchars((string) ($patient['main_complaint'] ?? '')); ?></textarea></div>
            </div>
            <div class="mt-3 d-flex gap-2">
              <a class="btn btn-outline-dark" href="<?php echo $appUrl; ?>/dashboard.php?action=patients">Cancelar</a>
              <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Salvar alteracoes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
window.addEventListener('load', function() {
  $('#patientEditForm').on('submit', function(e){
    e.preventDefault();
    const form = this;
    $.ajax({
      url: form.action,
      method: 'POST',
      data: $(form).serialize(),
      headers: {'X-Requested-With':'XMLHttpRequest'},
      success: function(res){ if (res.success) { window.location.href = res.redirect; return; } Swal.fire('Erro', res.message || 'Falha ao atualizar', 'error'); },
      error: function(xhr){ Swal.fire('Erro', xhr.responseJSON?.message || 'Falha ao atualizar', 'error'); }
    });
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
