<?php $title = 'Paciente'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<?php
$addictions = [];
if (!empty($patient['addictions_json'])) {
  $decoded = json_decode((string) $patient['addictions_json'], true);
  if (is_array($decoded)) {
    $addictions = $decoded;
  }
}
?>
<div class="page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Ficha do paciente</h3>
    <a class="btn btn-outline-dark" href="<?php echo $appUrl; ?>/dashboard.php?action=patients"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6"><strong>Nome:</strong> <?php echo htmlspecialchars((string) $patient['name']); ?></div>
        <div class="col-md-3"><strong>CPF:</strong> <?php echo htmlspecialchars((string) $patient['cpf']); ?></div>
        <div class="col-md-3"><strong>Telefone:</strong> <?php echo htmlspecialchars((string) $patient['phone']); ?></div>
        <div class="col-md-6"><strong>E-mail:</strong> <?php echo htmlspecialchars((string) ($patient['email'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>Nascimento:</strong> <?php echo htmlspecialchars((string) ($patient['birth_date'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>Estado civil:</strong> <?php echo htmlspecialchars((string) ($patient['marital_status'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>Filhos:</strong> <?php echo htmlspecialchars((string) ($patient['children'] ?? '-')); ?></div>
        <div class="col-md-6"><strong>Endereco:</strong> <?php echo htmlspecialchars((string) ($patient['address'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>Bairro:</strong> <?php echo htmlspecialchars((string) ($patient['neighborhood'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>CEP:</strong> <?php echo htmlspecialchars((string) ($patient['cep'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>Cidade:</strong> <?php echo htmlspecialchars((string) ($patient['city'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>UF:</strong> <?php echo htmlspecialchars((string) ($patient['state'] ?? '-')); ?></div>

        <div class="col-12"><hr class="my-2"><h5 class="mb-1">Saúde</h5></div>
        <div class="col-md-2"><strong>Depressão:</strong> <?php echo !empty($patient['depression']) ? 'Sim' : 'Não'; ?></div>
        <div class="col-md-2"><strong>Ansiedade:</strong> <?php echo !empty($patient['anxiety']) ? 'Sim' : 'Nao'; ?></div>
        <div class="col-md-2"><strong>Alcoolismo:</strong> <?php echo !empty($patient['alcoholism']) ? 'Sim' : 'Nao'; ?></div>
        <div class="col-md-2"><strong>Drogas:</strong> <?php echo !empty($patient['drugs']) ? 'Sim' : 'Nao'; ?></div>
        <div class="col-md-2"><strong>Convulsoes:</strong> <?php echo !empty($patient['convulsions']) ? 'Sim' : 'Nao'; ?></div>
        <div class="col-md-2"><strong>Fumante:</strong> <?php echo !empty($patient['smoker']) ? 'Sim' : 'Nao'; ?></div>
        <div class="col-md-2"><strong>Hepatite:</strong> <?php echo !empty($patient['hepatitis']) ? 'Sim' : 'Não'; ?></div>
        <div class="col-md-2"><strong>Hipertensao:</strong> <?php echo !empty($patient['hypertension']) ? 'Sim' : 'Nao'; ?></div>
        <div class="col-md-2"><strong>Diabetes:</strong> <?php echo !empty($patient['diabetes']) ? 'Sim' : 'Nao'; ?></div>
        <div class="col-12"><strong>Tratamento/medicacoes:</strong><br><?php echo nl2br(htmlspecialchars((string) ($patient['medical_treatment'] ?? '-'))); ?></div>

        <div class="col-12"><hr class="my-2"><h5 class="mb-1">Vícios</h5></div>
        <div class="col-12"><?php echo empty($addictions) ? '-' : htmlspecialchars(implode(', ', $addictions)); ?></div>

        <div class="col-12"><hr class="my-2"><h5 class="mb-1">Outros</h5></div>
        <div class="col-md-3"><strong>Já fez terapia?:</strong> <?php echo !empty($patient['had_therapy']) ? 'Sim' : 'Não'; ?></div>
        <div class="col-md-3"><strong>Inicio tratamento:</strong> <?php echo htmlspecialchars((string) ($patient['treatment_start_date'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>Menstruacao:</strong> <?php echo htmlspecialchars((string) ($patient['menstruation'] ?? '-')); ?></div>
        <div class="col-md-3"><strong>Intestino:</strong> <?php echo htmlspecialchars((string) ($patient['bowel'] ?? '-')); ?></div>
        <div class="col-12"><strong>Descricao terapia anterior:</strong><br><?php echo nl2br(htmlspecialchars((string) ($patient['therapy_description'] ?? '-'))); ?></div>
        <div class="col-12"><strong>Queixa principal:</strong><br><?php echo nl2br(htmlspecialchars((string) ($patient['main_complaint'] ?? '-'))); ?></div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
