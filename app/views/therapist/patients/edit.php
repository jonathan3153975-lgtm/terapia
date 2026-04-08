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
            <?php $comorbidities = $comorbidities ?? []; ?>
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label">Nome</label><input class="form-control" name="name" required value="<?php echo htmlspecialchars((string) $patient['name']); ?>"></div>
              <div class="col-md-3"><label class="form-label">Data de nascimento</label><input class="form-control" type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars((string) ($patient['birth_date'] ?? '')); ?>"></div>
              <div class="col-md-3"><label class="form-label">Idade</label><input class="form-control" id="age_preview" readonly></div>

              <div class="col-md-4"><label class="form-label">CPF</label><input class="form-control" value="<?php echo htmlspecialchars((string) ($patient['cpf'] ?? '')); ?>" readonly></div>
              <div class="col-md-4"><label class="form-label">Telefone</label><input class="form-control mask-phone" name="phone" value="<?php echo htmlspecialchars((string) $patient['phone']); ?>"></div>
              <div class="col-md-4"><label class="form-label">E-mail</label><input class="form-control" type="email" name="email" value="<?php echo htmlspecialchars((string) ($patient['email'] ?? '')); ?>"></div>

              <div class="col-md-4">
                <label class="form-label">Estado civil</label>
                <select class="form-select" name="marital_status">
                  <option value="">Selecione...</option>
                  <?php $marital = (string) ($patient['marital_status'] ?? ''); ?>
                  <option value="Solteiro(a)" <?php echo $marital === 'Solteiro(a)' ? 'selected' : ''; ?>>Solteiro(a)</option>
                  <option value="Casado(a)" <?php echo $marital === 'Casado(a)' ? 'selected' : ''; ?>>Casado(a)</option>
                  <option value="Uniao estavel" <?php echo $marital === 'Uniao estavel' ? 'selected' : ''; ?>>União estável</option>
                  <option value="Divorciado(a)" <?php echo $marital === 'Divorciado(a)' ? 'selected' : ''; ?>>Divorciado(a)</option>
                  <option value="Viuvo(a)" <?php echo $marital === 'Viuvo(a)' ? 'selected' : ''; ?>>Viúvo(a)</option>
                </select>
              </div>
              <div class="col-md-8"><label class="form-label">Filhos</label><input class="form-control" name="children" value="<?php echo htmlspecialchars((string) ($patient['children'] ?? '')); ?>"></div>

              <div class="col-12"><hr class="my-2"><h5 class="mb-1">Endereço (opcional)</h5></div>
              <div class="col-md-3"><label class="form-label">CEP</label><input class="form-control" id="cep" name="cep" maxlength="8" value="<?php echo htmlspecialchars((string) ($patient['cep'] ?? '')); ?>"></div>
              <div class="col-md-9"><label class="form-label">Endereço</label><input class="form-control" id="address" name="address" value="<?php echo htmlspecialchars((string) ($patient['address'] ?? '')); ?>"></div>
              <div class="col-md-5"><label class="form-label">Bairro</label><input class="form-control" id="neighborhood" name="neighborhood" value="<?php echo htmlspecialchars((string) ($patient['neighborhood'] ?? '')); ?>"></div>
              <div class="col-md-4"><label class="form-label">Cidade</label><input class="form-control" id="city" name="city" value="<?php echo htmlspecialchars((string) ($patient['city'] ?? '')); ?>"></div>
              <div class="col-md-3"><label class="form-label">UF</label><input class="form-control" id="state" name="state" value="<?php echo htmlspecialchars((string) ($patient['state'] ?? '')); ?>"></div>

              <div class="col-12"><hr class="my-2"><h5 class="mb-1">Saúde</h5></div>
              <div class="col-md-3 form-check ms-2"><input class="form-check-input" type="checkbox" id="depression" name="depression" <?php echo !empty($patient['depression']) ? 'checked' : ''; ?>><label class="form-check-label" for="depression">Depressão</label></div>
              <div class="col-md-8"><label class="form-label">Medicação para depressão</label><input class="form-control" id="depression_medication" name="depression_medication" value="<?php echo htmlspecialchars((string) ($patient['depression_medication'] ?? '')); ?>"></div>
              <div class="col-md-3 form-check ms-2"><input class="form-check-input" type="checkbox" id="anxiety" name="anxiety" <?php echo !empty($patient['anxiety']) ? 'checked' : ''; ?>><label class="form-check-label" for="anxiety">Ansiedade</label></div>
              <div class="col-md-8"><label class="form-label">Medicação para ansiedade</label><input class="form-control" id="anxiety_medication" name="anxiety_medication" value="<?php echo htmlspecialchars((string) ($patient['anxiety_medication'] ?? '')); ?>"></div>

              <div class="col-md-4 form-check ms-2"><input class="form-check-input" type="checkbox" id="has_medical_treatment" name="has_medical_treatment" <?php echo !empty($patient['has_medical_treatment']) ? 'checked' : ''; ?>><label class="form-check-label" for="has_medical_treatment">Faz tratamento médico</label></div>
              <div class="col-md-8"><label class="form-label">Tratamento médico (descrição)</label><textarea class="form-control" id="medical_treatment_description" name="medical_treatment_description" rows="2"><?php echo htmlspecialchars((string) ($patient['medical_treatment_description'] ?? '')); ?></textarea></div>
              <div class="col-12"><label class="form-label">Tratamento médico (medicação)</label><input class="form-control" id="medical_treatment_medication" name="medical_treatment_medication" value="<?php echo htmlspecialchars((string) ($patient['medical_treatment_medication'] ?? '')); ?>"></div>

              <div class="col-12"><h6 class="text-muted mt-1 mb-1">Comorbidades</h6></div>
              <div class="col-12">
                <div class="dropdown">
                  <button class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span id="comorbiditiesLabel">Nenhuma selecionada</span>
                    <i class="fa-solid fa-chevron-down"></i>
                  </button>
                  <div class="dropdown-menu p-3 w-100" style="max-height: 240px; overflow-y: auto;">
                    <div class="form-check"><input class="form-check-input comorbidity-option" type="checkbox" id="com_alcoolismo" name="comorbidities[]" value="Alcoolismo" <?php echo in_array('Alcoolismo', $comorbidities, true) ? 'checked' : ''; ?>><label class="form-check-label" for="com_alcoolismo">Alcoolismo</label></div>
                    <div class="form-check"><input class="form-check-input comorbidity-option" type="checkbox" id="com_drogas" name="comorbidities[]" value="Drogas" <?php echo in_array('Drogas', $comorbidities, true) ? 'checked' : ''; ?>><label class="form-check-label" for="com_drogas">Drogas</label></div>
                    <div class="form-check"><input class="form-check-input comorbidity-option" type="checkbox" id="com_convulsoes" name="comorbidities[]" value="Convulsões" <?php echo in_array('Convulsões', $comorbidities, true) ? 'checked' : ''; ?>><label class="form-check-label" for="com_convulsoes">Convulsões</label></div>
                    <div class="form-check"><input class="form-check-input comorbidity-option" type="checkbox" id="com_fumante" name="comorbidities[]" value="Fumante" <?php echo in_array('Fumante', $comorbidities, true) ? 'checked' : ''; ?>><label class="form-check-label" for="com_fumante">Fumante</label></div>
                    <div class="form-check"><input class="form-check-input comorbidity-option" type="checkbox" id="com_hepatite" name="comorbidities[]" value="Hepatite" <?php echo in_array('Hepatite', $comorbidities, true) ? 'checked' : ''; ?>><label class="form-check-label" for="com_hepatite">Hepatite</label></div>
                    <div class="form-check"><input class="form-check-input comorbidity-option" type="checkbox" id="com_hipertensao" name="comorbidities[]" value="Hipertensão" <?php echo in_array('Hipertensão', $comorbidities, true) ? 'checked' : ''; ?>><label class="form-check-label" for="com_hipertensao">Hipertensão</label></div>
                    <div class="form-check"><input class="form-check-input comorbidity-option" type="checkbox" id="com_diabetes" name="comorbidities[]" value="Diabetes" <?php echo in_array('Diabetes', $comorbidities, true) ? 'checked' : ''; ?>><label class="form-check-label" for="com_diabetes">Diabetes</label></div>
                  </div>
                </div>
              </div>

              <div class="col-12"><hr class="my-2"><h5 class="mb-1">Vícios</h5></div>
              <div class="col-12">
                <label class="form-label">Selecione os vícios</label>
                <div class="dropdown">
                  <button class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span id="addictionsLabel">Nenhum selecionado</span>
                    <i class="fa-solid fa-chevron-down"></i>
                  </button>
                  <div class="dropdown-menu p-3 w-100" style="max-height: 240px; overflow-y: auto;">
                    <div class="form-check"><input class="form-check-input addiction-option" type="checkbox" id="vicio_drogas" name="addictions[]" value="Drogas" <?php echo in_array('Drogas', $addictions, true) ? 'checked' : ''; ?>><label class="form-check-label" for="vicio_drogas">Drogas</label></div>
                    <div class="form-check"><input class="form-check-input addiction-option" type="checkbox" id="vicio_jogos" name="addictions[]" value="Jogos" <?php echo in_array('Jogos', $addictions, true) ? 'checked' : ''; ?>><label class="form-check-label" for="vicio_jogos">Jogos</label></div>
                    <div class="form-check"><input class="form-check-input addiction-option" type="checkbox" id="vicio_sexo" name="addictions[]" value="Sexo" <?php echo in_array('Sexo', $addictions, true) ? 'checked' : ''; ?>><label class="form-check-label" for="vicio_sexo">Sexo</label></div>
                    <div class="form-check"><input class="form-check-input addiction-option" type="checkbox" id="vicio_pornografia" name="addictions[]" value="Pornografia" <?php echo in_array('Pornografia', $addictions, true) ? 'checked' : ''; ?>><label class="form-check-label" for="vicio_pornografia">Pornografia</label></div>
                    <div class="form-check"><input class="form-check-input addiction-option" type="checkbox" id="vicio_telas" name="addictions[]" value="Telas" <?php echo in_array('Telas', $addictions, true) ? 'checked' : ''; ?>><label class="form-check-label" for="vicio_telas">Telas</label></div>
                    <div class="form-check"><input class="form-check-input addiction-option" type="checkbox" id="vicio_compras" name="addictions[]" value="Compras" <?php echo in_array('Compras', $addictions, true) ? 'checked' : ''; ?>><label class="form-check-label" for="vicio_compras">Compras</label></div>
                    <div class="form-check"><input class="form-check-input addiction-option" type="checkbox" id="vicio_comida" name="addictions[]" value="Comida" <?php echo in_array('Comida', $addictions, true) ? 'checked' : ''; ?>><label class="form-check-label" for="vicio_comida">Comida</label></div>
                    <div class="form-check"><input class="form-check-input addiction-option" type="checkbox" id="vicio_outros" name="addictions[]" value="Outros" <?php echo in_array('Outros', $addictions, true) ? 'checked' : ''; ?>><label class="form-check-label" for="vicio_outros">Outros</label></div>
                  </div>
                </div>
              </div>

              <div class="col-12"><hr class="my-2"><h5 class="mb-1">Outros</h5></div>
              <div class="col-md-4 form-check ms-2"><input class="form-check-input" type="checkbox" id="had_therapy" name="had_therapy" <?php echo !empty($patient['had_therapy']) ? 'checked' : ''; ?>><label class="form-check-label" for="had_therapy">Já fez terapia?</label></div>
              <div class="col-md-7"><label class="form-label">Descrição da terapia anterior</label><textarea class="form-control" id="therapy_description" name="therapy_description" rows="2"><?php echo htmlspecialchars((string) ($patient['therapy_description'] ?? '')); ?></textarea></div>
              <div class="col-md-4"><label class="form-label">Data de início do tratamento</label><input class="form-control" type="date" id="treatment_start_date" name="treatment_start_date" value="<?php echo htmlspecialchars((string) ($patient['treatment_start_date'] ?? '')); ?>"></div>
              <div class="col-md-4"><label class="form-label">Menstruação</label><input class="form-control" name="menstruation" value="<?php echo htmlspecialchars((string) ($patient['menstruation'] ?? '')); ?>"></div>
              <div class="col-md-4"><label class="form-label">Intestino</label><input class="form-control" name="bowel" value="<?php echo htmlspecialchars((string) ($patient['bowel'] ?? '')); ?>"></div>
              <div class="col-12"><label class="form-label">Queixa principal</label><textarea class="form-control" name="main_complaint" rows="3"><?php echo htmlspecialchars((string) ($patient['main_complaint'] ?? '')); ?></textarea></div>
            </div>
            <div class="mt-3 d-flex gap-2">
              <a class="btn btn-outline-dark" href="<?php echo $appUrl; ?>/dashboard.php?action=patients">Cancelar</a>
              <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Salvar alterações</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
