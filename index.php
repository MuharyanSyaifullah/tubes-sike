<?php
require 'includes/db.php';
require 'includes/functions.php';

$pageTitle = 'Dashboard Monitoring Pasien';
$activePage = 'dashboard';
$summary = getSummary($pdo);
$activities = getActivities($pdo);
$watchlist = getWatchlist($pdo);
$chartPatient = $watchlist[0] ?? getPatients($pdo)[0] ?? null;
$chartData = $chartPatient ? getSensorReadings($pdo, (int)$chartPatient['id']) : [];

require 'includes/header.php';
?>
<div class="topbar">
  <div class="page-title">
    <h2>Dashboard Monitoring</h2>
    <p>Ringkasan perkembangan pasien, laporan terapi, dan watchlist prioritas.</p>
  </div>
  <div class="badge">Data dari MySQL</div>
</div>

<section class="stats-grid">
  <div class="card"><div class="muted">Total Pasien</div><div class="stat-value"><?= $summary['totalPatients'] ?></div></div>
  <div class="card"><div class="muted">Total Laporan</div><div class="stat-value"><?= $summary['activeReports'] ?></div></div>
  <div class="card"><div class="muted">Rata-rata Progress</div><div class="stat-value"><?= $summary['avgProgress'] ?>%</div></div>
</section>

<section class="grid-2 mt-18">
  <div class="card chart-wrap">
    <h3>Grafik Sudut Angkat Kaki</h3>
    <p class="muted">Visualisasi perkembangan berdasarkan data sensor pasien.</p>
    <canvas id="dashboardChart"></canvas>
    <script>
      window.dashboardChartData = {
        labels: <?= json_encode(array_column($chartData, 'session_date')) ?>,
        values: <?= json_encode(array_map('floatval', array_column($chartData, 'angle'))) ?>,
        title: <?= json_encode($chartPatient ? $chartPatient['name'] : 'Tidak ada data') ?>
      };
    </script>
  </div>
  <div class="card">
    <h3>Aktivitas Terbaru</h3>
    <ul class="list">
      <?php foreach ($activities as $item): ?>
        <li>
          <strong><?= h($item['report_date']) ?></strong><br>
          <span class="muted"><?= h($item['activity_type']) ?> - <?= h($item['name']) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="mt-18">
  <h3>Critical Watchlist</h3>
  <div class="grid-3">
    <?php foreach ($watchlist as $patient): ?>
      <div class="card">
        <h4><?= h($patient['name']) ?></h4>
        <p class="muted"><?= h($patient['room']) ?> • <?= h($patient['diagnosis']) ?></p>
        <p class="status <?= h($patient['status']) ?>"><?= h(statusLabel($patient['status'])) ?></p>
        <div class="kv single">
          <div class="item"><strong>Heart Rate</strong><span><?= h((string)$patient['heart_rate']) ?> bpm</span></div>
          <div class="item"><strong>SpO2</strong><span><?= h((string)$patient['spo2']) ?>%</span></div>
          <div class="item"><strong>Progress</strong><span><?= h((string)$patient['progress']) ?>%</span></div>
        </div>
        <div class="footer-actions">
          <a class="btn btn-secondary" href="patient-detail.php?id=<?= (int)$patient['id'] ?>">Lihat Detail</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php require 'includes/footer.php'; ?>
