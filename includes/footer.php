            <footer class="app-footer">
                <div class="footer-content">
                    <div><strong>SIK Rehabilitasi</strong> © <?= date('Y') ?> — Sistem monitoring pasien.</div>
                    <div class="footer-links">
                        <a href="#">Bantuan</a>
                        <a href="#">Kebijakan Privasi</a>
                    </div>
                </div>
            </footer>
        </main>
    </div>

    <div id="page-loader" class="page-loader" aria-hidden="true">
        <div class="loader-box">
            <div class="spinner"></div>
            <p>Memuat...</p>
        </div>
    </div>

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
