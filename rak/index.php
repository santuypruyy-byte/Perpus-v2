<?php
<<<<<<< HEAD
/**
 * ============================================================================
 * FILE: rak/index.php (Manajemen Lokasi Rak Penyimpanan Buku)
 * ============================================================================
 * FUNGSI UTAMA:
 * Menampilkan daftar rak buku beserta kategori terkait dan jumlah buku di dalamnya,
 * serta menyediakan tombol untuk mengaktifkan atau menonaktifkan status rak.
 * ============================================================================
 */
=======
>>>>>>> d3a474d9f86ca513d111ac9b325518e17d65db66
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/connection.php';

$msg = '';
$type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0 && ($action === 'nonaktifkan' || $action === 'aktifkan')) {
        $status = $action === 'aktifkan' ? 1 : 0;
        $stmt = $conn->prepare('UPDATE rak SET status_aktif = ? WHERE id = ?');
        $stmt->bind_param('ii', $status, $id);

        if ($stmt->execute()) {
            $msg = $status ? 'Rak berhasil diaktifkan.' : 'Rak berhasil dinonaktifkan.';
        } else {
            $msg = 'Status rak gagal diperbarui.';
            $type = 'danger';
        }
    }
}

$rows = $conn->query(
    'SELECT r.id, r.kode_rak, r.nama_rak, r.status_aktif,
            k.nama_kategori, COUNT(b.id) AS jumlah_buku
     FROM rak r
     INNER JOIN kategori k ON k.id = r.kategori_id
     LEFT JOIN buku b ON b.id_rak = r.id
     GROUP BY r.id, r.kode_rak, r.nama_rak, r.status_aktif, k.nama_kategori
     ORDER BY r.id DESC'
);

$pageTitle = 'Data Rak';
$pageDescription = 'Kelola lokasi rak buku berdasarkan kategori.';
require_once __DIR__ . '/../includes/layout_header.php';
?>

<?php if ($msg): ?>
    <div class="alert <?= e($type) ?>"><?= e($msg) ?></div>
<?php endif; ?>

<section class="page-intro">
    <div>
        <span class="page-label">DATA MASTER</span>
        <h1>Data Rak</h1>
        <p>Kelola rak yang digunakan untuk menyimpan koleksi buku.</p>
    </div>
    <a href="<?= e(url('rak/form.php')) ?>" class="btn primary">＋ Tambah Rak</a>
</section>

<section class="panel">
    <div class="table-wrap">
        <table id="rakTable">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Rak</th>
                    <th>Kategori</th>
                    <th>Jumlah Buku</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($rows->num_rows): ?>
                <?php while ($row = $rows->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Kode">
                            <span class="book-code"><?= e($row['kode_rak']) ?></span>
                        </td>
                        <td data-label="Nama Rak"><b><?= e($row['nama_rak']) ?></b></td>
                        <td data-label="Kategori">
                            <span class="category-badge">▤ <?= e($row['nama_kategori']) ?></span>
                        </td>
                        <td data-label="Jumlah Buku"><?= (int)$row['jumlah_buku'] ?> buku</td>
                        <td data-label="Status">
                            <?php if ((int)$row['status_aktif'] === 1): ?>
                                <span class="rak-status active">Aktif</span>
                            <?php else: ?>
                                <span class="rak-status inactive">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Aksi" class="actions-cell">
                            <a class="action edit" href="<?= e(url('rak/form.php')) ?>?id=<?= (int)$row['id'] ?>">Edit</a>

                            <form method="post" class="inline-form" onsubmit="return confirm('<?= (int)$row['status_aktif'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?> rak ini?')">
                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                <?php if ((int)$row['status_aktif'] === 1): ?>
                                    <input type="hidden" name="action" value="nonaktifkan">
                                    <button type="submit" class="action delete">Nonaktif</button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="aktifkan">
                                    <button type="submit" class="action activate">Aktifkan</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="empty">Belum ada rak.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<style>
.category-badge { display:inline-flex; align-items:center; gap:5px; padding:5px 9px; border-radius:999px; background:#eff6ff; color:var(--primary); font-size:10px; font-weight:800; }
.rak-status { display:inline-flex; padding:5px 9px; border-radius:999px; font-size:10px; font-weight:800; }
.rak-status.active { background:#ecfdf5; color:var(--green); }
.rak-status.inactive { background:#fef2f2; color:var(--red); }
.action.activate { background:#ecfdf5; color:var(--green); border:0; cursor:pointer; }
</style>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
