<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/connection.php';

$msg = '';
$type = 'success';

// Menangkap session flash message jika ada
if (isset($_SESSION['success'])) {
    $msg = $_SESSION['success'];
    $type = 'success';
    unset($_SESSION['success']);
} elseif (isset($_SESSION['error'])) {
    $msg = $_SESSION['error'];
    $type = 'danger';
    unset($_SESSION['error']);
}

$rows = $conn->query('SELECT id, kode_anggota, nama, jenis_kelamin, tempat_lahir, tanggal_lahir, telpon, alamat, status_aktif FROM anggota ORDER BY id DESC');
$pageTitle = 'Data Anggota';
$pageDescription = 'Kelola data anggota perpustakaan.';
require_once __DIR__ . '/../includes/layout_header.php';
?>
<?php if ($msg): ?>
    <div class="alert <?= e($type) ?>"><?= e($msg) ?></div>
<?php endif; ?>

<section class="page-intro">
    <div>
        <span class="page-label">ANGGOTA</span>
        <h1>Data Anggota</h1>
        <p>Kelola anggota yang terdaftar di perpustakaan.</p>
    </div>
    <a href="<?= e(url('anggota/anggota_form.php')) ?>" class="btn primary">＋ Tambah Anggota</a>
</section>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Jenis Kelamin</th>
                    <th>Tempat/Tgl Lahir</th>
                    <th>Telpon</th>
                    <th>Alamat</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($rows->num_rows): ?>
                <?php while ($r = $rows->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Kode"><span class="book-code"><?= e($r['kode_anggota']) ?></span></td>
                        <td data-label="Nama"><b><?= e($r['nama']) ?></b></td>
                        <td data-label="Jenis Kelamin"><?= e(ucfirst($r['jenis_kelamin'] ?? '-')) ?></td>
                        <td data-label="Tempat/Tgl Lahir"><?= e($r['tempat_lahir'] ?? '-') ?> / <?= e($r['tanggal_lahir'] ?? '-') ?></td>
                        <td data-label="Telpon"><?= e($r['telpon'] ?? '-') ?></td>
                        <td data-label="Alamat"><?= e($r['alamat'] ?? '-') ?></td>
                        <td data-label="Status"><span class="badge <?= (int) $r['status_aktif'] === 1 ? 'green' : 'red' ?>"><?= (int) $r['status_aktif'] === 1 ? 'Aktif' : 'Nonaktif' ?></span></td>
                        <td data-label="Aksi" class="actions-cell">
                            <a class="action edit" href="<?= e(url('anggota/anggota_form.php')) ?>?id=<?= $r['id'] ?>">Edit</a>
                            <a class="action delete" href="<?= e(url('anggota/hapus.php')) ?>?id=<?= $r['id'] ?>" onclick="return confirm('Yakin ingin menghapus permanen anggota ini? Riwayat peminjaman buku akan tetap aman.')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" class="empty">Belum ada anggota.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>