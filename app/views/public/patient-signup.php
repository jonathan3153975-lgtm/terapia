<?php $title = 'Ficha de cadastro'; include __DIR__ . '/../partials/header.php'; ?>
<div class="container py-5" style="max-width:780px;">
  <div class="card shadow-sm border-0">
    <div class="card-body p-4 p-md-5">
      <h3 class="mb-1">Ficha de cadastro do paciente</h3>
      <p class="text-muted mb-4">Preencha a ficha completa para que seu terapeuta possa analisar e aprovar seu acesso.</p>

      <?php if (isset($_GET['status']) && isset($_GET['msg'])): ?>
        <div class="alert <?php echo (string) $_GET['status'] === 'success' ? 'alert-success' : 'alert-danger'; ?> mb-3"><?php echo htmlspecialchars((string) $_GET['msg']); ?></div>
      <?php endif; ?>

      <?php if (empty($linkData)): ?>
        <div class="alert alert-danger mb-0">Este link está inválido, expirado ou indisponível.</div>
      <?php else: ?>
        <form method="POST" action="<?php echo $appUrl; ?>/index.php?action=patient-signup-submit" class="row g-3">
          <input type="hidden" name="token" value="<?php echo htmlspecialchars((string) $token); ?>">
          <div class="col-md-8"><label class="form-label">Nome completo</label><input class="form-control" name="name" required></div>
          <div class="col-md-4"><label class="form-label">Data de nascimento</label><input class="form-control" type="date" name="birth_date"></div>
          <div class="col-md-4"><label class="form-label">CPF</label><input class="form-control mask-cpf" name="cpf" required></div>
          <div class="col-md-4"><label class="form-label">Telefone</label><input class="form-control mask-phone" name="phone" required></div>
          <div class="col-md-4"><label class="form-label">E-mail</label><input class="form-control" type="email" name="email" required></div>

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
          <div class="col-md-8"><label class="form-label">Filhos</label><input class="form-control" name="children" placeholder="Ex.: 2 filhos"></div>

          <div class="col-12"><hr class="my-2"><h5 class="mb-1">Dados familiares</h5></div>
          <div class="col-md-6"><label class="form-label">Pai</label><input class="form-control" name="father" placeholder="Nome do pai"></div>
          <div class="col-md-6"><label class="form-label">Mãe</label><input class="form-control" name="mother" placeholder="Nome da mãe"></div>
          <div class="col-md-12"><label class="form-label">Primeira palavra que vem à mente</label><input class="form-control" name="first_word"></div>

          <div class="col-12"><hr class="my-2"><h5 class="mb-1">Endereço (opcional)</h5></div>
          <div class="col-md-3"><label class="form-label">CEP</label><input class="form-control" id="cep" name="cep" maxlength="8"></div>
          <div class="col-md-9"><label class="form-label">Endereço</label><input class="form-control" id="address" name="address"></div>
          <div class="col-md-5"><label class="form-label">Bairro</label><input class="form-control" id="neighborhood" name="neighborhood"></div>
          <div class="col-md-4"><label class="form-label">Cidade</label><input class="form-control" id="city" name="city"></div>
          <div class="col-md-3"><label class="form-label">UF</label><input class="form-control" id="state" name="state"></div>

          <div class="col-12"><hr class="my-2"><h5 class="mb-1">Saúde</h5></div>
          <div class="col-md-3 form-check ms-2"><input class="form-check-input" type="checkbox" id="depression" name="depression"><label class="form-check-label" for="depression">Depressão</label></div>
          <div class="col-md-8"><label class="form-label">Medicação para depressão</label><input class="form-control" id="depression_medication" name="depression_medication"></div>
          <div class="col-md-3 form-check ms-2"><input class="form-check-input" type="checkbox" id="anxiety" name="anxiety"><label class="form-check-label" for="anxiety">Ansiedade</label></div>
          <div class="col-md-8"><label class="form-label">Medicação para ansiedade</label><input class="form-control" id="anxiety_medication" name="anxiety_medication"></div>

          <div class="col-md-4 form-check ms-2"><input class="form-check-input" type="checkbox" id="has_medical_treatment" name="has_medical_treatment"><label class="form-check-label" for="has_medical_treatment">Faz tratamento médico</label></div>
          <div class="col-md-8"><label class="form-label">Tratamento médico (descrição)</label><textarea class="form-control" id="medical_treatment_description" name="medical_treatment_description" rows="2"></textarea></div>
          <div class="col-12"><label class="form-label">Tratamento médico (medicação)</label><input class="form-control" id="medical_treatment_medication" name="medical_treatment_medication"></div>

          <div class="col-12"><h6 class="text-muted mt-1 mb-1">Comorbidades</h6></div>
          <div class="col-12">
            <div class="dropdown">
              <button class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span id="comorbiditiesLabel">Nenhuma selecionada</span>
                <i class="fa-solid fa-chevron-down"></i>
              </button>
              <div class="dropdown-menu p-3 w-100" style="max-height: 240px; overflow-y: auto;">
                <div class="form-check"><input class="form-check-input comorbidity-option" type="checkbox" id="com_alcoolismo" name="comorbidities[]" value="Alcoolismo"><label class="form-check-label" for="com_alcoolismo">Alcoolismo</label></div>
                <div class="form-check"><input class="form-check-input comorbidity-option" type="checkbox" id="com_drogas" name="comorbidities[]" value="Drogas"><label class="form-check-label" for="com_drogas">Drogas</label></div>
                <div class="form-check"><input class="form-check-input comorbidity-option" type="checkbox" id="com_convulsoes" name="comorbidities[]" value="Convulsões"><label class="form-check-label" for="com_convulsoes">Convulsões</label></div>
                <div class="form-check"><input class="form-check-input comorbidity-option" type="checkbox" id="com_fumante" name="comorbidities[]" value="Fumante"><label class="form-check-label" for="com_fumante">Fumante</label></div>
                <div class="form-check"><input class="form-check-input comorbidity-option" type="checkbox" id="com_hepatite" name="comorbidities[]" value="Hepatite"><label class="form-check-label" for="com_hepatite">Hepatite</label></div>
                <div class="form-check"><input class="form-check-input comorbidity-option" type="checkbox" id="com_hipertensao" name="comorbidities[]" value="Hipertensão"><label class="form-check-label" for="com_hipertensao">Hipertensão</label></div>
                <div class="form-check"><input class="form-check-input comorbidity-option" type="checkbox" id="com_diabetes" name="comorbidities[]" value="Diabetes"><label class="form-check-label" for="com_diabetes">Diabetes</label></div>
              </div>
            </div>
          </div>

          <div class="col-12"><hr class="my-2"><h5 class="mb-1">Vícios</h5></div>
          <div class="col-12">
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
          <div class="col-12"><label class="form-label">Queixa principal</label><textarea class="form-control" name="main_complaint" rows="4"></textarea></div>

          <div class="col-12"><button class="btn btn-primary w-100" type="submit">Enviar cadastro</button></div>
        </form>

        <script>
        window.addEventListener('load', function () {
          const toggleField = function (checkboxSelector, inputSelector) {
            const checkbox = document.querySelector(checkboxSelector);
            const input = document.querySelector(inputSelector);
            if (!checkbox || !input) return;
            input.disabled = !checkbox.checked;
            if (!checkbox.checked) input.value = '';
            checkbox.addEventListener('change', function () {
              input.disabled = !checkbox.checked;
              if (!checkbox.checked) input.value = '';
            });
          };

          const toggleByCheckbox = function (checkboxSelector, fieldSelectors) {
            const checkbox = document.querySelector(checkboxSelector);
            if (!checkbox) return;
            const applyState = function () {
              fieldSelectors.forEach(function (selector) {
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

          const updateAddictionsLabel = function () {
            const selected = Array.from(document.querySelectorAll('.addiction-option:checked')).map(function (i) { return i.value; });
            document.getElementById('addictionsLabel').textContent = selected.length ? selected.join(', ') : 'Nenhum selecionado';
          };
          document.querySelectorAll('.addiction-option').forEach(function (option) {
            option.addEventListener('change', updateAddictionsLabel);
          });
          updateAddictionsLabel();

          const updateComorbiditiesLabel = function () {
            const selected = Array.from(document.querySelectorAll('.comorbidity-option:checked')).map(function (i) { return i.value; });
            document.getElementById('comorbiditiesLabel').textContent = selected.length ? selected.join(', ') : 'Nenhuma selecionada';
          };
          document.querySelectorAll('.comorbidity-option').forEach(function (option) {
            option.addEventListener('change', updateComorbiditiesLabel);
          });
          updateComorbiditiesLabel();

          const cepInput = document.getElementById('cep');
          if (cepInput) {
            cepInput.addEventListener('input', function () {
              const cep = (cepInput.value || '').replace(/\D/g, '');
              if (cep.length !== 8) return;
              fetch('https://viacep.com.br/ws/' + cep + '/json/')
                .then(function (response) { return response.json(); })
                .then(function (data) {
                  if (data.erro) return;
                  const address = document.getElementById('address');
                  const neighborhood = document.getElementById('neighborhood');
                  const city = document.getElementById('city');
                  const state = document.getElementById('state');
                  if (address) address.value = data.logradouro || '';
                  if (neighborhood) neighborhood.value = data.bairro || '';
                  if (city) city.value = data.localidade || '';
                  if (state) state.value = data.uf || '';
                });
            });
          }
        });
        </script>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
