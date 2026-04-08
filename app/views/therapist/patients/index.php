<?php $title = 'Pacientes'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="container page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Pacientes</h3>
    <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=patients-create">Novo paciente</a>
  </div>
  <div class="card mb-3"><div class="card-body">
    <input id="patientSearch" class="form-control" placeholder="Digite para filtrar dinamicamente" value="<?php echo htmlspecialchars($search); ?>">
  </div></div>
  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>Nome</th><th>CPF</th><th>Telefone</th><th>Email</th><th>Acoes</th></tr></thead>
        <tbody>
          <?php if (empty($patients)): ?>
            <tr><td colspan="5" class="text-center py-4 text-muted">Nenhum paciente cadastrado.</td></tr>
          <?php else: foreach ($patients as $patient): ?>
            <tr class="row-patient" data-search="<?php echo strtolower(htmlspecialchars(($patient['name'] ?? '') . ' ' . ($patient['cpf'] ?? '') . ' ' . ($patient['email'] ?? ''))); ?>">
              <td><?php echo htmlspecialchars($patient['name']); ?></td>
              <td><?php echo htmlspecialchars($patient['cpf']); ?></td>
              <td><?php echo htmlspecialchars($patient['phone']); ?></td>
              <td><?php echo htmlspecialchars($patient['email'] ?? '-'); ?></td>
              <td><div class="btn-group table-actions" role="group"><button class="btn btn-sm btn-primary">Visualizar</button><button class="btn btn-sm btn-info">Historico</button><button class="btn btn-sm btn-warning">Editar</button><button class="btn btn-sm btn-danger">Excluir</button></div></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
$('#patientSearch').on('input', function(){
  const t = ($(this).val() || '').toLowerCase();
  $('.row-patient').each(function(){
    const h = ($(this).data('search') || '').toString();
    $(this).toggle(t === '' || h.includes(t));
  });
});
</script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
