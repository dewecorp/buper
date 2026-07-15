-- ============================================================
-- BUPER JEPARA - Database Schema & Seed Data
-- Sistem Reservasi Bumi Perkemahan
-- ============================================================

CREATE DATABASE IF NOT EXISTS `buper_jepara` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `buper_jepara`;

-- ============================================================
-- TABEL: users
-- ============================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `role` ENUM('admin','pengelola') NOT NULL DEFAULT 'pengelola',
  `foto` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL: profil
-- ============================================================
DROP TABLE IF EXISTS `profil`;
CREATE TABLE `profil` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_buper` VARCHAR(150) NOT NULL,
  `deskripsi` TEXT,
  `sejarah` TEXT,
  `visi` TEXT,
  `misi` TEXT,
  `alamat` TEXT,
  `telepon` VARCHAR(20),
  `email` VARCHAR(100),
  `foto` VARCHAR(255),
  `latitude` VARCHAR(50),
  `longitude` VARCHAR(50),
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL: pengelola
-- ============================================================
DROP TABLE IF EXISTS `pengelola`;
CREATE TABLE `pengelola` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `jabatan` VARCHAR(100) NOT NULL,
  `foto` VARCHAR(255),
  `urutan` INT DEFAULT 0,
  `status` ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL: fasilitas
-- ============================================================
DROP TABLE IF EXISTS `fasilitas`;
CREATE TABLE `fasilitas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_fasilitas` VARCHAR(100) NOT NULL,
  `deskripsi` TEXT,
  `icon` VARCHAR(50),
  `gambar` VARCHAR(255),
  `status` ENUM('tersedia','tidak_tersedia') NOT NULL DEFAULT 'tersedia',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL: biaya
-- ============================================================
DROP TABLE IF EXISTS `biaya`;
CREATE TABLE `biaya` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_biaya` VARCHAR(100) NOT NULL,
  `deskripsi` TEXT,
  `harga` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `satuan` VARCHAR(50) NOT NULL,
  `keterangan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL: izin_penggunaan
-- ============================================================
DROP TABLE IF EXISTS `izin_penggunaan`;
CREATE TABLE `izin_penggunaan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_peminjam` VARCHAR(100) NOT NULL,
  `organisasi` VARCHAR(150),
  `telepon` VARCHAR(20),
  `email` VARCHAR(100),
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `jumlah_peserta` INT NOT NULL DEFAULT 0,
  `keperluan` TEXT,
  `status` ENUM('pending','disetujui','ditolak','selesai') NOT NULL DEFAULT 'pending',
  `catatan_admin` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL: pengaturan
-- ============================================================
DROP TABLE IF EXISTS `pengaturan`;
CREATE TABLE `pengaturan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_pengaturan` VARCHAR(100) NOT NULL UNIQUE,
  `nilai` TEXT,
  `keterangan` VARCHAR(255),
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA: users
-- admin / admin123
-- pengelola / pengelola123
-- ============================================================
INSERT INTO `users` (`username`, `password`, `nama_lengkap`, `role`) VALUES
('admin', '$2y$12$HgMdqMO8xH0L0yAe7q5BF.sAFzQLCcqeMvmYotsBLmbQ6zy8/t7rS', 'Administrator', 'admin'),
('pengelola', '$2y$12$bv7.9gtLolw58Viux.I9DuopcYZRBhMF6WovuMsQYcbWezopuRF92', 'Pengelola Buper', 'pengelola');

-- ============================================================
-- SEED DATA: profil
-- ============================================================
INSERT INTO `profil` (`nama_buper`, `deskripsi`, `sejarah`, `visi`, `misi`, `alamat`, `telepon`, `email`, `latitude`, `longitude`) VALUES
('Bumi Perkemahan Kwartir Cabang Jepara',
'Bumi Perkemahan Kwartir Cabang Jepara merupakan kawasan perkemahan yang terletak di wilayah Kabupaten Jepara, Jawa Tengah. Tempat ini menyediakan fasilitas lengkap untuk kegiatan perkemahan, pelatihan, dan berbagai aktivitas outdoor lainnya bagi Pramuka maupun masyarakat umum.',
'Bumi Perkemahan Kwartir Cabang Jepara didirikan pada tahun 1980-an sebagai bagian dari upaya Kwarda Jateng untuk menyediakan wahana pendidikan kepramukaan di wilayah Jepara. Sejak saat itu, tempat ini telah menjadi pusat kegiatan kepramukaan dan rekreasi alam bagi masyarakat Jepara dan sekitarnya.',
'Menjadi pusat perkemahan dan pendidikan alam unggulan yang berwawasan lingkungan dan berkarakter kepramukaan.',
'Menyelenggarakan kegiatan perkemahan yang berkualitas, melestarikan lingkungan alam, membina karakter generasi muda melalui kegiatan kepramukaan, serta memberikan pelayanan terbaik kepada seluruh pengunjung.',
'Jl. Raya Donorojo, Kec. Donorojo, Kabupaten Jepara, Jawa Tengah 59455',
'081234567890',
'info@buperjepara.id',
'-6.5453',
'110.9543');

-- ============================================================
-- SEED DATA: pengelola
-- ============================================================
INSERT INTO `pengelola` (`nama`, `jabatan`, `urutan`, `status`) VALUES
('Budi Santoso', 'Kepala Pengelola', 1, 'aktif'),
('Siti Aminah', 'Sekretaris', 2, 'aktif'),
('Agus Pratama', 'Bendahara', 3, 'aktif');

-- ============================================================
-- SEED DATA: fasilitas
-- ============================================================
INSERT INTO `fasilitas` (`nama_fasilitas`, `deskripsi`, `icon`, `status`) VALUES
('Aula Utama', 'Aula besar berkapasitas 200 orang, cocok untuk rapat, seminar, dan kegiatan indoor lainnya.', 'bi-building', 'tersedia'),
('Area Kemah', 'Luas area perkemahan mencapai 3 hektar dengan kondisi tanah datar dan teduh, mampu menampung hingga 500 peserta.', 'bi-tent', 'tersedia'),
('Dapur Umum', 'Dapur umum lengkap dengan peralatan masak untuk kegiatan memasak bersama.', 'bi-fire', 'tersedia'),
('Toilet/MCK', 'Fasilitas toilet dan kamar mandi yang bersih dan terawat, tersedia air bersih 24 jam.', 'bi-droplet', 'tersedia'),
('Mushola', 'Mushola nyaman untuk kegiatan ibadah, dilengkapi sajadah dan mukena.', 'bi-moon', 'tersedia');

-- ============================================================
-- SEED DATA: biaya
-- ============================================================
INSERT INTO `biaya` (`nama_biaya`, `deskripsi`, `harga`, `satuan`, `keterangan`) VALUES
('Sewa Area Kemah', 'Biaya penyewaan area perkemahan per malam.', 1500000.00, 'per malam', 'Sudah termasuk area perkemahan dan akses air bersih.'),
('Sewa Aula Utama', 'Biaya penyewaan aula utama untuk kegiatan indoor.', 2000000.00, 'per hari', 'Kapasitas maksimal 200 orang, sudah termasuk kursi dan meja.'),
('Biaya Api Unggun', 'Paket kegiatan api unggun lengkap dengan kayu bakar dan perlengkapan.', 500000.00, 'per kali', 'Termasuk kayu bakar, tata layak, dan area khusus.'),
('Tiket Masuk Perorangan', 'Tiket masuk untuk pengunjung umum yang tidak menginap.', 10000.00, 'per orang', 'Berlaku untuk satu hari kunjungan.');

-- ============================================================
-- SEED DATA: pengaturan
-- ============================================================
INSERT INTO `pengaturan` (`nama_pengaturan`, `nilai`, `keterangan`) VALUES
('nama_website', 'Buper Jepara', 'Nama website yang ditampilkan di header'),
('tagline', 'Bumi Perkemahan Kwartir Cabang Jepara', 'Tagline website'),
('jam_buka', '08:00 - 17:00', 'Jam operasional buper'),
('kapasitas_max', '500', 'Kapasitas maksimal pengunjung'),
('email_admin', 'admin@buperjepara.id', 'Email admin untuk kontak'),
('telepon_admin', '081234567890', 'Nomor telepon admin'),
('alamat_buper', 'Jl. Raya Donorojo, Kec. Donorojo, Kabupaten Jepara, Jawa Tengah 59455', 'Alamat lengkap buper'),
('tentangSingkat', 'Bumi Perkemahan Kwartir Cabang Jepara menyediakan fasilitas perkemahan terbaik untuk kegiatan Pramuka dan rekreasi alam.', 'Deskripsi singkat untuk footer'),
('logo', '', 'Logo website (upload gambar)'),
('ketua_kwarcab', '', 'Nama Ketua Kwartir Cabang Jepara (untuk cetak bukti izin)');
