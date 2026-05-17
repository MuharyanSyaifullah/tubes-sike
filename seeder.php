<?php
require 'includes/db.php';

echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
echo "<h2>Membuat Data 100 Pasien & Sensor secara Otomatis...</h2>";

$firstNames = ['Budi', 'Siti', 'Ahmad', 'Sutrisno', 'Dewi', 'Haryanto', 'Ratna', 'Aminah', 'Joko', 'Rini', 'Eko', 'Sri', 'Agus', 'Yanti', 'Bambang', 'Wahyuni', 'Widodo', 'Endang', 'Supri', 'Ningsih', 'Andi', 'Rina', 'Hendra', 'Maya', 'Doni', 'Sari', 'Rizal', 'Fitri', 'Dedi', 'Lia', 'Rahmat', 'Nina', 'Fajar', 'Tari', 'Arif', 'Dina', 'Iwan', 'Rika', 'Surya', 'Anita'];
$lastNames = ['Santoso', 'Rahma', 'Fauzi', 'Kurniawan', 'Lestari', 'Ningsih', 'Pratama', 'Wijaya', 'Larasati', 'Saputra', 'Setiawan', 'Hidayat', 'Gunawan', 'Wibowo', 'Purnama', 'Putra', 'Putri', 'Sari', 'Utami', 'Kusuma', 'Siregar', 'Wulandari', 'Haryanti', 'Nugroho', 'Baskoro'];
$addresses = ['Bandar Lampung', 'Metro', 'Pringsewu', 'Pesawaran', 'Lampung Selatan', 'Lampung Tengah', 'Lampung Timur', 'Kotabumi', 'Kalianda', 'Perkotaan (Urban)', 'Pedesaan (Rural)'];

$diagnoses = [
    ['Hemiparesis Kiri', 'Neurological'],
    ['Hemiparesis Kanan', 'Neurological'],
    ['Cerebral Palsy', 'Mobility'],
    ['Stroke / Pasca Stroke', 'Neurological'],
    ['Spinal Cord Injury', 'Mobility'],
    ['Traumatic Brain Injury', 'Cognitive'],
    ['Multiple Sclerosis', 'Neurological'],
    ['Parkinson Disease', 'Neurological']
];

$clinicians = ['Dr. Sarah Wijaya', 'Dr. Aris Pratama', 'Dr. Dinda Larasati', 'Dr. Budi Santoso', 'Dr. Hendra Gunawan'];
$bloodTypes = ['A+', 'B+', 'AB+', 'O+', 'A-', 'B-', 'O-'];
$statuses = ['stable', 'monitoring', 'high-risk', 'urgent'];
$rooms = ['Rehab-01', 'Rehab-02', 'Rehab-03', 'Rehab-04', 'Rehab-05', 'Rehab-06', 'Rehab-07', 'Rehab-08'];
$phases = ['Fase I', 'Fase II', 'Fase III'];

try {
    $pdo->beginTransaction();

    // Membersihkan data pasien yang lama agar kita punya 100 data yang benar-benar bersih dan rapi
    $pdo->exec("DELETE FROM sensor_readings");
    $pdo->exec("DELETE FROM reports");
    $pdo->exec("UPDATE users SET patient_id = NULL");
    $pdo->exec("DELETE FROM patients");

    // Memastikan akun superadmin, admin, dan user default selalu ada dan tidak hilang
    $defaultPass = password_hash('password', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO users (username, password, role) VALUES 
        ('superadmin', '$defaultPass', 'super_admin'),
        ('admin', '$defaultPass', 'admin'),
        ('user', '$defaultPass', 'user')
    ");

    for ($i = 1; $i <= 100; $i++) {
        $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
        $code = 'RM-' . str_pad($i, 4, '0', STR_PAD_LEFT); // Menghasilkan RM-0001 hingga RM-0100
        $age = rand(18, 85);
        $gender = rand(0, 1) ? 'L' : 'P';
        $address = $addresses[array_rand($addresses)];
        $diag = $diagnoses[array_rand($diagnoses)];
        $status = $statuses[array_rand($statuses)];
        $room = $rooms[array_rand($rooms)];
        $blood_type = $bloodTypes[array_rand($bloodTypes)];
        $admission_date = date('Y-m-d', strtotime('-' . rand(20, 150) . ' days'));
        $clinician = $clinicians[array_rand($clinicians)];
        $progress = rand(10, 95);
        $phase = $phases[array_rand($phases)];
        $latest_assessment = date('Y-m-d H:i:s', strtotime('-' . rand(0, 5) . ' days'));
        
        // Random Vital Signs
        $hr = rand(60, 115);
        $spo2 = rand(92, 100);
        $bp = rand(110, 160) . '/' . rand(70, 100);

        // Simpan Biodata Pasien
        $stmt = $pdo->prepare("INSERT INTO patients (code, name, age, gender, address, diagnosis, disability_type, status, room, blood_type, admission_date, clinician, progress, phase, latest_assessment, heart_rate, spo2, blood_pressure) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $name, $age, $gender, $address, $diag[0], $diag[1], $status, $room, $blood_type, $admission_date, $clinician, $progress, $phase, $latest_assessment, $hr, $spo2, $bp]);
        
        $patientId = $pdo->lastInsertId();

        // Membuat riwayat laporan & grafik sensor untuk masing-masing pasien (3-7 hari ke belakang)
        $numReports = rand(3, 7);
        $currentAngle = rand(10, 25);
        
        for ($j = $numReports; $j >= 0; $j--) {
            $rDate = date('Y-m-d', strtotime("-$j days", strtotime($latest_assessment)));
            
            $act = ['Fisioterapi', 'Terapi Okupasi', 'Terapi Wicara'][rand(0, 2)];
            $nar = "Sesi terapi berjalan lancar. Pasien menunjukkan tingkat usaha yang baik dan perkembangan motorik mulai terlihat secara bertahap.";
            
            // Simpan Laporan
            $pdo->prepare("INSERT INTO reports (patient_id, report_date, activity_type, narrative, mobility, communication, social_skills) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$patientId, $rDate, $act, $nar, rand(2, 5), rand(2, 5), rand(2, 5)]);
            
            // Simpan Sensor Gyroscope
            $currentAngle += rand(1, 5); // Sudut angkat kaki perlahan naik seiring hari
            $pdo->prepare("INSERT INTO sensor_readings (patient_id, session_date, angle, gyro_x, gyro_y, gyro_z) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute([$patientId, $rDate, $currentAngle, rand(50, 150)/100, rand(30, 100)/100, rand(20, 80)/100]);
        }
    }

    $pdo->commit();
    echo "<h3 style='color: #536856;'>Berhasil!</h3>";
    echo "<p>100 Data Pasien beserta riwayat Laporan dan Grafik Sensor Gyroscope-nya telah disuntikkan ke dalam MySQL!</p>";
    echo "<a href='index.php' style='display: inline-block; padding: 12px 24px; background: #78907C; color: white; border-radius: 12px; text-decoration: none; font-weight: bold; margin-top: 20px;'>Kembali ke Dashboard SIK</a>";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<h3 style='color: #B46C6C;'>Gagal mengeksekusi Seeder:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
echo "</div>";