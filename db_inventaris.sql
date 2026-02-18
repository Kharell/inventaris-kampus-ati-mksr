-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 18, 2026 at 08:31 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_inventaris`
--

-- --------------------------------------------------------

--
-- Table structure for table `bahan_praktek`
--

CREATE TABLE `bahan_praktek` (
  `id_praktek` int NOT NULL,
  `kode_bahan` varchar(20) DEFAULT NULL,
  `nama_bahan` varchar(100) DEFAULT NULL,
  `spesifikasi` text,
  `id_jurusan` int DEFAULT NULL,
  `id_lab` int DEFAULT NULL,
  `stok` int DEFAULT NULL,
  `kondisi` enum('Baik','Kurang Baik','Rusak') DEFAULT 'Baik',
  `satuan` varchar(50) DEFAULT NULL,
  `tgl_masuk` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bahan_praktek`
--

INSERT INTO `bahan_praktek` (`id_praktek`, `kode_bahan`, `nama_bahan`, `spesifikasi`, `id_jurusan`, `id_lab`, `stok`, `kondisi`, `satuan`, `tgl_masuk`) VALUES
(27, 'BPR-26-001', 'Meteran', 'Meteran Listrik', NULL, NULL, 10, 'Baik', 'Pcs', '2026-01-30'),
(28, 'BPR-26-002', 'Isolasi Listrik', 'Isolasi Kabel Untuk Kabel Listrik', NULL, NULL, 7, 'Baik', 'Pcs', '2026-01-30'),
(29, 'BPR-26-003', 'Tang', 'Tang Besi', NULL, NULL, 7, 'Baik', 'Pcs', '2026-01-30'),
(30, 'BPR-26-004', 'Mouse Pad', 'Laboratorium Komputasi', NULL, NULL, 9, 'Baik', 'Pcs', '2026-01-30'),
(31, 'BPR-26-005', 'Plate Dumbble ', 'Plate Dumbble - 5 Kg', NULL, NULL, 1, 'Baik', 'Pcs', '2026-01-30'),
(32, 'BPR-26-006', 'Mata Gerinda Potong 4 Inch', 'Mesin Potong Besi', NULL, NULL, 10, 'Baik', 'Psc', '2026-01-30'),
(33, 'BPR-26-007', 'Gas CO2 1 kg', 'Tabung Gas CO2', NULL, NULL, 7, 'Baik', 'Tabung', '2026-01-30'),
(34, 'BPR-26-008', 'Selang Kecil Destilasi', 'Selang Kecil', NULL, NULL, 10, 'Baik', 'Meter', '2026-01-30'),
(35, 'BPR-26-009', 'Tespen', 'Tespen', NULL, NULL, 15, 'Baik', 'Pcs', '2026-01-30'),
(36, 'BPR-26-010', 'Kabel NYAF Merah', 'Kabel NYAF Merah', NULL, NULL, 2, 'Baik', 'Meter', '2026-01-30'),
(39, 'BPR-26-011', 'Filamen - Biru', 'Filamen', NULL, NULL, 2, 'Baik', 'Roll', '2026-02-13'),
(40, 'BPR-26-012', 'Box Kontainer Navara (Merah)', 'Box Kontainer Navara (Merah)', NULL, NULL, 30, 'Baik', 'Pcs', '2026-02-13'),
(41, 'BPR-26-013', 'Box Organizer', 'Box Organizer - Kuning', NULL, NULL, 5, 'Baik', 'Pcs', '2026-02-13'),
(42, 'BPR-26-014', 'Lakban Isolasi', 'Lakban Isolasi (jilid) merah', NULL, NULL, 4, 'Baik', 'Pcs', '2026-02-13'),
(43, 'BPR-26-015', 'Lakban Isolasi', 'Lakban Isolasi (jilid) Kuning', NULL, NULL, 4, 'Baik', 'Pcs', '2026-02-13'),
(44, 'BPR-26-016', 'Balok ', 'Balok Jati Putih', NULL, NULL, 50, 'Baik', 'Pcs', '2026-02-13'),
(45, 'BPR-26-017', 'Drump HDPE Torn Air', 'Drump HDPE Torn Air Tempat Sampah 160 Liter', NULL, NULL, 2, 'Baik', 'Pcs', '2026-02-13'),
(46, 'BPR-26-018', 'Tang', 'Tang Laboratorium Sistem Produksi', NULL, NULL, 5, 'Baik', 'Pcs', '2026-02-13'),
(47, 'BPR-26-019', 'Cat Duco Merah ', 'Cat Duco Merah Tuangan 1L - Cat besi kayu pagar', NULL, NULL, 20, 'Baik', 'Kaleng', '2026-02-13'),
(48, 'BPR-26-020', 'Tombol Power on off', 'Tombol Power on off - Laboratorium Komputasi', NULL, NULL, 5, 'Baik', 'Pcs', '2026-02-13'),
(49, 'BPR-26-021', 'Manajemen kabel velcro', 'Manajemen kabel velcro - Komputasi', NULL, NULL, 10, 'Baik', 'Pcs', '2026-02-13'),
(50, 'BPR-26-022', 'Plate Dumbble', 'Plate Dumbble - 5 Kg', NULL, NULL, 2, 'Baik', 'Pcs', '2026-02-13'),
(51, 'BPR-26-023', 'Plate Dumbble ', 'Plate Dumbble - 5 Kg', NULL, NULL, 2, 'Baik', 'Pcs', '2026-02-13'),
(52, 'BPR-26-024', 'Keranjang Industri', 'Keranjang Industri', NULL, NULL, 5, 'Baik', 'Pcs', '2026-02-13'),
(53, 'BPR-26-025', 'Baterai', 'Baterai 5 Pack', NULL, NULL, 5, 'Baik', 'Pack', '2026-02-13');

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` int NOT NULL,
  `kode_barang` varchar(20) DEFAULT NULL,
  `id_lab` int DEFAULT NULL,
  `kategori` enum('ATK','Kebersihan','Bahan Praktek') DEFAULT NULL,
  `nama_barang` varchar(100) DEFAULT NULL,
  `spesifikasi` text,
  `kondisi` varchar(50) DEFAULT NULL,
  `stok` int DEFAULT '0',
  `satuan` varchar(20) DEFAULT NULL,
  `tgl_masuk` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `kode_barang`, `id_lab`, `kategori`, `nama_barang`, `spesifikasi`, `kondisi`, `stok`, `satuan`, `tgl_masuk`) VALUES
(2, 'ATK-26-002', NULL, 'ATK', 'buku cerita baru', NULL, NULL, 24, 'lembar', '2026-01-16'),
(3, 'ATK-26-003', NULL, 'ATK', 'buku', NULL, NULL, 20, 'kertas', '2026-01-09'),
(4, 'KBR-26-001', NULL, 'Kebersihan', 'sapu lantai laju', NULL, NULL, 23, 'pcs', '2026-01-06'),
(5, 'ATK-26-004', NULL, 'ATK', 'pensil', NULL, NULL, 21, 'biji', '2026-01-07');

-- --------------------------------------------------------

--
-- Table structure for table `distribusi`
--

CREATE TABLE `distribusi` (
  `id_distribusi` int NOT NULL,
  `id_barang` int DEFAULT NULL,
  `id_lab` int DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `status` enum('proses','diterima','ditolak') DEFAULT 'proses',
  `tanggal_distribusi` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `distribusi_lab`
--

CREATE TABLE `distribusi_lab` (
  `id_distribusi` int NOT NULL,
  `id_praktek` int DEFAULT NULL,
  `id_lab` int DEFAULT NULL,
  `kode_distribusi` varchar(50) DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `tanggal_distribusi` date DEFAULT NULL,
  `spesifikasi` text,
  `kondisi` varchar(50) DEFAULT NULL,
  `status` enum('dikirim','diterima','ditolak') NOT NULL,
  `keterangan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `distribusi_lab`
