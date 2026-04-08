<?php $title = 'Novo Paciente'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="container page-wrap">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="card">
        <div class="card-body">
          <?php include __DIR__ . '/../../partials/flash-alert.php'; ?>

          <h4 class="mb-3">Cadastro de Paciente</h4>
          <form id="patientForm" method="POST" action="<?php echo $appUrl; ?>/dashboard.php?action=patients-store">
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label">Nome</label><input class="form-control" name="name" required></div>
              <div class="col-md-3"><label class="form-label">Data de nascimento</label><input class="form-control" type="date" id="birth_date" name="birth_date"></div>
              <div class="col-md-3"><label class="form-label">Idade</label><input class="form-control" id="age_preview" readonly></div>

              <div class="col-md-4"><label class="form-label">CPF</label><input class="form-control mask-cpf" name="cpf" required></div>
              <div class="col-md-4"><label class="form-label">Telefone</label><input class="form-control mask-phone" name="phone" required></div>
              <div class="col-md-4"><label class="form-label">E-mail</label><input class="form-control" type="email" name="email"></div>

              <div class="col-md-4">
                <label class="form-label">Estado civil</label>
                <select class="form-select" name="marital_status">
                  <option value="">Selecione...</option>
                  <option value="Solteiro(a)">Solteiro(a)</option>
                  <option value="Casado(a)">Casado(a)</option>
                  <option value="Uniao estavel">União estável</option>
                  <option value="Divorciado(a)">Divorciado(a)</option>
                  <option value="Viuvo(a)">Viúvo(a)</option>
                </select>
              </div>
              <div class="col-md-4"><label class="form-label">Filhos</label><input class="form-control" name="children" placeholder="Ex.: 2 filhos"></div>

              <div class="col-12"><hr class="my-2"><h5 class="mb-1">Endereço (opcional)</h5></div>
              <div class="col-md-4"><label class="form-label">CEP</label><input class="form-control" id="cep" name="cep" maxlength="8"></div>
              <div class="col-md-4"><label class="form-label">Endereço</label><input class="form-control" id="address" name="address"></div>
              <div class="col-md-4"><label class="form-label">Bairro</label><input class="form-control" id="neighborhood" name="neighborhood"></div>
              <div class="col-md-3"><label class="form-label">Cidade</label><input class="form-control" id="city" name="city"></div>
              <div class="col-md-2"><label class="form-label">UF</label><input class="form-control" id="state" name="state"></div>

              <div class="col-12"><hr class="my-2"><h5 class="mb-1">Saúde</h5></div>
              <div class="col-md-3 form-check ms-2"><input class="form-check-input" type="checkbox" id="depression" name="depression"><label class="form-check-label" for="depression">Depressão</label></div>
              <div class="col-md-8"><label class="form-label">Medicação para depressão</label><input class="form-control" id="depression_medication" name="depression_medication"></div>
              <div class="col-md-3 form-check ms-2"><input class="form-check-input" type="checkbox" id="anxiety" name="anxiety"><label class="form-check-label" for="anxiety">Ansiedade</label></div>
              <div class="col-md-8"><label class="form-label">Medicação para ansiedade</label><input class="form-control" id="anxiety_medication" name="anxiety_medication"></div>

              <div class="col-md-4 form-check ms-2"><input class="form-check-input" type="checkbox" id="has_medical_treatment" name="has_medical_treatment"><label class="form-check-label" for="has_medical_treatment">Faz tratamento médico</label></div>
              <div class="col-md-7"><label class="form-label">Tratamento médico (descrição)</label><textarea class="form-control" id="medical_treatment_description" name="medical_treatment_description" rows="2"></textarea></div>
              <div class="col-md-5"><label class="form-label">Tratamento médico (medicação)</label><input class="form-control" id="medical_treatment_medication" name="medical_treatment_medication"></div>

              <div class="col-md-3 form-check ms-2"><input class="form-check-input" type="checkbox" id="alcoholism" name="alcoholism"><label class="form-check-label" for="alcoholism">Alcoolismo</label></div>
              <div class="col-md-3 form-check"><input class="form-check-input" type="checkbox" id="drugs" name="drugs"><label class="form-check-label" for="drugs">Drogas</label></div>
              <div class="col-md-3 form-check"><input class="form-check-input" type="checkbox" id="convulsions" name="convulsions"><label class="form-check-label" for="convulsions">Convulsões</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="smoker" name="smoker"><label class="form-check-label" for="smoker">Fumante</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="hepatitis" name="hepatitis"><label class="form-check-label" for="hepatitis">Hepatite</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="hypertension" name="hypertension"><label class="form-check-label" for="hypertension">Hipertensão</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="diabetes" name="diabetes"><label class="form-check-label" for="diabetes">Diabetes</label></div>

              <div class="col-12"><hr class="my-2"><h5 class="mb-1">Vícios</h5></div>
              <div class="col-md-6">
                <label class="form-label">Selecione os vícios</label>
                <div class="dropdown">
                  <button class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span id="addictionsLabel">Nenhum selecionado</span>
                    <i class="fa-solid fa-chevron-down"></i>
                  </button>
                  <div class="dropdown-menu p-3 w-100" style="max-height: 240px; overflow-y: auto;">
                    <div class="form-check"><input class="form-check-input addiction-option" type="checkbox" id="vicio_drogas" name="addictions[]" value="Drogas"><label class="form-check-label" for="vicio_drogas">Drogas</label></div>
                    <div class="form-check"><input class="form-check-input addiction-option" type="checkbox" id="vicio_jogos" name="addictions[]" value="Jogos"><label class="form-check-label" for="vicio_jogos">Jogos</label></div>
                    <div class="form-check"><input class="form-check-input addiction-option" type="checkbox" id="vicio_sexo" name="addictions[]" value="Sexo"><label class="form-check-label" for="vicio_sexo">Sexo</label></div>
                    <div class="form-check"><input class="form-check-input addiction-option" type="checkbox" id="vicio_pornografia" name="addictions[]" value="Pornografia"><label class="form-check-label" for="vicio_pornografia">Pornografia</label></div>
                    <div class="form-check"><input class="form-check-input addiction-option" type="checkbox" id="vicio_telas" name="addictions[]" value="Telas"><label class="form-check-label" for="vicio_telas">Telas</label></div>
                    <div class="form-check"><input class="form-check-input addiction-option" type="checkbox" id="vicio_compras" name="addictions[]" value="Compras"><label class="form-check-label" for="vicio_compras">Compras</label></div>
                    <div class="form-check"><input class="form-check-input addiction-option" type="checkbox" id="vicio_comida" name="addictions[]" value="Comida"><label class="form-check-label" for="vicio_comida">Comida</label></div>
                    <div class="form-check"><input class="form-check-input addiction-option" type="checkbox" id="vicio_outros" name="addictions[]" value="Outros"><label class="form-check-label" for="vicio_outros">Outros</label></div>
                  </div>
                </div>
              </div>

              <div class="col-12"><hr class="my-2"><h5 class="mb-1">Outros</h5></div>
              <div class="col-md-4 form-check ms-2"><input class="form-check-input" type="checkbox" id="had_therapy" name="had_therapy"><label class="form-check-label" for="had_therapy">Já fez terapia?</label></div>
              <div class="col-md-7"><label class="form-label">Descrição da terapia anterior</label><textarea class="form-control" id="therapy_description" name="therapy_description" rows="2"></textarea></div>
              <div class="col-md-4"><label class="form-label">Data de início do tratamento</label><input class="form-control" type="date" id="treatment_start_date" name="treatment_start_date"></div>
              <div class="col-md-4"><label class="form-label">Menstruação</label><input class="form-control" name="menstruation"></div>
              <div class="col-md-4"><label class="form-label">Intestino</label><input class="form-control" name="bowel"></div>
              <div class="col-md-12"><label class="form-label">Queixa principal</label><textarea class="form-control" name="main_complaint" rows="3"></textarea></div>
            </div>
            <div class="mt-3 d-flex gap-2">
              <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=patients">Voltar</a>
              <button class="btn btn-primary" type="submit">Salvar paciente</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card mt-3">
        <div class="card-body">
          <h5>Área do Paciente (Interno)</h5>
          <ul class="nav nav-tabs mb-3"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#atendimentos" type="button">Atendimentos</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tarefas" type="button">Tarefas</button></li></ul>
          <div class="tab-content">
            <div class="tab-pane fade show active" id="atendimentos">
              <div class="mb-2"><input class="form-control" type="date"></div>
              <div class="mb-2"><input class="form-control" placeholder="Descrição"></div>
              <div id="editorAtendimento" style="height:160px;"></div>
            </div>
            <div class="tab-pane fade" id="tarefas">
              <div class="row g-2">
                <div class="col-md-3"><input class="form-control" type="date"></div>
                <div class="col-md-9"><input class="form-control" placeholder="Título"></div>
              </div>
              <div id="editorTarefa" style="height:180px;" class="mt-2"></div>
              <div class="row g-2 mt-2"><div class="col-md-6"><input class="form-control" type="file" accept=".pdf,image/*"></div><div class="col-md-6"><input class="form-control" type="url" placeholder="Link"></div></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
window.addEventListener('load', function() {
  const q1 = new Quill('#editorAtendimento', { theme: 'snow' });
  const q2 = new Quill('#editorTarefa', { theme: 'snow', modules: { toolbar: [[{header:[1,2,3,false]}],['bold','italic','underline','strike'],[{list:'ordered'},{list:'bullet'}],['blockquote','code-block','link'],[{color:[]},{background:[]}],['clean']] } });

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
  });

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

  $('#patientForm').on('submit', function(e){
    e.preventDefault();
    const form = this;
    $.ajax({
      url: form.action,
      method: 'POST',
      data: $(form).serialize(),
      headers: {'X-Requested-With':'XMLHttpRequest'},
      success: function(res){ if (res.success) { window.location.href = res.redirect; return; } Swal.fire('Erro', res.message || 'Falha ao salvar', 'error'); },
      error: function(xhr){ Swal.fire('Erro', xhr.responseJSON?.message || 'Falha ao salvar', 'error'); }
    });
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
