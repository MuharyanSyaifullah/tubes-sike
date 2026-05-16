document.addEventListener('DOMContentLoaded', () => {
    // 1. --- LOGIKA PAGE LOADER ---
    const loader = document.getElementById('page-loader');
    if (loader) {
        // Munculkan loader saat klik link (kecuali link '#' atau target blank)
        document.querySelectorAll('a:not([href^="#"]):not([target="_blank"])').forEach(link => {
            link.addEventListener('click', () => {
                loader.classList.add('visible');
            });
        });
        // Hilangkan loader saat kembali ke halaman (back button browser cache)
        window.addEventListener('pageshow', () => {
            loader.classList.remove('visible');
        });

        // Munculkan loader saat form dikirim (Kecuali form pencarian AJAX)
        document.querySelectorAll('form:not(#search-form)').forEach(form => {
            form.addEventListener('submit', () => {
                loader.classList.add('visible');
            });
        });
    }

    // --- LOGIKA SMART STICKY TOPBAR ---
    const globalTopbar = document.getElementById('global-topbar');
    if (globalTopbar) {
        let lastScroll = window.pageYOffset || document.documentElement.scrollTop;
        window.addEventListener('scroll', () => {
            let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
            if (currentScroll > lastScroll && currentScroll > 20) {
                // Scroll ke bawah: Tambahkan class transparent (background hilang)
                globalTopbar.classList.add('transparent');
            } else {
                // Scroll ke atas: Hapus class transparent (background muncul)
                globalTopbar.classList.remove('transparent');
            }
            lastScroll = currentScroll <= 0 ? 0 : currentScroll;
        });
    }

    // --- LOGIKA MOBILE SIDEBAR TOGGLE ---
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.querySelector('.sidebar');
    if (mobileMenuBtn && sidebar) {
        mobileMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('show');
        });
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });
    }

    // 2. --- LOGIKA POPUP TOAST ---
    const popup = document.getElementById('popup-toast');
    const popupClose = document.querySelector('.popup-close');
    
    window.showPopup = function(title, message, type = 'success') {
        if(!popup) return;
        
        document.getElementById('popup-title').innerText = title;
        document.getElementById('popup-message').innerText = message;
        
        const icon = popup.querySelector('.popup-icon');
        if(type === 'warning' || type === 'error') {
            icon.innerHTML = '!';
            icon.style.background = 'var(--danger)';
            icon.style.color = 'white';
        } else {
            icon.innerHTML = '✓';
            icon.style.background = 'var(--primary-light)';
            icon.style.color = 'var(--primary-dark)';
        }

        popup.classList.add('visible');
        
        // Otomatis hilang setelah 4.5 detik
        setTimeout(() => popup.classList.remove('visible'), 4500);
    }

    if (popupClose) {
        popupClose.addEventListener('click', () => popup.classList.remove('visible'));
    }

    // Trigger popup bawaan jika dikirim melalui PHP (contoh dari form atau halaman pasien)
    if (window.initialPopup) {
        setTimeout(() => {
            showPopup(window.initialPopup.title, window.initialPopup.message, window.initialPopup.type);
        }, 400); // Beri jeda 0.4 detik agar munculnya harmonis dengan animasi halaman
    }

    // 3. --- INISIALISASI GRAFIK CHART.JS ---
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
        Chart.defaults.color = "#5c6f62";

        // A. Grafik Dashboard (Line)
        if (document.getElementById('dashboardChart') && window.dashboardChartData) {
            new Chart(document.getElementById('dashboardChart'), {
                type: 'line',
                data: {
                    labels: window.dashboardChartData.labels,
                    datasets: [{
                        label: 'Sudut Angkat Kaki (Derajat)',
                        data: window.dashboardChartData.values,
                        borderColor: '#4a7d5d',
                        backgroundColor: 'rgba(74, 125, 93, 0.1)',
                        borderWidth: 3, tension: 0.4, fill: true, pointBackgroundColor: '#355c46'
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        }

        // B. Grafik Detail Pasien (Line & Radar)
        if (document.getElementById('detailLineChart') && window.detailLineChartData) {
            new Chart(document.getElementById('detailLineChart'), {
                type: 'line',
                data: {
                    labels: window.detailLineChartData.labels,
                    datasets: [{
                        label: 'Perkembangan Sudut Angkat Kaki',
                        data: window.detailLineChartData.values,
                        borderColor: '#2196F3', borderWidth: 3, tension: 0.4, pointBackgroundColor: '#1976D2'
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
        if (document.getElementById('detailRadarChart') && window.detailRadarChartData) {
            const d = window.detailRadarChartData;
            new Chart(document.getElementById('detailRadarChart'), {
                type: 'radar',
                data: {
                    labels: ['Kognitif', 'Motorik', 'Bicara', 'Memori'],
                    datasets: [{ label: 'Skor Klinis', data: [d.kognitif, d.motorik, d.bicara, d.memori], backgroundColor: 'rgba(74, 125, 93, 0.25)', borderColor: '#4a7d5d' }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { r: { min: 0, max: 100 } } }
            });
        }
    }
});