<?php
$status = strtolower((string) ($_GET['status'] ?? ''));
$message = trim((string) ($_GET['msg'] ?? ''));
if ($status === '' || $message === '') {
    return;
}

$isSuccess = $status === 'success';
$alertClass = $isSuccess ? 'alert-success' : 'alert-danger';
$iconClass = $isSuccess ? 'fa-circle-check' : 'fa-circle-exclamation';
?>
<div class="alert <?php echo $alertClass; ?> d-flex align-items-center gap-2 mb-3" role="alert">
  <i class="fa-solid <?php echo $iconClass; ?>"></i>
  <span><?php echo htmlspecialchars($message); ?></span>
</div>
