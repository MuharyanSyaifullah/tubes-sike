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
    'motorik' => min(100, max(10, (int)$patient['progress'])),
    'bicara' => min(100, max(10, (int)$patient['progress'] - 8)),
    'memori' => min(100, max(10, (int)$patient['progress'] - 4)),
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
  <div class="card chart-wrap">
    <h3>Grafik Sudut Angkat Kaki</h3>
    <canvas id="detailLineChart"></canvas>
  </div>
  <div class="card chart-wrap">
    <h3>Profil Neurologis</h3>
    <canvas id="detailRadarChart"></canvas>
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
      <div class="item"><strong>Heart Rate</strong><span><?= h((string)$patient['heart_rate']) ?> bpm</span></div>
      <div class="item"><strong>SpO2</strong><span><?= h((string)$patient['spo2']) ?>%</span></div>
      <div class="item"><strong>Blood Pressure</strong><span><?= h($patient['blood_pressure']) ?></span></div>
      <div class="item"><strong>Progress</strong><span><?= h((string)$patient['progress']) ?>%</span></div>
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
