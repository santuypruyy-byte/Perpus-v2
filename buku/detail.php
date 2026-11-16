<?php
require_once __DIR__ . "/../includes/auth.php";
requireLogin();

require_once __DIR__ . "/../config/connection.php";
require_once __DIR__ . "/../includes/book_cover_helper.php";

$id = (int) ($_GET["id"] ?? 0);

if ($id < 1) {
  header("Location: " . url("buku/tampil.php"));
  exit();
}

$userId = (int) $_SESSION["user_id"];

$sql = "SELECT
            buku.*,
            kategori.nama_kategori,
            penerbit.nama_penerbit,
            rak.kode_rak,
            rak.nama_rak,
            COALESCE((SELECT ROUND(AVG(r.rating),1) FROM rating_buku r WHERE r.buku_id = buku.id),0) AS rating_rata,
            COALESCE((SELECT COUNT(*) FROM rating_buku r WHERE r.buku_id = buku.id),0) AS jumlah_rating,
            COALESCE((SELECT rating FROM rating_buku r WHERE r.buku_id = buku.id AND r.user_id = $userId LIMIT 1),0) AS rating_saya,
            EXISTS(SELECT 1 FROM bookmark bm WHERE bm.buku_id = buku.id AND bm.user_id = $userId) AS is_bookmarked,
            COALESCE((SELECT SUM(pd.jumlah) FROM peminjaman_detail pd JOIN peminjaman pm ON pm.id = pd.peminjaman_id WHERE pd.buku_id = buku.id AND pm.status = 'dipinjam'),0) AS jumlah_dipinjam
        FROM buku
        LEFT JOIN kategori ON buku.kategori_id = kategori.id
        LEFT JOIN penerbit ON buku.penerbit_id = penerbit.id
        LEFT JOIN rak ON buku.id_rak = rak.id
        WHERE buku.id = ?
          AND buku.status_aktif = 1
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$buku = $stmt->get_result()->fetch_assoc();

if (!$buku) {
  header("Location: " . url("buku/tampil.php"));
  exit();
}

$conn->query(
  "UPDATE buku SET views_count = views_count + 1 WHERE id = " .
    $id .
    " AND status_aktif = 1"
);

$stok = (int) $buku["jumlah_stok"];
$dipinjam = (int) $buku["jumlah_dipinjam"];
$tersedia = max(0, $stok - $dipinjam);

$gambarOtomatis = empty($buku["gambar"]) || $buku["gambar"] === "default.svg";
$gambar = $gambarOtomatis
  ? url("assets/images/default.svg")
  : normalizeBookCoverUrl($buku["gambar"]);

$pageTitle = "Detail Buku";
$pageDescription = "Informasi lengkap koleksi buku.";

require_once __DIR__ . "/../includes/layout_header.php";
?>

