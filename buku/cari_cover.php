<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/upload_helper.php';
require_once __DIR__ . '/../config/connection.php';

header('Content-Type: application/json; charset=utf-8');

$judul = trim($_GET['judul'] ?? '');
$pengarang = trim($_GET['pengarang'] ?? '');
$penerbitId = (int) ($_GET['penerbit_id'] ?? 0);
$penerbit = '';

if ($penerbitId > 0) {
    $stmt = $conn->prepare('SELECT nama_penerbit FROM penerbit WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $penerbitId);
    $stmt->execute();
    $penerbit = (string) ($stmt->get_result()->fetch_assoc()['nama_penerbit'] ?? '');
}

$cover = fetchBookCoverOnline($judul, $pengarang, $penerbit);

echo json_encode([
    'success' => true,
    'cover' => $cover,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
