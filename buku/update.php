<?php
<<<<<<< HEAD
/**
 * ============================================================================
 * FILE: buku/update.php (Proses Pembaruan Data Buku)
 * ============================================================================
 * FUNGSI UTAMA:
 * Memperbarui informasi buku di database beserta mengganti file sampul atau e-book lama
 * jika Admin mengunggah file yang baru.
 *
 * ALUR LOGIKA:
 * 1. Mengecek keberadaan data buku lama di database berdasarkan ID.
 * 2. Memvalidasi ulang kecocokan rak dengan kategori buku.
 * 3. Jika ada upload sampul/e-book baru, file baru disimpan dan file lama dihapus dari server
 *    (`@unlink()`) agar tidak memenuhi penyimpanan.
 * 4. Menjalankan query `UPDATE buku SET ... WHERE id = ?` dengan Prepared Statement.
 * ============================================================================
 */
=======
>>>>>>> d3a474d9f86ca513d111ac9b325518e17d65db66
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('buku/tampil.php'));
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$judul = trim($_POST['judul'] ?? '');
$kategoriId = (int) ($_POST['kategori_id'] ?? 0);
$penerbitId = (int) ($_POST['penerbit_id'] ?? 0);
$pengarang = trim($_POST['pengarang'] ?? '');
$jumlahHalaman = ($_POST['jumlah_halaman'] ?? '') !== '' ? (int) $_POST['jumlah_halaman'] : null;
$jumlahStok = (int) ($_POST['jumlah_stok'] ?? 0);
$tahunTerbit = ($_POST['tahun_terbit'] ?? '') !== '' ? (int) $_POST['tahun_terbit'] : null;
$sinopsis = trim($_POST['sinopsis'] ?? '');
$rakId = (int) ($_POST['rak_id'] ?? 0);
$gambarLama = trim($_POST['gambar'] ?? '');

$oldStmt = $conn->prepare('SELECT gambar, ebook_file FROM buku WHERE id = ? AND status_aktif = 1');
$oldStmt->bind_param('i', $id);
$oldStmt->execute();
$old = $oldStmt->get_result()->fetch_assoc();

if (!$old) {
    header('Location: ' . url('buku/tampil.php'));
    exit;
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

// Mode 2: upload sampul lokal menjadi prioritas utama.
$cover = uploadCover('cover_file', false);
if (!$cover['success']) {
    die(e($cover['error']) . '<br><br><a href="' . e(url('buku/edit.php')) . '?id=' . $id . '">Kembali</a>');
}

if (!empty($cover['filename'])) {
    $gambar = 'covers/' . $cover['filename'];
} elseif (!empty($old['gambar']) && preg_match('~^covers/~i', trim($old['gambar']))) {
    // Sampul lokal lama tidak pernah diganti otomatis.
    $gambar = $old['gambar'];
} else {
    $gambar = fetchBookCoverOnline($judul, $pengarang, $penerbit);
    if (!$gambar) {
        $gambar = $gambarLama !== '' ? $gambarLama : ($old['gambar'] ?? 'default.svg');
    }
}

$ebook = uploadEbook('ebook_file', false);
if (!$ebook['success']) {
    if (!empty($cover['path']) && is_file($cover['path'])) @unlink($cover['path']);
    die(e($ebook['error']) . '<br><br><a href="' . e(url('buku/edit.php')) . '?id=' . $id . '">Kembali</a>');
}
$ebookFile = $ebook['filename'] ?: ($old['ebook_file'] ?? null);

$sql = 'UPDATE buku SET
            judul = ?, kategori_id = ?, penerbit_id = ?, pengarang = ?,
            jumlah_halaman = ?, jumlah_stok = ?, tahun_terbit = ?, sinopsis = ?,
            gambar = ?, id_rak = ?, ebook_file = ?
        WHERE id = ? AND status_aktif = 1';

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'siisiiissisi',
    $judul, $kategoriId, $penerbitId, $pengarang, $jumlahHalaman,
    $jumlahStok, $tahunTerbit, $sinopsis, $gambar, $rak, $ebookFile, $id
);

if ($stmt->execute()) {
    if (!empty($cover['filename']) && !empty($old['gambar']) && preg_match('~^covers/~i', $old['gambar'])) {
        $oldCoverPath = __DIR__ . '/../assets/' . ltrim($old['gambar'], '/');
        if (is_file($oldCoverPath)) @unlink($oldCoverPath);
    }
    if ($ebook['filename'] && !empty($old['ebook_file']) && $old['ebook_file'] !== $ebookFile) {
        $oldPath = __DIR__ . '/../assets/ebooks/' . basename($old['ebook_file']);
        if (is_file($oldPath)) @unlink($oldPath);
    }
    header('Location: ' . url('buku/tampil.php'));
    exit;
}

if (!empty($ebook['path']) && is_file($ebook['path'])) @unlink($ebook['path']);
die('Data gagal diubah: ' . e($stmt->error));
