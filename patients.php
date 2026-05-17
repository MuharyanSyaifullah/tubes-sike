<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'includes/db.php';
require 'includes/functions.php';

$pageTitle = 'Daftar Pasien';
$activePage = 'patients';
$search = trim($_GET['search'] ?? '');

// Logika Paginasi (Maksimal 10 baris per halaman)
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$patients = getPatients($pdo, $search, $limit, $offset);
$totalPatients = getTotalPatientsCount($pdo, $search);
$totalPages = ceil($totalPatients / $limit);

require 'includes/header.php';
?>
<div class="topbar">
    <div class="page-title">
        <h2>Daftar Pasien</h2>
        <p>Data pasien tersimpan permanen di database MySQL.</p>
    </div>
    <?php if ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin'): ?>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="print-patients.php?search=<?= urlencode($search) ?>" target="_blank" class="btn btn-secondary" style="box-shadow: var(--shadow); color: #536856; border-color: #C3D0C1; background: #F2F5F0;">Cetak PDF</a>
            <a href="export-csv.php?search=<?= urlencode($search) ?>" class="btn btn-secondary" style="box-shadow: var(--shadow);">Export Excel</a>
            <a href="add-patient.php" class="btn btn-primary">+ Pasien Baru</a>
        </div>
    <?php endif; ?>
</div>

<form id="search-form" class="search-row" method="get" style="background: var(--surface); padding: 12px; border-radius: 20px; border: 1px solid var(--border); box-shadow: var(--shadow);">
    <input id="search-input" name="search" class="input" placeholder="Cari nama pasien, kode rekam medis, atau diagnosis..." value="<?= h($search) ?>" style="border: none; background: #F2F5F0;" autocomplete="off">
    <button class="btn btn-primary" type="submit" style="min-width: 130px; border-radius: 14px; display: none;">Cari</button>
</form>

<div class="card" id="patients-card">
    <div class="table-wrapper" style="border: none; border-radius: 0;">
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

        <!-- Navigasi Tombol Pagination -->
        <?php if ($totalPages > 1): ?>
        <div style="display: flex; justify-content: center; gap: 8px; padding: 20px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?search=<?= urlencode($search) ?>&page=<?= $i ?>" 
                   class="btn <?= ($i === $page) ? 'btn-primary' : 'btn-secondary' ?>" 
                   style="padding: 8px 14px; border-radius: 12px; font-size: 14px;">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php if (empty($patients)): ?>
        <script>
            window.initialPopup = {
                title: 'Pencarian tidak ditemukan',
                message: 'Tidak ada hasil yang cocok. Coba kata kunci lain.',
                type: 'warning'
            };
        </script>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('search-input');
        const patientsCard = document.getElementById('patients-card');
        let debounceTimer;

        if (searchInput && patientsCard) {
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                
                // Beri jeda 0.3 detik agar tidak membebani server saat mengetik cepat
                debounceTimer = setTimeout(() => {
                    const query = this.value;
                    
                    // Tampilkan animasi loading spinner di tabel sebelum data selesai diambil
                    patientsCard.innerHTML = `<div style="padding: 80px 20px; text-align: center; border-radius: 20px;">
                        <div class="spinner" style="margin: 0 auto; border-color: var(--border); border-top-color: var(--primary-dark);"></div>
                        <p class="muted" style="margin-top: 16px;">Mencari data pasien...</p>
                    </div>`;

                    // Ambil data terbaru dari server tanpa me-reload halaman (AJAX)
                    fetch('patients.php?search=' + encodeURIComponent(query))
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newCard = doc.getElementById('patients-card');
                            
                            if (newCard) {
                                patientsCard.innerHTML = newCard.innerHTML;
                            }
                            
                            // Update URL di browser agar riwayat pencarian tersimpan (bisa di-refresh)
                            const newUrl = new URL(window.location.href);
                            if (query) {
                                newUrl.searchParams.set('search', query);
                                newUrl.searchParams.set('page', 1); // Reset ke hal. 1 jika mencari
                            } else {
                                newUrl.searchParams.delete('search');
                                newUrl.searchParams.delete('page');
                            }
                            window.history.replaceState({}, '', newUrl);
                        })
                        .catch(err => console.error('Error:', err));
                }, 300);
            });
        }
    });
</script>
<?php require 'includes/footer.php'; ?>
