<?php
/**
 * ============================================================================
 * FILE: aksi/bookmark.php (Aksi Simpan / Hapus Bookmark Buku)
 * ============================================================================
 * FUNGSI UTAMA:
 * Bekerja sebagai tombol sakelar (toggle): jika buku belum ada di daftar bookmark user,
 * sistem akan menambahkannya (`INSERT`); jika sudah ada, sistem akan menghapusnya (`DELETE`).
 * ============================================================================
 */
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
requireValidCsrf();
require_once __DIR__ . '/../config/connection.php';

$userId = (int) $_SESSION['user_id'];
if ($userId < 1) {
    http_response_code(401);
    exit('Silakan login kembali.');
}
$bukuId = (int) ($_POST['buku_id'] ?? 0);
$return = $_POST['return'] ?? 'dashboard/dashboard.php';
$returnQ = trim($_POST['return_q'] ?? '');
$returnKategori = (int) ($_POST['return_kategori'] ?? 0);
$returnPenerbit = (int) ($_POST['return_penerbit'] ?? 0);
$returnTahun = (int) ($_POST['return_tahun'] ?? 0);
$returnStatus = trim($_POST['return_status'] ?? '');

$allowed = ['dashboard/dashboard.php', 'buku/detail.php', 'bookmark/index.php', 'bookmark/admin.php', 'pencarian/cari_buku.php'];
if (!in_array($return, $allowed, true)) {
    $return = 'dashboard/dashboard.php';
}

if ($bukuId > 0) {
    $check = $conn->prepare("SELECT id FROM buku WHERE id = ? AND status_aktif = 1 LIMIT 1");
    $check->bind_param('i', $bukuId);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();

    if ($exists) {
        $check = $conn->prepare("SELECT id FROM bookmark WHERE user_id = ? AND buku_id = ? LIMIT 1");
        $check->bind_param('ii', $userId, $bukuId);
        $check->execute();
        $saved = $check->get_result()->fetch_assoc();

        if ($saved) {
            $stmt = $conn->prepare("DELETE FROM bookmark WHERE id = ?");
            $stmt->bind_param('i', $saved['id']);
        } else {
            $stmt = $conn->prepare("INSERT INTO bookmark (user_id, buku_id) VALUES (?, ?)");
            $stmt->bind_param('ii', $userId, $bukuId);
        }
        $stmt->execute();
    }
}

$location = url($return);

if ($return === 'buku/detail.php') {
    $location .= '?id=' . $bukuId;
} elseif ($return === 'pencarian/cari_buku.php') {
    $query = [];
    if ($returnQ !== '') $query['q'] = $returnQ;
    if ($returnKategori > 0) $query['kategori'] = $returnKategori;
    if ($returnPenerbit > 0) $query['penerbit'] = $returnPenerbit;
    if ($returnTahun > 0) $query['tahun'] = $returnTahun;
    if ($returnStatus !== '') $query['status'] = $returnStatus;

    if (!empty($query)) {
        $location .= '?' . http_build_query($query);
    }
}

header('Location: ' . $location);
exit;
