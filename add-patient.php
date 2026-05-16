<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['role'] === 'user') {
    die('<h2 style="color: red; text-align: center; margin-top: 50px;">Akses Ditolak: Role Anda (User) tidak memiliki izin untuk menambah pasien.</h2>');
}

require 'includes/db.php';
require 'includes/functions.php';

$pageTitle = 'Tambah Pasien Baru';
$activePage = 'add-patient';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $gender = trim($_POST['gender'] ?? 'L');
    $address = trim($_POST['address'] ?? '');
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $disability_type = trim($_POST['disability_type'] ?? '');
    $room = trim($_POST['room'] ?? '');
    $blood_type = trim($_POST['blood_type'] ?? '-');
    $clinician = trim($_POST['clinician'] ?? '');

    if ($code && $name && $age) {
        try {
            // Simpan ke MySQL dengan nilai default untuk status & tanda vital
            $stmt = $pdo->prepare("INSERT INTO patients (code, name, age, gender, address, diagnosis, disability_type, status, room, blood_type, admission_date, clinician, progress, phase, latest_assessment, heart_rate, spo2, blood_pressure) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, 'stable', ?, ?, CURDATE(), ?, 0, 'Fase I', NOW(), 80, 98, '120/80')");
            $stmt->execute([$code, $name, $age, $gender, $address, $diagnosis, $disability_type, $room, $blood_type, $clinician]);
            $message = 'Pasien baru berhasil ditambahkan ke sistem!';
        } catch (Throwable $e) {
            $error = 'Gagal menyimpan pasien: Pastikan Kode Pasien unik. (' . $e->getMessage() . ')';
        }
    } else {
        $error = 'Mohon lengkapi Kode Rekam Medis, Nama, dan Umur pasien.';
    }
}

// --- AUTO-GENERATE KODE REKAM MEDIS ---
$stmtCode = $pdo->query("SELECT id FROM patients ORDER BY id DESC LIMIT 1");
$lastId = (int)$stmtCode->fetchColumn();
$autoCode = 'RM-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

require 'includes/header.php';
?>
<div class="page-title">
    <h2>Tambah Pasien Baru</h2>
    <p>Masukkan data pasien baru yang akan tersimpan permanen ke dalam database.</p>
</div>

<?php if ($message || $error): ?>
<script>
    window.initialPopup = {
        title: '<?= $message ? "Berhasil" : "Gagal" ?>',
        message: '<?= $message ? h($message) : h($error) ?>',
        type: '<?= $message ? "success" : "error" ?>'
    };
</script>
<?php endif; ?>

<form method="post" class="card mt-18" style="padding: 32px;">
    <h3 class="section-title">Informasi Pribadi</h3>
    <div class="form-grid" style="margin-bottom: 28px;">
        <div>
            <label class="label">Kode Rekam Medis (Otomatis)</label>
            <input name="code" class="input" type="text" value="<?= $autoCode ?>" readonly style="background: #e8f3ea; color: var(--primary-dark); font-weight: bold; cursor: not-allowed; border-color: transparent;">
        </div>
        <div>
            <label class="label">Nama Lengkap (Wajib)</label>
            <input name="name" class="input" type="text" placeholder="Nama Pasien" required>
        </div>
        <div>
            <label class="label">Umur</label>
            <input name="age" class="input" type="number" min="1" max="150" required>
        </div>
        <div>
            <label class="label">Jenis Kelamin</label>
            <select name="gender" class="select" required>
                <option value="L">Laki-laki (L)</option>
                <option value="P">Perempuan (P)</option>
            </select>
        </div>
        <div class="full">
            <label class="label">Alamat Lengkap</label>
            <input name="address" class="input" type="text" placeholder="Jl. Contoh Alamat...">
        </div>
    </div>

    <h3 class="section-title">Informasi Medis Awal</h3>
    <div class="form-grid">
        <div>
            <label class="label">Diagnosis</label>
            <input name="diagnosis" class="input" type="text" placeholder="Misal: Hemiparesis Kanan" required>
        </div>
        <div>
            <label class="label">Tipe Disabilitas</label>
            <select name="disability_type" class="select" required>
                <option value="Neurological">Neurological</option>
                <option value="Mobility">Mobility</option>
                <option value="Cognitive">Cognitive</option>
            </select>
        </div>
        <div>
            <label class="label">Dokter / Terapis Penanggung Jawab</label>
            <input name="clinician" class="input" type="text" placeholder="Nama Dokter/Terapis">
        </div>
    </div>
    <div class="footer-actions">
        <button type="submit" class="btn btn-primary">Simpan Pasien Baru</button>
    </div>
</form>
<?php require 'includes/footer.php'; ?>