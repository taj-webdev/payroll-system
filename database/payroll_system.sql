-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 06, 2026 at 04:07 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `payroll_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` int NOT NULL,
  `karyawan_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('Hadir','Izin','Sakit','Alpha') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_pulang` time DEFAULT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id`, `karyawan_id`, `tanggal`, `status`, `jam_masuk`, `jam_pulang`, `keterangan`, `created_at`) VALUES
(1, 9, '2026-01-01', 'Hadir', '08:00:00', '17:00:00', 'PEKERJA HEBAT', '2026-01-02 09:03:30'),
(2, 10, '2026-01-01', 'Hadir', '07:05:00', '17:05:00', 'KARYAWAN TELADAN', '2026-01-02 09:04:59'),
(5, 4, '2026-01-01', 'Izin', NULL, NULL, 'IZIN TAHUN BARU', '2026-01-02 09:07:37'),
(6, 7, '2026-01-01', 'Sakit', NULL, NULL, 'DEMAM', '2026-01-02 09:07:53'),
(8, 5, '2026-01-01', 'Alpha', NULL, NULL, 'HILANG DARI BUMI', '2026-01-02 09:08:47'),
(9, 9, '2026-01-02', 'Hadir', '07:10:00', '17:10:00', '', '2026-01-02 09:09:17'),
(10, 10, '2026-01-02', 'Hadir', '07:09:00', '17:10:00', '', '2026-01-02 09:09:41'),
(11, 4, '2026-01-02', 'Hadir', '08:00:00', '17:00:00', '', '2026-01-02 09:10:13'),
(12, 7, '2026-01-02', 'Hadir', '07:15:00', '17:15:00', '', '2026-01-02 09:10:44'),
(13, 5, '2026-01-02', 'Hadir', '07:15:00', '18:15:00', '', '2026-01-02 09:11:05'),
(14, 9, '2026-01-03', 'Hadir', '07:30:00', '17:00:00', 'OK ONPH. KIRK', '2026-01-05 03:37:46'),
(15, 10, '2026-01-03', 'Hadir', '07:40:00', '17:00:00', 'OK RORA. DEMONKITE', '2026-01-05 03:40:24'),
(16, 4, '2026-01-03', 'Hadir', '07:30:00', '17:00:00', 'OK LOID FORGER', '2026-01-05 03:42:23'),
(17, 7, '2026-01-03', 'Hadir', '07:30:00', '17:00:00', 'OK. FLCN KYLETZY', '2026-01-05 03:43:19'),
(18, 5, '2026-01-03', 'Hadir', '06:50:00', '17:00:00', 'OK TLPH. SANFORD', '2026-01-05 03:44:17'),
(19, 9, '2026-01-05', 'Izin', NULL, NULL, 'IZIN M7', '2026-01-05 03:45:10'),
(20, 10, '2026-01-05', 'Izin', NULL, NULL, 'IZIN M7', '2026-01-05 03:45:22'),
(21, 4, '2026-01-05', 'Sakit', NULL, NULL, 'DEMAM ABIS PERANG BOSS', '2026-01-05 03:47:38'),
(22, 7, '2026-01-05', 'Izin', NULL, NULL, 'IZIN M7', '2026-01-05 03:47:50'),
(23, 5, '2026-01-05', 'Izin', NULL, NULL, 'IZIN M7', '2026-01-05 03:48:06'),
(24, 9, '2026-01-06', 'Hadir', '08:00:00', '22:00:00', '', '2026-01-06 07:19:48'),
(25, 10, '2026-01-06', 'Hadir', '07:20:00', '22:20:00', '', '2026-01-06 07:20:49'),
(26, 4, '2026-01-06', 'Hadir', '07:30:00', '22:30:00', '', '2026-01-06 07:21:24'),
(27, 7, '2026-01-06', 'Hadir', '07:15:00', '23:00:00', '', '2026-01-06 07:22:01'),
(28, 5, '2026-01-06', 'Hadir', '07:00:00', '22:30:00', '', '2026-01-06 07:22:32'),
(29, 9, '2026-01-07', 'Hadir', '07:40:00', '17:40:00', '', '2026-01-13 04:39:29'),
(30, 10, '2026-01-07', 'Hadir', '07:30:00', '17:30:00', '', '2026-01-13 04:39:53'),
(31, 4, '2026-01-07', 'Hadir', '06:40:00', '17:15:00', '', '2026-01-13 04:40:18'),
(32, 7, '2026-01-07', 'Hadir', '06:55:00', '17:25:00', '', '2026-01-13 04:40:45'),
(33, 5, '2026-01-07', 'Hadir', '07:45:00', '18:10:00', '', '2026-01-13 04:41:05'),
(34, 12, '2026-01-07', 'Hadir', '06:50:00', '17:30:00', '', '2026-01-13 04:41:28'),
(36, 12, '2026-01-08', 'Hadir', '07:25:00', '17:25:00', '', '2026-01-15 02:25:34'),
(37, 13, '2026-01-08', 'Hadir', '07:30:00', '17:20:00', '', '2026-01-15 02:25:55'),
(38, 9, '2026-01-08', 'Hadir', NULL, NULL, '', '2026-01-20 01:57:30'),
(39, 14, '2026-01-08', 'Hadir', NULL, NULL, '', '2026-01-20 01:57:45'),
(40, 10, '2026-01-08', 'Hadir', NULL, NULL, '', '2026-01-20 01:57:56'),
(41, 4, '2026-01-08', 'Hadir', NULL, NULL, '', '2026-01-20 01:58:08'),
(42, 7, '2026-01-08', 'Hadir', NULL, NULL, '', '2026-01-20 01:58:17'),
(43, 5, '2026-01-08', 'Hadir', NULL, NULL, '', '2026-01-20 01:58:26');

-- --------------------------------------------------------

--
-- Table structure for table `divisi`
--

CREATE TABLE `divisi` (
  `id` int NOT NULL,
  `nama_divisi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `divisi`
