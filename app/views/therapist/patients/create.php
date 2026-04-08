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
              <div class="col-md-12"><label class="form-label">Queixa principal</label><textarea class="form-control" name="main_complaint" rows="2"></textarea></div>
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
