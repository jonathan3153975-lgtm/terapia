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
            <?php $addictions = $addictions ?? []; ?>
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label">Nome</label><input class="form-control" name="name" required value="<?php echo htmlspecialchars((string) $patient['name']); ?>"></div>
              <div class="col-md-3"><label class="form-label">Nascimento</label><input class="form-control" type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars((string) ($patient['birth_date'] ?? '')); ?>"></div>
              <div class="col-md-3"><label class="form-label">Idade</label><input class="form-control" id="age_preview" readonly></div>
              <div class="col-md-4"><label class="form-label">CPF</label><input class="form-control" value="<?php echo htmlspecialchars((string) ($patient['cpf'] ?? '')); ?>" readonly></div>
              <div class="col-md-6"><label class="form-label">Telefone</label><input class="form-control mask-phone" name="phone" value="<?php echo htmlspecialchars((string) $patient['phone']); ?>"></div>
              <div class="col-md-6"><label class="form-label">E-mail</label><input class="form-control" type="email" name="email" value="<?php echo htmlspecialchars((string) ($patient['email'] ?? '')); ?>"></div>
              <div class="col-md-3"><label class="form-label">Estado civil</label><input class="form-control" name="marital_status" value="<?php echo htmlspecialchars((string) ($patient['marital_status'] ?? '')); ?>"></div>
              <div class="col-md-3"><label class="form-label">Filhos</label><input class="form-control" name="children" value="<?php echo htmlspecialchars((string) ($patient['children'] ?? '')); ?>"></div>
              <div class="col-md-4"><label class="form-label">CEP</label><input class="form-control" id="cep" name="cep" maxlength="8" value="<?php echo htmlspecialchars((string) ($patient['cep'] ?? '')); ?>"></div>
              <div class="col-md-4"><label class="form-label">Endereco</label><input class="form-control" id="address" name="address" value="<?php echo htmlspecialchars((string) ($patient['address'] ?? '')); ?>"></div>
              <div class="col-md-4"><label class="form-label">Bairro</label><input class="form-control" id="neighborhood" name="neighborhood" value="<?php echo htmlspecialchars((string) ($patient['neighborhood'] ?? '')); ?>"></div>
              <div class="col-md-3"><label class="form-label">Cidade</label><input class="form-control" id="city" name="city" value="<?php echo htmlspecialchars((string) ($patient['city'] ?? '')); ?>"></div>
              <div class="col-md-1"><label class="form-label">UF</label><input class="form-control" id="state" name="state" value="<?php echo htmlspecialchars((string) ($patient['state'] ?? '')); ?>"></div>

              <div class="col-12"><hr class="my-2"><h5 class="mb-1">Saude</h5></div>
              <div class="col-md-3 form-check ms-2"><input class="form-check-input" type="checkbox" id="depression" name="depression" <?php echo !empty($patient['depression']) ? 'checked' : ''; ?>><label class="form-check-label" for="depression">Depressao</label></div>
              <div class="col-md-8"><label class="form-label">Medicacao para depressao</label><input class="form-control" name="depression_medication" value="<?php echo htmlspecialchars((string) ($patient['depression_medication'] ?? '')); ?>"></div>
              <div class="col-md-3 form-check ms-2"><input class="form-check-input" type="checkbox" id="anxiety" name="anxiety" <?php echo !empty($patient['anxiety']) ? 'checked' : ''; ?>><label class="form-check-label" for="anxiety">Ansiedade</label></div>
              <div class="col-md-8"><label class="form-label">Medicacao para ansiedade</label><input class="form-control" name="anxiety_medication" value="<?php echo htmlspecialchars((string) ($patient['anxiety_medication'] ?? '')); ?>"></div>
              <div class="col-md-8"><label class="form-label">Tratamento medico (descricao)</label><textarea class="form-control" name="medical_treatment_description" rows="2"><?php echo htmlspecialchars((string) ($patient['medical_treatment_description'] ?? '')); ?></textarea></div>
              <div class="col-md-4"><label class="form-label">Tratamento medico (medicacao)</label><input class="form-control" name="medical_treatment_medication" value="<?php echo htmlspecialchars((string) ($patient['medical_treatment_medication'] ?? '')); ?>"></div>
              <div class="col-md-3 form-check ms-2"><input class="form-check-input" type="checkbox" id="alcoholism" name="alcoholism" <?php echo !empty($patient['alcoholism']) ? 'checked' : ''; ?>><label class="form-check-label" for="alcoholism">Alcoolismo</label></div>
              <div class="col-md-3 form-check"><input class="form-check-input" type="checkbox" id="drugs" name="drugs" <?php echo !empty($patient['drugs']) ? 'checked' : ''; ?>><label class="form-check-label" for="drugs">Drogas</label></div>
              <div class="col-md-3 form-check"><input class="form-check-input" type="checkbox" id="convulsions" name="convulsions" <?php echo !empty($patient['convulsions']) ? 'checked' : ''; ?>><label class="form-check-label" for="convulsions">Convulsoes</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="smoker" name="smoker" <?php echo !empty($patient['smoker']) ? 'checked' : ''; ?>><label class="form-check-label" for="smoker">Fumante</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="hepatitis" name="hepatitis" <?php echo !empty($patient['hepatitis']) ? 'checked' : ''; ?>><label class="form-check-label" for="hepatitis">Hepatite</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="hypertension" name="hypertension" <?php echo !empty($patient['hypertension']) ? 'checked' : ''; ?>><label class="form-check-label" for="hypertension">Hipertensao</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="diabetes" name="diabetes" <?php echo !empty($patient['diabetes']) ? 'checked' : ''; ?>><label class="form-check-label" for="diabetes">Diabetes</label></div>

              <div class="col-12"><hr class="my-2"><h5 class="mb-1">Vicios</h5><small class="text-muted">Selecione um ou mais</small></div>
              <div class="col-md-2 form-check ms-2"><input class="form-check-input" type="checkbox" id="vicio_drogas" name="addictions[]" value="Drogas" <?php echo in_array('Drogas', $addictions, true) ? 'checked' : ''; ?>><label class="form-check-label" for="vicio_drogas">Drogas</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="vicio_jogos" name="addictions[]" value="Jogos" <?php echo in_array('Jogos', $addictions, true) ? 'checked' : ''; ?>><label class="form-check-label" for="vicio_jogos">Jogos</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="vicio_sexo" name="addictions[]" value="Sexo" <?php echo in_array('Sexo', $addictions, true) ? 'checked' : ''; ?>><label class="form-check-label" for="vicio_sexo">Sexo</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="vicio_pornografia" name="addictions[]" value="Pornografia" <?php echo in_array('Pornografia', $addictions, true) ? 'checked' : ''; ?>><label class="form-check-label" for="vicio_pornografia">Pornografia</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="vicio_telas" name="addictions[]" value="Telas" <?php echo in_array('Telas', $addictions, true) ? 'checked' : ''; ?>><label class="form-check-label" for="vicio_telas">Telas</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="vicio_compras" name="addictions[]" value="Compras" <?php echo in_array('Compras', $addictions, true) ? 'checked' : ''; ?>><label class="form-check-label" for="vicio_compras">Compras</label></div>
              <div class="col-md-2 form-check ms-2"><input class="form-check-input" type="checkbox" id="vicio_comida" name="addictions[]" value="Comida" <?php echo in_array('Comida', $addictions, true) ? 'checked' : ''; ?>><label class="form-check-label" for="vicio_comida">Comida</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="vicio_outros" name="addictions[]" value="Outros" <?php echo in_array('Outros', $addictions, true) ? 'checked' : ''; ?>><label class="form-check-label" for="vicio_outros">Outros</label></div>

              <div class="col-12"><hr class="my-2"><h5 class="mb-1">Outros</h5></div>
              <div class="col-md-3 form-check ms-2"><input class="form-check-input" type="checkbox" id="had_therapy" name="had_therapy" <?php echo !empty($patient['had_therapy']) ? 'checked' : ''; ?>><label class="form-check-label" for="had_therapy">Ja fez terapia?</label></div>
              <div class="col-md-8"><label class="form-label">Descricao terapia anterior</label><textarea class="form-control" name="therapy_description" rows="2"><?php echo htmlspecialchars((string) ($patient['therapy_description'] ?? '')); ?></textarea></div>
              <div class="col-md-4"><label class="form-label">Data inicio tratamento</label><input class="form-control" type="date" name="treatment_start_date" value="<?php echo htmlspecialchars((string) ($patient['treatment_start_date'] ?? '')); ?>"></div>
              <div class="col-md-4"><label class="form-label">Menstruacao</label><input class="form-control" name="menstruation" value="<?php echo htmlspecialchars((string) ($patient['menstruation'] ?? '')); ?>"></div>
              <div class="col-md-4"><label class="form-label">Intestino</label><input class="form-control" name="bowel" value="<?php echo htmlspecialchars((string) ($patient['bowel'] ?? '')); ?>"></div>
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
  $('#birth_date').on('change', function(){
    if (!this.value) return;
    const b = new Date(this.value);
    const n = new Date();
    let age = n.getFullYear() - b.getFullYear();
    const m = n.getMonth() - b.getMonth();
    if (m < 0 || (m === 0 && n.getDate() < b.getDate())) age--;
    $('#age_preview').val(age + ' anos');
  }).trigger('change');

  $('#cep').on('input', function(){
    const cep = (this.value || '').replace(/\D/g, '');
    if (cep.length !== 8) return;
    fetch('https://viacep.com.br/ws/' + cep + '/json/')
      .then(r => r.json())
      .then(d => {
        if (d.erro) return;
        $('#address').val(d.logradouro || '');
        $('#neighborhood').val(d.bairro || '');
        $('#city').val(d.localidade || '');
        $('#state').val(d.uf || '');
      });
  });

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
