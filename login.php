<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require 'includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Mohon isi username dan password.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - SIK Rehabilitasi</title>
    <link rel="stylesheet" href="css/style.css">
    <?php if ($error): ?>
    <script>
        window.initialPopup = {
            title: 'Gagal',
            message: '<?= htmlspecialchars($error) ?>',
            type: 'error'
        };
    </script>
    <?php endif; ?>
</head>
<body style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #d4f0ea;">
    <form method="post" class="card" style="padding: 40px; width: 100%; max-width: 400px; box-shadow: var(--shadow);">
        <div style="text-align: center; margin-bottom: 32px;">
            <h2 style="color: var(--primary-dark); margin-bottom: 8px;">SIK Rehabilitasi</h2>
            <p class="muted">Silakan masuk ke akun Anda</p>
        </div>
        
        
        <label class="label">Username</label>
        <input type="text" name="username" class="input" style="margin-bottom: 20px;" required autofocus>
        
        <label class="label">Password</label>
        <div style="position: relative; margin-bottom: 32px;">
            <input type="password" name="password" id="password" class="input" style="width: 100%; padding-right: 60px;" required>
            <button type="button" onclick="togglePassword('password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--primary-dark); cursor: pointer; padding: 4px; display: flex; align-items: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            </button>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Masuk</button>
        
        <div style="text-align: center; margin-top: 24px;">
            <p class="muted" style="font-size: 14px;">Belum punya akun? <a href="register.php" style="color: var(--primary-dark); font-weight: 600;">Daftar di sini</a></p>
        </div>
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
</body>
</html>