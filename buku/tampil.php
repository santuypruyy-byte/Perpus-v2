<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../includes/book_cover_helper.php';

$sql = "SELECT
            buku.*,
            kategori.nama_kategori,
            penerbit.nama_penerbit,
            rak.kode_rak,
            rak.nama_rak,
            COALESCE(
                SUM(
                    CASE
                        WHEN peminjaman.status = 'dipinjam'
                        THEN peminjaman_detail.jumlah
                        ELSE 0
                    END
                ),
                0
            ) AS jumlah_dipinjam
        FROM buku
        LEFT JOIN kategori
            ON buku.kategori_id = kategori.id
        LEFT JOIN penerbit
            ON buku.penerbit_id = penerbit.id
        LEFT JOIN rak
            ON buku.id_rak = rak.id
        LEFT JOIN peminjaman_detail
            ON buku.id = peminjaman_detail.buku_id
        LEFT JOIN peminjaman
            ON peminjaman_detail.peminjaman_id = peminjaman.id
        WHERE buku.status_aktif = 1
        GROUP BY buku.id
        ORDER BY buku.id DESC";

$result = $conn->query($sql);

if (!$result) {
    die('Query gagal: ' . e($conn->error));
}

$total = $result->num_rows;

$pageTitle = 'Data Buku';
$pageDescription = 'Kelola koleksi buku perpustakaan.';

require_once __DIR__ . '/../includes/layout_header.php';
?>



<section class="page-intro">
    <div>
        <span class="page-label">KOLEKSI</span>
        <h1>Data Buku</h1>
        <p>
            Daftar koleksi dengan informasi utama dan ketersediaan stok.
        </p>
    </div>

    <div class="form-actions">
        <a href="<?= e(url('buku/form_tambah.php')) ?>" class="btn primary">＋ Tambah Buku</a>
    </div>
</section>

<div class="summary-row">
    <div class="summary-card">
        <span>📚</span>

        <div>
            <small>Total buku</small>
            <strong><?= $total ?></strong>
        </div>
    </div>
</div>

<section class="book-grid">
    <?php while ($row = $result->fetch_assoc()): ?>
        <?php
        $stok = (int) $row['jumlah_stok'];
        $dipinjam = (int) $row['jumlah_dipinjam'];
        $tersedia = max(0, $stok - $dipinjam);

        $gambarOtomatis = empty($row['gambar']) || $row['gambar'] === 'default.svg';
        $gambar = $gambarOtomatis
            ? url('assets/images/default.svg')
            : normalizeBookCoverUrl($row['gambar']);

        $sinopsis = $row['sinopsis'] ?? 'Belum ada sinopsis.';

        if (strlen($sinopsis) > 120) {
            $sinopsis = substr($sinopsis, 0, 120) . '...';
        }
        ?>

        <article class="book-card">
            <div class="book-cover">
                <img
                    src="<?= e($gambar) ?>"
                    data-book-id="<?= (int) $row['id'] ?>"
                    data-auto-cover="<?= $gambarOtomatis ? '1' : '0' ?>"
                    alt="<?= e($row['judul']) ?>"
                    onerror="this.src='<?= e(url('assets/images/default.svg')) ?>'"
                >

                <span
                    class="availability <?= $tersedia > 0 ? 'available' : 'unavailable' ?>"
                >
                    <?= $tersedia > 0 ? 'Tersedia' : 'Habis' ?>
                </span>
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
                    <?= e($sinopsis) ?>
                </p>

                <div class="book-meta">
                    <span>
                        📅 <?= e($row['tahun_terbit'] ?? '-') ?>
                    </span>

                    <span>
                        📦 <?= $tersedia ?>/<?= $stok ?>
                    </span>
                    <span>
                        📍 <?= e($row['kode_rak'] ?? '-') ?>
                    </span>
                </div>

                <div class="book-actions">
                    <a
                        class="btn primary"
                        href="<?= e(url('buku/detail.php')) ?>?id=<?= (int) $row['id'] ?>"
                    >
                        Detail
                    </a>

                    <a
                        class="btn"
                        href="<?= e(url('buku/edit.php')) ?>?id=<?= (int) $row['id'] ?>"
                    >
                        Edit
                    </a>

                    <form method="post" action="<?= e(url('buku/hapus.php')) ?>" onsubmit="return confirm('Buku akan diarsipkan. Riwayat peminjaman tetap disimpan. Lanjutkan?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                        <button class="btn danger" type="submit">Arsipkan</button>
                    </form>
                </div>
            </div>
        </article>
    <?php endwhile; ?>

    <?php if ($total === 0): ?>
        <div class="empty-card">
            <div>📚</div>
            <h3>Belum ada buku</h3>
            <p>Tambahkan koleksi buku terlebih dahulu.</p>

            <a
                href="<?= e(url('buku/form_tambah.php')) ?>"
                class="btn primary"
            >
                Tambah Buku
            </a>
        </div>
    <?php endif; ?>
</section>


<script>
(function () {
    const endpoint = <?= json_encode(url('buku/auto_cover.php')) ?>;
    const fallback = <?= json_encode(url('assets/images/default.svg')) ?>;
    const images = document.querySelectorAll('img[data-auto-cover="1"]');

    images.forEach(function (img) {
        const id = img.dataset.bookId;
        if (!id) return;

        fetch(endpoint + '?id=' + encodeURIComponent(id), {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data.success && data.cover) {
                img.src = data.cover;
                img.dataset.autoCover = '0';
            }
        })
        .catch(function () {
            img.src = fallback;
        });
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