<section class="detail-page">
    <div class="detail-cover">
        <img
            src="<?= e($gambar) ?>"
            data-book-id="<?= (int) $buku["id"] ?>"
            data-auto-cover="<?= $gambarOtomatis ? "1" : "0" ?>"
            alt="<?= e($buku["judul"]) ?>"
            onerror="this.src='<?= e(url("assets/images/default.svg")) ?>'"
        >
    </div>

    <div class="detail-body">
        <span class="book-category">
            <?= e($buku["nama_kategori"] ?? "Umum") ?>
        </span>

        <h1><?= e($buku["judul"]) ?></h1>

        <p class="detail-author">
            ✍ <?= e($buku["pengarang"] ?? "-") ?>
        </p>

        <div class="detail-synopsis">
            <h3>Sinopsis</h3>
            <p>
                <?= nl2br(e($buku["sinopsis"] ?? "Belum ada sinopsis.")) ?>
            </p>
        </div>

        <div class="detail-info">

            <div>
                <small>Kategori</small>
                <b><?= e($buku["nama_kategori"] ?? "-") ?></b>
            </div>

            <div>
                <small>Penerbit</small>
                <b><?= e($buku["nama_penerbit"] ?? "-") ?></b>
            </div>

            <div>
                <small>Tahun Terbit</small>
                <b><?= e($buku["tahun_terbit"] ?? "-") ?></b>
            </div>

            <div>
                <small>Halaman</small>
                <b><?= e($buku["jumlah_halaman"] ?? "-") ?></b>
            </div>

            <div>
                <small>Total Stok</small>
                <b><?= $stok ?></b>
            </div>

            <div>
                <small>Tersedia</small>
                <b class="text-green"><?= $tersedia ?></b>
            </div>

            <div>
                <small>Sedang Dipinjam</small>
                <b class="text-red"><?= $dipinjam ?></b>
            </div>

            <div>
                <small>Rak</small>
                <b><?= e(
                  !empty($buku["kode_rak"])
                    ? $buku["kode_rak"] . " - " . ($buku["nama_rak"] ?? "")
                    : "-"
                ) ?></b>
            </div>

            <div>
                <small>Dilihat</small>
                <b><?= (int) ($buku["views_count"] ?? 0) + 1 ?> kali</b>
            </div>
        </div>

        <div class="book-social-panel">
            <div class="rating-box">
                <div class="rating-score">⭐ <?= e(
                  $buku["rating_rata"]
                ) ?><small>/5</small></div>
                <div>
                    <strong>Rating Buku</strong>
                    <p><?= (int) $buku[
                      "jumlah_rating"
                    ] ?> pengguna sudah memberi rating.</p>
                </div>
            </div>

            <form class="rating-form" method="post"
      action="<?= e(url("aksi/rating.php")) ?>">
    <?= csrfField() ?>

    <input type="hidden" name="buku_id" value="<?= $id ?>">

    <span>Rating kamu:</span>

    <div class="star-picker" role="radiogroup" aria-label="Pilih rating">
        <?php for ($star = 5; $star >= 1; $star--): ?>
            <input
                type="radio"
                id="star<?= $star ?>"
                name="rating"
                value="<?= $star ?>"
                <?= (int) $buku["rating_saya"] === $star ? "checked" : "" ?>
                required
            >
            <label for="star<?= $star ?>" title="<?= $star ?> bintang">★</label>
        <?php endfor; ?>
    </div>

    <button type="submit">Simpan Rating</button>
</form>
                </div>
                <button class="btn primary small" type="submit">Simpan Rating</button>
            </form>

            <form method="post" action="<?= e(url("aksi/bookmark.php")) ?>">
    <?= csrfField() ?>

    <input type="hidden" name="buku_id" value="<?= $id ?>">
    <input type="hidden" name="return" value="buku/detail.php">

    <button type="submit"
            class="btn <?= !empty($buku["is_bookmarked"])
              ? "bookmark-active"
              : "" ?>">
        <?= !empty($buku["is_bookmarked"])
          ? "✓ Tersimpan di Bookmark"
          : "🔖 Tambahkan ke Bookmark" ?>
    </button>
</form>
        </div>

        <div class="detail-actions">
            <?php if (!empty($buku["ebook_file"])): ?>
                <a
                    class="btn ebook-btn"
                    href="<?= e(url("buku/ebook.php?id=" . $id)) ?>"
                    target="_blank"
                    rel="noopener"
                >
                    📄 Baca E-Book
                </a>
            <?php endif; ?>

            <?php if ($_SESSION["level"] === "user" && $tersedia > 0): ?>
                <a
                    class="btn primary"
                    href="<?= e(
                      url("transaksi/peminjaman.php?buku_id=" . $id)
                    ) ?>"
                >
                    📖 Pinjam Buku
                </a>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <a
                href="<?= e(
                  url(
                    $_SESSION["level"] === "admin"
                      ? "buku/tampil.php"
                      : "pencarian/cari_buku.php"
                  )
                ) ?>"
                class="btn"
            >
                ← Kembali ke Koleksi
            </a>

            <?php if ($_SESSION["level"] === "admin"): ?>
                <a
                    href="<?= e(url("buku/edit.php")) ?>?id=<?= $id ?>"
                    class="btn primary"
                >
                    Edit Buku
                </a>

                <a
                    href="<?= e(url("buku/hapus.php")) ?>?id=<?= $id ?>"
                    class="btn danger"
                    onclick="return confirm('Yakin ingin menghapus buku ini?')"
                >
                    Hapus
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>


<script>
(function () {
    const img = document.querySelector('img[data-auto-cover="1"]');
    if (!img) return;
    fetch(<?= json_encode(
      url("buku/auto_cover.php")
    ) ?> + '?id=' + encodeURIComponent(img.dataset.bookId), {headers: {'Accept':'application/json'}})
        .then(function (response) { return response.json(); })
        .then(function (data) { if (data.success && data.cover) img.src = data.cover; });
})();
</script>

<?php require_once __DIR__ . "/../includes/layout_footer.php"; ?>
