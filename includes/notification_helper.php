<?php
<<<<<<< HEAD
/**
 * ============================================================================
 * FILE: includes/notification_helper.php (Helper Pengiriman Notifikasi Internal)
 * ============================================================================
 * FUNGSI UTAMA:
 * Menyimpan pesan notifikasi ke tabel `notifikasi` untuk memberitahu user atau admin
 * mengenai status peminjaman dan pengembalian buku.
 *
 * ALUR LOGIKA:
 * 1. addNotification(): Mengirim notifikasi ke satu user tertentu menggunakan Prepared Statement.
 * 2. addNotificationToAdmins(): Mengambil semua akun berlevel 'admin' lalu mengirimkan
 *    notifikasi ke masing-masing admin (misal saat ada pengajuan pinjam buku baru).
 * ============================================================================
 */
=======
>>>>>>> d3a474d9f86ca513d111ac9b325518e17d65db66
function addNotification(mysqli $conn, int $userId, string $judul, string $pesan, string $tipe = 'info'): void
{
    if ($userId < 1) return;
    $stmt = $conn->prepare('INSERT INTO notifikasi (user_id, judul, pesan, tipe) VALUES (?, ?, ?, ?)');
    if (!$stmt) return;
    $stmt->bind_param('isss', $userId, $judul, $pesan, $tipe);
    $stmt->execute();
    $stmt->close();
}

function addNotificationToAdmins(mysqli $conn, string $judul, string $pesan, string $tipe = 'info'): void
{
    $res = $conn->query("SELECT id FROM user WHERE level='admin'");
    if (!$res) return;
    while ($row = $res->fetch_assoc()) {
        addNotification($conn, (int)$row['id'], $judul, $pesan, $tipe);
    }
}
