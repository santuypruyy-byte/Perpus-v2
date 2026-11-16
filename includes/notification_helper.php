<?php
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
