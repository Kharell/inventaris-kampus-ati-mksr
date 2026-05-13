-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
<<<<<<< HEAD
-- Generation Time: May 13, 2026 at 01:42 AM
=======
-- Generation Time: May 11, 2026 at 06:29 AM
>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e
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

--
-- Dumping data for table `bahan_praktek`
--

INSERT INTO `bahan_praktek` (`id_praktek`, `kode_bahan`, `nama_bahan`, `spesifikasi`, `id_jurusan`, `id_lab`, `stok`, `kondisi`, `satuan`, `tgl_masuk`) VALUES
<<<<<<< HEAD
(99, 'p1', 'beasi', 'ss', 3, 20, 103, 'Baik', 'sss', '2026-05-12');
=======
(85, 'P1', 'Meja', '123', 3, 20, 7, 'Baik', 'Pcs', '2026-05-11'),
(86, 'P2', 'Kursi', '123', 3, 20, 3, 'Baik', 'Pcs', '2026-05-11'),
(87, 'P3', 'Besi', 'Kawat baja', 3, 20, 0, 'Baik', 'Roll', '2026-05-11');
>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e

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

--
-- Dumping data for table `distribusi_lab`
--

INSERT INTO `distribusi_lab` (`id_distribusi`, `id_permintaan`, `id_praktek`, `id_lab`, `kode_distribusi`, `jumlah`, `jumlah_diterima`, `tanggal_distribusi`, `spesifikasi`, `kondisi`, `status`, `keterangan`, `tanggal_diterima`, `balasan_admin`, `is_read_lab`) VALUES
<<<<<<< HEAD
(292, NULL, 99, 20, 'p1', 20, 20, '2026-05-12 00:00:00', 'ss', 'Baik', 'diterima', 'Pencatatan Stok Oleh Kepala Lab (Di-ACC Admin)', '2026-05-12 13:38:15', NULL, 0),
(293, NULL, 99, 20, 'p1', 10, 10, '2026-05-12 00:00:00', 'ss', 'Baik', 'diterima', 'Pencatatan Stok Oleh Kepala Lab (Di-ACC Admin)', '2026-05-12 13:38:23', NULL, 0),
(294, NULL, 99, 20, 'p1', 20, 20, '2026-05-12 00:00:00', 'ss', 'Baik', 'diterima', 'Pencatatan Stok Oleh Kepala Lab (Di-ACC Admin)', '2026-05-12 13:43:21', NULL, 0),
(295, NULL, 99, 20, 'p1', 30, 30, '2026-05-12 00:00:00', 'ss', 'Baik', 'diterima', 'Pencatatan Stok Oleh Kepala Lab (Di-ACC Admin)', '2026-05-12 13:43:30', NULL, 0),
(296, NULL, 99, 20, 'p1', 20, 20, '2026-05-12 00:00:00', 'ss', 'Baik', 'diterima', 'Terima sebagian (Fisik: 20). Alasan: blaaaa', '2026-05-12 13:57:18', NULL, 0),
(297, NULL, 99, 20, 'p1', 0, 0, '2026-05-12 13:59:10', 'ss', 'Baik', 'diterima', 'AZJZJABJAB', NULL, NULL, 0),
(298, NULL, 99, 20, 'p1', 3, 3, '2026-05-12 13:59:52', 'ss', 'Baik', 'diterima', 'Diterima sesuai dokumen', '2026-05-12 14:00:36', NULL, 0);
=======
(250, NULL, 86, 20, 'P2', 10, 10, '2026-05-11 00:00:00', '123', 'Baik', 'diterima', 'Pencatatan stok awal (Otomatis)', NULL, NULL, 0),
(251, NULL, 87, 20, 'P3', 3, 3, '2026-05-11 00:00:00', 'Kawat baja', 'Baik', 'diterima', 'Terima sebagian: jumlah kurang (Fisik: 3)', '2026-05-11 10:50:08', NULL, 0),
(252, NULL, 86, 20, 'P2', 3, 3, '2026-05-11 00:00:00', '123', 'Baik', 'diterima', 'Terima sebagian: jumlah kurang (Fisik: 3)', '2026-05-11 10:50:24', NULL, 0),
(253, NULL, 87, 20, 'P3', 17, 17, '2026-05-11 10:51:36', 'Kawat baja', 'Baik', 'diterima', 'Diterima sesuai dokumen', '2026-05-11 10:52:25', NULL, 0),
(254, NULL, 86, 20, 'P2', 0, 0, '2026-05-11 10:52:46', '123', 'Baik', 'diterima', 'kurang bagus', NULL, NULL, 0),
(259, NULL, 85, 20, 'P1', 0, 0, '2026-05-11 10:59:31', '123', 'Baik', 'diterima', 'uhdiauifg', NULL, NULL, 0),
(261, NULL, 85, 20, 'P1', 5, 5, '2026-05-11 11:06:30', '123', 'Baik', 'diterima', 'Terima sebagian: jumlah kurang (Fisik: 5)', '2026-05-11 11:07:32', NULL, 0),
(262, NULL, 86, 20, 'P2', 3, 3, '2026-05-11 11:06:38', '123', 'Baik', 'diterima', 'Diterima sesuai dokumen', '2026-05-11 11:07:12', NULL, 0),
(263, NULL, 85, 20, 'P1', 0, 0, '2026-05-11 11:08:20', '123', 'Baik', 'diterima', 'weh', NULL, NULL, 0),
(264, NULL, 85, 20, 'P1', 5, 5, '2026-05-11 11:08:47', '123', 'Baik', 'diterima', 'Diterima sesuai dokumen', '2026-05-11 11:09:05', NULL, 0),
(265, NULL, 85, 20, 'P1', 2, 2, '2026-05-11 00:00:00', '123', 'Baik', 'diterima', 'Terima sebagian: jumlah kurang (Fisik: 2)', '2026-05-11 11:23:35', NULL, 0),
(266, NULL, 85, 20, 'P1', 2, 2, '2026-05-11 11:26:20', '123', 'Baik', 'diterima', 'Terima sebagian (Fisik: 2). Alasan: barng kurang bagus', '2026-05-11 11:28:14', NULL, 0),
(267, NULL, 85, 20, 'P1', 0, 0, '2026-05-11 11:31:56', '123', 'Baik', 'diterima', 'barangnnya juga tidak bgaus gays', NULL, NULL, 0),
(268, NULL, 85, 20, 'P1', 2, 2, '2026-05-11 11:32:32', '123', 'Baik', 'diterima', 'Terima sebagian (Fisik: 2). Alasan: adadeh', '2026-05-11 11:33:01', NULL, 0),
(269, NULL, 85, 20, 'P1', 0, 0, '2026-05-11 11:33:49', '123', 'Baik', 'diterima', 'aduh bang', NULL, NULL, 0),
(270, NULL, 85, 20, 'P1', 4, 0, '2026-05-11 13:28:22', '123', 'Baik', 'dikirim', 'Kirim kekurangan/ulang (4 unit)', NULL, NULL, 0);
>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e

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
<<<<<<< HEAD
=======

