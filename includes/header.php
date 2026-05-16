<?php 
// Mencegah browser menyimpan cache halaman (mencegah bug tombol Back setelah logout)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($pageTitle)) { $pageTitle = 'SIK Rehabilitasi'; } 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin')): ?>
                    <a class="<?= ($activePage === 'add-patient') ? 'active' : '' ?>" href="add-patient.php">Tambah Pasien</a>
                    <a class="<?= ($activePage === 'report-form') ? 'active' : '' ?>" href="report-form.php">Input Laporan</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin'): ?>
                    <a class="<?= ($activePage === 'manage-users') ? 'active' : '' ?>" href="manage-users.php">Manage User</a>
                <?php endif; ?>
            </nav>
        </aside>
        <main class="main">
