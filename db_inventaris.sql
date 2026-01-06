-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 05, 2026 at 12:40 AM
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
  `id_jurusan` int DEFAULT NULL,
  `id_lab` int DEFAULT NULL,
  `stok` int DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `tgl_masuk` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bahan_praktek`
--

INSERT INTO `bahan_praktek` (`id_praktek`, `kode_bahan`, `nama_bahan`, `id_jurusan`, `id_lab`, `stok`, `satuan`, `tgl_masuk`) VALUES
(1, 'BPR-26-001', 'kabel utp', NULL, NULL, 21, 'roll', '2026-01-04'),
(2, 'BPR-26-002', 'kabel utp lagi', NULL, NULL, 15, 'pcs', '2026-01-04'),
(3, 'BPR-26-003', 'kabel', NULL, NULL, 4, 'roll', '2026-01-04'),
(4, 'BPR-26-004', 'meja', NULL, NULL, 2, 'pcs', '2026-01-04'),
(5, 'BPR-26-005', 'tang', NULL, NULL, 4, 'lembar', '2026-01-04'),
(6, 'BPR-26-006', 'kunci inggris', NULL, NULL, 2, '4', '2026-01-04'),
(7, 'BPR-26-007', 'kabel jumper', NULL, NULL, 3, 'roll', '2026-01-04'),
(8, 'BPR-26-008', 'jam', NULL, NULL, 3, 'butir', '2026-01-04'),
(9, 'BPR-26-009', 'karpet', NULL, NULL, 12, 'roll', '2026-01-04'),
(10, 'BPR-26-010', 'kursi', NULL, NULL, 24, 'pcs', '2026-01-04'),
(11, 'BPR-26-011', 'helm', NULL, NULL, 0, 'butir', '2026-01-04');

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
(1, 11, 6, 'OSP/P/BPR-26-011', 21, '2026-01-04', 'diterima'),
(3, 3, 6, 'OSP/P/BPR-26-003', 12, '2026-01-04', 'diterima'),
(4, 8, 6, 'OSP/P/BPR-26-008', 6, '2026-01-04', 'diterima'),
(5, 3, 6, 'OSP/P/BPR-26-003', 2, '2026-01-04', 'dikirim'),
(6, 11, 6, 'OSP/P/BPR-26-011', 2, '2026-01-04', 'dikirim'),
(7, 2, 2, 'TKM/LKL/BPR-26-002', 6, '2026-01-04', 'diterima'),
(8, 4, 2, 'TKM/LKL/BPR-26-004', 3, '2026-01-04', 'diterima'),
(9, 3, 2, 'TKM/LKL/BPR-26-003', 5, '2026-01-04', 'diterima'),
(10, 8, 8, 'TMIA/K/BPR-26-008', 3, '2026-01-05', 'dikirim');

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
(4, 'Otomasi Sistem Permesinan');

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
(1, 1, 'KAREL', 'karel', '$2y$10$g.rZ1qzHTccSR.e/mYE6/.vHYI1MwHt3IDdTnLS75GL9vuF01UsR2', '123', '2385y9385683', '023895735', 'kepala_lab'),
(2, 1, 'DIAN', 'dian', '$2y$10$64E5fbg0tTvDqa3cVjHvfuHyjwzToxyxf6xHR1wgrf7lNt8HXpt/6', '123', '24214', '535353', 'kepala_lab'),
(4, 2, 'MUH. IQRA', 'iqra', '$2y$10$3HjIky/cEHjGEzSU4aWDTOtDEtTlSf4cuXIHwe2mtY8YxB7VHTXmq', '123', '85938953', '93493894', 'kepala_lab'),
(5, 6, 'HIKMA', 'hikma', '$2y$10$G920tn92QOjJJEKnbbm5MuIcDj4KXu8HWehRaQsb3UpDiEVVGS3KG', '123', '098617711', '0821768196672', 'kepala_lab');

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
(1, 2, 'LAB PERTAMA'),
(2, 1, 'LAB KEDUA LAGI'),
(3, 1, 'JARINGAN'),
(4, 2, 'MATEMATIKA'),
(5, 2, 'MULTIMEDIA'),
(6, 4, 'Laboratorium Pneumatik'),
(7, 4, 'Laboratorium Instalasi Listrik'),
(8, 3, 'ketiga'),
(9, 4, 'Laboratorium Kontrol dan Otomasi'),
(10, 4, 'Laboratorium Mekatronika'),
(11, 4, 'Laboratorium Jaringan Komputer'),
(12, 4, 'Laboratorium Elektronika & Instrumentasi'),
(13, 4, 'Laboratorium Teknik Tenaga Listrik');

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
  MODIFY `id_praktek` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
  MODIFY `id_distribusi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `jurusan`
--
ALTER TABLE `jurusan`
  MODIFY `id_jurusan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kepala_lab`
--
ALTER TABLE `kepala_lab`
  MODIFY `id_kepala` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lab`
--
ALTER TABLE `lab`
  MODIFY `id_lab` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
