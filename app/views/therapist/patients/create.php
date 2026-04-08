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
              <div class="col-md-3"><label class="form-label">Nascimento</label><input class="form-control" type="date" id="birth_date" name="birth_date"></div>
              <div class="col-md-3"><label class="form-label">Idade</label><input class="form-control" id="age_preview" readonly></div>
              <div class="col-md-4"><label class="form-label">CPF</label><input class="form-control mask-cpf" name="cpf" required></div>
              <div class="col-md-4"><label class="form-label">Telefone</label><input class="form-control mask-phone" name="phone" required></div>
              <div class="col-md-4"><label class="form-label">E-mail</label><input class="form-control" type="email" name="email"></div>
              <div class="col-md-4"><label class="form-label">Estado civil</label><input class="form-control" name="marital_status"></div>
              <div class="col-md-4"><label class="form-label">Filhos</label><input class="form-control" name="children"></div>
              <div class="col-md-4"><label class="form-label">CEP</label><input class="form-control" id="cep" name="cep" maxlength="8"></div>
              <div class="col-md-4"><label class="form-label">Endereco</label><input class="form-control" id="address" name="address"></div>
              <div class="col-md-4"><label class="form-label">Bairro</label><input class="form-control" id="neighborhood" name="neighborhood"></div>
              <div class="col-md-3"><label class="form-label">Cidade</label><input class="form-control" id="city" name="city"></div>
              <div class="col-md-1"><label class="form-label">UF</label><input class="form-control" id="state" name="state"></div>

              <div class="col-12"><hr class="my-2"><h5 class="mb-1">Saude</h5></div>
              <div class="col-md-3 form-check ms-2"><input class="form-check-input" type="checkbox" id="depression" name="depression"><label class="form-check-label" for="depression">Depressao</label></div>
              <div class="col-md-8"><label class="form-label">Medicacao para depressao</label><input class="form-control" name="depression_medication"></div>
              <div class="col-md-3 form-check ms-2"><input class="form-check-input" type="checkbox" id="anxiety" name="anxiety"><label class="form-check-label" for="anxiety">Ansiedade</label></div>
              <div class="col-md-8"><label class="form-label">Medicacao para ansiedade</label><input class="form-control" name="anxiety_medication"></div>
              <div class="col-md-8"><label class="form-label">Tratamento medico (descricao)</label><textarea class="form-control" name="medical_treatment_description" rows="2"></textarea></div>
              <div class="col-md-4"><label class="form-label">Tratamento medico (medicacao)</label><input class="form-control" name="medical_treatment_medication"></div>
              <div class="col-md-3 form-check ms-2"><input class="form-check-input" type="checkbox" id="alcoholism" name="alcoholism"><label class="form-check-label" for="alcoholism">Alcoolismo</label></div>
              <div class="col-md-3 form-check"><input class="form-check-input" type="checkbox" id="drugs" name="drugs"><label class="form-check-label" for="drugs">Drogas</label></div>
              <div class="col-md-3 form-check"><input class="form-check-input" type="checkbox" id="convulsions" name="convulsions"><label class="form-check-label" for="convulsions">Convulsoes</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="smoker" name="smoker"><label class="form-check-label" for="smoker">Fumante</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="hepatitis" name="hepatitis"><label class="form-check-label" for="hepatitis">Hepatite</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="hypertension" name="hypertension"><label class="form-check-label" for="hypertension">Hipertensao</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="diabetes" name="diabetes"><label class="form-check-label" for="diabetes">Diabetes</label></div>

              <div class="col-12"><hr class="my-2"><h5 class="mb-1">Vicios</h5><small class="text-muted">Selecione um ou mais</small></div>
              <div class="col-md-2 form-check ms-2"><input class="form-check-input" type="checkbox" id="vicio_drogas" name="addictions[]" value="Drogas"><label class="form-check-label" for="vicio_drogas">Drogas</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="vicio_jogos" name="addictions[]" value="Jogos"><label class="form-check-label" for="vicio_jogos">Jogos</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="vicio_sexo" name="addictions[]" value="Sexo"><label class="form-check-label" for="vicio_sexo">Sexo</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="vicio_pornografia" name="addictions[]" value="Pornografia"><label class="form-check-label" for="vicio_pornografia">Pornografia</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="vicio_telas" name="addictions[]" value="Telas"><label class="form-check-label" for="vicio_telas">Telas</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="vicio_compras" name="addictions[]" value="Compras"><label class="form-check-label" for="vicio_compras">Compras</label></div>
              <div class="col-md-2 form-check ms-2"><input class="form-check-input" type="checkbox" id="vicio_comida" name="addictions[]" value="Comida"><label class="form-check-label" for="vicio_comida">Comida</label></div>
              <div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" id="vicio_outros" name="addictions[]" value="Outros"><label class="form-check-label" for="vicio_outros">Outros</label></div>

              <div class="col-12"><hr class="my-2"><h5 class="mb-1">Outros</h5></div>
              <div class="col-md-3 form-check ms-2"><input class="form-check-input" type="checkbox" id="had_therapy" name="had_therapy"><label class="form-check-label" for="had_therapy">Ja fez terapia?</label></div>
              <div class="col-md-8"><label class="form-label">Descricao terapia anterior</label><textarea class="form-control" name="therapy_description" rows="2"></textarea></div>
              <div class="col-md-4"><label class="form-label">Data inicio tratamento</label><input class="form-control" type="date" name="treatment_start_date"></div>
              <div class="col-md-4"><label class="form-label">Menstruacao</label><input class="form-control" name="menstruation"></div>
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
          <h5>Area do Paciente (Interno)</h5>
          <ul class="nav nav-tabs mb-3"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#atendimentos" type="button">Atendimentos</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tarefas" type="button">Tarefas</button></li></ul>
          <div class="tab-content">
            <div class="tab-pane fade show active" id="atendimentos">
              <div class="mb-2"><input class="form-control" type="date"></div>
              <div class="mb-2"><input class="form-control" placeholder="Descricao"></div>
              <div id="editorAtendimento" style="height:160px;"></div>
            </div>
            <div class="tab-pane fade" id="tarefas">
              <div class="row g-2">
                <div class="col-md-3"><input class="form-control" type="date"></div>
                <div class="col-md-9"><input class="form-control" placeholder="Titulo"></div>
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

  $('#birth_date').on('change', function(){
    if (!this.value) return;
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
