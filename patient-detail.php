<?php
require 'includes/db.php';
require 'includes/functions.php';

$id = (int)($_GET['id'] ?? 1);
$patient = getPatientById($pdo, $id);

if (!$patient) {
    die('Pasien tidak ditemukan.');
}

$pageTitle = 'Detail Pasien';
$activePage = '';
$reports = getPatientReports($pdo, $id);
$sensors = getSensorReadings($pdo, $id);

$neuroProfile = [
    'kognitif' => min(100, max(10, (int)$patient['progress'] + 5)),
    'motorik'  => min(100, max(10, (int)$patient['progress'])),
    'bicara'   => min(100, max(10, (int)$patient['progress'] - 8)),
    'memori'   => min(100, max(10, (int)$patient['progress'] - 4)),
];

require 'includes/header.php';
?>
<section class="card patient-header">
    <div class="avatar"><?= h(mb_strtoupper(mb_substr($patient['name'], 0, 1))) ?></div>
    <div>
        <h2><?= h($patient['name']) ?></h2>
        <p class="muted"><?= h($patient['age']) ?> tahun • <?= h($patient['address']) ?> • <?= h($patient['diagnosis']) ?></p>
        <p class="status <?= h($patient['status']) ?>"><?= h(statusLabel($patient['status'])) ?></p>
        <div class="kv">
            <div class="item"><strong>Kode Pasien</strong><span><?= h($patient['code']) ?></span></div>
            <div class="item"><strong>Golongan Darah</strong><span><?= h($patient['blood_type']) ?></span></div>
            <div class="item"><strong>Tanggal Masuk</strong><span><?= h($patient['admission_date']) ?></span></div>
            <div class="item"><strong>Dokter/Terapis</strong><span><?= h($patient['clinician']) ?></span></div>
            <div class="item"><strong>Asesmen Terakhir</strong><span><?= h($patient['latest_assessment']) ?></span></div>
            <div class="item"><strong>Fase Rehabilitasi</strong><span><?= h($patient['phase']) ?></span></div>
        </div>
    </div>
</section>

<section class="grid-2 mt-18">
    <div class="card">
        <h3>Grafik Sudut Angkat Kaki</h3>
        <div class="chart-container">
            <canvas id="detailLineChart"></canvas>
        </div>
    </div>
    <div class="card">
        <h3>Profil Neurologis</h3>
        <div class="chart-container">
            <canvas id="detailRadarChart"></canvas>
        </div>
    </div>
</section>

<section class="grid-2 mt-18">
    <div class="card">
        <h3>Catatan Klinis</h3>
        <ul class="list">
            <?php foreach ($reports as $report): ?>
                <li>
                    <strong><?= h($report['report_date']) ?> - <?= h($report['activity_type']) ?></strong><br>
                    <span class="muted"><?= h($report['narrative']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="card">
        <h3>Vital Signs</h3>
        <div class="kv single">
            <div class="item" style="border-left: 4px solid var(--danger);"><strong>Heart Rate</strong><span style="font-size: 1.15rem; font-weight: bold; color: var(--danger);"><?= h((string)$patient['heart_rate']) ?> bpm</span></div>
            <div class="item" style="border-left: 4px solid #2196F3;"><strong>SpO2</strong><span style="font-size: 1.15rem; font-weight: bold; color: #2196F3;"><?= h((string)$patient['spo2']) ?>%</span></div>
            <div class="item" style="border-left: 4px solid var(--warning);"><strong>Blood Pressure</strong><span style="font-size: 1.15rem; font-weight: bold; color: var(--warning);"><?= h($patient['blood_pressure']) ?></span></div>
            <div class="item" style="border-left: 4px solid var(--success);"><strong>Progress Keseluruhan</strong><div class="progress" style="margin-top: 8px;"><span style="width: <?= (int)$patient['progress'] ?>%"></span></div></div>
        </div>
    </div>
</section>

<script>
    window.detailLineChartData = {
        labels: <?= json_encode(array_column($sensors, 'session_date')) ?>,
        values: <?= json_encode(array_map('floatval', array_column($sensors, 'angle'))) ?>,
        title: <?= json_encode($patient['name']) ?>
    };
    window.detailRadarChartData = <?= json_encode($neuroProfile) ?>;
</script>
<?php require 'includes/footer.php'; ?>
