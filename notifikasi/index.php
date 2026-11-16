<?php
/**
 * ============================================================================
 * FILE: notifikasi/index.php (Pusat Notifikasi Aktivitas Pengguna)
 * ============================================================================
 * FUNGSI UTAMA:
 * Menampilkan daftar pemberitahuan (persetujuan pinjam, penolakan, atau selesai pengembalian)
 * serta menyediakan tombol untuk menandai notifikasi sebagai sudah dibaca (`dibaca = 1`).
 * ============================================================================
 */
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../config/connection.php';

$userId = (int)$_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'read_all') {
        $stmt = $conn->prepare('UPDATE notifikasi SET dibaca=1 WHERE user_id=?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
    } elseif (($_POST['action'] ?? '') === 'read') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare('UPDATE notifikasi SET dibaca=1 WHERE id=? AND user_id=?');
        $stmt->bind_param('ii', $id, $userId);
        $stmt->execute();
    }
    header('Location: ' . url('notifikasi/index.php'));
    exit;
}

$unread = $conn->prepare('SELECT COUNT(*) total FROM notifikasi WHERE user_id=? AND dibaca=0');
$unread->bind_param('i', $userId);
$unread->execute();
$jumlahBelumDibaca = (int)$unread->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare('SELECT id, judul, pesan, tipe, dibaca, created_at FROM notifikasi WHERE user_id=? ORDER BY created_at DESC, id DESC');
$stmt->bind_param('i', $userId);
$stmt->execute();
$rows = $stmt->get_result();

$pageTitle = 'Notifikasi';
$pageDescription = 'Informasi aktivitas dan status transaksi perpustakaan.';
require_once __DIR__ . '/../includes/layout_header.php';
?>
<section class="panel">
  <div class="page-intro">
    <div><span class="page-label">AKTIVITAS</span><h1>🔔 Notifikasi</h1><p>Informasi terbaru dari sistem perpustakaan.</p></div>
    <?php if ($jumlahBelumDibaca > 0): ?>
      <form method="post"><input type="hidden" name="action" value="read_all"><button class="btn" type="submit">Tandai Semua Dibaca</button></form>
    <?php endif; ?>
  </div>
  <?php if ($rows->num_rows === 0): ?>
    <div class="empty-card"><div>🔔</div><h3>Belum ada notifikasi</h3><p>Notifikasi aktivitas akan muncul di sini.</p></div>
  <?php else: ?>
    <div class="notification-list">
      <?php while ($row = $rows->fetch_assoc()): ?>
        <article class="notification-item <?= (int)$row['dibaca'] === 0 ? 'unread' : '' ?>">
          <div class="notification-icon">🔔</div>
          <div class="notification-body">
            <strong><?= e($row['judul']) ?></strong>
            <p><?= nl2br(e($row['pesan'])) ?></p>
            <small><?= e(date('d-m-Y H:i', strtotime($row['created_at']))) ?></small>
          </div>
          <?php if ((int)$row['dibaca'] === 0): ?>
            <form method="post"><input type="hidden" name="action" value="read"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button class="btn small" type="submit">Tandai Dibaca</button></form>
          <?php endif; ?>
        </article>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
