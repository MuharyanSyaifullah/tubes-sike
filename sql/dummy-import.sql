USE sik_rehabilitasi;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Password default untuk semua akun adalah: password
INSERT INTO users (username, password, role) VALUES
    ('superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin'),
    ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
    ('user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

INSERT INTO patients (id, code, name, age, gender, address, diagnosis, disability_type, status, room, blood_type, admission_date, clinician, progress, phase, latest_assessment, heart_rate, spo2, blood_pressure) VALUES
    (1, 'PD-001', 'Budi Santoso', 52, 'L', 'Bandar Lampung', 'Hemiparesis Kiri', 'Neurological', 'stable', 'Rehab-01', 'O+', '2025-04-10', 'Dr. Sarah Wijaya', 74, 'Fase III', '2025-04-14 09:30:00', 84, 97, '120/80'),
    (2, 'PD-002', 'Siti Rahma', 47, 'P', 'Metro', 'Cerebral Palsy', 'Mobility', 'monitoring', 'Rehab-03', 'A+', '2025-04-09', 'Dr. Aris Pratama', 58, 'Fase II', '2025-04-14 08:15:00', 90, 95, '118/78'),
    (3, 'PD-003', 'Ahmad Fauzi', 60, 'L', 'Pringsewu', 'Hemiparesis Kanan', 'Neurological', 'high-risk', 'Rehab-02', 'B+', '2025-04-05', 'Dr. Dinda Larasati', 45, 'Fase I', '2025-04-14 10:10:00', 112, 92, '140/90'),
    (4, 'ST-9046', 'Sutrisno Kurniawan', 67, 'L', 'Perkotaan (Urban)', 'Stroke / Pasca Stroke', 'Neurological', 'high-risk', 'Rehab-04', 'O+', '2025-05-01', 'Dr. Sarah Wijaya', 30, 'Fase I', '2025-05-16 08:00:00', 95, 96, '150/95'),
    (5, 'ST-5167', 'Dewi Lestari', 61, 'P', 'Pedesaan (Rural)', 'Stroke / Pasca Stroke', 'Neurological', 'monitoring', 'Rehab-05', 'A+', '2025-04-20', 'Dr. Aris Pratama', 55, 'Fase II', '2025-05-16 09:00:00', 88, 98, '130/85'),
    (6, 'ST-3111', 'Haryanto', 80, 'L', 'Pedesaan (Rural)', 'Stroke / Pasca Stroke', 'Neurological', 'urgent', 'Rehab-06', 'B+', '2025-05-10', 'Dr. Sarah Wijaya', 15, 'Fase I', '2025-05-16 07:30:00', 105, 93, '160/100'),
    (7, 'ST-6018', 'Ratna Ningsih', 49, 'P', 'Perkotaan (Urban)', 'Stroke / Pasca Stroke', 'Neurological', 'stable', 'Rehab-07', 'AB+', '2025-03-15', 'Dr. Dinda Larasati', 80, 'Fase III', '2025-05-16 10:00:00', 78, 99, '120/80'),
    (8, 'ST-1665', 'Aminah', 79, 'P', 'Perkotaan (Urban)', 'Stroke / Pasca Stroke', 'Neurological', 'high-risk', 'Rehab-08', 'O+', '2025-05-05', 'Dr. Aris Pratama', 25, 'Fase I', '2025-05-16 11:00:00', 98, 94, '155/95');

INSERT INTO reports (patient_id, report_date, activity_type, narrative, mobility, communication, social_skills) VALUES
    (1, '2025-04-14', 'Fisioterapi', 'Pasien menunjukkan peningkatan sudut angkat kaki dan keseimbangan saat latihan berdiri.', 4, 3, 3),
    (1, '2025-04-13', 'Fisioterapi', 'Latihan berjalan dengan bantuan tongkat, respon baik tanpa keluhan nyeri berlebih.', 3, 3, 2),
    (2, '2025-04-14', 'Terapi Okupasi', 'Pasien mampu mengikuti instruksi dasar dan koordinasi tangan mulai membaik.', 3, 4, 3),
    (3, '2025-04-14', 'Fisioterapi', 'Masih membutuhkan pendampingan penuh, progres belum stabil.', 2, 2, 2),
    (4, '2025-05-12', 'Fisioterapi', 'Latihan mobilitas awal pasca serangan stroke. Pasien masih kesulitan menggerakkan sisi kanan tubuh.', 2, 3, 2),
    (4, '2025-05-14', 'Terapi Wicara', 'Latihan mengucapkan huruf vokal. Otot rahang masih kaku namun mulai ada respon.', 2, 2, 2),
    (4, '2025-05-16', 'Fisioterapi', 'Penggunaan sensor gyro pada kaki kanan. Sudut angkat kaki meningkat perlahan.', 3, 3, 3);

INSERT INTO sensor_readings (patient_id, session_date, angle, gyro_x, gyro_y, gyro_z) VALUES
    (1, '2025-04-08', 18.00, 1.20, 0.80, 0.60),
    (1, '2025-04-09', 24.00, 1.40, 0.90, 0.70),
    (1, '2025-04-10', 30.00, 1.60, 1.10, 0.70),
    (1, '2025-04-11', 40.00, 1.90, 1.20, 0.90),
    (1, '2025-04-12', 52.00, 2.00, 1.50, 1.00),
    (1, '2025-04-13', 61.00, 2.10, 1.60, 1.10),
    (1, '2025-04-14', 70.00, 2.30, 1.80, 1.20),
    (2, '2025-04-10', 22.00, 1.10, 0.90, 0.50),
    (2, '2025-04-11', 28.00, 1.30, 1.00, 0.60),
    (2, '2025-04-12', 31.00, 1.40, 1.10, 0.60),
    (2, '2025-04-13', 35.00, 1.60, 1.20, 0.70),
    (2, '2025-04-14', 41.00, 1.70, 1.30, 0.80),
    (3, '2025-04-10', 20.00, 1.00, 0.70, 0.50),
    (3, '2025-04-11', 22.00, 1.10, 0.80, 0.50),
    (3, '2025-04-12', 25.00, 1.20, 0.80, 0.60),
    (3, '2025-04-13', 30.00, 1.30, 0.90, 0.70),
    (3, '2025-04-14', 34.00, 1.50, 1.00, 0.80),
    (4, '2025-05-10', 12.00, 0.50, 0.30, 0.20),
    (4, '2025-05-12', 15.00, 0.60, 0.40, 0.20),
    (4, '2025-05-14', 18.00, 0.80, 0.40, 0.30),
    (4, '2025-05-15', 22.00, 0.90, 0.50, 0.40),
    (4, '2025-05-16', 26.00, 1.10, 0.60, 0.50);
