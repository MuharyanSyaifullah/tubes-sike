<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['role'] === 'user') {
    die('<h2 style="color: red; text-align: center; margin-top: 50px;">Akses Ditolak: Role Anda (User) tidak memiliki izin untuk menambah laporan terapi.</h2>');
}

require 'includes/db.php';
require 'includes/functions.php';

$pageTitle = 'Input Laporan Harian';
$activePage = 'report-form';
$patients = getPatients($pdo);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = (int)($_POST['patient_id'] ?? 0);
    $reportDate = $_POST['report_date'] ?? '';
    $activityType = trim($_POST['activity_type'] ?? '');
    $narrative = trim($_POST['narrative'] ?? '');
    $mobility = (int)($_POST['mobility'] ?? 0);
    $communication = (int)($_POST['communication'] ?? 0);
    $socialSkills = (int)($_POST['social_skills'] ?? 0);
    $angle = (float)($_POST['angle'] ?? 0);
    $gyroX = (float)($_POST['gyro_x'] ?? 0);
    $gyroY = (float)($_POST['gyro_y'] ?? 0);
    $gyroZ = (float)($_POST['gyro_z'] ?? 0);

    if ($patientId && $reportDate && $activityType !== '') {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO reports (patient_id, report_date, activity_type, narrative, mobility, communication, social_skills)
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$patientId, $reportDate, $activityType, $narrative, $mobility, $communication, $socialSkills]);

            $stmt = $pdo->prepare("INSERT INTO sensor_readings (patient_id, session_date, angle, gyro_x, gyro_y, gyro_z)
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$patientId, $reportDate, $angle, $gyroX, $gyroY, $gyroZ]);

            $newProgress = max(0, min(100, (int)round((($mobility + $communication + $socialSkills) / 15) * 100)));
            $stmt = $pdo->prepare("UPDATE patients SET progress = ?, latest_assessment = CONCAT(?, ' 08:00:00') WHERE id = ?");
            $stmt->execute([$newProgress, $reportDate, $patientId]);

            $pdo->commit();
            $message = 'Laporan dan data sensor berhasil disimpan ke database.';
        } catch (Throwable $e) {
            $pdo->rollBack();
            $error = 'Gagal menyimpan data: ' . $e->getMessage();
        }
    } else {
        $error = 'Mohon lengkapi data wajib.';
    }
}

require 'includes/header.php';
?>
<div class="page-title">
    <h2>Input Laporan Harian</h2>
    <p>Form ini menyimpan laporan terapi dan bacaan sensor langsung ke MySQL.</p>
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
    <h3 class="section-title">Informasi Umum</h3>
    <div class="form-grid" style="margin-bottom: 28px;">
        <div>
            <label class="label">Pasien</label>
            <select name="patient_id" class="select" required>
                <option value="">Pilih pasien</option>
                <?php foreach ($patients as $patient): ?>
                    <option value="<?= (int)$patient['id'] ?>"><?= h($patient['name']) ?> - <?= h($patient['code']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="label">Tanggal Laporan</label>
            <input name="report_date" class="input" type="date" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div>
            <label class="label">Jenis Aktivitas</label>
            <select name="activity_type" class="select" required>
                <option>Fisioterapi</option>
                <option>Terapi Okupasi</option>
                <option>Terapi Wicara</option>
            </select>
        </div>
    </div>

    <h3 class="section-title">Data Sensor & Gyroscope</h3>
    <div class="form-grid" style="margin-bottom: 28px;">
        <div>
            <label class="label">Sudut Angkat Kaki</label>
            <input name="angle" class="input" type="number" step="0.01" value="45.00" required>
        </div>
        <div>
            <label class="label">Gyro X</label>
            <input name="gyro_x" class="input" type="number" step="0.01" value="1.20" required>
        </div>
        <div>
            <label class="label">Gyro Y</label>
            <input name="gyro_y" class="input" type="number" step="0.01" value="0.80" required>
        </div>
        <div>
            <label class="label">Gyro Z</label>
            <input name="gyro_z" class="input" type="number" step="0.01" value="0.60" required>
        </div>
    </div>

    <h3 class="section-title">Penilaian Klinis</h3>
    <div class="form-grid">
        <div>
            <label class="label">Skor Mobilitas</label>
            <input name="mobility" class="input" type="number" min="1" max="5" value="4" required>
        </div>
        <div>
            <label class="label">Skor Komunikasi</label>
            <input name="communication" class="input" type="number" min="1" max="5" value="3" required>
        </div>
        <div>
            <label class="label">Skor Sosial</label>
            <input name="social_skills" class="input" type="number" min="1" max="5" value="3" required>
        </div>
        <div class="full">
            <label class="label">Narasi Klinis</label>
            <textarea name="narrative" class="textarea" rows="6" required>Pasien menunjukkan perkembangan yang cukup baik pada latihan hari ini.</textarea>
        </div>
    </div>
    <div class="footer-actions">
        <button type="reset" class="btn btn-secondary">Reset</button>
        <button type="submit" class="btn btn-primary">Simpan ke Database</button>
    </div>
</form>
<?php require 'includes/footer.php'; ?>