--

INSERT INTO `distribusi_lab` (`id_distribusi`, `id_praktek`, `id_lab`, `kode_distribusi`, `jumlah`, `tanggal_distribusi`, `spesifikasi`, `kondisi`, `status`, `keterangan`) VALUES
(73, 29, 20, 'BPR-26-003-20260130', 1, '2026-01-30', 'Tang Besi', 'Baik', 'diterima', NULL),
(76, 28, 20, 'TMIA/LP/BPR-26-002', 2, '2026-02-02', 'Isolasi Kabel Untuk Kabel Listrik', 'Baik', 'diterima', NULL),
(77, 28, 20, 'TMIA/LP/BPR-26-002', 1, '2026-02-02', 'Isolasi Kabel Untuk Kabel Listrik', 'Baik', 'diterima', NULL),
(79, 31, 6, 'OSP/LP/BPR-26-005', 4, '2026-01-30', 'Besi Lembar ', 'Baik', 'diterima', NULL),
(80, 36, 6, 'OSP/LP/BPR-26-010', 4, '2026-01-30', 'Kabel NYAF Merah', 'Baik', 'diterima', NULL),
(81, 36, 6, 'OSP/LP/BPR-26-010', 1, '2026-02-02', 'Kabel NYAF Merah', 'Baik', 'dikirim', NULL),
(84, 33, 20, 'TMIA/LP/BPR-26-007', 3, '2026-02-10', 'Tabung Gas CO2', 'Baik', 'diterima', NULL),
(86, 29, 20, 'TMIA/LP/BPR-26-003', 2, '2026-02-10', 'Tang Besi', 'Baik', 'diterima', NULL),
(87, 36, 20, 'TMIA/LP/BPR-26-010', 3, '2026-02-10', 'Kabel NYAF Merah', 'Baik', 'diterima', NULL),
(90, 31, 20, 'TMIA/LP/BPR-26-005', 3, '2026-02-13', 'Besi Lembar ', 'Baik', 'diterima', NULL),
(91, 31, 20, 'TMIA/LP/BPR-26-005', 3, '2026-02-10', 'Besi Lembar ', 'Baik', 'ditolak', 'Kondisi Barang Rusak/Cacat - Barang tidak dapat digunakan');

