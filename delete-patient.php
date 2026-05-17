<?php
session_start();
// Hanya super_admin dan admin yang boleh menghapus
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'user') {
    die('Akses Ditolak');
}

require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Mencegah penghapusan via URL langsung (GET)
    die('Metode tidak diizinkan.');
}

$id = (int)($_POST['id'] ?? 0);

if ($id) {
    try {
        $pdo->beginTransaction();
        
        // Hapus data terkait terlebih dahulu (Laporan & Sensor)
        $stmt = $pdo->prepare("DELETE FROM sensor_readings WHERE patient_id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM reports WHERE patient_id = ?");
        $stmt->execute([$id]);
        
        // Putuskan tautan akun user dengan rekam medis yang akan dihapus
        $stmt = $pdo->prepare("UPDATE users SET patient_id = NULL WHERE patient_id = ?");
        $stmt->execute([$id]);

        // Terakhir, hapus biodata pasien
        $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
    }
}

// Kembali ke halaman daftar pasien
header("Location: patients.php");
exit;