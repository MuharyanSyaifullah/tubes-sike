<?php
session_start();
// Jika sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require 'includes/db.php';
require 'includes/functions.php';

$error = '';
$success = '';

// --- AUTO-GENERATE KODE REKAM MEDIS ---
$stmtCode = $pdo->query("SELECT code FROM patients WHERE code LIKE 'RM-%' ORDER BY CAST(SUBSTRING(code, 4) AS UNSIGNED) DESC LIMIT 1");
$lastCode = $stmtCode->fetchColumn();
$lastNum = $lastCode ? (int)substr($lastCode, 3) : 0;
$autoCode = 'RM-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $patient_code = trim($_POST['patient_code'] ?? '');

    if ($username && $password && $confirm_password) {
        if (strlen($password) < 8) {
            $error = 'Password minimal harus terdiri dari 8 karakter.';
        } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $error = 'Password harus mengandung kombinasi huruf dan angka.';
        } else {
            if ($password === $confirm_password) {
                // Cek apakah username sudah dipakai
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = 'Username sudah terdaftar. Silakan gunakan username lain.';
                } else {
                    // Enkripsi password sebelum disimpan ke database
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    try {
                        $pdo->beginTransaction();

                        // 1. Buat record pasien baru secara otomatis
                            $stmtPatient = $pdo->prepare("INSERT INTO patients (code, name, age, gender, address, diagnosis, disability_type, status, room, blood_type, admission_date, clinician, progress, phase, latest_assessment, heart_rate, spo2, blood_pressure) VALUES (?, ?, 0, 'L', 'Belum Diisi', 'Belum Didata', 'Mobility', 'stable', '-', '-', CURDATE(), '-', 0, 'Fase I', NOW(), 80, 98, '120/80')");
                            $stmtPatient->execute([$autoCode, $username]);
                        $patient_id = $pdo->lastInsertId();

                        // 2. Simpan user baru dan tautkan ke ID pasien
                        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, patient_id) VALUES (?, ?, 'user', ?)");
                        $stmt->execute([$username, $hashed_password, $patient_id]);
                        
                        $pdo->commit();
                        $success = 'Registrasi berhasil! Silakan kembali ke halaman login.';
                    } catch (Throwable $e) {
                        $pdo->rollBack();
                        $error = 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage();
                    }
                }
            } else {
                $error = 'Konfirmasi password tidak cocok dengan password yang dimasukkan.';
            }
        }
    } else {
        $error = 'Mohon isi semua kolom yang disediakan.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi - SIK Rehabilitasi</title>
    <link rel="stylesheet" href="css/style.css">
    <?php if ($success || $error): ?>
    <script>
        window.initialPopup = {
            title: <?= json_encode($success ? "Berhasil" : "Gagal") ?>,
            message: <?= json_encode($success ? $success : $error) ?>,
            type: <?= json_encode($success ? "success" : "error") ?>
        };
    </script>
    <?php endif; ?>
</head>
<body style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #EAEFE9;">
    <form method="post" class="card" style="padding: 40px; width: 100%; max-width: 400px; box-shadow: var(--shadow);">
        <div style="text-align: center; margin-bottom: 32px;">
            <h2 style="color: var(--primary-dark); margin-bottom: 8px;">Daftar Akun Baru</h2>
            <p class="muted">Isi form di bawah untuk membuat akun</p>
        </div>
        
        <label class="label">Kode Rekam Medis (Otomatis)</label>
        <input type="text" name="patient_code" class="input" style="margin-bottom: 20px; background: #DDE5DD; color: var(--primary-dark); font-weight: bold; cursor: not-allowed; border-color: transparent;" value="<?= $autoCode ?>" readonly>
        
        <label class="label">Username</label><input type="text" name="username" class="input" style="margin-bottom: 20px;" placeholder="contoh : ryan" required autofocus>
        <label class="label">Password</label>
        <div style="position: relative; margin-bottom: 20px;">
            <input type="password" id="reg-password" name="password" class="input" style="width: 100%; padding-right: 60px;" minlength="8" placeholder="Gabungan huruf dan angka" required>
            <button type="button" onclick="togglePassword('reg-password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--primary-dark); cursor: pointer; padding: 4px; display: flex; align-items: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            </button>
        </div>
        <label class="label">Konfirmasi Password</label>
        <div style="position: relative; margin-bottom: 32px;">
            <input type="password" id="reg-confirm" name="confirm_password" class="input" style="width: 100%; padding-right: 60px;" minlength="8" placeholder="Gabungan huruf dan angka" required>
            <button type="button" onclick="togglePassword('reg-confirm', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--primary-dark); cursor: pointer; padding: 4px; display: flex; align-items: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            </button>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Daftar Sekarang</button>
        <div style="text-align: center; margin-top: 24px;"><p class="muted" style="font-size: 14px;">Sudah punya akun? <a href="login.php" style="color: var(--primary-dark); font-weight: 600;">Masuk di sini</a></p></div>
    </form>
<script>
function togglePassword(inputId, btn) {
    const p = document.getElementById(inputId);
    const eyeOpen = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
    const eyeClosed = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;
    if (p.type === 'password') {
        p.type = 'text';
        btn.innerHTML = eyeClosed;
    } else {
        p.type = 'password';
        btn.innerHTML = eyeOpen;
    }
}
</script>

<div id="popup-toast" class="popup-toast" role="alert" aria-live="assertive">
    <div class="popup-card">
        <div class="popup-icon">✓</div>
        <div class="popup-content">
            <strong id="popup-title">Berhasil</strong>
            <p id="popup-message">Operasi berhasil dilakukan.</p>
        </div>
        <button type="button" class="popup-close" aria-label="Tutup">×</button>
    </div>
</div>
<script src="js/app.js?v=<?= time() ?>"></script>

<?php if ($success): ?>
<script>
    setTimeout(() => { window.location.href = 'login.php'; }, 3000);
</script>
<?php endif; ?>
</body>
</html>