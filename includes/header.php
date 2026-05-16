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
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                    <a class="<?= ($activePage === 'patients') ? 'active' : '' ?>" href="patient-detail.php?id=<?= $_SESSION['patient_id'] ?? 0 ?>">Data Rekam Medis</a>
                <?php else: ?>
                    <a class="<?= ($activePage === 'patients') ? 'active' : '' ?>" href="patients.php">Pasien</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin')): ?>
                    <a class="<?= ($activePage === 'add-patient') ? 'active' : '' ?>" href="add-patient.php">Tambah Pasien</a>
                    <a class="<?= ($activePage === 'report-form') ? 'active' : '' ?>" href="report-form.php">Input Laporan</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin'): ?>
                    <a class="<?= ($activePage === 'manage-users') ? 'active' : '' ?>" href="manage-users.php">Manage User</a>
                <?php endif; ?>
                <a class="<?= ($activePage === 'profile') ? 'active' : '' ?>" href="profile.php">Profil Saya</a>
            </nav>
        </aside>
        <main class="main">
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="global-topbar" id="global-topbar">
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <button id="mobile-menu-btn" aria-label="Menu" style="background: none; border: none; cursor: pointer; color: var(--primary-dark); display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 16px; margin-left: auto;">
                        <span class="muted hide-mobile" style="font-size: 14px;">Halo, <strong style="color: var(--primary-dark);"><?= h($_SESSION['username']) ?></strong> <span style="background: var(--surface); padding: 2px 8px; border-radius: 12px; font-size: 12px; border: 1px solid var(--border); margin-left: 4px;"><?= strtoupper(h($_SESSION['role'])) ?></span></span>
                        <a href="logout.php" class="btn btn-secondary" style="padding: 6px 12px; font-size: 14px; box-shadow: var(--shadow);">Logout</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
