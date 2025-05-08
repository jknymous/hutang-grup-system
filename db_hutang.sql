-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 08 Bulan Mei 2025 pada 19.17
-- Versi server: 10.4.22-MariaDB
-- Versi PHP: 8.1.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_hutang`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `netting_result`
--

CREATE TABLE `netting_result` (
  `id` int(11) NOT NULL,
  `pengutang` varchar(50) NOT NULL,
  `penerima` varchar(50) NOT NULL,
  `jumlah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `pengutang` varchar(50) NOT NULL,
  `penerima` varchar(50) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `tipe` enum('hutang','bayar') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `tanggal`, `pengutang`, `penerima`, `jumlah`, `tipe`) VALUES
(13, '2025-04-11', 'Viktor', 'Mawang', 14000, 'hutang'),
(14, '2025-04-11', 'Erik', 'Cinok', 146000, 'hutang'),
(15, '2025-04-11', 'Jerry', 'Cinok', 73000, 'hutang'),
(16, '2025-04-11', 'Agus', 'Cinok', 2000, 'hutang'),
(17, '2025-04-11', 'Rizz', 'Erik', 158000, 'hutang'),
(18, '2025-04-11', 'Leo', 'Erik', 47000, 'hutang'),
(19, '2025-04-11', 'Cinok', 'Rizz', 37000, 'hutang'),
(20, '2025-04-11', 'Rizz', 'Agus', 14000, 'hutang'),
(21, '2025-04-11', 'Erik', 'Agus', 20000, 'hutang'),
(22, '2025-05-08', 'Cinok', 'Leo', 100000, 'hutang'),
(23, '2025-05-08', 'Leo', 'Cinok', 50000, 'hutang'),
(24, '2025-05-08', 'Cinok', 'Leo', 20000, 'hutang');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$eLslVzMuIY4U0n6rCA030O.Wj6HqTjQGevdpe.P.UIUOBmANtFKja'),
(2, 'agusyatno', '$2y$10$OMtzMcoWE9aNJSn3kvXMue4M8Bg7GRfEqL0xSN.RRmS5fwp2CXKOu');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `netting_result`
--
ALTER TABLE `netting_result`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `netting_result`
--
ALTER TABLE `netting_result`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