--
-- Dumping data for table `gudang_persediaan`
--

INSERT INTO `gudang_persediaan` (`id_persediaan`, `nama_barang`, `satuan`, `stok_awal`, `pengajuan_barang`, `pemakaian_barang`, `tgl_input`) VALUES
(2, 'EWTEYasfasg', 'SDGSgcgcg', 2354, 235, 235, '2026-04-06 05:29:10');
>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e

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
  `keterangan` text,
  `status_kunci` int DEFAULT '0'
<<<<<<< HEAD
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan_sistem`
--

CREATE TABLE `pengaturan_sistem` (
  `nama_pengaturan` varchar(50) NOT NULL,
  `nilai_pengaturan` tinyint(1) DEFAULT NULL
=======
>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengaturan_sistem`
--

<<<<<<< HEAD
=======
INSERT INTO `pemakaian_lab` (`id_pemakaian`, `id_distribusi`, `kode_distribusi`, `id_praktek`, `id_lab`, `jumlah_pakai`, `tgl_pakai`, `keterangan`, `status_kunci`) VALUES
(47, 0, 'P2', 86, 20, 5, '2026-05-11 13:12:34', NULL, 1),
(48, 0, 'P1', 85, 20, 4, '2026-05-11 13:12:40', NULL, 1),
(49, 0, 'P1', 85, 20, 3, '2026-05-11 13:17:10', NULL, 1);

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

