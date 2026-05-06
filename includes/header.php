<?php if (!isset($pageTitle)) { $pageTitle = 'SIK Rehabilitasi'; } ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($pageTitle) ?></title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <div class="brand">
      <h1>SIK Rehabilitasi</h1>
      <p>Monitoring Pasien Disabilitas</p>
    </div>
    <nav class="nav">
      <a class="<?= ($activePage === 'dashboard') ? 'active' : '' ?>" href="index.php">Dashboard</a>
      <a class="<?= ($activePage === 'patients') ? 'active' : '' ?>" href="patients.php">Pasien</a>
      <a class="<?= ($activePage === 'report-form') ? 'active' : '' ?>" href="report-form.php">Input Laporan</a>
    </nav>
  </aside>
  <main class="main">
