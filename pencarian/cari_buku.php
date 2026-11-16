<?php
<<<<<<< HEAD
/**
 * ============================================================================
 * FILE: pencarian/cari_buku.php (Halaman Pencarian & Filter Katalog OPAC)
 * ============================================================================
 * FUNGSI UTAMA:
 * Menyediakan fitur pencarian buku tingkat lanjut (OPAC) berdasarkan kata kunci, kategori,
 * penerbit, tahun terbit, dan ketersediaan stok atau e-book.
 *
 * ALUR LOGIKA:
 * 1. Membangun query SQL secara dinamis berdasarkan filter yang diisi oleh pengguna.
 * 2. Menyimpan seluruh parameter pencarian ke dalam array `$params` dan mengeksekusinya
 *    menggunakan Prepared Statement (`bind_param`) sehingga aman dari SQL Injection.
 * ============================================================================
 */
=======
>>>>>>> d3a474d9f86ca513d111ac9b325518e17d65db66
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../includes/book_cover_helper.php';

$cari       = trim($_GET['q'] ?? '');
$kategoriId = (int) ($_GET['kategori'] ?? 0);
$penerbitId = (int) ($_GET['penerbit'] ?? 0);
$tahun      = (int) ($_GET['tahun'] ?? 0);
$status     = $_GET['status'] ?? '';
$userId    = (int) ($_SESSION['user_id'] ?? 0);

/*
|--------------------------------------------------------------------------
| Data filter OPAC
|--------------------------------------------------------------------------
*/

