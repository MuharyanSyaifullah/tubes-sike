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
    <div>
        <a href="add-patient.php" class="btn btn-primary">+ Pasien Baru</a>
    </div>
</div>

<form id="search-form" class="search-row" method="get" style="background: var(--surface); padding: 12px; border-radius: 20px; border: 1px solid var(--border); box-shadow: var(--shadow);">
    <input id="search-input" name="search" class="input" placeholder="Cari nama pasien, kode rekam medis, atau diagnosis..." value="<?= h($search) ?>" style="border: none; background: #f4fbf6;" autocomplete="off">
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
    </div>
    <?php if (empty($patients)): ?>
        <div class="alert error">Tidak ada pasien yang cocok dengan kata kunci pencarian.</div>
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
                            } else {
                                newUrl.searchParams.delete('search');
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
