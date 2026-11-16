<?php
<<<<<<< HEAD
/**
 * ============================================================================
 * FILE: bookmark/index.php (Halaman Koleksi Bookmark Pribadi User)
 * ============================================================================
 * FUNGSI UTAMA:
 * Menampilkan daftar buku yang telah disimpan (di-bookmark) oleh pengguna yang sedang login.
 * ============================================================================
 */
=======
>>>>>>> d3a474d9f86ca513d111ac9b325518e17d65db66
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../includes/book_cover_helper.php';

$userId = (int) $_SESSION['user_id'];
$pageTitle = 'Bookmark Saya';
$pageDescription = 'Daftar buku yang kamu simpan.';

$stmt = $conn->prepare("SELECT b.id, b.judul, b.pengarang, b.gambar, b.tahun_terbit,
    k.nama_kategori,
    COALESCE((SELECT ROUND(AVG(r.rating),1) FROM rating_buku r WHERE r.buku_id=b.id),0) AS rating_rata,
    COALESCE((SELECT COUNT(*) FROM rating_buku r WHERE r.buku_id=b.id),0) AS jumlah_rating
    FROM bookmark bm
    JOIN buku b ON b.id=bm.buku_id
    LEFT JOIN kategori k ON k.id=b.kategori_id
    WHERE bm.user_id=? AND b.status_aktif=1
    ORDER BY bm.created_at DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$books = $stmt->get_result();

require_once __DIR__ . '/../includes/layout_header.php';
?>
<section class="panel">
    <div class="page-intro">
        <div>
            <span class="page-label">KOLEKSI PRIBADI</span>
            <h1>🔖 Bookmark Saya</h1>
            <p>Buku yang kamu simpan untuk dibaca atau dipinjam nanti.</p>
        </div>
        <a class="btn" href="<?= e(url('dashboard/dashboard.php')) ?>">← Kembali ke Dashboard</a>
    </div>

    <?php if ($books->num_rows === 0): ?>
        <div class="empty-card">
            <div>🔖</div>
            <h3>Belum ada bookmark</h3>
            <p>Klik tombol Bookmark pada buku yang ingin kamu simpan.</p>
            <a class="btn primary" href="<?= e(url('dashboard/dashboard.php')) ?>">Cari Buku</a>
        </div>
    <?php else: ?>
        <div class="book-grid">
            <?php while ($book = $books->fetch_assoc()): ?>
                <?php $gambarOtomatis = empty($book['gambar']) || $book['gambar'] === 'default.svg'; $img = $gambarOtomatis ? url('assets/images/default.svg') : normalizeBookCoverUrl($book['gambar']); ?>
                <article class="book-card">
                    <div class="book-cover">
                        <img src="<?= e($img) ?>" data-book-id="<?= (int) $book['id'] ?>" data-auto-cover="<?= $gambarOtomatis ? '1' : '0' ?>" alt="<?= e($book['judul']) ?>" onerror="this.src='<?= e(url('assets/images/default.svg')) ?>'">
                        <span class="availability available">★ <?= e($book['rating_rata']) ?></span>
                    </div>
                    <div class="book-content">
                        <span class="book-category"><?= e($book['nama_kategori'] ?? 'Umum') ?></span>
                        <h3><?= e($book['judul']) ?></h3>
                        <p class="book-author"><?= e($book['pengarang'] ?: '-') ?></p>
                        <p class="rating-summary">⭐ <?= e($book['rating_rata']) ?>/5 · <?= (int)$book['jumlah_rating'] ?> rating</p>
                        <div class="book-actions">
                            <a class="btn small" href="<?= e(url('buku/detail.php?id=' . (int)$book['id'])) ?>">Detail</a>
                            <a class="btn small primary" href="<?= e(url('transaksi/peminjaman.php?buku_id=' . (int)$book['id'])) ?>">📖 Pinjam</a>
                            <form method="post" action="<?= e(url('aksi/bookmark.php')) ?>">
                                <?= csrfField() ?>
                                <input type="hidden" name="buku_id" value="<?= (int)$book['id'] ?>">
                                <input type="hidden" name="return" value="bookmark/index.php">
                                <button class="btn small" type="submit">✓ Hapus</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</section>

<script>
(function () {
    const endpoint = <?= json_encode(url('buku/auto_cover.php')) ?>;
    document.querySelectorAll('img[data-auto-cover="1"]').forEach(function (img) {
        const id = img.dataset.bookId;
        if (!id) return;
        fetch(endpoint + '?id=' + encodeURIComponent(id), {headers: {'Accept':'application/json'}})
            .then(function (response) { return response.json(); })
            .then(function (data) { if (data.success && data.cover) img.src = data.cover; });
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
