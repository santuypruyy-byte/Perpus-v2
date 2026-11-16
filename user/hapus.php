<?php
<<<<<<< HEAD
/**
 * ============================================================================
 * FILE: user/hapus.php (Proses Hapus Akun Pengguna)
 * ============================================================================
 * FUNGSI UTAMA:
 * Menghapus akun user dari database sambil melindungi akun Administrator utama (ID 1)
 * agar tidak bisa dihapus secara tidak sengaja.
 * ============================================================================
 */
=======
>>>>>>> d3a474d9f86ca513d111ac9b325518e17d65db66
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/connection.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Jangan hapus akun admin utama (ID 1) agar sistem tetap aman
    if ($id === 1) {
        $_SESSION['error'] = "Akun Administrator utama tidak dapat dihapus!";
    } else {
        // Hapus user secara permanen. 
        // Berkat aturan ON DELETE SET NULL, riwayat peminjaman & pengembalian tetap aman 
        // (kolom user_id di tabel transaksi/pengembalian otomatis menjadi NULL).
        $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Data user berhasil dihapus permanen. Riwayat transaksi tetap aman tersimpan.";
        } else {
            $_SESSION['error'] = "Gagal menghapus data user.";
        }
        $stmt->close();
    }
}

header("Location: index.php"); // Sesuaikan dengan halaman daftar user Anda
exit;