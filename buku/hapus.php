<?php
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