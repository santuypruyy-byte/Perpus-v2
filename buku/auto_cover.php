<?php
/**
 * ============================================================================
 * FILE: buku/auto_cover.php (Endpoint AJAX Pencarian Sampul Otomatis per Buku)
 * ============================================================================
 * FUNGSI UTAMA:
 * Mengambil sampul buku secara otomatis dari internet untuk buku yang belum memiliki sampul,
 * lalu menyimpan URL gambarnya ke database dan mengembalikan respons JSON ke JavaScript.
 * ============================================================================
 */
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../includes/book_cover_helper.php';

header('Content-Type: application/json; charset=utf-8');

$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
if ($id < 1) {
    echo json_encode(['success' => false, 'message' => 'ID buku tidak valid']);
    exit;
}

$stmt = $conn->prepare('SELECT b.id, b.judul, b.pengarang, b.gambar, p.nama_penerbit
    FROM buku b
    LEFT JOIN penerbit p ON p.id = b.penerbit_id
    WHERE b.id = ? AND b.status_aktif = 1 LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$book) {
    echo json_encode(['success' => false, 'message' => 'Buku tidak ditemukan']);
    exit;
}

$current = trim((string) ($book['gambar'] ?? ''));
$hasOnlineCover = preg_match('~^https?://~i', $current) === 1;
$hasLocalCover = preg_match('~^covers/~i', $current) === 1;

// Sampul lokal yang dipilih Admin adalah prioritas dan tidak boleh ditimpa.
if ($hasOnlineCover || $hasLocalCover) {
    echo json_encode([
        'success' => true,
        'cover' => normalizeBookCoverUrl($current),
        'updated' => false,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$cover = fetchBookCoverOnline(
    (string) $book['judul'],
    (string) ($book['pengarang'] ?? ''),
    (string) ($book['nama_penerbit'] ?? '')
);

if ($cover) {
    $update = $conn->prepare('UPDATE buku SET gambar = ? WHERE id = ? AND status_aktif = 1');
    $update->bind_param('si', $cover, $id);
    $update->execute();
    $update->close();

    echo json_encode([
        'success' => true,
        'cover' => $cover,
        'updated' => true,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'success' => false,
    'cover' => url('assets/images/default.svg'),
    'message' => 'Sampul online tidak ditemukan',
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
