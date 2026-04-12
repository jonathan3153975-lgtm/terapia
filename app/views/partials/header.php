<?php
/** @var string $title */
/** @var string $appUrl */
/** @var string $faviconUrl */
$title = $title ?? 'Tera-Tech';
$appUrl = $appUrl ?? '';

$assetBase = rtrim($appUrl, '/');
if ($assetBase === '') {
  $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
  $scriptDir = rtrim($scriptDir, '/');
  $assetBase = ($scriptDir === '.' || $scriptDir === '') ? '' : $scriptDir;
}

$faviconUrl = trim((string) ($faviconUrl ?? ''));
if ($faviconUrl === '') {
  $faviconUrl = $assetBase . '/app/images/logo.png';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($title); ?></title>
  <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($faviconUrl); ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
  <link href="<?php echo $assetBase; ?>/public/css/app.css" rel="stylesheet">
</head>
<body class="app-body">
