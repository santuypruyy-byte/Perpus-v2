<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/connection.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Langsung hapus data anggota dari database.
    // Berkat aturan ON DELETE SET NULL yang sudah kita pasang di database, 
    // riwayat peminjaman otomatis aman (anggota_id di tabel peminjaman akan berubah menjadi NULL).
    $stmt = $conn->prepare("DELETE FROM anggota WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Data anggota berhasil dihapus permanen. Riwayat peminjaman tetap aman.";
    } else {
        $_SESSION['error'] = "Gagal menghapus data anggota.";
    }
    $stmt->close();
}

header("Location: anggota.php");
exit;