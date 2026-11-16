<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/upload_helper.php';

$result = $conn->query("SELECT b.id, b.judul, b.pengarang, p.nama_penerbit FROM buku b LEFT JOIN penerbit p ON p.id=b.penerbit_id WHERE b.status_aktif=1 ORDER BY b.id");
$updated = 0;
$notFound = 0;

while ($book = $result->fetch_assoc()) {
    $cover = fetchBookCoverOnline(
        (string) $book['judul'],
        (string) ($book['pengarang'] ?? ''),
        (string) ($book['nama_penerbit'] ?? '')
    );

    if ($cover) {
        $stmt = $conn->prepare('UPDATE buku SET gambar=? WHERE id=?');
        $stmt->bind_param('si', $cover, $book['id']);
        $stmt->execute();
        $stmt->close();
        $updated++;
    } else {
        $notFound++;
    }
}

header('Location: ' . url('buku/tampil.php') . '?cover_sync=1&updated=' . $updated . '&not_found=' . $notFound);
exit;
