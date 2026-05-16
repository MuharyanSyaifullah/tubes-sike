<?php
session_start();
// Hanya super_admin yang boleh masuk halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: index.php');
    exit;
}

require 'includes/db.php';
require 'includes/functions.php';

$success = '';
$error = '';

// --- LOGIKA PEMROSESAN AKSI SUPER ADMIN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $target_id = (int)($_POST['target_id'] ?? 0);

    // 1. TAMBAH PENGGUNA BARU
    if ($action === 'add_user') {
        $new_username = trim($_POST['new_username'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $new_role = $_POST['new_role'] ?? 'user';
        $patient_id = !empty($_POST['patient_id']) ? (int)$_POST['patient_id'] : null;
        if ($new_role === 'admin') $patient_id = null; // Admin tidak ditautkan ke pasien

        if ($new_username && $new_password) {
            if (strlen($new_password) < 8) {
                $error = "Password minimal 8 karakter.";
            } else {
                $stmtCek = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmtCek->execute([$new_username]);
                if ($stmtCek->fetch()) {
                    $error = "Username sudah terdaftar, silakan gunakan yang lain.";
                } else {
                    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, patient_id) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$new_username, $hashed, $new_role, $patient_id]);
                    $success = "Pengguna baru berhasil ditambahkan!";
                }
            }
        } else {
            $error = "Username dan Password wajib diisi.";
        }
    }
    // Mencegah Super Admin tidak sengaja mengubah/menghapus akunnya sendiri
    elseif ($target_id && $target_id !== $_SESSION['user_id']) {
        if ($action === 'delete') {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$target_id]);
                $success = "Pengguna berhasil dihapus secara permanen.";
            } catch (Throwable $e) {
                $error = "Gagal menghapus pengguna.";
            }
        } elseif ($action === 'change_role') {
            $new_role = ($_POST['new_role'] === 'admin') ? 'admin' : 'user';
            try {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$new_role, $target_id]);
                $success = "Role pengguna berhasil diperbarui menjadi " . ucfirst($new_role) . ".";
            } catch (Throwable $e) {
                $error = "Gagal mengubah role pengguna.";
            }
        } elseif ($action === 'reset_password') {
            $new_password = $_POST['new_password'] ?? '';
            if (strlen($new_password) < 8) {
                $error = "Password baru minimal 8 karakter.";
            } else {
                try {
                    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed, $target_id]);
                    $success = "Password pengguna berhasil direset!";
                } catch (Throwable $e) {
                    $error = "Gagal mereset password.";
                }
            }
        }
    } else {
        $error = "Aksi ditolak: Anda tidak dapat mengubah atau menghapus akun Anda sendiri dari sini.";
    }
}

// --- AMBIL DATA DARI DATABASE ---
// Ambil data Admin (Kecuali Super Admin)
$admins = $pdo->query("SELECT * FROM users WHERE role = 'admin' ORDER BY id DESC")->fetchAll();
// Ambil data User biasa
$users = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY id DESC")->fetchAll();
// Ambil data pasien untuk dropdown pilihan tautan akun
$all_patients = $pdo->query("SELECT id, code, name FROM patients ORDER BY name ASC")->fetchAll();

$pageTitle = 'Manage User';
$activePage = 'manage-users';

require 'includes/header.php';
?>
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
    <div class="page-title" style="margin-bottom: 0;">
        <h2>Manajemen Pengguna</h2>
        <p>Halaman khusus Super Admin untuk mengelola akun Admin dan User.</p>
    </div>
    <button class="btn btn-primary" onclick="openAddUserModal()" style="box-shadow: var(--shadow);">+ Tambah Pengguna Baru</button>
</div>

<?php if ($success || $error): ?>
<script>
    window.initialPopup = {
        title: '<?= $success ? "Berhasil" : "Gagal" ?>',
        message: '<?= $success ? h($success) : h($error) ?>',
        type: '<?= $success ? "success" : "error" ?>'
    };
</script>
<?php endif; ?>

<!-- TABEL 1: DAFTAR ADMIN -->
<section class="mt-18">
    <h3 class="section-title">Daftar Admin</h3>
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-wrapper" style="border: none; border-radius: 0;">
            <table class="table">
                <thead style="background: #fdfdfd;">
                    <tr>
                        <th>Username</th>
                        <th>Terdaftar Pada</th>
                        <th>Aksi Pengelolaan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($admins)): ?>
                        <tr><td colspan="3" style="text-align: center; color: var(--muted);">Tidak ada admin terdaftar.</td></tr>
                    <?php else: ?>
                        <?php foreach ($admins as $u): ?>
                        <tr>
                            <td>
                                <strong><?= h($u['username']) ?></strong><br>
                                <span class="badge" style="margin-top: 4px; padding: 4px 8px; font-size: 11px;">Admin</span>
                            </td>
                            <td class="muted"><?= h(date('d M Y, H:i', strtotime($u['created_at']))) ?></td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <!-- Tombol Turunkan Role -->
                                    <form method="post" style="margin: 0;" onsubmit="return confirm('Turunkan pengguna ini menjadi User biasa?\nMereka tidak akan bisa menambah/mengubah data pasien lagi.')">
                                        <input type="hidden" name="action" value="change_role">
                                        <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="new_role" value="user">
                                        <button type="submit" class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px;">Jadikan User</button>
                                    </form>
                                    <!-- Tombol Reset Password -->
                                    <button type="button" class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px;" onclick="openPasswordModal(<?= $u['id'] ?>, '<?= h($u['username']) ?>')">Reset Password</button>
                                    <!-- Tombol Hapus -->
                                    <form method="post" style="margin: 0;" onsubmit="return confirm('PERINGATAN!\nAnda yakin ingin menghapus akun Admin ini secara permanen?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px; color: var(--danger); border-color: #f3c1be; background: #fffcfc;">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- TABEL 2: DAFTAR USER -->
