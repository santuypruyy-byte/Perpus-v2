<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('buku/form_tambah.php'));
    exit;
}

$judul = trim($_POST['judul'] ?? '');
$kategoriId = (int) ($_POST['kategori_id'] ?? 0);
$penerbitId = (int) ($_POST['penerbit_id'] ?? 0);
$pengarang = trim($_POST['pengarang'] ?? '');
$jumlahHalaman = ($_POST['jumlah_halaman'] ?? '') !== '' ? (int) $_POST['jumlah_halaman'] : null;
$jumlahStok = (int) ($_POST['jumlah_stok'] ?? 0);
$tahunTerbit = ($_POST['tahun_terbit'] ?? '') !== '' ? (int) $_POST['tahun_terbit'] : null;
$sinopsis = trim($_POST['sinopsis'] ?? '');
$rakId = (int) ($_POST['rak_id'] ?? 0);

if ($judul === '' || $kategoriId < 1 || $penerbitId < 1 || $rakId < 1 || $jumlahStok < 0) {
    die('Data buku belum lengkap.');
}

$rakStmt = $conn->prepare('SELECT id, kode_rak FROM rak WHERE id = ? AND kategori_id = ? AND status_aktif = 1 LIMIT 1');
$rakStmt->bind_param('ii', $rakId, $kategoriId);
$rakStmt->execute();
$rakData = $rakStmt->get_result()->fetch_assoc();
if (!$rakData) {
    die('Rak tidak sesuai dengan kategori yang dipilih.');
}

$rak = $rakId;

$penerbitStmt = $conn->prepare('SELECT nama_penerbit FROM penerbit WHERE id = ? LIMIT 1');
$penerbitStmt->bind_param('i', $penerbitId);
$penerbitStmt->execute();
$penerbit = (string) ($penerbitStmt->get_result()->fetch_assoc()['nama_penerbit'] ?? '');

// Mode 2: jika Admin memilih sampul lokal, gunakan sampul lokal.
// Jika tidak ada upload, baru cari sampul otomatis dari internet.
$cover = uploadCover('cover_file', false);
if (!$cover['success']) {
    die(e($cover['error']) . '<br><br><a href="' . e(url('buku/form_tambah.php')) . '">Kembali</a>');
}

if (!empty($cover['filename'])) {
    $gambar = 'covers/' . $cover['filename'];
} else {
    $gambar = fetchBookCoverOnline($judul, $pengarang, $penerbit);
    if (!$gambar) {
        $gambar = 'default.svg';
    }
}

$ebook = uploadEbook('ebook_file', false);
if (!$ebook['success']) {
    if (!empty($cover['path']) && is_file($cover['path'])) @unlink($cover['path']);
    die(e($ebook['error']) . '<br><br><a href="' . e(url('buku/form_tambah.php')) . '">Kembali</a>');
}
$ebookFile = $ebook['filename'];

$sql = 'INSERT INTO buku (
            judul, kategori_id, penerbit_id, pengarang, jumlah_halaman,
            jumlah_stok, tahun_terbit, sinopsis, gambar, id_rak, ebook_file
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'siisiiissis',
    $judul, $kategoriId, $penerbitId, $pengarang, $jumlahHalaman,
    $jumlahStok, $tahunTerbit, $sinopsis, $gambar, $rak, $ebookFile
);

if ($stmt->execute()) {
    header('Location: ' . url('buku/tampil.php'));
    exit;
}

if (!empty($ebook['path']) && is_file($ebook['path'])) @unlink($ebook['path']);
die('Data gagal ditambahkan: ' . e($stmt->error));
