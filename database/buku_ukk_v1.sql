-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 15 Nov 2026 pada 17.17
-- Versi server: 10.4.6-MariaDB
-- Versi PHP: 8.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `buku_ukk_v1`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `anggota`
--

CREATE TABLE `anggota` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode_anggota` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('pria','wanita') DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `telpon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `anggota`
--

INSERT INTO `anggota` (`id`, `kode_anggota`, `nama`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `telpon`, `alamat`, `status_aktif`, `deleted_at`) VALUES
(1, 'AG001', 'Ahmad', 'pria', 'Bandung', '2005-03-12', '081234567801', 'Bandung', 1, NULL),
(2, 'AG002', 'Budi', 'pria', 'Cimahi', '2004-01-11', '081234567802', 'Cimahi', 1, NULL),
(3, 'AG003', 'Citra', 'wanita', 'Bandung', '2006-07-10', '081234567803', 'Bandung', 1, NULL),
(4, 'AG004', 'Dina', 'wanita', 'Garut', '2005-05-21', '081234567804', 'Garut', 1, NULL),
(5, 'AG005', 'Eko', 'pria', 'Tasikmalaya', '2004-09-15', '081234567805', 'Tasikmalaya', 1, NULL),
(6, 'AG006', 'Farah', 'wanita', 'Subang', '2006-02-17', '081234567806', 'Subang', 1, NULL),
(7, 'AG007', 'Gilang', 'pria', 'Sumedang', '2005-12-05', '081234567807', 'Sumedang', 1, NULL),
(8, 'AG008', 'Hani', 'wanita', 'Bandung', '2006-08-14', '081234567808', 'Bandung', 1, NULL),
(9, 'AG009', 'Indra', 'pria', 'Majalengka', '2004-06-18', '081234567809', 'Majalengka', 1, NULL),
(10, 'AG010', 'Jihan', 'wanita', 'Sukabumi', '2005-10-20', '081234567810', 'Sukabumi', 1, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `bookmark`
--

CREATE TABLE `bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `buku_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `buku`
--

CREATE TABLE `buku` (
  `id` int(10) UNSIGNED NOT NULL,
  `judul` varchar(255) NOT NULL,
  `kategori_id` int(10) UNSIGNED NOT NULL,
  `penerbit_id` int(10) UNSIGNED NOT NULL,
  `pengarang` varchar(150) DEFAULT NULL,
  `jumlah_halaman` int(10) UNSIGNED DEFAULT NULL,
  `jumlah_stok` int(10) UNSIGNED DEFAULT 0,
  `tahun_terbit` smallint(5) UNSIGNED DEFAULT NULL,
  `sinopsis` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `views_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_rak` int(10) UNSIGNED DEFAULT NULL,
  `ebook_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `buku`
--

INSERT INTO `buku` (`id`, `judul`, `kategori_id`, `penerbit_id`, `pengarang`, `jumlah_halaman`, `jumlah_stok`, `tahun_terbit`, `sinopsis`, `gambar`, `status_aktif`, `views_count`, `id_rak`, `ebook_file`) VALUES
(1, 'Laskar Pelangi', 1, 2, 'Andrea Hirata', 350, 12, 2018, 'Novel inspiratif', NULL, 1, 1, 1, NULL),
(2, 'Naruto Vol.1', 2, 2, 'Masashi Kishimoto', 180, 20, 2016, 'Komik Jepang', NULL, 1, 1, 2, NULL),
(3, 'Pemrograman PHP', 4, 3, 'Jubilee Enterprise', 420, 8, 2022, 'Belajar PHP', NULL, 1, 1, 3, NULL),
(4, 'Basis Data', 4, 4, 'Rosa A.S.', 300, 10, 2021, 'Belajar Database', NULL, 1, 2, 4, NULL),
(5, 'Matematika XI', 3, 1, 'Kemendikbud', 280, 15, 2023, 'Buku Pelajaran', NULL, 0, 1, 5, NULL),
(6, 'Fiqih Islam', 5, 5, 'Abdul Karim', 250, 9, 2019, 'Agama Islam', NULL, 1, 1, 6, NULL),
(7, 'Sejarah Indonesia', 6, 6, 'Sartono', 330, 7, 2020, 'Sejarah Indonesia', NULL, 1, 1, 7, NULL),
(8, 'Biografi B.J. Habibie', 7, 8, 'Alberthiene', 290, 11, 2017, 'Biografi', NULL, 1, 1, 8, NULL),
(9, 'Fisika Dasar', 8, 7, 'Halliday', 510, 6, 2021, 'Fisika Dasar', NULL, 1, 1, 9, NULL),
(10, 'Kamus Bahasa Indonesia', 9, 10, 'Tim Bahasa', 700, 5, 2020, 'Kamus Bahasa', NULL, 1, 1, 10, NULL),
(11, 'Bumi', 1, 2, 'Tere Liye', 440, 5, 2014, 'Novel petualangan', NULL, 1, 1, 11, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode_kategori` varchar(20) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `kode_kategori`, `nama_kategori`) VALUES
(1, 'KT001', 'Novel'),
(2, 'KT002', 'Komik'),
(3, 'KT003', 'Pelajaran'),
(4, 'KT004', 'Teknologi'),
(5, 'KT005', 'Agama'),
(6, 'KT006', 'Sejarah'),
(7, 'KT007', 'Biografi'),
(8, 'KT008', 'Sains'),
(9, 'KT009', 'Bahasa'),
(10, 'KT010', 'Ensiklopedia');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `judul` varchar(150) NOT NULL,
  `pesan` text NOT NULL,
  `tipe` varchar(30) NOT NULL DEFAULT 'info',
  `dibaca` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`id`, `user_id`, `judul`, `pesan`, `tipe`, `dibaca`, `created_at`) VALUES