window.addEventListener('load', function() {
  const toggleField = function(checkboxSelector, inputSelector) {
    const checkbox = document.querySelector(checkboxSelector);
    const input = document.querySelector(inputSelector);
    if (!checkbox || !input) return;
    input.disabled = !checkbox.checked;
    if (!checkbox.checked) {
      input.value = '';
    }
    checkbox.addEventListener('change', function() {
      input.disabled = !checkbox.checked;
      if (!checkbox.checked) {
        input.value = '';
      }
    });
  };

  const toggleByCheckbox = function(checkboxSelector, fieldSelectors) {
    const checkbox = document.querySelector(checkboxSelector);
    if (!checkbox) return;
    const applyState = function() {
      fieldSelectors.forEach(function(selector) {
        const field = document.querySelector(selector);
        if (!field) return;
        field.disabled = !checkbox.checked;
        if (!checkbox.checked) field.value = '';
      });
    };
    applyState();
    checkbox.addEventListener('change', applyState);
  };

  toggleField('#depression', '#depression_medication');
  toggleField('#anxiety', '#anxiety_medication');
  toggleByCheckbox('#has_medical_treatment', ['#medical_treatment_description', '#medical_treatment_medication']);
  toggleByCheckbox('#had_therapy', ['#therapy_description', '#treatment_start_date']);

  const updateAddictionsLabel = function() {
    const selected = Array.from(document.querySelectorAll('.addiction-option:checked')).map(function(i) { return i.value; });
    document.getElementById('addictionsLabel').textContent = selected.length ? selected.join(', ') : 'Nenhum selecionado';
  };
  document.querySelectorAll('.addiction-option').forEach(function(option) {
    option.addEventListener('change', updateAddictionsLabel);
  });
  updateAddictionsLabel();

  const updateComorbiditiesLabel = function() {
    const selected = Array.from(document.querySelectorAll('.comorbidity-option:checked')).map(function(i) { return i.value; });
    document.getElementById('comorbiditiesLabel').textContent = selected.length ? selected.join(', ') : 'Nenhuma selecionada';
  };
  document.querySelectorAll('.comorbidity-option').forEach(function(option) {
    option.addEventListener('change', updateComorbiditiesLabel);
  });
  updateComorbiditiesLabel();

  $('#birth_date').on('change', function(){
    if (!this.value) {
      $('#age_preview').val('');
      return;
    }
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
    if (!window.FormSubmitGuard.lock(form, 'Salvando...')) {
      return;
    }
    $.ajax({
      url: form.action,
      method: 'POST',
      data: $(form).serialize(),
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