>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e
INSERT INTO `pengaturan_sistem` (`nama_pengaturan`, `nilai_pengaturan`) VALUES
('status_input_stok', 1);

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

--
-- Dumping data for table `permintaan_bahan`
--

INSERT INTO `permintaan_bahan` (`id_permintaan`, `id_barang`, `id_user`, `stok_saat_ini`, `tgl_permintaan`, `status`) VALUES
<<<<<<< HEAD
(53, 99, 11, 20, '2026-05-12 12:37:28', 'approved'),
(54, 99, 11, 10, '2026-05-12 12:37:43', 'approved'),
(55, 99, 11, 20, '2026-05-12 12:42:38', 'approved'),
(56, 99, 11, 30, '2026-05-12 12:42:48', 'approved');
=======
(37, 86, 11, 10, '2026-05-11 09:47:14', 'approved');
>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e

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

--
-- Dumping data for table `permintaan_barang`
--

INSERT INTO `permintaan_barang` (`id_permintaan`, `id_kepala`, `id_barang`, `stok_awal`, `spesifikasi`, `jumlah_minta`, `kondisi`, `jumlah_disetujui`, `jumlah_terpenuhi`, `tgl_permintaan`, `tgl_proses`, `status`, `keterangan_kepala`, `catatan_admin`) VALUES
<<<<<<< HEAD
(214, 11, 99, 80, 'ss', 23, 'Baik', 23, 0, '2026-05-12 13:45:23', '2026-05-12 13:51:50', 'disetujui', '', NULL);
=======
(205, 11, 87, 0, 'Kawat baja', 20, 'Baik', 20, 0, '2026-05-11 10:48:40', '2026-05-11 10:49:20', 'disetujui', '', NULL),
(206, 11, 86, 10, '123', 6, 'Baik', 6, 0, '2026-05-11 10:48:50', '2026-05-11 10:49:27', 'disetujui', '', NULL),
(207, 11, 85, 0, '123', 10, 'Baik', 10, 0, '2026-05-11 10:58:35', '2026-05-11 10:58:54', 'disetujui', '', NULL),
(208, 11, 85, 10, '123', 10, 'Baik', 10, 0, '2026-05-11 11:16:48', '2026-05-11 11:17:09', 'disetujui', '', NULL);
>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','admin-acc','kepala_lab') DEFAULT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `nip` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `nama_lengkap`, `nip`) VALUES
(17, 'ibudiann', '$2y$10$RIYks5pewj1.RNT/iWq3hOI8MK6Jl7Q8anBd0/r7TkIgNCHokr0o6', 'admin', 'Ibu Dian', '10101010101010101'),
(18, 'iqra', '$2y$10$pPEHPODE4dJd6mOI8gdSH.WxBeaHtc.U9oZcGqGK3lZ5KAP1v1CES', 'admin-acc', 'Muh iqra', '08218');

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
<<<<<<< HEAD
  MODIFY `id_praktek` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;
=======
  MODIFY `id_praktek` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;
>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e

--
-- AUTO_INCREMENT for table `distribusi_history`
--
ALTER TABLE `distribusi_history`
  MODIFY `id_history` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `distribusi_history`
--
ALTER TABLE `distribusi_history`
  MODIFY `id_history` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `distribusi_lab`
--
ALTER TABLE `distribusi_lab`
<<<<<<< HEAD
  MODIFY `id_distribusi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=299;
=======
  MODIFY `id_distribusi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=271;
>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e

--
-- AUTO_INCREMENT for table `gudang_persediaan`
--
ALTER TABLE `gudang_persediaan`
  MODIFY `id_persediaan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
<<<<<<< HEAD
  MODIFY `id_pemakaian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;
=======
  MODIFY `id_pemakaian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;
>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e

--
-- AUTO_INCREMENT for table `permintaan_bahan`
--
ALTER TABLE `permintaan_bahan`
<<<<<<< HEAD
  MODIFY `id_permintaan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;
=======
  MODIFY `id_permintaan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;
>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e

--
-- AUTO_INCREMENT for table `permintaan_barang`
--
ALTER TABLE `permintaan_barang`
<<<<<<< HEAD
  MODIFY `id_permintaan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=215;
=======
  MODIFY `id_permintaan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=209;
>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

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
