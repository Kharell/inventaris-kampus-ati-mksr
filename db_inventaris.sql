-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 13, 2026 at 02:10 AM
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
(12, 'BPR-26-001', 'Kabel 5 M', NULL, NULL, NULL, 8, 'Baik', 'Meter', '2026-01-08'),
(13, 'BPR-26-002', 'Tang', NULL, NULL, NULL, 11, 'Baik', 'Pcs', '2026-01-08'),
(14, 'BPR-26-003', 'meja', NULL, NULL, NULL, 19, 'Baik', '', '2026-01-12'),
(15, 'BPR-26-004', 'kamera', NULL, NULL, NULL, 10, 'Baik', 'kg', '2026-01-13');

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
  `stok` int DEFAULT '0',
  `satuan` varchar(20) DEFAULT NULL,
  `tgl_masuk` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `kode_barang`, `id_lab`, `kategori`, `nama_barang`, `stok`, `satuan`, `tgl_masuk`) VALUES
(2, 'ATK-26-002', NULL, 'ATK', 'buku cerita baru', 24, 'lembar', '2026-01-16'),
(3, 'ATK-26-003', NULL, 'ATK', 'buku', 20, 'kertas', '2026-01-09'),
(4, 'KBR-26-001', NULL, 'Kebersihan', 'sapu lantai laju', 23, 'pcs', '2026-01-06'),
(5, 'ATK-26-004', NULL, 'ATK', 'pensil', 21, 'biji', '2026-01-07');

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
  `status` enum('dikirim','diterima') NOT NULL DEFAULT 'dikirim'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `distribusi_lab`
--

INSERT INTO `distribusi_lab` (`id_distribusi`, `id_praktek`, `id_lab`, `kode_distribusi`, `jumlah`, `tanggal_distribusi`, `status`) VALUES
(29, 12, 6, 'OSP/LP/BPR-26-001', 1, '2026-01-08', 'diterima'),
(30, 13, 6, 'OSP/LP/BPR-26-002', 2, '2026-01-08', 'diterima'),
(31, 12, 6, 'OSP/LP/BPR-26-001', 1, '2026-01-08', 'dikirim'),
(32, 14, 14, 'JC/CS/BPR-26-003', 1, '2026-01-12', 'diterima'),
(33, 13, 14, 'JC/CS/BPR-26-002', 2, '2026-01-12', 'diterima');

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
(5, 'jurusan coba');

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
(9, 6, 'Kepala Lab Testing', 'testing', '$2y$10$cWTCZpvWh3TjVneYD6WAoec1Er/Jhhp801flFL..69bmIMdT3sIJS', 'testing', '9999999999', '034023023029', 'kepala_lab'),
(10, 14, 'muslimin', 'muslimin', '$2y$10$6AKo6gep1oqW4CeOAOfb5.s9rROLx76i.d3gUT8WOyd922/Ptpkaq', 'muslimin', '00000', '080000', 'kepala_lab');

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
(6, 4, 'Laboratorium Pneumatik'),
(7, 4, 'Laboratorium Instalasi Listrik'),
(9, 4, 'Laboratorium Kontrol dan Otomasi'),
(10, 4, 'Laboratorium Mekatronika'),
(11, 4, 'Laboratorium Jaringan Komputer'),
(12, 4, 'Laboratorium Elektronika & Instrumentasi'),
(13, 4, 'Laboratorium Teknik Tenaga Listrik'),
(14, 5, 'coba satu');

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
(14, 32, '', 14, 14, 1, '2026-01-12 09:14:42', NULL),
(15, 33, '', 13, 14, 2, '2026-01-12 09:14:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `permintaan_barang`
--

CREATE TABLE `permintaan_barang` (
  `id_permintaan` int NOT NULL,
  `id_kepala` int NOT NULL,
  `id_barang` int NOT NULL,
  `jumlah_minta` int NOT NULL,
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

INSERT INTO `permintaan_barang` (`id_permintaan`, `id_kepala`, `id_barang`, `jumlah_minta`, `jumlah_disetujui`, `tgl_permintaan`, `tgl_proses`, `status`, `keterangan_kepala`, `catatan_admin`) VALUES
(17, 9, 12, 2, 0, '2026-01-08 13:15:35', NULL, 'disetujui', NULL, NULL),
(18, 9, 13, 3, 0, '2026-01-08 13:15:46', NULL, 'disetujui', NULL, NULL),
(19, 9, 12, 2, 0, '2026-01-08 13:29:40', NULL, 'disetujui', NULL, NULL),
(20, 10, 14, 4, 0, '2026-01-12 09:05:32', NULL, 'disetujui', NULL, NULL),
(21, 10, 13, 2, 0, '2026-01-12 09:05:42', NULL, 'disetujui', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','kepala_lab') DEFAULT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `nama_lengkap`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrator Pusat'),
(2, 'kalab', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kepala_lab', 'Kepala Lab Teknik');

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
  MODIFY `id_praktek` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  MODIFY `id_distribusi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `jurusan`
--
ALTER TABLE `jurusan`
  MODIFY `id_jurusan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kepala_lab`
--
ALTER TABLE `kepala_lab`
  MODIFY `id_kepala` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `lab`
--
ALTER TABLE `lab`
  MODIFY `id_lab` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `master_key`
--
ALTER TABLE `master_key`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pemakaian_lab`
--
ALTER TABLE `pemakaian_lab`
  MODIFY `id_pemakaian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `permintaan_barang`
--
ALTER TABLE `permintaan_barang`
  MODIFY `id_permintaan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
