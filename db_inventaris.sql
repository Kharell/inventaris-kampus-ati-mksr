-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 24, 2026 at 11:12 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

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

-- --------------------------------------------------------

--
-- Table structure for table `distribusi_history`
--

CREATE TABLE `distribusi_history` (
  `id_history` int NOT NULL,
  `id_distribusi` int DEFAULT NULL,
  `jumlah_masuk` int DEFAULT NULL,
  `tanggal_log` datetime DEFAULT NULL,
  `keterangan_log` text,
  `status_log` enum('Lengkap','Sebagian') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `distribusi_lab`
--

CREATE TABLE `distribusi_lab` (
  `id_distribusi` int NOT NULL,
  `id_permintaan` int DEFAULT NULL,
  `id_praktek` int DEFAULT NULL,
  `id_lab` int DEFAULT NULL,
  `kode_distribusi` varchar(50) DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `jumlah_diterima` int DEFAULT '0',
  `tanggal_distribusi` datetime DEFAULT NULL,
  `spesifikasi` text,
  `kondisi` varchar(50) DEFAULT NULL,
  `status` enum('dikirim','diterima','ditolak') NOT NULL,
  `keterangan` text,
  `tanggal_diterima` datetime DEFAULT NULL,
  `balasan_admin` text,
  `is_read_lab` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gudang_persediaan`
--

CREATE TABLE `gudang_persediaan` (
  `id_persediaan` int NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `stok_awal` int DEFAULT '0',
  `pengajuan_barang` int DEFAULT '0',
  `pemakaian_barang` int DEFAULT '0',
  `stok_akhir` int GENERATED ALWAYS AS (((`stok_awal` + `pengajuan_barang`) - `pemakaian_barang`)) VIRTUAL,
  `tgl_input` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `pin_pemulihan` varchar(6) DEFAULT NULL,
  `password_plain` varchar(255) DEFAULT NULL,
  `nip` varchar(50) DEFAULT NULL,
  `kontak` varchar(20) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'kepala_lab'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kepala_lab`
--

INSERT INTO `kepala_lab` (`id_kepala`, `id_lab`, `nama_kepala`, `username`, `password`, `pin_pemulihan`, `password_plain`, `nip`, `kontak`, `role`) VALUES
(9, 6, 'KAROLUS GANTENG', 'testing', '$2y$10$cWTCZpvWh3TjVneYD6WAoec1Er/Jhhp801flFL..69bmIMdT3sIJS', NULL, 'testing', '9999999999', '034023023029', 'kepala_lab'),
(11, 20, 'Iman Pradana A. Assegaf, M.Eng', 'pengelasan', '$2y$10$msHZ8.zOsxwzDhlNjT/A/ec6A/tuidpIv9KX6IeBSE9PscbFir5oG', NULL, 'pengelasan', '9999999999', '034023023029', 'kepala_lab'),
(12, 21, 'Nursida, A.Md', 'nursida', '$2y$10$b65FbUZBpL7PDjNSJkEjHOppCq9nh1sRxLq25aVjP.51cjM08Us7q', NULL, 'atim123', '1997xxx', '0812315151', 'kepala_lab');

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
(36, 8, 'Laboratorium Fisika');

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
  `keterangan` text,
  `status_kunci` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan_sistem`
--

CREATE TABLE `pengaturan_sistem` (
  `nama_pengaturan` varchar(50) NOT NULL,
  `nilai_pengaturan` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengaturan_sistem`
--

INSERT INTO `pengaturan_sistem` (`nama_pengaturan`, `nilai_pengaturan`) VALUES
('status_input_stok', 0);

-- --------------------------------------------------------

--
-- Table structure for table `permintaan_bahan`
--

CREATE TABLE `permintaan_bahan` (
  `id_permintaan` int NOT NULL,
  `id_barang` int NOT NULL,
  `id_user` int NOT NULL,
  `stok_saat_ini` int DEFAULT '0',
  `tgl_permintaan` datetime NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permintaan_barang`
--

CREATE TABLE `permintaan_barang` (
  `id_permintaan` int NOT NULL,
  `id_kepala` int NOT NULL,
  `id_barang` int NOT NULL,
  `stok_awal` int DEFAULT '0',
  `spesifikasi` text,
  `jumlah_minta` int NOT NULL,
  `kondisi` varchar(50) DEFAULT NULL,
  `jumlah_disetujui` int DEFAULT '0',
  `jumlah_terpenuhi` int DEFAULT '0',
  `tgl_permintaan` datetime DEFAULT CURRENT_TIMESTAMP,
  `tgl_proses` datetime DEFAULT NULL,
  `status` enum('pending','disetujui','ditolak','dikirim') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'pending',
  `keterangan_kepala` text,
  `catatan_admin` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `pin_pemulihan` varchar(6) DEFAULT NULL,
  `role` enum('admin','admin-acc','kepala_lab') DEFAULT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `nip` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `pin_pemulihan`, `role`, `nama_lengkap`, `nip`) VALUES
(17, 'ibudiann', '$2y$10$RIYks5pewj1.RNT/iWq3hOI8MK6Jl7Q8anBd0/r7TkIgNCHokr0o6', '212223', 'admin', 'Ibu Dian', '10101010101010101');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bahan_praktek`
--
ALTER TABLE `bahan_praktek`
  ADD PRIMARY KEY (`id_praktek`);

--
-- Indexes for table `distribusi_history`
--
ALTER TABLE `distribusi_history`
  ADD PRIMARY KEY (`id_history`);

--
-- Indexes for table `distribusi_lab`
--
ALTER TABLE `distribusi_lab`
  ADD PRIMARY KEY (`id_distribusi`),
  ADD KEY `id_praktek` (`id_praktek`),
  ADD KEY `id_lab` (`id_lab`);

--
-- Indexes for table `gudang_persediaan`
--
ALTER TABLE `gudang_persediaan`
  ADD PRIMARY KEY (`id_persediaan`);

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
-- Indexes for table `pengaturan_sistem`
--
ALTER TABLE `pengaturan_sistem`
  ADD PRIMARY KEY (`nama_pengaturan`);

--
-- Indexes for table `permintaan_bahan`
--
ALTER TABLE `permintaan_bahan`
  ADD PRIMARY KEY (`id_permintaan`);

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
  MODIFY `id_praktek` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `distribusi_history`
--
ALTER TABLE `distribusi_history`
  MODIFY `id_history` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `distribusi_lab`
--
ALTER TABLE `distribusi_lab`
  MODIFY `id_distribusi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=308;

--
-- AUTO_INCREMENT for table `gudang_persediaan`
--
ALTER TABLE `gudang_persediaan`
  MODIFY `id_persediaan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `jurusan`
--
ALTER TABLE `jurusan`
  MODIFY `id_jurusan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `kepala_lab`
--
ALTER TABLE `kepala_lab`
  MODIFY `id_kepala` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `lab`
--
ALTER TABLE `lab`
  MODIFY `id_lab` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `master_key`
--
ALTER TABLE `master_key`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pemakaian_lab`
--
ALTER TABLE `pemakaian_lab`
  MODIFY `id_pemakaian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `permintaan_bahan`
--
ALTER TABLE `permintaan_bahan`
  MODIFY `id_permintaan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `permintaan_barang`
--
ALTER TABLE `permintaan_barang`
  MODIFY `id_permintaan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

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