<section class="mt-18" style="margin-top: 32px;">
    <h3 class="section-title">Daftar User</h3>
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-wrapper" style="border: none; border-radius: 0;">
            <table class="table">
                <thead style="background: #fdfdfd;">
                    <tr>
                        <th>Username</th>
                        <th>Terdaftar Pada</th>
                        <th>Aksi Pengelolaan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="3" style="text-align: center; color: var(--muted);">Tidak ada user terdaftar.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <strong><?= h($u['username']) ?></strong><br>
                                <span class="badge" style="margin-top: 4px; padding: 4px 8px; font-size: 11px; background: #f0f0f0; color: #555; border: 1px solid #ddd;">User</span>
                            </td>
                            <td class="muted"><?= h(date('d M Y, H:i', strtotime($u['created_at']))) ?></td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <!-- Tombol Naikkan Role -->
                                    <form method="post" style="margin: 0;" onsubmit="return confirm('Naikkan pengguna ini menjadi Admin?\nMereka akan memiliki akses untuk menambah dan mengubah data medis.')">
                                        <input type="hidden" name="action" value="change_role">
                                        <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="new_role" value="admin">
                                        <button type="submit" class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px;">Jadikan Admin</button>
                                    </form>
                                    <!-- Tombol Reset Password -->
                                    <button type="button" class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px;" onclick="openPasswordModal(<?= $u['id'] ?>, '<?= h($u['username']) ?>')">Reset Password</button>
                                    <!-- Tombol Hapus -->
                                    <form method="post" style="margin: 0;" onsubmit="return confirm('PERINGATAN!\nAnda yakin ingin menghapus akun User ini secara permanen?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px; color: var(--danger); border-color: #f3c1be; background: #fffcfc;">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- MODAL GANTI PASSWORD MELAYANG -->
<div id="password-modal" class="page-loader" style="z-index: 6000;">
    <div class="card" style="width: 100%; max-width: 400px; transform: none; animation: popIn 0.3s ease; background: var(--surface);">
        <h3 style="margin-top: 0;">Reset Password</h3>
        <p class="muted">Masukkan kata sandi baru untuk pengguna <strong id="modal-username" style="color: var(--text);"></strong></p>
        
        <form method="post" style="margin-top: 20px;">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="target_id" id="modal-target-id">
            
            <label class="label">Password Baru</label>
            <div style="position: relative; margin-bottom: 24px;">
                <input type="password" id="modal-password" name="new_password" class="input" style="width: 100%; padding-right: 60px;" minlength="8" required>
                <button type="button" onclick="const p = document.getElementById('modal-password'); if(p.type === 'password') { p.type = 'text'; this.innerHTML = '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'20\' height=\'20\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24\'></path><line x1=\'1\' y1=\'1\' x2=\'23\' y2=\'23\'></line></svg>'; } else { p.type = 'password'; this.innerHTML = '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'20\' height=\'20\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z\'></path><circle cx=\'12\' cy=\'12\' r=\'3\'></circle></svg>'; }" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--primary-dark); cursor: pointer; padding: 4px; display: flex; align-items: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </button>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closePasswordModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Password</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH PENGGUNA BARU -->
<div id="adduser-modal" class="page-loader" style="z-index: 6000;">
    <div class="card" style="width: 100%; max-width: 400px; transform: none; animation: popIn 0.3s ease; background: var(--surface);">
        <h3 style="margin-top: 0;">Tambah Pengguna Baru</h3>
        <p class="muted">Buat akun untuk staf Admin atau Pasien (User).</p>
        
        <form method="post" style="margin-top: 20px;">
            <input type="hidden" name="action" value="add_user">
            
            <label class="label">Username</label>
            <input type="text" name="new_username" class="input" style="margin-bottom: 16px;" placeholder="contoh: budi_pasien" required>
            
            <label class="label">Password (Minimal 8 karakter)</label>
            <input type="password" name="new_password" class="input" style="margin-bottom: 16px;" minlength="8" required>
            
            <label class="label">Hak Akses (Role)</label>
            <select name="new_role" id="new_role_select" class="select" style="margin-bottom: 16px;" onchange="togglePatientSelect()" required>
                <option value="admin">Staf / Admin</option>
                <option value="user" selected>Pasien / Keluarga (User)</option>
            </select>

            <div id="patient_select_group">
                <label class="label">Tautkan ke Rekam Medis (Opsional)</label>
                <select name="patient_id" class="select" style="margin-bottom: 24px;">
                    <option value="">-- Tidak ditautkan --</option>
                    <?php foreach ($all_patients as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= h($p['code']) ?> - <?= h($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeAddUserModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPasswordModal(id, username) {
    document.getElementById('modal-target-id').value = id;
    document.getElementById('modal-username').innerText = username;
    document.getElementById('modal-password').value = '';
    
    // Tampilkan modal dengan efek latar gelap
    document.getElementById('password-modal').classList.add('visible');
}

function closePasswordModal() {
    document.getElementById('password-modal').classList.remove('visible');
}

function openAddUserModal() {
    document.getElementById('adduser-modal').classList.add('visible');
}

function closeAddUserModal() {
    document.getElementById('adduser-modal').classList.remove('visible');
}

function togglePatientSelect() {
    const role = document.getElementById('new_role_select').value;
    const group = document.getElementById('patient_select_group');
    if (role === 'user') {
        group.style.display = 'block';
    } else {
        group.style.display = 'none';
    }
}
togglePatientSelect(); // Jalankan saat inisialisasi
</script>
<?php require 'includes/footer.php'; ?>