--

INSERT INTO `divisi` (`id`, `nama_divisi`, `keterangan`, `created_at`) VALUES
(14, 'Human Resources', '-', '2026-01-02 05:34:13'),
(15, 'General Affairs', '-', '2026-01-02 05:34:48'),
(16, 'Sales', '-', '2026-01-02 05:35:06'),
(17, 'Marketing', '-', '2026-01-02 05:35:20'),
(18, 'Branding', '-', '2026-01-02 05:35:51'),
(19, 'Production', '-', '2026-01-02 05:36:01'),
(20, 'Accounting', '-', '2026-01-02 05:36:23'),
(21, 'Finance', '-', '2026-01-02 05:36:30'),
(22, 'Purchasing', '-', '2026-01-02 05:36:47'),
(23, 'Research and Development', '-', '2026-01-02 05:37:31'),
(24, 'Corporate Social Responsibility', '-', '2026-01-02 05:39:03'),
(25, 'Information and Technology', '-', '2026-01-02 05:39:25'),
(26, 'Public Relation', '-', '2026-01-02 05:40:32'),
(27, 'Quality Control', '-', '2026-01-02 05:42:46'),
(28, 'Health, Security, and Environment', '-', '2026-01-02 05:43:31');

-- --------------------------------------------------------

--
-- Table structure for table `gaji_bulan`
--