-- --------------------------------------------------------

--
-- Table structure for table `jurusan`
--

CREATE TABLE `jurusan` (
  `id_jurusan` int NOT NULL,
  `nama_jurusan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jurusan`
--

INSERT INTO `jurusan` (`id_jurusan`, `nama_jurusan`) VALUES
(1, 'Teknik Kimia Mineral'),
(2, 'Teknik Industri Agro'),
(3, 'Teknik Manufaktur Industri Agro'),
(4, 'Otomasi Sistem Permesinan'),
(8, 'Laboratorium Mata Kuliah Umum');

-- --------------------------------------------------------

--
-- Table structure for table `kepala_lab`
--

CREATE TABLE `kepala_lab` (
  `id_kepala` int NOT NULL,
  `id_lab` int DEFAULT NULL,
  `nama_kepala` varchar(100) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `password_plain` varchar(255) DEFAULT NULL,
  `nip` varchar(50) DEFAULT NULL,
  `kontak` varchar(20) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'kepala_lab'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kepala_lab`
--

INSERT INTO `kepala_lab` (`id_kepala`, `id_lab`, `nama_kepala`, `username`, `password`, `password_plain`, `nip`, `kontak`, `role`) VALUES
(9, 6, 'KAROLUS GANTENG', 'testing', '$2y$10$cWTCZpvWh3TjVneYD6WAoec1Er/Jhhp801flFL..69bmIMdT3sIJS', 'testing', '9999999999', '034023023029', 'kepala_lab'),
(11, 20, 'Iman Pradana A. Assegaf, M.Eng', 'pengelasan', '$2y$10$msHZ8.zOsxwzDhlNjT/A/ec6A/tuidpIv9KX6IeBSE9PscbFir5oG', 'pengelasan', '9999999999', '034023023029', 'kepala_lab');

-- --------------------------------------------------------

--
-- Table structure for table `lab`
--

CREATE TABLE `lab` (
  `id_lab` int NOT NULL,
  `id_jurusan` int DEFAULT NULL,
  `nama_lab` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lab`
--

INSERT INTO `lab` (`id_lab`, `id_jurusan`, `nama_lab`) VALUES
(6, 4, 'Laboratorium Pneumatik dan Hidrolik'),
(7, 4, 'Laboratorium Instalasi Listrik'),
(9, 4, 'Laboratorium Otomasi'),
(10, 4, 'Laboratorium Sistem Permesinan '),
(11, 4, 'Laboratorium Teknologi Informasi dan Jaringan'),
(12, 4, 'Laboratorium Elektronika dan Instrumentasi'),
(13, 4, 'Laboratorium Mesin Listrik'),
(15, 2, 'Laboratorium Sistem Produksi '),
(16, 2, 'Laboratorium Perancangan Teknik Industri '),
(17, 2, 'Laboratorium Sistem Kerja dan Ergonomi'),
(18, 2, 'Laboratorium Rekayasa Tekno-Ekonomi'),
(19, 1, 'Laboratorium Dasar Proses dan Pengujian Terpadu'),
(20, 3, 'Workshop Pengelasan'),
(21, 3, 'Laboratorium CNC'),
(22, 3, 'Laboratorium Gambar'),
(23, 3, ' Laboratorium Pengujian Material'),
(24, 3, 'Workshop Proses Produksi'),
(25, 2, 'Laboratorium Teknologi Proses Agro'),
(26, 2, 'Laboratorium Statistik dan Rekayasa Kualitas'),
(27, 4, 'Laboratorium Manufaktur Terapan'),
(28, 1, 'Laboratorium Konversi Biomassa dan Lingkungan '),
(29, 1, 'Laboratorium Material dan Metalurgi Ekstraktif'),
(30, 1, 'Laboratorium Simulasi Proses'),
(31, 1, 'Laboratorium Operasi Teknik Kimia'),
(32, 1, 'Laboratorium Benefisiasi Mineral'),
(33, 3, 'Laboratorium Pemeliharaan Mesin Industri'),
(34, 3, 'Laboratorium Desain'),
(35, 8, 'Laboratorium Fisika');

-- --------------------------------------------------------

--
-- Table structure for table `master_key`
--

CREATE TABLE `master_key` (
  `id` int NOT NULL,
  `kode_rahasia` varchar(255) NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `master_key`
--

INSERT INTO `master_key` (`id`, `kode_rahasia`, `updated_at`) VALUES
(1, 'ADMIN_SECRET_2026', '2026-01-09 02:18:09');

-- --------------------------------------------------------

--
-- Table structure for table `pemakaian_lab`
--

CREATE TABLE `pemakaian_lab` (
  `id_pemakaian` int NOT NULL,
  `id_distribusi` int NOT NULL,
  `kode_distribusi` varchar(50) NOT NULL,
  `id_praktek` int NOT NULL,
  `id_lab` int NOT NULL,
  `jumlah_pakai` int NOT NULL,
  `tgl_pakai` datetime DEFAULT CURRENT_TIMESTAMP,
  `keterangan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pemakaian_lab`
--

INSERT INTO `pemakaian_lab` (`id_pemakaian`, `id_distribusi`, `kode_distribusi`, `id_praktek`, `id_lab`, `jumlah_pakai`, `tgl_pakai`, `keterangan`) VALUES
(20, 73, '', 29, 20, 1, '2026-01-30 10:47:54', NULL),
(21, 79, '', 31, 6, 3, '2026-01-30 15:03:12', NULL),
(22, 80, '', 36, 6, 3, '2026-01-30 15:03:25', NULL),
(23, 77, '', 28, 20, 1, '2026-02-10 10:40:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `permintaan_barang`
--

CREATE TABLE `permintaan_barang` (
  `id_permintaan` int NOT NULL,
  `id_kepala` int NOT NULL,
  `id_barang` int NOT NULL,
  `spesifikasi` text,
  `jumlah_minta` int NOT NULL,
  `kondisi` varchar(50) DEFAULT NULL,
  `jumlah_disetujui` int DEFAULT '0',
  `tgl_permintaan` datetime DEFAULT CURRENT_TIMESTAMP,
  `tgl_proses` datetime DEFAULT NULL,
  `status` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `keterangan_kepala` text,
  `catatan_admin` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `permintaan_barang`
--

INSERT INTO `permintaan_barang` (`id_permintaan`, `id_kepala`, `id_barang`, `spesifikasi`, `jumlah_minta`, `kondisi`, `jumlah_disetujui`, `tgl_permintaan`, `tgl_proses`, `status`, `keterangan_kepala`, `catatan_admin`) VALUES
(52, 11, 29, 'TANG BESI', 2, 'Baik', 0, '2026-01-30 10:42:34', NULL, 'disetujui', NULL, NULL),
(54, 11, 30, '-', 5, 'BAIK', 0, '2026-01-30 11:00:56', NULL, 'disetujui', NULL, NULL),
(55, 11, 28, '-', 3, 'BAIK', 0, '2026-01-30 11:01:33', NULL, 'disetujui', NULL, NULL),
(56, 11, 28, '-', 2, 'BAIK', 0, '2026-01-30 11:02:54', NULL, 'disetujui', NULL, NULL),
(57, 11, 28, '-', 12, 'BAIK', 0, '2026-01-30 11:15:01', NULL, 'disetujui', NULL, NULL),
(58, 11, 33, '-', 3, 'BAIK', 0, '2026-01-30 11:23:18', NULL, 'disetujui', NULL, NULL),
(59, 9, 31, '-', 4, 'BAIK', 0, '2026-01-30 14:49:58', NULL, 'disetujui', NULL, NULL),
(60, 9, 36, '-', 4, 'BAIK', 0, '2026-01-30 14:50:07', NULL, 'disetujui', NULL, NULL),
(61, 9, 36, '-', 2, 'BAIK', 0, '2026-01-30 22:48:47', NULL, 'disetujui', NULL, NULL),
(62, 11, 30, '-', 2, 'BAIK', 0, '2026-02-02 09:53:46', NULL, 'disetujui', NULL, NULL),
(63, 11, 33, '-', 2, 'BAIK', 0, '2026-02-10 10:56:41', NULL, 'disetujui', NULL, NULL),
(64, 11, 33, '-', 3, 'BAIK', 0, '2026-02-10 10:56:49', NULL, 'disetujui', NULL, NULL),
(65, 11, 36, '-', 2, 'BAIK', 0, '2026-02-10 10:56:54', NULL, 'disetujui', NULL, NULL),
(66, 11, 29, '-', 2, 'BAIK', 0, '2026-02-10 11:10:59', NULL, 'disetujui', NULL, NULL),
(67, 11, 36, '-', 3, 'BAIK', 0, '2026-02-10 11:11:06', NULL, 'disetujui', NULL, NULL),
(68, 11, 32, '-', 3, 'BAIK', 0, '2026-02-10 11:11:13', NULL, 'disetujui', NULL, NULL),
(69, 11, 32, '-', 3, 'BAIK', 0, '2026-02-10 11:15:03', NULL, 'disetujui', NULL, NULL),
(70, 11, 31, '-', 3, 'BAIK', 0, '2026-02-10 11:18:13', NULL, 'disetujui', NULL, NULL),
(71, 11, 31, '-', 3, 'BAIK', 0, '2026-02-10 11:18:18', NULL, 'disetujui', NULL, NULL),
(72, 11, 44, '-', 2, 'BAIK', 0, '2026-02-13 15:17:13', NULL, 'pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','kepala_lab') DEFAULT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `nip` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `nama_lengkap`, `nip`) VALUES
(17, 'ibudiann', '$2y$10$ucWAisFX2OVrVRUuFDoQB.p2Fe42ILszxbJprE3mEwDL575zDhDm6', 'admin', 'Ibu Dian', '10101010101010101');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bahan_praktek`
--
ALTER TABLE `bahan_praktek`
  ADD PRIMARY KEY (`id_praktek`);

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD KEY `id_lab` (`id_lab`);

--
-- Indexes for table `distribusi`
--
ALTER TABLE `distribusi`
  ADD PRIMARY KEY (`id_distribusi`),
  ADD KEY `id_barang` (`id_barang`),
  ADD KEY `id_lab` (`id_lab`);

--
-- Indexes for table `distribusi_lab`
--
ALTER TABLE `distribusi_lab`
  ADD PRIMARY KEY (`id_distribusi`),
  ADD KEY `id_praktek` (`id_praktek`),
  ADD KEY `id_lab` (`id_lab`);

--
-- Indexes for table `jurusan`
--
ALTER TABLE `jurusan`
  ADD PRIMARY KEY (`id_jurusan`);

--
-- Indexes for table `kepala_lab`
--
ALTER TABLE `kepala_lab`
  ADD PRIMARY KEY (`id_kepala`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_lab` (`id_lab`);

--
-- Indexes for table `lab`
--
ALTER TABLE `lab`
  ADD PRIMARY KEY (`id_lab`),
  ADD KEY `id_jurusan` (`id_jurusan`);

--
-- Indexes for table `master_key`
--
ALTER TABLE `master_key`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pemakaian_lab`
--
ALTER TABLE `pemakaian_lab`
  ADD PRIMARY KEY (`id_pemakaian`),
  ADD KEY `id_praktek` (`id_praktek`);

--
-- Indexes for table `permintaan_barang`
--
ALTER TABLE `permintaan_barang`
  ADD PRIMARY KEY (`id_permintaan`),
  ADD KEY `fk_permintaan_bahan` (`id_barang`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bahan_praktek`
--
ALTER TABLE `bahan_praktek`
  MODIFY `id_praktek` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `distribusi`
--
ALTER TABLE `distribusi`
  MODIFY `id_distribusi` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `distribusi_lab`
--
ALTER TABLE `distribusi_lab`
  MODIFY `id_distribusi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `jurusan`
--
ALTER TABLE `jurusan`
  MODIFY `id_jurusan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `kepala_lab`
--
ALTER TABLE `kepala_lab`
  MODIFY `id_kepala` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `lab`
--
ALTER TABLE `lab`
  MODIFY `id_lab` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `master_key`
--
ALTER TABLE `master_key`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pemakaian_lab`
--
ALTER TABLE `pemakaian_lab`
  MODIFY `id_pemakaian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `permintaan_barang`
--
ALTER TABLE `permintaan_barang`
  MODIFY `id_permintaan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `barang_ibfk_1` FOREIGN KEY (`id_lab`) REFERENCES `lab` (`id_lab`);

--
-- Constraints for table `distribusi`
--
ALTER TABLE `distribusi`
  ADD CONSTRAINT `distribusi_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE,
  ADD CONSTRAINT `distribusi_ibfk_2` FOREIGN KEY (`id_lab`) REFERENCES `lab` (`id_lab`) ON DELETE CASCADE;

--
-- Constraints for table `distribusi_lab`
--
ALTER TABLE `distribusi_lab`
  ADD CONSTRAINT `distribusi_lab_ibfk_1` FOREIGN KEY (`id_praktek`) REFERENCES `bahan_praktek` (`id_praktek`),
  ADD CONSTRAINT `distribusi_lab_ibfk_2` FOREIGN KEY (`id_lab`) REFERENCES `lab` (`id_lab`);

--
-- Constraints for table `kepala_lab`
--
ALTER TABLE `kepala_lab`
  ADD CONSTRAINT `kepala_lab_ibfk_1` FOREIGN KEY (`id_lab`) REFERENCES `lab` (`id_lab`) ON DELETE CASCADE;

--
-- Constraints for table `lab`
--
ALTER TABLE `lab`
  ADD CONSTRAINT `lab_ibfk_1` FOREIGN KEY (`id_jurusan`) REFERENCES `jurusan` (`id_jurusan`);

--
-- Constraints for table `pemakaian_lab`
--
ALTER TABLE `pemakaian_lab`
  ADD CONSTRAINT `pemakaian_lab_ibfk_1` FOREIGN KEY (`id_praktek`) REFERENCES `bahan_praktek` (`id_praktek`);

--
-- Constraints for table `permintaan_barang`
--
ALTER TABLE `permintaan_barang`
  ADD CONSTRAINT `fk_permintaan_bahan` FOREIGN KEY (`id_barang`) REFERENCES `bahan_praktek` (`id_praktek`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
