<?php
/**
 * ============================================================================
 * FILE: buku/ebook.php (Penampil File E-Book PDF Aman)
 * ============================================================================
 * FUNGSI UTAMA:
 * Menyajikan file PDF e-book langsung di browser (inline) hanya untuk pengguna yang sudah login.
 *
 * ALUR LOGIKA:
 * 1. Mengecek sesi login (`requireLogin()`) agar file tidak bisa diakses bebas tanpa login.
 * 2. Mengambil nama file dari database, memvalidasi keberadaan file fisik di folder `assets/ebooks/`
 *    menggunakan `basename()` untuk mencegah serangan Directory Traversal.
 * 3. Mengirim header `Content-Type: application/pdf` dan membaca isi file dengan `readfile()`.
 * ============================================================================
 */
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../config/connection.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare('SELECT judul, ebook_file FROM buku WHERE id=? AND status_aktif=1 LIMIT 1');
$stmt->bind_param('i', $id); $stmt->execute(); $book = $stmt->get_result()->fetch_assoc();
if (!$book || empty($book['ebook_file'])) { http_response_code(404); exit('E-book tidak tersedia.'); }
$file = __DIR__ . '/../assets/ebooks/' . basename($book['ebook_file']);
if (!is_file($file)) { http_response_code(404); exit('File e-book tidak ditemukan.'); }
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($book['ebook_file']) . '"');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;
