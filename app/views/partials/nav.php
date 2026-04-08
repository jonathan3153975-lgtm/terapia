<?php
use Helpers\Auth;
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="#">Terapia SaaS</a>
    <div class="ms-auto d-flex align-items-center gap-3 text-white">
      <span><?php echo htmlspecialchars((string) Auth::name()); ?></span>
      <a class="btn btn-sm btn-outline-light" href="<?php echo $appUrl; ?>/index.php?action=logout">Sair</a>
    </div>
  </div>
</nav>
