-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2025 at 04:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sportory`
--

-- --------------------------------------------------------

--
-- Table structure for table `alat_olahraga`
--

CREATE TABLE `alat_olahraga` (
  `id` int(11) NOT NULL,
  `nama_alat` varchar(100) NOT NULL,
  `kategori` enum('Bola','Raket','Matras','Jaring','Lainnya') NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 0,
  `jumlah_rusak` int(11) NOT NULL DEFAULT 0,
  `lokasi_penyimpanan` varchar(100) NOT NULL,
  `tanggal_masuk` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alat_olahraga`
--

INSERT INTO `alat_olahraga` (`id`, `nama_alat`, `kategori`, `jumlah`, `jumlah_rusak`, `lokasi_penyimpanan`, `tanggal_masuk`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 'Bola Basket', 'Bola', 15, 0, 'Gudang A', '2024-01-15', 'Bola basket ukuran standar', '2025-11-08 09:09:18', '2025-11-08 09:09:18'),
(2, 'Bola Voli', 'Bola', 12, 0, 'Gudang A', '2024-01-20', 'Bola Voli Arcane', '2025-11-08 09:09:18', '2025-11-10 11:08:28'),
(3, 'Raket Badminton', 'Raket', 20, 0, 'Gudang B', '2024-02-10', 'Raket badminton berbagai ukuran', '2025-11-08 09:09:18', '2025-11-08 09:09:18'),
(5, 'Net Voli', 'Jaring', 2, 1, 'Gudang A', '2024-03-05', 'Net untuk lapangan voli', '2025-11-08 09:09:18', '2025-11-12 14:49:20'),
(7, 'Bola 8 Pool', 'Bola', 14, 4, 'GSG', '2025-11-03', 'Bola billiard', '2025-11-08 09:47:01', '2025-11-12 14:47:41'),
(8, 'Tongkat Billiard', 'Lainnya', 4, 0, 'Gudang Utama', '2025-11-08', 'Pengadaan berdasarkan rekomendasi dari budianakstaff', '2025-11-08 10:12:01', '2025-11-08 10:12:01'),
(9, 'Meja Billiard', 'Lainnya', 2, 0, 'Gudang Utama', '2025-11-10', 'Pengadaan berdasarkan rekomendasi dari budianakstaff', '2025-11-10 11:10:57', '2025-11-10 11:10:57'),
(11, 'Net Padel', 'Jaring', 2, 0, 'Gudang Utama', '2025-11-12', 'Pengadaan berdasarkan rekomendasi dari dilan1990', '2025-11-12 15:04:13', '2025-11-12 15:04:13');

-- --------------------------------------------------------

--
-- Table structure for table `rekomendasi_alat`
--

CREATE TABLE `rekomendasi_alat` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_alat` varchar(100) NOT NULL,
  `kategori` enum('Bola','Raket','Matras','Jaring','Lainnya') NOT NULL,
  `jumlah_diminta` int(11) NOT NULL DEFAULT 0,
  `alasan` text NOT NULL,
  `perkiraan_harga` decimal(15,2) DEFAULT NULL,
  `prioritas` enum('Rendah','Sedang','Tinggi') NOT NULL DEFAULT 'Sedang',
  `status` enum('Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
  `catatan_admin` text DEFAULT NULL,
  `tanggal_disetujui` datetime DEFAULT NULL,
  `disetujui_oleh` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rekomendasi_alat`
--

INSERT INTO `rekomendasi_alat` (`id`, `user_id`, `nama_alat`, `kategori`, `jumlah_diminta`, `alasan`, `perkiraan_harga`, `prioritas`, `status`, `catatan_admin`, `tanggal_disetujui`, `disetujui_oleh`, `created_at`, `updated_at`) VALUES
(2, 2, 'Tongkat Billiard', 'Lainnya', 4, 'untuk bermain billiard', 500000.00, 'Tinggi', 'Disetujui', 'oke deh', '2025-11-08 17:12:01', 1, '2025-11-08 10:10:22', '2025-11-08 10:12:01'),
(3, 2, 'Bola bekel', 'Bola', 5, 'buat gabut pak', 20000.00, 'Rendah', 'Ditolak', 'elek', '2025-11-08 18:20:29', 1, '2025-11-08 11:18:47', '2025-11-08 11:20:29'),
(4, 2, 'Meja Billiard', 'Lainnya', 2, 'untuk bermain billiard', 1000000.00, 'Tinggi', 'Disetujui', 'oke bang', '2025-11-10 18:10:57', 1, '2025-11-10 11:09:53', '2025-11-10 11:10:57'),
(6, 2, 'Raket Padel', 'Raket', 4, 'untuk bermain padel', 1000000.00, 'Sedang', 'Disetujui', 'culture abiez', '2025-11-12 21:26:05', 1, '2025-11-12 14:25:08', '2025-11-12 14:26:05'),
(8, 3, 'Net Padel', 'Jaring', 2, 'untuk main padel\r\n', 2500000.00, 'Tinggi', 'Disetujui', 'okee', '2025-11-12 22:04:13', 4, '2025-11-12 15:03:11', '2025-11-12 15:04:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', '2025-11-08 09:09:18'),
(2, 'budi', '$2y$10$r0uoY7qrHRxtnhmbZTVGne3MScIu8hDLjKs0sjz4bf1aar/y/GTpS', 'budi01gaming', 'user', '2025-11-08 09:22:48'),
(3, 'dilan', '$2y$10$7ZB8WLcbiDQM1N3zjFDlruk0DM6rnWQP2JIOtCQZhQwW2kHr309He', 'dilan1990', 'user', '2025-11-12 14:27:35'),
(4, 'mandor', '$2y$10$KZ1uFCYQgJ2khRyR29uJDuFkZ4tnht20wwIDzgoy2/U0hHjJgYewS', 'Moderator', 'admin', '2025-11-12 14:55:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alat_olahraga`
--
ALTER TABLE `alat_olahraga`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rekomendasi_alat`
--
ALTER TABLE `rekomendasi_alat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `disetujui_oleh` (`disetujui_oleh`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alat_olahraga`
--
ALTER TABLE `alat_olahraga`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `rekomendasi_alat`
--
ALTER TABLE `rekomendasi_alat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rekomendasi_alat`
--
ALTER TABLE `rekomendasi_alat`
  ADD CONSTRAINT `rekomendasi_alat_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rekomendasi_alat_ibfk_2` FOREIGN KEY (`disetujui_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