$kategoriQuery = $conn->query("
    SELECT id, nama_kategori
    FROM kategori
    ORDER BY nama_kategori ASC
");

$penerbitQuery = $conn->query("
    SELECT id, nama_penerbit
    FROM penerbit
    ORDER BY nama_penerbit ASC
");

$tahunQuery = $conn->query("
    SELECT DISTINCT tahun_terbit
    FROM buku
    WHERE status_aktif = 1
      AND tahun_terbit IS NOT NULL
      AND tahun_terbit > 0
    ORDER BY tahun_terbit DESC
");

/*
|--------------------------------------------------------------------------
| Query koleksi buku
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            b.id,
            b.judul,
            b.pengarang,
            b.jumlah_halaman,
            b.jumlah_stok,
            b.tahun_terbit,
            b.sinopsis,
            b.gambar,
            b.ebook_file,
            EXISTS(SELECT 1 FROM bookmark bm WHERE bm.buku_id = b.id AND bm.user_id = ?) AS is_bookmarked,
            k.nama_kategori,
            p.nama_penerbit,
            COALESCE(
                (
                    SELECT SUM(pd.jumlah)
                    FROM peminjaman_detail pd
                    JOIN peminjaman pm
                        ON pm.id = pd.peminjaman_id
                    WHERE pd.buku_id = b.id
                      AND pm.status = 'dipinjam'
                ),
                0
            ) AS dipinjam
        FROM buku b
        LEFT JOIN kategori k
            ON k.id = b.kategori_id
        LEFT JOIN penerbit p
            ON p.id = b.penerbit_id
        WHERE b.status_aktif = 1";

$params = [$userId];
$types = 'i';

if ($cari !== '') {
    $sql .= " AND (
                b.judul LIKE ?
                OR b.pengarang LIKE ?
                OR b.sinopsis LIKE ?
                OR k.nama_kategori LIKE ?
              )";

    $like = '%' . $cari . '%';

    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;

    $types .= 'ssss';
}

if ($kategoriId > 0) {
    $sql .= " AND b.kategori_id = ?";
    $params[] = $kategoriId;
    $types .= 'i';
}

if ($penerbitId > 0) {
    $sql .= " AND b.penerbit_id = ?";
    $params[] = $penerbitId;
    $types .= 'i';
}

if ($tahun > 0) {
    $sql .= " AND b.tahun_terbit = ?";
    $params[] = $tahun;
    $types .= 'i';
}

if ($status === 'tersedia') {
    $sql .= " AND b.jumlah_stok > (
        SELECT COALESCE(SUM(pd2.jumlah), 0)
        FROM peminjaman_detail pd2
        JOIN peminjaman pm2
            ON pm2.id = pd2.peminjaman_id
        WHERE pd2.buku_id = b.id
          AND pm2.status = 'dipinjam'
    )";
} elseif ($status === 'ebook') {
    $sql .= " AND b.ebook_file IS NOT NULL AND b.ebook_file <> ''";
}

$sql .= " ORDER BY b.judul ASC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Gagal menyiapkan pencarian koleksi: ' . e($conn->error));
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$rows = $stmt->get_result();

$pageTitle = 'Koleksi Buku';
$pageDescription = 'Cari koleksi seperti OPAC, lihat detail, dan ajukan peminjaman.';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<section class="search-hero collection-hero">
    <div>
        <span class="page-label">OPAC PERPUSTAKAAN</span>
        <h1>Koleksi Buku</h1>
        <p>
            Temukan buku berdasarkan judul, pengarang, kategori, penerbit, tahun,
            ketersediaan, atau e-book.
        </p>
    </div>

    <div class="collection-icon">📚</div>
</section>

<section class="panel collection-filter-panel">
    <div class="panel-head">
        <div>
            <h3>Pencarian Koleksi</h3>
            <p class="muted">Gunakan satu atau beberapa filter sekaligus.</p>
        </div>
    </div>

    <div class="search-big collection-search">
        <form method="get">
            <input
                type="search"
                name="q"
                value="<?= e($cari) ?>"
                placeholder="Cari judul, pengarang, atau sinopsis..."
            >

            <select name="kategori" aria-label="Filter kategori">
                <option value="0">Semua Kategori</option>
                <?php while ($kategori = $kategoriQuery->fetch_assoc()): ?>
                    <option
                        value="<?= (int) $kategori['id'] ?>"
                        <?= $kategoriId === (int) $kategori['id'] ? 'selected' : '' ?>
                    >
                        <?= e($kategori['nama_kategori']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <select name="penerbit" aria-label="Filter penerbit">
                <option value="0">Semua Penerbit</option>
                <?php while ($penerbit = $penerbitQuery->fetch_assoc()): ?>
                    <option
                        value="<?= (int) $penerbit['id'] ?>"
                        <?= $penerbitId === (int) $penerbit['id'] ? 'selected' : '' ?>
                    >
                        <?= e($penerbit['nama_penerbit']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <select name="tahun" aria-label="Filter tahun">
                <option value="0">Semua Tahun</option>
                <?php while ($tahunRow = $tahunQuery->fetch_assoc()): ?>
                    <option
                        value="<?= (int) $tahunRow['tahun_terbit'] ?>"
                        <?= $tahun === (int) $tahunRow['tahun_terbit'] ? 'selected' : '' ?>
                    >
                        <?= (int) $tahunRow['tahun_terbit'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <select name="status" aria-label="Filter status">
                <option value="">Semua Status</option>
                <option value="tersedia" <?= $status === 'tersedia' ? 'selected' : '' ?>>
                    Buku tersedia
                </option>
                <option value="ebook" <?= $status === 'ebook' ? 'selected' : '' ?>>
                    Memiliki e-book
                </option>
            </select>

            <button type="submit" class="btn primary">🔎 Cari</button>

            <?php if (
                $cari !== '' ||
                $kategoriId > 0 ||
                $penerbitId > 0 ||
                $tahun > 0 ||
                $status !== ''
            ): ?>
                <a
                    href="<?= e(url('pencarian/cari_buku.php')) ?>"
                    class="btn"
                >
                    Reset
                </a>
            <?php endif; ?>
        </form>
    </div>
</section>

<section class="search-results">
    <div class="panel-head collection-result-head">
        <div>
            <h3>Daftar Koleksi</h3>
            <p class="muted">
                <?= (int) $rows->num_rows ?> buku ditemukan.
                Klik <strong>Pinjam</strong> untuk langsung membuka form peminjaman
                dengan buku yang sudah dipilih.
            </p>
        </div>
    </div>

    <div class="book-grid compact-grid">
        <?php if ($rows->num_rows): ?>

            <?php while ($row = $rows->fetch_assoc()): ?>
                <?php
                $tersedia = max(
                    0,
                    (int) $row['jumlah_stok'] - (int) $row['dipinjam']
                );

                $gambarOtomatis = empty($row['gambar']) || $row['gambar'] === 'default.svg';
                $gambar = $gambarOtomatis
                    ? url('assets/images/default.svg')
                    : normalizeBookCoverUrl($row['gambar']);

                $ebookTersedia = !empty($row['ebook_file']);
                ?>

                <article class="book-card collection-card">
                    <div class="book-cover">
                        <img
                            src="<?= e($gambar) ?>"
                            data-book-id="<?= (int) $row['id'] ?>"
                            data-auto-cover="<?= $gambarOtomatis ? '1' : '0' ?>"
                            alt="<?= e($row['judul']) ?>"
                            onerror="this.src='<?= e(url('assets/images/default.svg')) ?>'"
                        >

                        <span class="availability <?= $tersedia > 0 ? 'available' : 'unavailable' ?>">
                            <?= $tersedia > 0 ? 'Tersedia' : 'Habis' ?>
                        </span>

                        <?php if ($ebookTersedia): ?>
                            <span class="ebook-badge">📄 E-BOOK</span>
                        <?php endif; ?>
                    </div>

                    <div class="book-content">
                        <span class="book-category">
                            <?= e($row['nama_kategori'] ?? 'Umum') ?>
                        </span>

                        <h3><?= e($row['judul']) ?></h3>

                        <p class="book-author">
                            ✍ <?= e($row['pengarang'] ?? '-') ?>
                        </p>

                        <p class="book-description">
                            <?= e(
                                $row['sinopsis']
                                ?: 'Belum ada sinopsis untuk buku ini.'
                            ) ?>
                        </p>

                        <div class="book-meta">
                            <span>📅 <?= e($row['tahun_terbit'] ?: '-') ?></span>
                            <span>📦 <?= $tersedia ?> tersedia</span>
                        </div>

                        <div class="book-actions collection-actions">
                            <a
                                class="btn small"
                                href="<?= e(url('buku/detail.php?id=' . (int) $row['id'])) ?>"
                            >
                                Detail
                            </a>

                            <?php if ($tersedia > 0): ?>
                                <a
                                    class="btn small primary"
                                    href="<?= e(url('transaksi/peminjaman.php?buku_id=' . (int) $row['id'])) ?>"
                                >
                                    📖 Pinjam
                                </a>
                            <?php else: ?>
                                <span class="btn small disabled-btn">
                                    Habis
                                </span>
                            <?php endif; ?>

                            <form method="post" action="<?= e(url('aksi/bookmark.php')) ?>">
                                <?= csrfField() ?>
                                <input type="hidden" name="buku_id" value="<?= (int) $row['id'] ?>">
                                <input type="hidden" name="return" value="pencarian/cari_buku.php">
                                <input type="hidden" name="return_q" value="<?= e($cari) ?>">
                                <input type="hidden" name="return_kategori" value="<?= $kategoriId ?>">
                                <input type="hidden" name="return_penerbit" value="<?= $penerbitId ?>">
                                <input type="hidden" name="return_tahun" value="<?= $tahun ?>">
                                <input type="hidden" name="return_status" value="<?= e($status) ?>">
                                <button
                                    type="submit"
                                    class="btn small <?= !empty($row['is_bookmarked']) ? 'bookmark-active' : '' ?>"
                                    title="<?= !empty($row['is_bookmarked']) ? 'Hapus dari buku tersimpan' : 'Simpan buku' ?>"
                                >
                                    <?= !empty($row['is_bookmarked']) ? '✓ Tersimpan' : '🔖 Simpan' ?>
                                </button>
                            </form>

                            <?php if ($ebookTersedia): ?>
                                <a
                                    class="btn small ebook-btn"
                                    href="<?= e(url('buku/ebook.php?id=' . (int) $row['id'])) ?>"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    📄 Baca E-Book
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>

            <?php endwhile; ?>

        <?php else: ?>
            <div class="empty-card">
                <div>🔎</div>
                <h3>Koleksi tidak ditemukan</h3>
                <p>Coba ubah kata kunci atau filter yang digunakan.</p>
                <a
                    href="<?= e(url('pencarian/cari_buku.php')) ?>"
                    class="btn"
                >
                    Tampilkan Semua Buku
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>


<script>
(function () {
    const endpoint = <?= json_encode(url('buku/auto_cover.php')) ?>;
    const fallback = <?= json_encode(url('assets/images/default.svg')) ?>;
    document.querySelectorAll('img[data-auto-cover="1"]').forEach(function (img) {
        const id = img.dataset.bookId;
        if (!id) return;
        fetch(endpoint + '?id=' + encodeURIComponent(id), {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data.success && data.cover) img.src = data.cover;
        })
        .catch(function () { img.src = fallback; });
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
