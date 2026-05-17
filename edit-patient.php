<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['role'] === 'user') {
    die('<h2 style="color: red; text-align: center; margin-top: 50px;">Akses Ditolak: Anda tidak memiliki izin untuk mengubah data pasien.</h2>');
}

require 'includes/db.php';
require 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$patient = getPatientById($pdo, $id);

if (!$patient) {
    die('<h2 style="text-align: center; margin-top: 50px;">Pasien tidak ditemukan.</h2>');
}

$pageTitle = 'Edit Data Pasien';
$activePage = 'patients';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $gender = trim($_POST['gender'] ?? 'L');
    $address = trim($_POST['address'] ?? '');
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $disability_type = trim($_POST['disability_type'] ?? '');
    $room = trim($_POST['room'] ?? '');
    $blood_type = trim($_POST['blood_type'] ?? '-');
    $clinician = trim($_POST['clinician'] ?? '');
    $status = trim($_POST['status'] ?? 'stable');

    if ($name && $age) {
        try {
            $stmt = $pdo->prepare("UPDATE patients SET name=?, age=?, gender=?, address=?, diagnosis=?, disability_type=?, room=?, blood_type=?, clinician=?, status=? WHERE id=?");
            $stmt->execute([$name, $age, $gender, $address, $diagnosis, $disability_type, $room, $blood_type, $clinician, $status, $id]);
            
            $message = 'Data pasien berhasil diperbarui!';
            // Refresh data setelah update
            $patient = getPatientById($pdo, $id);
        } catch (Throwable $e) {
            $error = 'Gagal memperbarui data: ' . $e->getMessage();
        }
    } else {
        $error = 'Nama dan Umur wajib diisi.';
    }
}

require 'includes/header.php';
?>
<div class="page-title">
    <h2>Edit Data Pasien: <?= h($patient['code']) ?></h2>
    <p>Perbarui informasi biodata maupun rekam medis pasien di bawah ini.</p>
</div>

<?php if ($message || $error): ?>
<script>
    window.initialPopup = {
        title: <?= json_encode($message ? "Berhasil" : "Gagal") ?>,
        message: <?= json_encode($message ? $message : $error) ?>,
        type: <?= json_encode($message ? "success" : "error") ?>
    };
</script>
<?php endif; ?>

<form method="post" class="card mt-18" style="padding: 32px;">
    <div class="form-grid">
        <div>
            <label class="label">Nama Lengkap</label>
            <input name="name" class="input" type="text" value="<?= h($patient['name']) ?>" required>
        </div>
        <div>
            <label class="label">Umur</label>
            <input name="age" class="input" type="number" min="1" max="150" value="<?= h($patient['age']) ?>" required>
        </div>
        <div>
            <label class="label">Jenis Kelamin</label>
            <select name="gender" class="select" required>
                <option value="L" <?= $patient['gender'] === 'L' ? 'selected' : '' ?>>Laki-laki (L)</option>
                <option value="P" <?= $patient['gender'] === 'P' ? 'selected' : '' ?>>Perempuan (P)</option>
            </select>
        </div>
        <div>
            <label class="label">Status Kondisi Pasien</label>
            <select name="status" class="select" required>
                <option value="stable" <?= $patient['status'] === 'stable' ? 'selected' : '' ?>>Stable</option>
                <option value="monitoring" <?= $patient['status'] === 'monitoring' ? 'selected' : '' ?>>Monitoring</option>
                <option value="high-risk" <?= $patient['status'] === 'high-risk' ? 'selected' : '' ?>>High Risk</option>
                <option value="urgent" <?= $patient['status'] === 'urgent' ? 'selected' : '' ?>>Urgent</option>
            </select>
        </div>
        <div class="full">
            <label class="label">Diagnosis</label>
            <input name="diagnosis" class="input" type="text" value="<?= h($patient['diagnosis']) ?>" required>
        </div>
    </div>
    <div class="footer-actions mt-18">
        <a href="patient-detail.php?id=<?= $id ?>" class="btn btn-secondary">Batal / Kembali</a>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </div>
</form>
<?php require 'includes/footer.php'; ?>