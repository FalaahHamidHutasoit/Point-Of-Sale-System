-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 21 Sep 2026 pada 04.00
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pos_ci4`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang`
--

CREATE TABLE `barang` (
  `id_barang` int(11) NOT NULL,
  `id_kategori` int(11) NOT NULL,
  `kode_barang` varchar(50) NOT NULL,
  `nama_barang` varchar(150) NOT NULL,
  `harga_beli` decimal(15,2) NOT NULL DEFAULT 0.00,
  `harga_jual` decimal(15,2) NOT NULL DEFAULT 0.00,
  `stok` int(11) NOT NULL DEFAULT 0,
  `satuan` varchar(30) DEFAULT 'pcs',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `barang`
--

INSERT INTO `barang` (`id_barang`, `id_kategori`, `kode_barang`, `nama_barang`, `harga_beli`, `harga_jual`, `stok`, `satuan`, `created_at`, `updated_at`) VALUES
(1, 1, 'BRG001', 'INDOMIE', 3000.00, 3500.00, 3, 'pcs', '2026-09-10 07:20:51', '2026-09-18 02:12:47'),
(3, 1, 'BRG002', 'MIE SEDAP', 3000.00, 3500.00, 3, 'pcs', '2026-09-10 07:22:29', '2026-09-18 02:12:47'),
(4, 1, 'BRG003', 'MIE SOTO', 3000.00, 3500.00, 2, 'pcs', '2026-09-10 07:53:02', '2026-09-18 02:12:47'),
(6, 4, 'BRG004', 'Air Mineral 600ml', 3000.00, 5000.00, 42, 'botol', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(10, 4, 'BRG005', 'Kopi Susu Botol', 10000.00, 15000.00, 18, 'botol', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(14, 4, 'BRG006', 'Teh Botol', 4000.00, 7000.00, 25, 'botol', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(18, 4, 'BRG007', 'Jus Jeruk', 7000.00, 10000.00, 15, 'botol', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(22, 5, 'BRG008', 'Roti Cokelat', 7000.00, 10000.00, 40, 'pcs', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(26, 5, 'BRG009', 'Roti Keju', 7000.00, 10000.00, 22, 'pcs', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(30, 5, 'BRG0010', 'Mie Goreng', 2500.00, 3500.00, 35, 'pcs', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(34, 5, 'BRG0011', 'Mie Sedap', 2200.00, 3000.00, 30, 'pcs', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(38, 6, 'BRG0012', 'Keripik Singkong', 6000.00, 9000.00, 25, 'pack', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(42, 6, 'BRG013', 'Keripik Kentang', 8000.00, 12000.00, 20, 'pack', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(46, 6, 'BRG014', 'Biskuit Cokelat', 7000.00, 10000.00, 28, 'pack', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(50, 6, 'BRG015', 'Wafer Vanilla', 5000.00, 8000.00, 32, 'pack', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(54, 7, 'BRG016', 'Sabun Cuci Tangan', 13000.00, 18500.00, 5, 'botol', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(58, 7, 'BRG017', 'Tisu Wajah', 8000.00, 11000.00, 30, 'pack', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(62, 7, 'BRG018', 'Sabun Mandi', 4000.00, 6500.00, 24, 'pcs', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(66, 8, 'BRG019', 'Beras 5 Kg', 65000.00, 75000.00, 12, 'pack', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(70, 8, 'BRG020', 'Minyak Goreng 1 Liter', 16000.00, 19000.00, 16, 'liter', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(74, 8, 'BRG021', 'Gula Pasir 1 Kg', 15000.00, 18000.00, 20, 'kg', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(78, 9, 'BRG022', 'Shampoo 170ml', 15000.00, 21000.00, 14, 'botol', '2026-09-18 09:22:10', '2026-09-18 09:22:10'),
(82, 9, 'BRG023', 'Pasta Gigi 120gr', 10000.00, 14000.00, 18, 'pcs', '2026-09-18 09:22:10', '2026-09-18 09:22:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `customer`
--

CREATE TABLE `customer` (
  `id_customer` int(11) NOT NULL,
  `nama_customer` varchar(100) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `customer`
--

INSERT INTO `customer` (`id_customer`, `nama_customer`, `no_telp`, `alamat`, `created_at`) VALUES
(2, 'RAJA IBLIS', '083840912821', 'JALAN MANA AJA YANG PENTING SAMPE', '2026-09-10 14:30:58');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_penjualan`
--

CREATE TABLE `detail_penjualan` (
  `id_detail` int(11) NOT NULL,
  `id_penjualan` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_penjualan`
--

INSERT INTO `detail_penjualan` (`id_detail`, `id_penjualan`, `id_barang`, `qty`, `harga`, `subtotal`) VALUES
(1, 1, 3, 50, 3500.00, 175000.00),
(2, 2, 1, 19, 3500.00, 66500.00),
(3, 3, 1, 99, 3500.00, 346500.00),
(4, 4, 1, 1, 3500.00, 3500.00),
(5, 5, 1, 1, 3500.00, 3500.00),
(6, 5, 3, 12, 3500.00, 42000.00),
(7, 6, 3, 19, 3500.00, 66500.00),
(8, 7, 1, 188, 3500.00, 658000.00),
(9, 8, 1, 3, 3500.00, 10500.00),
(10, 8, 3, 148, 3500.00, 518000.00),
(11, 9, 1, 5, 3500.00, 17500.00),
(12, 9, 3, 2, 3500.00, 7000.00),
(13, 10, 1, 1, 3500.00, 3500.00),
(14, 11, 3, 90, 3500.00, 315000.00),
(15, 12, 1, 30, 3500.00, 105000.00),
(16, 12, 3, 30, 3500.00, 105000.00),
(17, 12, 4, 50, 3500.00, 175000.00),
(18, 13, 1, 55, 3500.00, 192500.00),
(19, 13, 3, 49, 3500.00, 171500.00),
(20, 13, 4, 91, 3500.00, 318500.00),
(21, 14, 1, 5, 3500.00, 17500.00),
(22, 14, 3, 5, 3500.00, 17500.00),
(23, 14, 4, 56, 3500.00, 196000.00),
(24, 15, 1, 6, 3500.00, 21000.00),
(25, 15, 3, 2, 3500.00, 7000.00),
(26, 15, 4, 50, 3500.00, 175000.00),
(27, 16, 3, 1, 3500.00, 3500.00),
(28, 16, 4, 1, 3500.00, 3500.00),
(29, 16, 1, 1, 3500.00, 3500.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `created_at`) VALUES
(1, 'Makanan & Minuman', '2026-09-10 14:06:09'),
(3, 'Snack 2', '2026-09-16 09:24:03'),
(4, 'Minuman', '2026-09-18 09:22:09'),
(5, 'Makanan', '2026-09-18 09:22:09'),
(6, 'Snack', '2026-09-18 09:22:09'),
(7, 'Kebutuhan Rumah', '2026-09-18 09:22:09'),
(8, 'Sembako', '2026-09-18 09:22:09'),
(9, 'Perawatan Diri', '2026-09-18 09:22:09'),
(10, 'Minuman', '2026-09-18 09:22:09'),
(11, 'Makanan', '2026-09-18 09:22:09'),
(12, 'Snack', '2026-09-18 09:22:09'),
(13, 'Kebutuhan Rumah', '2026-09-18 09:22:09'),
(14, 'Sembako', '2026-09-18 09:22:09'),
(15, 'Perawatan Diri', '2026-09-18 09:22:09');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan`
--

CREATE TABLE `penjualan` (
  `id_penjualan` int(11) NOT NULL,
  `no_transaksi` varchar(30) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `id_customer` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `bayar` decimal(15,2) NOT NULL DEFAULT 0.00,
  `kembalian` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penjualan`
--

INSERT INTO `penjualan` (`id_penjualan`, `no_transaksi`, `tanggal`, `id_customer`, `id_user`, `total`, `bayar`, `kembalian`) VALUES
(1, 'TRX-20260910075414', '2026-09-10 07:54:14', NULL, 1, 175000.00, 175000.00, 0.00),
(2, 'TRX-20260910081327', '2026-09-10 08:13:27', 2, 1, 66500.00, 70000.00, 3500.00),
(3, 'TRX-20260910091626', '2026-09-10 09:16:26', 2, 1, 346500.00, 350000.00, 3500.00),
(4, 'TRX-20260911042022', '2026-09-11 04:20:22', NULL, 2, 3500.00, 5000.00, 1500.00),
(5, 'TRX-20260916012754', '2026-09-16 01:27:54', 2, 2, 45500.00, 50000.00, 4500.00),
(6, 'TRX-20260916012823', '2026-09-16 01:28:23', 2, 2, 66500.00, 100000.00, 33500.00),
(7, 'TRX-20260916014451', '2026-09-16 01:44:51', NULL, 2, 658000.00, 700000.00, 42000.00),
(8, 'TRX-20260916014526', '2026-09-16 01:45:26', 2, 2, 528500.00, 530000.00, 1500.00),
(9, 'TRX-20260916014629', '2026-09-16 01:46:29', NULL, 2, 24500.00, 50000.00, 25500.00),
(10, 'TRX-20260916014654', '2026-09-16 01:46:54', 2, 2, 3500.00, 5000.00, 1500.00),
(11, 'TRX-20260916070332', '2026-09-16 07:03:32', NULL, 2, 315000.00, 350000.00, 35000.00),
(12, 'TRX-20260916070428', '2026-09-16 07:04:28', NULL, 2, 385000.00, 400000.00, 15000.00),
(13, 'TRX-20260916070539', '2026-09-16 07:05:39', 2, 2, 682500.00, 700000.00, 17500.00),
(14, 'TRX-20260917012730', '2026-09-17 01:27:30', NULL, 2, 231000.00, 250000.00, 19000.00),
(15, 'TRX-20260917013506', '2026-09-17 01:35:06', 2, 2, 203000.00, 210000.00, 7000.00),
(16, 'TRX-20260918021247', '2026-09-18 02:12:47', NULL, 2, 10500.00, 14983.00, 4483.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` int(11) NOT NULL,
  `nama_supplier` varchar(100) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `nama_supplier`, `no_telp`, `alamat`, `created_at`) VALUES
(1, 'Raja', '023949324902', 'JALAN MANA SAJA ', '2026-09-10 14:47:00'),
(2, 'KING', '024923040924', 'JALAN APA SAJA ', '2026-09-10 14:47:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','kasir') DEFAULT 'kasir',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$L1l0bGO6HfXHoKWK..zNEuGlyR5Ias4LEXMMGFZxe4kLFcBwD8p/C', 'Administrator', 'admin', '2026-09-10 13:29:21'),
(2, 'kasir', '$2y$12$pcGZtR2ksBC0LYwE7Jqjk.GLP1gEBXEc7WveaL64Qv3XRRfbtx5ZK', 'Kasir', 'kasir', '2026-09-11 10:48:41');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`),
  ADD KEY `fk_barang_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id_customer`);

--
-- Indeks untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `fk_detail_penjualan` (`id_penjualan`),
  ADD KEY `fk_detail_barang` (`id_barang`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id_penjualan`),
  ADD UNIQUE KEY `no_transaksi` (`no_transaksi`),
  ADD KEY `fk_penjualan_customer` (`id_customer`),
  ADD KEY `fk_penjualan_user` (`id_user`);

--
-- Indeks untuk tabel `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT untuk tabel `customer`
--
ALTER TABLE `customer`
  MODIFY `id_customer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `fk_barang_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD CONSTRAINT `fk_detail_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_penjualan` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan` (`id_penjualan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `fk_penjualan_customer` FOREIGN KEY (`id_customer`) REFERENCES `customer` (`id_customer`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_penjualan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