(1, 2, 'Peminjaman sedang diproses', 'Pengajuan peminjaman buku sedang menunggu persetujuan admin.', 'info', 0, '2026-11-15 22:23:14'),
(2, 1, 'Pengajuan Peminjaman Baru', 'User Syahla putri mengajukan peminjaman #12. Menunggu persetujuan admin.', 'peminjaman', 1, '2026-11-15 23:21:33'),
(5, 1, 'Pengajuan Peminjaman Baru', 'User Syahla putri mengajukan peminjaman #13. Menunggu persetujuan admin.', 'peminjaman', 1, '2026-11-15 23:40:16'),
(6, 2, 'Peminjaman Disetujui', 'Permintaan peminjaman #11 telah disetujui admin. Buku sekarang berstatus sedang dipinjam.', 'success', 0, '2026-11-15 23:40:54'),
(9, 2, 'Peminjaman Selesai', 'Peminjaman #11 telah dikembalikan oleh admin. Tidak ada denda.', 'success', 0, '2026-11-16 00:15:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int(10) UNSIGNED NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `lama_pinjam` int(10) UNSIGNED DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `status` enum('diproses','dipinjam','sudah dikembalikan','ditolak') NOT NULL DEFAULT 'diproses',
  `anggota_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `snapshot_kode_anggota` varchar(20) DEFAULT NULL,
  `snapshot_nama_anggota` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `peminjaman`
--

INSERT INTO `peminjaman` (`id`, `tanggal_pinjam`, `lama_pinjam`, `keterangan`, `status`, `anggota_id`, `user_id`, `snapshot_kode_anggota`, `snapshot_nama_anggota`) VALUES
(1, '2026-08-01', 7, 'Baik', 'sudah dikembalikan', 1, 2, 'AG001', 'Ahmad'),
(2, '2026-08-02', 5, 'Baik', 'sudah dikembalikan', 2, 3, 'AG002', 'Budi'),
(3, '2026-08-03', 7, 'Baik', 'sudah dikembalikan', 3, 4, 'AG003', 'Citra'),
(4, '2026-08-04', 3, 'Baik', 'sudah dikembalikan', 4, 5, 'AG004', 'Dina'),
(5, '2026-08-05', 7, 'Baik', 'sudah dikembalikan', 5, 6, 'AG005', 'Eko'),
(6, '2026-08-06', 7, 'Baik', 'sudah dikembalikan', 6, 7, 'AG006', 'Farah'),
(7, '2026-08-07', 5, 'Baik', 'sudah dikembalikan', 7, 8, 'AG007', 'Gilang'),
(8, '2026-08-08', 4, 'Baik', 'sudah dikembalikan', 8, 9, 'AG008', 'Hani'),
(9, '2026-08-09', 7, 'Baik', 'sudah dikembalikan', 9, 10, 'AG009', 'Indra'),
(10, '2026-08-10', 7, 'Baik', 'sudah dikembalikan', 10, 11, 'AG010', 'Jihan'),
(11, '2026-11-15', 7, 'Contoh pengajuan menunggu ACC', 'sudah dikembalikan', 1, 2, 'AG001', 'Ahmad'),
(12, '2026-11-15', 7, '', 'sudah dikembalikan', NULL, NULL, 'AG011', 'Syahla'),
(13, '2026-11-15', 7, '', 'sudah dikembalikan', NULL, NULL, 'AG012', 'Udin');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman_detail`
--

CREATE TABLE `peminjaman_detail` (
  `id` int(10) UNSIGNED NOT NULL,
  `peminjaman_id` int(10) UNSIGNED NOT NULL,
  `buku_id` int(10) UNSIGNED NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `peminjaman_detail`
--

INSERT INTO `peminjaman_detail` (`id`, `peminjaman_id`, `buku_id`, `jumlah`) VALUES
(1, 1, 1, 1),
(2, 2, 2, 2),
(3, 3, 3, 1),
(4, 4, 4, 1),
(5, 5, 5, 2),
(6, 6, 6, 1),
(7, 7, 7, 1),
(8, 8, 8, 2),
(9, 9, 9, 1),
(10, 10, 10, 1),
(11, 11, 6, 1),
(12, 12, 4, 1),
(13, 13, 4, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `penerbit`
--

CREATE TABLE `penerbit` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode_penerbit` varchar(20) NOT NULL,
  `nama_penerbit` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `penerbit`
--

INSERT INTO `penerbit` (`id`, `kode_penerbit`, `nama_penerbit`) VALUES
(1, 'PB001', 'Erlangga'),
(2, 'PB002', 'Gramedia'),
(3, 'PB003', 'Andi Publisher'),
(4, 'PB004', 'Informatika'),
(5, 'PB005', 'Mizan'),
(6, 'PB006', 'Yudhistira'),
(7, 'PB007', 'Deepublish'),
(8, 'PB008', 'Bentang Pustaka'),
(9, 'PB009', 'Tiga Serangkai'),
(10, 'PB010', 'Pustaka Pelajar');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengembalian`
--

CREATE TABLE `pengembalian` (
  `id` int(10) UNSIGNED NOT NULL,
  `peminjaman_id` int(10) UNSIGNED NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `keterlambatan_hari` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `denda` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `pengembalian`
--

INSERT INTO `pengembalian` (`id`, `peminjaman_id`, `tanggal_kembali`, `keterlambatan_hari`, `denda`, `user_id`) VALUES
(1, 1, '2026-08-08', 0, 0, 1),
(2, 2, '2026-08-07', 0, 0, 1),
(3, 3, '2026-08-10', 0, 0, 1),
(4, 4, '2026-08-07', 0, 0, 1),
(5, 5, '2026-08-12', 0, 0, 1),
(6, 6, '2026-08-13', 0, 0, 1),
(7, 7, '2026-08-12', 0, 0, 1),
(8, 8, '2026-08-12', 0, 0, 1),
(9, 9, '2026-08-16', 0, 0, 1),
(10, 10, '2026-08-17', 0, 0, 1),
(11, 12, '2026-11-15', 0, 0, 1),
(12, 13, '2026-11-15', 0, 0, 1),
(13, 11, '2026-11-15', 0, 0, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `rak`
--

CREATE TABLE `rak` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode_rak` varchar(50) NOT NULL,
  `nama_rak` varchar(100) NOT NULL,
  `kategori_id` int(10) UNSIGNED NOT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `rak`
--

INSERT INTO `rak` (`id`, `kode_rak`, `nama_rak`, `kategori_id`, `status_aktif`) VALUES
(1, 'A1', 'Rak Novel 1', 1, 1),
(2, 'B1', 'Rak Komik 1', 2, 1),
(3, 'C1', 'Rak Teknologi 1', 4, 1),
(4, 'C2', 'Rak Teknologi 2', 4, 1),
(5, 'D1', 'Rak Pelajaran 1', 3, 1),
(6, 'E1', 'Rak Agama 1', 5, 1),
(7, 'F1', 'Rak Sejarah 1', 6, 1),
(8, 'G1', 'Rak Biografi 1', 7, 1),
(9, 'H1', 'Rak Sains 1', 8, 1),
(10, 'I1', 'Rak Bahasa 1', 9, 1),
(11, 'A2', 'Rak Novel 2', 1, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `rating_buku`
--

CREATE TABLE `rating_buku` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `buku_id` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` enum('admin','user') NOT NULL,
  `anggota_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `nama`, `username`, `password`, `level`, `anggota_id`) VALUES
(1, 'Administrator', 'admin', '$2y$10$W4wlPrgOQxbhIaHzZOYTX.u/5ZgA2Rc2MIDY55T0QmXqiujhWe3Ua', 'admin', NULL),
(2, 'User 1', 'user1', '12345', 'user', 1),
(3, 'User 2', 'user2', '12345', 'user', 2),
(4, 'User 3', 'user3', '12345', 'user', 3),
(5, 'User 4', 'user4', '12345', 'user', 4),
(6, 'User 5', 'user5', '12345', 'user', 5),
(7, 'User 6', 'user6', '12345', 'user', 6),
(8, 'User 7', 'user7', '12345', 'user', 7),
(9, 'User 8', 'user8', '12345', 'user', 8),
(10, 'User 9', 'user9', '12345', 'user', 9),
(11, 'User 10', 'user10', '12345', 'user', 10);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_anggota_kode` (`kode_anggota`),
  ADD KEY `idx_anggota_status_aktif` (`status_aktif`);

--
-- Indeks untuk tabel `bookmark`
--
ALTER TABLE `bookmark`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_bookmark_user_buku` (`user_id`,`buku_id`),
  ADD KEY `idx_bookmark_buku` (`buku_id`);

--
-- Indeks untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_buku_kategori` (`kategori_id`),
  ADD KEY `idx_buku_penerbit` (`penerbit_id`),
  ADD KEY `idx_buku_status` (`status_aktif`),
  ADD KEY `idx_buku_rak` (`id_rak`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_kategori_kode` (`kode_kategori`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifikasi_user` (`user_id`,`dibaca`,`created_at`);

--
-- Indeks untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_peminjaman_anggota` (`anggota_id`),
  ADD KEY `idx_peminjaman_user` (`user_id`),
  ADD KEY `idx_peminjaman_status` (`status`);

--
-- Indeks untuk tabel `peminjaman_detail`
--
ALTER TABLE `peminjaman_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_detail_peminjaman` (`peminjaman_id`),
  ADD KEY `idx_detail_buku` (`buku_id`);

--
-- Indeks untuk tabel `penerbit`
--
ALTER TABLE `penerbit`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_penerbit_kode` (`kode_penerbit`);

--
-- Indeks untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pengembalian_peminjaman` (`peminjaman_id`),
  ADD KEY `idx_pengembalian_user` (`user_id`);

--
-- Indeks untuk tabel `rak`
--
ALTER TABLE `rak`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_rak_kategori_kode` (`kategori_id`,`kode_rak`),
  ADD KEY `idx_rak_kategori` (`kategori_id`);

--
-- Indeks untuk tabel `rating_buku`
--
ALTER TABLE `rating_buku`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_rating_user_buku` (`user_id`,`buku_id`),
  ADD KEY `idx_rating_buku` (`buku_id`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_username` (`username`),
  ADD UNIQUE KEY `uq_user_anggota` (`anggota_id`),
  ADD KEY `idx_user_anggota` (`anggota_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `bookmark`
--
ALTER TABLE `bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `peminjaman_detail`
--
ALTER TABLE `peminjaman_detail`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `penerbit`
--
ALTER TABLE `penerbit`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `rak`
--
ALTER TABLE `rak`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `rating_buku`
--
ALTER TABLE `rating_buku`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bookmark`
--
ALTER TABLE `bookmark`
  ADD CONSTRAINT `fk_bookmark_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bookmark_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `fk_buku_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_buku_penerbit` FOREIGN KEY (`penerbit_id`) REFERENCES `penerbit` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_buku_rak` FOREIGN KEY (`id_rak`) REFERENCES `rak` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `fk_notifikasi_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `fk_peminjaman_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_peminjaman_user_setnull` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `peminjaman_detail`
--
ALTER TABLE `peminjaman_detail`
  ADD CONSTRAINT `fk_detail_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_peminjaman` FOREIGN KEY (`peminjaman_id`) REFERENCES `peminjaman` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD CONSTRAINT `fk_pengembalian_peminjaman` FOREIGN KEY (`peminjaman_id`) REFERENCES `peminjaman` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pengembalian_user_setnull` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `rak`
--
ALTER TABLE `rak`
  ADD CONSTRAINT `fk_rak_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `rating_buku`
--
ALTER TABLE `rating_buku`
  ADD CONSTRAINT `fk_rating_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rating_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_user_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
