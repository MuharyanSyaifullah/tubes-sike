<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'includes/db.php';
require 'includes/functions.php';

$pageTitle = 'Dashboard Monitoring Pasien';
$activePage = 'dashboard';
$summary = getSummary($pdo);
$watchlist = getWatchlist($pdo);

$selectedPatientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : null;
if ($selectedPatientId) {
    $chartPatient = getPatientById($pdo, $selectedPatientId);
} else {
    $chartPatient = $watchlist[0] ?? getPatients($pdo)[0] ?? null;
}

$activities = getActivities($pdo, $chartPatient ? (int)$chartPatient['id'] : null);
$chartData = $chartPatient ? getSensorReadings($pdo, (int)$chartPatient['id']) : [];

require 'includes/header.php';
?>
<div class="topbar">
    <div class="page-title">
        <h2>Dashboard Monitoring</h2>
        <p>Ringkasan perkembangan pasien, laporan terapi, dan watchlist prioritas.</p>
    </div>
</div>

<section class="stats-grid">
    <div class="card"><div class="muted" style="font-weight: 600;">Total Pasien Aktif</div><div class="stat-value"><?= $summary['totalPatients'] ?></div></div>
    <div class="card"><div class="muted" style="font-weight: 600;">Total Laporan Terapi</div><div class="stat-value"><?= $summary['activeReports'] ?></div></div>
    <div class="card"><div class="muted" style="font-weight: 600;">Rata-rata Progress</div><div class="stat-value"><?= $summary['avgProgress'] ?>%</div></div>
</section>

<section class="grid-2 mt-18">
    <div class="card">
        <h3>Grafik Sudut Angkat Kaki <?= $chartPatient ? '- ' . h($chartPatient['name']) : '' ?></h3>
        <p class="muted">Visualisasi perkembangan berdasarkan data sensor pasien.</p>
        <div class="chart-container">
            <canvas id="dashboardChart"></canvas>
        </div>
        <script>
            window.dashboardChartData = {
                labels: <?= json_encode(array_column($chartData, 'session_date')) ?>,
                values: <?= json_encode(array_map('floatval', array_column($chartData, 'angle'))) ?>,
                title: <?= json_encode($chartPatient ? $chartPatient['name'] : 'Tidak ada data') ?>
            };
        </script>
    </div>
    <div class="card">
        <h3>Aktivitas Terbaru <?= $chartPatient ? '- ' . h($chartPatient['name']) : '' ?></h3>
        <?php if (empty($activities)): ?>
            <p class="muted">Belum ada aktivitas untuk pasien ini.</p>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($activities as $item): ?>
                    <li>
                        <strong style="color: var(--primary-dark);"><?= h($item['report_date']) ?></strong><br>
                        <span class="muted"><?= h($item['activity_type']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>

<section class="mt-18">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <h3 style="margin: 0;">Critical Watchlist</h3>
        <div style="flex: 1; min-width: 200px; max-width: 400px; margin-left: auto;">
            <input type="text" id="dashboard-search" class="input" placeholder="Cari nama atau ruangan pasien..." style="padding: 10px 16px; border-radius: 20px;">
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-secondary" onclick="slideWatchlist(-1)" style="padding: 8px 16px; font-size: 16px; border-radius: 12px;">&#10094;</button>
            <button class="btn btn-secondary" onclick="slideWatchlist(1)" style="padding: 8px 16px; font-size: 16px; border-radius: 12px;">&#10095;</button>
        </div>
    </div>
    <div id="watchlist-track" class="slider-track">
        <?php foreach ($watchlist as $patient): ?>
            <?php 
            $isSelected = $chartPatient && $chartPatient['id'] === $patient['id'];
            $cardStyle = 'cursor: pointer; transition: all 0.2s ease;';
            if ($isSelected) {
                $cardStyle .= ' border: 2px solid var(--primary); box-shadow: 0 12px 28px rgba(83, 104, 86, 0.15); transform: translateY(-4px); background: #fdfdfd;';
            }
            ?>
            <div class="card slider-card" style="<?= $cardStyle ?>" onclick="if(!event.target.closest('a')) window.location.href='?patient_id=<?= (int)$patient['id'] ?>'">
                <h4><?= h($patient['name']) ?></h4>
                <p class="muted"><?= h($patient['room']) ?> • <?= h($patient['diagnosis']) ?></p>
                <p class="status <?= h($patient['status']) ?>"><?= h(statusLabel($patient['status'])) ?></p>
                <div class="kv single" style="margin-top: 16px;">
                    <div class="item" style="background: #fdfdfd; padding: 10px;"><strong>Heart Rate</strong><span style="color: #B46C6C; font-weight: bold;"><?= h((string)$patient['heart_rate']) ?> bpm</span></div>
                    <div class="item" style="background: #fdfdfd; padding: 10px;"><strong>SpO2</strong><span style="color: #5C7C8A; font-weight: bold;"><?= h((string)$patient['spo2']) ?>%</span></div>
                    <div class="item"><strong>Progress</strong><span><?= h((string)$patient['progress']) ?>%</span></div>
                </div>
                <div class="footer-actions">
                    <a class="btn btn-secondary" href="patient-detail.php?id=<?= (int)$patient['id'] ?>">Lihat Detail</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        // Logika Pencarian Langsung (Instan) di Watchlist
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('dashboard-search');
            const track = document.getElementById('watchlist-track');
            
            if (searchInput && track) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    const cards = track.querySelectorAll('.slider-card');
                    
                    cards.forEach(card => {
                        const name = card.querySelector('h4').innerText.toLowerCase();
                        const details = card.querySelector('.muted').innerText.toLowerCase();
                        if (name.includes(query) || details.includes(query)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }
        });

        let isSliding = false;

        function slideWatchlist(direction) {
            if (isSliding) return;
            const track = document.getElementById('watchlist-track');
            if (!track || track.children.length < 2) return;
            
            // Cari kartu pertama yang sedang tidak disembunyikan (visible)
            const firstVisibleCard = Array.from(track.children).find(c => c.style.display !== 'none');
            if (!firstVisibleCard) return;

            // Jika semua kartu sudah muat di layar, tidak perlu digeser
            if (track.scrollWidth <= track.clientWidth + 10) return;

            isSliding = true;
            const cardWidth = firstVisibleCard.offsetWidth + 20;
            const isSearching = document.getElementById('dashboard-search').value.trim() !== '';
            track.style.scrollSnapType = 'none'; // Matikan snap sementara agar mulus

            if (direction === 1) { // Kanan
                track.scrollBy({ left: cardWidth, behavior: 'smooth' });
                setTimeout(() => {
                    if (!isSearching) {
                        track.appendChild(track.firstElementChild); // Pindah kartu awal ke ujung
                        track.scrollLeft -= cardWidth; // Sesuaikan scroll secara transparan
                    }
                    track.style.scrollSnapType = 'x mandatory';
                    isSliding = false;
                }, 350);
            } else { // Kiri
                if (!isSearching) {
                    track.prepend(track.lastElementChild); // Pindah kartu ujung ke awal
                    track.scrollLeft += cardWidth;
                }
                
                requestAnimationFrame(() => {
                    track.scrollBy({ left: -cardWidth, behavior: 'smooth' });
                    setTimeout(() => {
                        track.style.scrollSnapType = 'x mandatory';
                        isSliding = false;
                    }, 350);
                });
            }
        }
    </script>
</section>
<?php require 'includes/footer.php'; ?>