CREATE TABLE `gaji_bulan` (
  `id` int NOT NULL,
  `gaji_tahun_id` int NOT NULL,
  `bulan` tinyint NOT NULL COMMENT '1-12',
  `status` enum('draft','open','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gaji_bulan`
--

INSERT INTO `gaji_bulan` (`id`, `gaji_tahun_id`, `bulan`, `status`, `catatan`, `created_at`) VALUES
(1, 1, 1, 'closed', 'CLOSED - JANUARI 2026', '2026-01-06 05:59:34'),
(2, 1, 2, 'open', 'OPEN - FEBRUARI 2026', '2026-01-06 05:59:41'),
(3, 1, 3, 'draft', '', '2026-01-06 05:59:46'),
(4, 1, 4, 'draft', NULL, '2026-01-06 05:59:59'),
(5, 1, 5, 'draft', NULL, '2026-01-06 06:00:05'),
(6, 1, 6, 'draft', NULL, '2026-01-06 06:00:09'),
(7, 1, 7, 'draft', NULL, '2026-01-13 05:16:23'),
(8, 1, 8, 'draft', NULL, '2026-01-14 06:10:40'),
(10, 1, 9, 'draft', NULL, '2026-01-14 07:21:59'),
(11, 1, 10, 'draft', NULL, '2026-01-14 07:22:08');

-- --------------------------------------------------------

--
-- Table structure for table `gaji_detail`
--

CREATE TABLE `gaji_detail` (
  `id` int NOT NULL,
  `gaji_bulan_id` int NOT NULL,
  `karyawan_id` int NOT NULL,
  `gaji_pokok` decimal(12,2) NOT NULL,
  `total_tunjangan` decimal(12,2) DEFAULT '0.00',
  `total_lembur` decimal(12,2) DEFAULT '0.00',
  `total_potongan` decimal(12,2) DEFAULT '0.00',
  `gaji_bersih` decimal(12,2) NOT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `approved_by_manager` int DEFAULT NULL,
  `approved_by_direktur` int DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gaji_detail`
--

INSERT INTO `gaji_detail` (`id`, `gaji_bulan_id`, `karyawan_id`, `gaji_pokok`, `total_tunjangan`, `total_lembur`, `total_potongan`, `gaji_bersih`, `status`, `approved_by_manager`, `approved_by_direktur`, `approved_at`, `catatan`, `created_at`) VALUES
(1, 1, 9, 6000000.00, 4000000.00, 500000.00, 50000.00, 10450000.00, 'approved', 3, NULL, '2026-01-14 05:36:21', 'KIRK - GAJI JANUARI 2026', '2026-01-06 07:07:04'),
(2, 1, 10, 6000000.00, 4000000.00, 600000.00, 50000.00, 10550000.00, 'approved', 3, NULL, '2026-01-14 05:41:53', 'DEMONKITE - GAJI JANUARI 2026', '2026-01-06 07:08:32'),
(5, 1, 4, 6500000.00, 5000000.00, 1400000.00, 50000.00, 12850000.00, 'approved', 3, NULL, '2026-01-14 06:25:28', 'LOORAND - GAJI JANUARI 2026', '2026-01-06 07:12:28'),
(6, 1, 7, 6500000.00, 4500000.00, 1050000.00, 50000.00, 12000000.00, 'approved', 3, NULL, '2026-01-14 06:25:31', 'KYLETZY - GAJI JANUARI 2026', '2026-01-06 07:14:13'),
(7, 1, 5, 5000000.00, 4500000.00, 900000.00, 100000.00, 10300000.00, 'approved', 3, NULL, '2026-01-14 06:25:34', 'SANFORD - GAJI JANUARI 2026', '2026-01-06 07:17:17'),
(11, 1, 12, 6500000.00, 6000000.00, 1500000.00, 0.00, 14000000.00, 'approved', NULL, 2, '2026-01-14 07:37:24', 'TERTU - GAJI JANUARI 2026', '2026-01-14 06:24:40'),
(13, 1, 13, 5500000.00, 5500000.00, 750000.00, 0.00, 11750000.00, 'approved', NULL, 2, '2026-01-15 02:31:23', 'BENNYQT - GAJI JANUARI 2026', '2026-01-15 02:29:47'),
(14, 1, 14, 5500000.00, 6000000.00, 600000.00, 0.00, 12100000.00, 'approved', NULL, 2, '2026-01-20 02:34:12', 'INNOCENT - GAJI JANUARI 2026', '2026-01-20 02:01:19'),
(15, 2, 13, 5500000.00, 5500000.00, 0.00, 0.00, 11000000.00, 'pending', NULL, NULL, NULL, 'BENNYQT - GAJI FEBRUARI 2026', '2026-02-04 09:10:52'),
(16, 2, 9, 6000000.00, 4000000.00, 0.00, 0.00, 10000000.00, 'pending', NULL, NULL, NULL, 'KIRK - GAJI FEBRUARI 2026', '2026-02-04 09:11:31');

-- --------------------------------------------------------

--
-- Table structure for table `gaji_tahun`
--

CREATE TABLE `gaji_tahun` (
  `id` int NOT NULL,
  `tahun` year NOT NULL,
  `status` enum('draft','locked') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gaji_tahun`
--

INSERT INTO `gaji_tahun` (`id`, `tahun`, `status`, `created_at`) VALUES
(1, '2026', 'draft', '2026-01-06 05:40:21');

-- --------------------------------------------------------

--
-- Table structure for table `karyawan`
--

CREATE TABLE `karyawan` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `divisi_id` int NOT NULL,
  `nik` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_ktp` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_lengkap` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tempat_lahir` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `jenis_kelamin` enum('Laki-laki','Perempuan') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `agama` enum('Kristen','Islam','Katholik','Buddha','Konghucu','Hindu') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Lajang','Menikah','Janda','Duda') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `karyawan`
--

INSERT INTO `karyawan` (`id`, `user_id`, `divisi_id`, `nik`, `no_ktp`, `nama_lengkap`, `tempat_lahir`, `tanggal_lahir`, `alamat`, `jenis_kelamin`, `agama`, `status`, `foto`, `created_at`) VALUES
(4, 4, 28, '1501', '6201010404', 'Loorand Spoofy', 'Westalis', '1998-07-25', 'Jl. Yos Sudarso 15 No. 15', 'Laki-laki', 'Katholik', 'Menikah', 'karyawan_1767334677.jpg', '2026-01-02 06:17:57'),
(5, 5, 27, '1502', '620202010', 'Sanford Marin Vinuya', 'Buntok', '2006-06-24', 'Jl. RTA Milono Km. 7 No. 150', 'Laki-laki', 'Kristen', 'Lajang', 'karyawan_1767336091.png', '2026-01-02 06:41:31'),
(7, 6, 25, '1503', '6230304040', 'Michael Angle Sayson', 'Palangka Raya', '2005-08-19', 'Jl. Rajawali', 'Laki-laki', 'Katholik', 'Menikah', 'karyawan_1767336620.png', '2026-01-02 06:50:20'),
(9, 7, 20, '1504', '6250506060', 'Jann Kirk Solcruz Gutierrez', 'Ostania', '2006-02-08', 'Jl. Hiu Putih Raya', 'Laki-laki', 'Buddha', 'Menikah', 'karyawan_1767337197.png', '2026-01-02 06:59:57'),
(10, 8, 24, '1505', '6260601010', 'Jonard Cedrix Caranto', 'Manila', '2003-01-10', 'Jl. Manduhara', 'Laki-laki', 'Katholik', 'Lajang', 'karyawan_1767337279.png', '2026-01-02 07:01:19'),
(12, 9, 25, '1506', '620405060780', 'Tertu Akikkuti Jordan', 'Berlin', '1998-09-25', 'Jl. Menteng 17', 'Laki-laki', 'Kristen', 'Menikah', 'karyawan_1768278864.jpg', '2026-01-13 04:34:24'),
(13, 10, 23, '1507', '6290904040', 'Frederic Benedict Gonzales', 'Hamburg', '2001-09-10', 'Jl. Matal', 'Laki-laki', 'Katholik', 'Menikah', 'karyawan_1768443878.png', '2026-01-15 02:24:38'),
(14, 11, 21, '1508', '6250508080', 'John Vincent Banal', 'Madrid', '2004-05-12', 'Jl. Adonis Samad', 'Laki-laki', 'Katholik', 'Menikah', 'karyawan_1768806512.png', '2026-01-19 07:08:32');

-- --------------------------------------------------------

--
-- Table structure for table `lembur`
--

CREATE TABLE `lembur` (
  `id` int NOT NULL,
  `karyawan_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `jumlah_jam` int NOT NULL,
  `tarif_per_jam` decimal(12,2) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lembur`
--

INSERT INTO `lembur` (`id`, `karyawan_id`, `tanggal`, `jumlah_jam`, `tarif_per_jam`, `total`, `keterangan`, `created_at`) VALUES
(1, 9, '2026-01-06', 5, 100000.00, 500000.00, 'LEMBUR GANTI TANGGAL 5 JANUARI 2026', '2026-01-05 03:59:04'),
(2, 10, '2026-01-06', 6, 100000.00, 600000.00, 'LEMBUR GANTI TANGGAL 5 JANUARI 2026', '2026-01-05 04:03:08'),
(3, 4, '2026-01-06', 7, 200000.00, 1400000.00, 'LEMBUR GANTI TANGGAL 5 JANUARI 2026', '2026-01-05 04:04:33'),
(6, 7, '2026-01-06', 7, 150000.00, 1050000.00, 'LEMBUR GANTI TANGGAL 5 JANUARI 2026', '2026-01-05 04:06:16'),
(7, 5, '2026-01-06', 6, 150000.00, 900000.00, 'LEMBUR GANTI TANGGAL 5 JANUARI 2026', '2026-01-05 04:06:29'),
(8, 12, '2026-01-14', 5, 300000.00, 1500000.00, 'LEMBUR BOSS GANTI CUTI NATAL WKWKWK', '2026-01-13 05:13:10'),
(10, 13, '2026-01-15', 5, 150000.00, 750000.00, 'LEMBUR NEWBIE', '2026-01-15 02:28:39'),
(11, 14, '2026-01-09', 4, 150000.00, 600000.00, 'NEWBIE LEMBUR', '2026-01-20 02:00:15');

-- --------------------------------------------------------

--
-- Table structure for table `potongan`
--

CREATE TABLE `potongan` (
  `id` int NOT NULL,
  `karyawan_id` int DEFAULT NULL,
  `divisi_id` int DEFAULT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nominal` decimal(12,2) NOT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `potongan`
--

INSERT INTO `potongan` (`id`, `karyawan_id`, `divisi_id`, `nama`, `nominal`, `keterangan`, `created_at`) VALUES
(1, 9, 20, 'POTONGAN IZIN', 50000.00, 'POTONGAN IZIN 5 JANUARI 2026', '2026-01-05 04:08:42'),
(2, 10, 24, 'POTONGAN IZIN', 50000.00, 'POTONGAN IZIN 5 JANUARI 2026', '2026-01-05 04:09:48'),
(3, 4, 28, 'POTONGAN IZIN', 50000.00, 'POTONGAN IZIN 1 JANUARI 2025', '2026-01-05 04:10:55'),
(6, 7, 25, 'POTONGAN IZIN', 50000.00, 'POTONGAN IZIN 5 JANUARI 2026', '2026-01-05 04:14:22'),
(7, 5, 27, 'POTONGAN IZIN + ALPHA', 100000.00, 'ALPHA 1 JANUARI & IZIN 5 JANUARI 2026', '2026-01-05 04:14:49');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(3, 'admin_hrd'),
(1, 'direktur'),
(4, 'karyawan'),
(2, 'manager');

-- --------------------------------------------------------

--
-- Table structure for table `tunjangan`
--

CREATE TABLE `tunjangan` (
  `id` int NOT NULL,
  `karyawan_id` int DEFAULT NULL,
  `divisi_id` int DEFAULT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nominal` decimal(12,2) NOT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tunjangan`
--

INSERT INTO `tunjangan` (`id`, `karyawan_id`, `divisi_id`, `nama`, `nominal`, `keterangan`, `created_at`) VALUES
(1, 9, 20, 'TUNJANGAN ACCOUNTING', 4000000.00, 'TUNJANGAN 2026 - OK', '2026-01-05 03:49:51'),
(4, 10, 24, 'TUNJANGAN CSR', 4000000.00, 'TUNJANGAN 2026 - OK', '2026-01-05 03:54:58'),
(5, 4, 28, 'TUNJANGAN HSE', 5000000.00, 'TUNJANGAN 2026 - OK', '2026-01-05 03:56:04'),
(6, 7, 25, 'TUNJANGAN IT', 4500000.00, 'TUNJANGAN 2026 - OK', '2026-01-05 03:56:25'),
(7, 5, 27, 'TUNJANGAN QUALITY CONTROL', 4500000.00, 'TUNJANGAN 2026 - OK', '2026-01-05 03:57:12'),
(8, 12, 25, 'TUNJANGAN IT SEPUH', 6000000.00, 'TUNJANGAN 2026 - OK', '2026-01-13 04:46:08'),
(10, 13, 23, 'TUNJANGAN R&D', 5500000.00, 'TUNJANGAN 2026 - OK', '2026-01-15 02:28:14'),
(11, 14, 21, 'TUNJANGAN FINANCE', 6000000.00, 'TUNJANGAN 2026 - OK', '2026-01-20 01:59:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `role_id` int NOT NULL,
  `nama_lengkap` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `nama_lengkap`, `username`, `password`, `created_at`) VALUES
(1, 3, 'YURI BRIAR', 'yuri', '$2y$10$c6YkbIjnRxLeiQHPxtd84.5Z9URh7upqe.Ogj/RWJRO9qQTLP8iA2', '2025-12-31 07:21:59'),
(2, 1, 'LOID FORGER', 'loid', '$2y$10$WQJJ3rH.s8yIaM/fPOk.VuOF2WZd1JEnZxL50Ei9cqJieK8W1h6ka', '2025-12-31 07:24:30'),
(3, 2, 'YOR BRIAR', 'yor', '$2y$10$DpDzJRnQMDuf0/IWHgrg5eShBM30Lcz6wU8W5Xw26JMw2EHsYa/0O', '2025-12-31 07:24:55'),
(4, 4, 'LOORAND SPOOFY', 'loorand', '$2y$10$4ScN8RMoklb2NY1iWfXdXe7mxmXnuIBOWjaLc1ivxWMD6AqLOZplG', '2025-12-31 07:25:19'),
(5, 4, 'Sanford Marin Vinuya', 'sanford', '$2y$10$kBvV7EPh9HXd9fUX0dBWrOodiX1yvl0EU1Nycj2wY.roTKRHYdrP.', '2026-01-02 06:27:17'),
(6, 4, 'Michael Angelo Sayson', 'kyle', '$2y$10$rnDx5kk8ViXaQ1D5JAZ.3.k8Fk/QbDItcsvd1CP.URaddV3qqci3e', '2026-01-02 06:35:50'),
(7, 4, 'Jann Kirk Solcruz Gutierrez', 'kirk', '$2y$10$W1joUWu5bJmB8JbAAdOzT.vcxQdahzUdbthedxOu2VGFSXJKglVDG', '2026-01-02 06:36:24'),
(8, 4, 'Jonard Cedrix Caranto', 'demonkite', '$2y$10$79xcSOG36XJzh7CoiUfP9.sJGkGiZySu/cRmQg.04gtgKOrBGeob.', '2026-01-02 06:38:46'),
(9, 4, 'Tertu Akikkuti Jordan', 'kirito', '$2y$10$/zF.2uqCPMqdl9PWApMmO.n4c4b7NnnO5MeDyFdmyOBOCvmC5TJ6G', '2026-01-13 04:28:51'),
(10, 4, 'Frederic Benedict Gonzales', 'bennyqt', '$2y$10$mdb5oXLd/STs/nZhkIkJBuj4NCRBSD1YGx0acnPfmhvBRst9w3H3m', '2026-01-15 02:22:46'),
(11, 4, 'John Vincent Banal', 'innocent', '$2y$10$YcSMHyrtTYwXHLMLZg3zAOxYbCuLAUK1H1Wz7VkfpWQlaq/WgcBMy', '2026-01-19 07:06:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `karyawan_id` (`karyawan_id`);

--
-- Indexes for table `divisi`
--
ALTER TABLE `divisi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gaji_bulan`
--
ALTER TABLE `gaji_bulan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gaji_tahun_id` (`gaji_tahun_id`,`bulan`);

--
-- Indexes for table `gaji_detail`
--
ALTER TABLE `gaji_detail`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gaji_bulan_id` (`gaji_bulan_id`,`karyawan_id`),
  ADD KEY `karyawan_id` (`karyawan_id`),
  ADD KEY `approved_by_manager` (`approved_by_manager`),
  ADD KEY `approved_by_direktur` (`approved_by_direktur`);

--
-- Indexes for table `gaji_tahun`
--
ALTER TABLE `gaji_tahun`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tahun` (`tahun`);

--
-- Indexes for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD UNIQUE KEY `no_ktp` (`no_ktp`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `divisi_id` (`divisi_id`);

--
-- Indexes for table `lembur`
--
ALTER TABLE `lembur`
  ADD PRIMARY KEY (`id`),
  ADD KEY `karyawan_id` (`karyawan_id`);

--
-- Indexes for table `potongan`
--
ALTER TABLE `potongan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `karyawan_id` (`karyawan_id`),
  ADD KEY `divisi_id` (`divisi_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `tunjangan`
--
ALTER TABLE `tunjangan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `karyawan_id` (`karyawan_id`),
  ADD KEY `divisi_id` (`divisi_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `divisi`
--
ALTER TABLE `divisi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `gaji_bulan`
--
ALTER TABLE `gaji_bulan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `gaji_detail`
--
ALTER TABLE `gaji_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `gaji_tahun`
--
ALTER TABLE `gaji_tahun`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `lembur`
--
ALTER TABLE `lembur`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `potongan`
--
ALTER TABLE `potongan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tunjangan`
--
ALTER TABLE `tunjangan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`id`);

--
-- Constraints for table `gaji_bulan`
--
ALTER TABLE `gaji_bulan`
  ADD CONSTRAINT `gaji_bulan_ibfk_1` FOREIGN KEY (`gaji_tahun_id`) REFERENCES `gaji_tahun` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gaji_detail`
--
ALTER TABLE `gaji_detail`
  ADD CONSTRAINT `gaji_detail_ibfk_1` FOREIGN KEY (`gaji_bulan_id`) REFERENCES `gaji_bulan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gaji_detail_ibfk_2` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gaji_detail_ibfk_3` FOREIGN KEY (`approved_by_manager`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `gaji_detail_ibfk_4` FOREIGN KEY (`approved_by_direktur`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD CONSTRAINT `karyawan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `karyawan_ibfk_2` FOREIGN KEY (`divisi_id`) REFERENCES `divisi` (`id`);

--
-- Constraints for table `lembur`
--
ALTER TABLE `lembur`
  ADD CONSTRAINT `lembur_ibfk_1` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`id`);

--
-- Constraints for table `potongan`
--
ALTER TABLE `potongan`
  ADD CONSTRAINT `potongan_ibfk_1` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`id`),
  ADD CONSTRAINT `potongan_ibfk_2` FOREIGN KEY (`divisi_id`) REFERENCES `divisi` (`id`);

--
-- Constraints for table `tunjangan`
--
ALTER TABLE `tunjangan`
  ADD CONSTRAINT `tunjangan_ibfk_1` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`id`),
  ADD CONSTRAINT `tunjangan_ibfk_2` FOREIGN KEY (`divisi_id`) REFERENCES `divisi` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
