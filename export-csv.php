<?php
session_start();
// Hanya admin dan super_admin yang bisa meng-export data
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'user') {
    die('Akses Ditolak');
}

require 'includes/db.php';
require 'includes/functions.php';

$search = trim($_GET['search'] ?? '');

// Ambil semua data tanpa limit paginasi agar semuanya ter-export
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE name LIKE :q OR code LIKE :q OR diagnosis LIKE :q ORDER BY id DESC");
    $stmt->execute(['q' => "%$search%"]);
    $patients = $stmt->fetchAll();
} else {
    $patients = $pdo->query("SELECT * FROM patients ORDER BY id DESC")->fetchAll();
}

// Set header agar browser mengenali ini sebagai file unduhan CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Data_Pasien_SIK_Rehab_' . date('Ymd_His') . '.csv"');

$output = fopen('php://output', 'w');

// Tulis baris pertama (Judul Kolom)
fputcsv($output, ['ID', 'Kode Rekam Medis', 'Nama Lengkap', 'Umur', 'Gender', 'Alamat', 'Diagnosis', 'Tipe Disabilitas', 'Status', 'Ruangan', 'Gol. Darah', 'Tanggal Masuk', 'Dokter PJ', 'Progress', 'Fase', 'Update Terakhir']);

// Tulis baris-baris data pasien
foreach ($patients as $row) {
    fputcsv($output, [
        $row['id'], $row['code'], $row['name'], $row['age'], $row['gender'], $row['address'], $row['diagnosis'], $row['disability_type'], statusLabel($row['status']), $row['room'], $row['blood_type'], $row['admission_date'], $row['clinician'], $row['progress'] . '%', $row['phase'], $row['latest_assessment']
    ]);
}

fclose($output);
exit;