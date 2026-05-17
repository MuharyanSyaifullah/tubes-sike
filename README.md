<div align="center">
  <h1>🏥 SIK Rehabilitasi</h1>
  <p><strong>Sistem Informasi Kesehatan & Monitoring Pasien Disabilitas Berbasis IoT</strong></p>
  
  [![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
  [![MySQL](https://img.shields.io/badge/MySQL-Supported-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
  [![Chart.js](https://img.shields.io/badge/Chart.js-Visualizations-FF6384?style=flat-square&logo=chartdotjs&logoColor=white)](https://www.chartjs.org/)
  [![License](https://img.shields.io/badge/License-MIT-success?style=flat-square)](#)
</div>

---

## 📖 Tentang Proyek
**SIK Rehabilitasi** adalah aplikasi rekam medis berbasis web yang dirancang khusus untuk memantau perkembangan fisik dan neurologis pasien disabilitas (seperti pasca-stroke, cerebral palsy, dll). Aplikasi ini dilengkapi dengan integrasi data pembacaan sensor (*Gyroscope*) untuk memvisualisasikan sudut angkat kaki pasien selama sesi fisioterapi secara *real-time*.

Proyek ini dikembangkan sebagai Tugas Besar (Tubes) dengan menerapkan konsep keamanan yang baik (*Password Hashing*, *Prepared Statements* / Anti SQL-Injection) serta desain antarmuka modern (*Glassmorphism*, *Responsive Mobile*).

## ✨ Fitur Utama

🔐 **Keamanan & Autentikasi**
- **Role-Based Access Control (RBAC):** Memiliki 3 level pengguna (Super Admin, Admin, dan User/Read-Only).
- **Enkripsi Kata Sandi:** Menggunakan algoritma BCRYPT (`password_hash`).
- **Manajemen Akun:** Profil pengguna, Ganti Password Mandiri, dan Panel Super Admin.

📊 **Dashboard & Monitoring (IoT)**
- **Visualisasi Data Interaktif:** Grafik perkembangan sudut kaki dan profil neurologis menggunakan **Chart.js**.
- **Animasi UI & Feedback:** Popup notifikasi toast dan loading spinner untuk pengalaman pengguna lebih responsif.
- **Critical Watchlist:** *Slider* daftar pasien berisiko tinggi yang dilengkapi dengan *Real-time Search* (AJAX).
- **Status Medis:** Pemantauan Tanda Vital (*Heart Rate*, *SpO2*, *Blood Pressure*).

📂 **Manajemen Rekam Medis (CRUD)**
- **Data Pasien & Laporan Terapi:** Tambah, Edit, dan Hapus riwayat rekam medis secara komprehensif.
- **Database Seeder:** Menghasilkan 100 data pasien fiktif secara otomatis untuk keperluan *testing* dan demo.
- **Smart Pagination:** Menampilkan 10 data pasien per halaman tanpa membebani *server*.

🖨️ **Export & Reporting**
- **Cetak PDF:** Halaman khusus *print-friendly* untuk mencetak rekam medis satuan.
- **Export to CSV/Excel:** Mengunduh seluruh data pasien sekaligus untuk analisis lebih lanjut.

## 💻 Teknologi yang Digunakan
- **Backend:** PHP 8+ (Native / Procedural dengan pola terstruktur)
- **Database:** MySQL / MariaDB (via PDO)
- **Frontend:** HTML5, CSS3 (Custom Variables, Flexbox, Grid), Vanilla JavaScript (AJAX/Fetch API)
- **Library Eksternal:** Chart.js (untuk visualisasi grafik)

---

## 🚀 Panduan Instalasi (Lokal)

Ikuti langkah-langkah berikut untuk menjalankan aplikasi ini di komputer Anda menggunakan **XAMPP**:

### 1. Kloning Repositori
Buka terminal atau Git Bash di folder `htdocs` XAMPP Anda, lalu jalankan:
```bash
git clone https://github.com/MuharyanSyaifullah/tubes-sike.git sik_rehab_php
cd sik_rehab_php
```

### 2. Persiapan Database
1. Buka aplikasi **XAMPP Control Panel** dan jalankan modul **Apache** serta **MySQL**.
2. Buka *browser* dan akses http://localhost/phpmyadmin.
3. Buat *database* baru dengan nama: `sik_rehabilitasi`
4. Klik tab **Import**, lalu unggah file `sql/dummy-import.sql` yang terdapat di dalam folder proyek ini.
5. Klik **Go** / **Kirim**.

### 3. Akses Aplikasi
Buka *browser* Anda dan kunjungi tautan berikut:
👉 **http://localhost/sik_rehab_php**

---

## 🔑 Akun Default (Untuk Pengujian)

Gunakan kredensial berikut untuk masuk ke dalam sistem:

| Role | Username | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `superadmin` | `password` | Akses penuh (Termasuk hapus akun Admin) |
| **Admin** | `admin` | `password` | Kelola pasien, input laporan terapi, cetak/export |
| **User (Tamu)** | `user` | `password` | Hanya-baca (*Read-only*), lihat *dashboard* |

> **Catatan:** Anda bisa membuat lebih banyak pasien fiktif secara otomatis dengan menjalankan *script* http://localhost/sik_rehab_php/seeder.php.

---

## 📸 Tangkapan Layar (Screenshots)
*(Catatan: Anda bisa mengunggah gambar ke repository Anda dan mengganti link di bawah ini nantinya)*

<details>
  <summary>Tampilkan Tangkapan Layar</summary>
  
  - **Halaman Login & Register**
  - **Dashboard & Grafik Sensor IoT**
  - **Tabel Daftar Pasien (Pagination & Export Excel)**
  - **Cetak Laporan PDF**
  
</details>

---

## 👨‍💻 Pengembang
- **Muharyan Syaifullah** - *Mahasiswa / Pengembang Utama*

Dibuat dengan ❤️ untuk memenuhi Tugas Besar Sistem Informasi Kesehatan.