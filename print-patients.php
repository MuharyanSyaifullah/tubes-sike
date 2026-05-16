<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die('Akses ditolak.');
}

require 'includes/db.php';
require 'includes/functions.php';

$search = trim($_GET['search'] ?? '');

$roleFilter = "";
$params = [];
if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
    $roleFilter = " WHERE id = :pid";
    $params['pid'] = $_SESSION['patient_id'] ?? 0;
}

if ($search !== '') {
    $searchCondition = " (name LIKE :q OR code LIKE :q OR diagnosis LIKE :q)";
    $whereClause = $roleFilter ? $roleFilter . " AND " . $searchCondition : " WHERE " . $searchCondition;
    $stmt = $pdo->prepare("SELECT * FROM patients" . $whereClause . " ORDER BY id DESC");
    $params['q'] = "%$search%";
    $stmt->execute($params);
    $patients = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT * FROM patients" . $roleFilter . " ORDER BY id DESC");
    $stmt->execute($params);
    $patients = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Riwayat Pasien</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #111; line-height: 1.5; margin: 0; padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table, th, td { border: 1px solid #aaa; }
        th, td { padding: 10px; text-align: left; font-size: 13px; }
        th { background-color: #f4f4f4; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #2196F3; color: white; border: none; border-radius: 6px;">Cetak / Simpan PDF</button>
    </div>

    <div class="header">
        <h1>Daftar Rekam Medis Pasien</h1>
        <p>Dicetak pada: <?= date('d M Y, H:i') ?> oleh: <?= h($_SESSION['username']) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode RM</th>
                <th>Nama Pasien</th>
                <th>Gender / Umur</th>
                <th>Diagnosis</th>
                <th>Status</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($patients)): ?>
                <tr><td colspan="6" style="text-align: center;">Tidak ada data pasien.</td></tr>
            <?php else: ?>
                <?php foreach ($patients as $p): ?>
                <tr>
                    <td><?= h($p['code']) ?></td>
                    <td><?= h($p['name']) ?></td>
                    <td><?= h($p['gender']) ?> / <?= h($p['age']) ?> Thn</td>
                    <td><?= h($p['diagnosis']) ?></td>
                    <td><?= h(statusLabel($p['status'])) ?></td>
                    <td><?= h($p['progress']) ?>%</td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>