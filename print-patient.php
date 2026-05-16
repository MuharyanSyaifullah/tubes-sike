<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die('Akses ditolak. Silakan login terlebih dahulu.');
}

require 'includes/db.php';
require 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$patient = getPatientById($pdo, $id);

if (!$patient) {
    die('Pasien tidak ditemukan.');
}

$reports = getPatientReports($pdo, $id);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekam Medis - <?= h($patient['code']) ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #111; line-height: 1.5; margin: 0; padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 26px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #555; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
        .box { border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
        .box h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table, th, td { border: 1px solid #aaa; }
        th, td { padding: 12px; text-align: left; font-size: 14px; }
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
        <h1>Laporan Rekam Medis Rehabilitasi</h1>
        <p>Dicetak pada: <?= date('d M Y, H:i') ?> oleh Staf: <?= h($_SESSION['username']) ?></p>
    </div>

    <div class="grid">
        <div class="box">
            <h3>Biodata Pasien</h3>
            <p><strong>Nama:</strong> <?= h($patient['name']) ?></p>
            <p><strong>Kode RM:</strong> <?= h($patient['code']) ?></p>
            <p><strong>Umur / Gender:</strong> <?= h($patient['age']) ?> Thn / <?= h($patient['gender']) ?></p>
            <p><strong>Alamat:</strong> <?= h($patient['address']) ?></p>
            <p><strong>Gol. Darah:</strong> <?= h($patient['blood_type']) ?></p>
        </div>
        <div class="box">
            <h3>Informasi Medis</h3>
            <p><strong>Diagnosis:</strong> <?= h($patient['diagnosis']) ?></p>
            <p><strong>Disabilitas:</strong> <?= h($patient['disability_type']) ?></p>
            <p><strong>Status Kondisi:</strong> <?= ucfirst(h($patient['status'])) ?></p>
            <p><strong>Fase Saat Ini:</strong> <?= h($patient['phase']) ?> (Progress: <?= h((string)$patient['progress']) ?>%)</p>
            <p><strong>Dokter PJ:</strong> <?= h($patient['clinician']) ?></p>
        </div>
    </div>

    <h3>Riwayat Laporan Terapi</h3>
    <?php if (empty($reports)): ?>
        <p style="color: #666; font-style: italic;">Belum ada riwayat laporan terapi untuk pasien ini.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th width="15%">Tanggal</th>
                    <th width="20%">Jenis Aktivitas</th>
                    <th width="65%">Catatan Klinis / Narasi Terapis</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $r): ?>
                <tr>
                    <td><?= h($r['report_date']) ?></td>
                    <td><?= h($r['activity_type']) ?></td>
                    <td><?= h($r['narrative']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>