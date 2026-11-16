<?php
/**
 * ============================================================================
 * FILE: buku/hapus.php (Arsip / Soft Delete Data Buku)
 * ============================================================================
 * FUNGSI UTAMA:
 * Menonaktifkan buku dari katalog tanpa menghapus baris datanya secara permanen.
 *
 * ALUR LOGIKA:
 * 1. Memvalidasi metode request harus POST dan memeriksa token CSRF (`requireValidCsrf()`).
 * 2. Menerapkan konsep **Soft Delete** (`UPDATE buku SET status_aktif = 0 WHERE id = ?`)
 *    bukan `DELETE`. Tujuannya agar riwayat transaksi peminjaman masa lalu yang merujuk
 *    ke buku ini tetap utuh dan tidak rusak/error.
 * ============================================================================
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    header('Location: ' . url('buku/tampil.php'));
    exit();
}
require_once __DIR__ . '/../includes/auth.php';
requireValidCsrf();

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Location: ' . url('buku/tampil.php'));
    exit();
}

$id = (int)$_POST['id'];

// Buku diarsipkan, bukan dihapus dari database.
// Dengan cara ini riwayat peminjaman tetap aman dan tidak ikut terhapus.
$stmt = $conn->prepare("UPDATE buku SET status_aktif = 0 WHERE id = ? AND status_aktif = 1");
$stmt->bind_param("i", $id);
$stmt->execute();

header('Location: ' . url('buku/tampil.php'));
exit();
?>