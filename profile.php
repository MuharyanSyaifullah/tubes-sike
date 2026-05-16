<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'includes/db.php';
require 'includes/functions.php';

$pageTitle = 'Profil Saya';
$activePage = 'profile';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($current_password && $new_password && $confirm_password) {
        // Ambil password lama dari database untuk dicocokkan
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user && password_verify($current_password, $user['password'])) {
            if (strlen($new_password) < 8) {
                $error = 'Password baru minimal harus 8 karakter.';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Konfirmasi password baru tidak cocok.';
            } else {
                // Enkripsi dan simpan password baru
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $_SESSION['user_id']]);
                $message = 'Kata sandi Anda berhasil diperbarui!';
            }
        } else {
            $error = 'Password saat ini yang Anda masukkan salah.';
        }
    } else {
        $error = 'Mohon isi semua kolom yang disediakan.';
    }
}

require 'includes/header.php';
?>
<div class="page-title">
    <h2>Profil & Keamanan</h2>
    <p>Kelola akun Anda dan ubah kata sandi secara mandiri di sini.</p>
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

<div class="grid-2 mt-18">
    <div class="card">
        <h3>Informasi Akun</h3>
        <div class="kv single mt-18">
            <div class="item"><strong>Username Login</strong><span style="font-size: 16px; color: var(--primary-dark); font-weight: bold;"><?= h($_SESSION['username']) ?></span></div>
            <div class="item"><strong>Hak Akses (Role)</strong><span class="badge" style="display: inline-block; margin-top: 4px;"><?= strtoupper(h($_SESSION['role'])) ?></span></div>
        </div>
    </div>

    <form method="post" class="card">
        <h3>Ganti Password</h3>
        <p class="muted" style="margin-bottom: 20px;">Pastikan Anda menggunakan kata sandi yang kuat dan mudah diingat.</p>
        
        <label class="label">Password Saat Ini</label>
        <input type="password" name="current_password" class="input" style="margin-bottom: 20px;" required>

        <label class="label">Password Baru</label>
        <input type="password" name="new_password" class="input" style="margin-bottom: 20px;" minlength="8" required>

        <label class="label">Konfirmasi Password Baru</label>
        <input type="password" name="confirm_password" class="input" style="margin-bottom: 24px;" minlength="8" required>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Update Password</button>
    </form>
</div>

<?php require 'includes/footer.php'; ?>