<?php
require 'includes/db.php';
require 'includes/functions.php';

$pageTitle = 'Daftar Pasien';
$activePage = 'patients';
$search = trim($_GET['search'] ?? '');
$patients = getPatients($pdo, $search);

require 'includes/header.php';
?>
<div class="topbar">
  <div class="page-title">
    <h2>Daftar Pasien</h2>
    <p>Data pasien tersimpan permanen di database MySQL.</p>
  </div>
</div>

<form class="search-row" method="get">
  <input name="search" class="input" placeholder="Cari nama, kode, atau diagnosis..." value="<?= h($search) ?>">
  <button class="btn btn-primary" type="submit">Cari</button>
</form>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>Profil Pasien</th>
        <th>Tipe Disabilitas</th>
        <th>Diagnosis</th>
        <th>Progress</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($patients as $patient): ?>
      <tr>
        <td>
          <strong><?= h($patient['name']) ?></strong><br>
          <span class="muted"><?= h($patient['code']) ?></span>
        </td>
        <td><?= h($patient['disability_type']) ?></td>
        <td><?= h($patient['diagnosis']) ?></td>
        <td>
          <div class="progress"><span style="width: <?= (int)$patient['progress'] ?>%"></span></div>
          <small><?= (int)$patient['progress'] ?>% - <?= h($patient['phase']) ?></small>
        </td>
        <td><span class="status <?= h($patient['status']) ?>"><?= h(statusLabel($patient['status'])) ?></span></td>
        <td><a class="btn btn-secondary" href="patient-detail.php?id=<?= (int)$patient['id'] ?>">Detail</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require 'includes/footer.php'; ?>
