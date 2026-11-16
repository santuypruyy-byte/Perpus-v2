<?php
<<<<<<< HEAD
/**
 * ============================================================================
 * FILE: buku/edit.php (Form Ubah Data Buku)
 * ============================================================================
 * FUNGSI UTAMA:
 * Mengambil data lama buku berdasarkan ID dan menampilkannya di dalam form agar bisa
 * diperbarui oleh Admin.
 *
 * ALUR LOGIKA:
 * 1. Mengambil parameter `id` dari URL ($_GET['id']) dan mencari datanya di tabel `buku`.
 * 2. Jika buku tidak ditemukan atau sudah diarsipkan (`status_aktif = 0`), kembalikan ke `tampil.php`.
 * 3. Memuat daftar kategori, penerbit, dan rak untuk dropdown pilihan, lalu mengirim hasil
 *    perubahan ke `buku/update.php`.
 * ============================================================================
 */
=======
>>>>>>> d3a474d9f86ca513d111ac9b325518e17d65db66
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../includes/book_cover_helper.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id < 1) {
    header('Location: ' . url('buku/tampil.php'));
    exit;
}

$stmt = $conn->prepare(
    'SELECT *
     FROM buku
     WHERE id = ? AND status_aktif = 1'
);

$stmt->bind_param('i', $id);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header('Location: ' . url('buku/tampil.php'));
    exit;
}

$kategori = $conn->query(
    'SELECT id, nama_kategori
     FROM kategori
     ORDER BY nama_kategori'
);

$penerbit = $conn->query(
    'SELECT id, nama_penerbit
     FROM penerbit
     ORDER BY nama_penerbit'
);

$rakRows = $conn->query(
    'SELECT id, kategori_id, kode_rak, nama_rak FROM rak WHERE status_aktif = 1 ORDER BY kode_rak'
);

$pageTitle = 'Edit Buku';
$pageDescription = 'Perbarui informasi buku yang dipilih.';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<section class="panel form-panel">
    <div class="form-title">
        <span class="page-label">DATA BUKU</span>
        <h1>Edit Buku</h1>
        <p>Perbarui informasi buku.</p>
    </div>

    <form
        action="<?= e(url('buku/update.php')) ?>"
        method="post"
        enctype="multipart/form-data"
        class="form-grid"
    >
        <input
            type="hidden"
            name="id"
            value="<?= $id ?>"
        >

        <div>
            <label>Judul Buku</label>
            <input
                type="text"
                name="judul"
                value="<?= e($data['judul']) ?>"
                required
            >
        </div>

        <div>
            <label>Kategori</label>
            <select name="kategori_id" required>
                <?php while ($row = $kategori->fetch_assoc()): ?>
                    <option
                        value="<?= (int) $row['id'] ?>"
                        <?= $row['id'] == $data['kategori_id'] ? 'selected' : '' ?>
                    >
                        <?= e($row['nama_kategori']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label>Penerbit</label>
            <select name="penerbit_id" required>
                <?php while ($row = $penerbit->fetch_assoc()): ?>
                    <option
                        value="<?= (int) $row['id'] ?>"
                        <?= $row['id'] == $data['penerbit_id'] ? 'selected' : '' ?>
                    >
                        <?= e($row['nama_penerbit']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label>Pengarang</label>
            <input
                type="text"
                name="pengarang"
                value="<?= e($data['pengarang'] ?? '') ?>"
            >
        </div>

        <div>
            <label>Jumlah Halaman</label>
            <input
                type="number"
                name="jumlah_halaman"
                min="1"
                value="<?= e($data['jumlah_halaman'] ?? '') ?>"
            >
        </div>

        <div>
            <label>Jumlah Stok</label>
            <input
                type="number"
                name="jumlah_stok"
                min="0"
                value="<?= e($data['jumlah_stok']) ?>"
                required
            >
        </div>

        <div>
            <label>Tahun Terbit</label>
            <input
                type="number"
                name="tahun_terbit"
                min="1900"
                max="2100"
                value="<?= e($data['tahun_terbit'] ?? '') ?>"
            >
        </div>

        <div>
            <label>Rak</label>
            <select name="rak_id" id="rakSelect" required>
                <option value="">Pilih rak</option>
            </select>
            <small class="file-help">Rak hanya menampilkan pilihan yang sesuai dengan kategori.</small>
        </div>

        <div class="full">
            <label>Sampul Buku</label>
            <div class="cover-online-preview" id="coverPreviewBox">
                <img id="coverPreview" src="<?= e(normalizeBookCoverUrl($data['gambar'] ?? '')) ?>" alt="Pratinjau sampul buku">
                <div>
                    <strong id="coverStatus">Sampul tersimpan</strong>
                    <small class="file-help">Jika ingin mengganti sampul, pilih gambar lokal. Sampul lokal akan menjadi prioritas dan tidak akan diganti otomatis.</small>
                </div>
            </div>
            <input type="file" name="cover_file" id="coverFile" accept="image/jpeg,image/png,image/webp">
            <small class="file-help">JPG, PNG, atau WEBP. Maksimal 5 MB. Kosongkan jika ingin mempertahankan sampul saat ini.</small>
            <input type="hidden" name="gambar" id="gambarUrl" value="<?= e($data['gambar'] ?? '') ?>">
        </div>

        <div>
            <label>E-Book (PDF)</label>
            <?php if (!empty($data['ebook_file'])): ?>
                <p class="file-help"><a href="<?= e(url('buku/ebook.php?id=' . $id)) ?>" target="_blank">Lihat e-book saat ini</a></p>
            <?php endif; ?>
            <input type="file" name="ebook_file" accept="application/pdf">
            <small class="file-help">Kosongkan jika ingin mempertahankan e-book lama. PDF maksimal 10 MB.</small>
        </div>

        <div class="full">
            <label>Sinopsis</label>
            <textarea
                name="sinopsis"
                rows="6"
            ><?= e($data['sinopsis'] ?? '') ?></textarea>
        </div>

        <div class="full form-actions">
            <button type="submit" class="btn primary">
                Simpan Perubahan
            </button>

            <a
                href="<?= e(url('buku/detail.php')) ?>?id=<?= $id ?>"
                class="btn"
            >
                Batal
            </a>
        </div>
    </form>
</section>

<script>
(function () {
    const rakData = <?= json_encode($rakRows ? $rakRows->fetch_all(MYSQLI_ASSOC) : []) ?>;
    const kategori = document.querySelector('[name="kategori_id"]');
    const rakSelect = document.getElementById('rakSelect');
    const rakAwal = <?= json_encode((string)($data['id_rak'] ?? '')) ?>;

    function isiRak() {
        const kategoriId = kategori.value;
        const rows = rakData.filter(function (rak) { return String(rak.kategori_id) === String(kategoriId); });
        rakSelect.innerHTML = '<option value="">Pilih rak</option>';
        rows.forEach(function (rak) {
            const option = document.createElement('option');
            option.value = rak.id;
            option.textContent = rak.kode_rak + ' - ' + rak.nama_rak;
            if (String(rak.id) === String(rakAwal)) option.selected = true;
            rakSelect.appendChild(option);
        });
        rakSelect.disabled = rows.length === 0;
    }

    kategori.addEventListener('change', function () {
        if (String(kategori.value) !== String(<?= json_encode((string)($data['kategori_id'] ?? '')) ?>)) {
            rakSelect.value = '';
        }
        isiRak();
    });
    isiRak();

    const judul = document.querySelector('[name="judul"]');
    const pengarang = document.querySelector('[name="pengarang"]');
    const penerbit = document.querySelector('[name="penerbit_id"]');
    const img = document.getElementById('coverPreview');
    const status = document.getElementById('coverStatus');
    const hidden = document.getElementById('gambarUrl');
    let timer;
    async function cari() {
        if (judul.value.trim().length < 3) return;
        const q = new URLSearchParams({judul: judul.value, pengarang: pengarang.value, penerbit_id: penerbit.value});
        status.textContent = 'Mencari sampul online...';
        try {
            const r = await fetch('<?= e(url('buku/cari_cover.php')) ?>?' + q.toString(), {headers: {'Accept':'application/json'}});
            const data = await r.json();
            if (data.cover) {
                img.src = data.cover; hidden.value = data.cover; status.textContent = 'Sampul ditemukan otomatis.';
            } else {
                status.textContent = 'Sampul online tidak ditemukan. Sampul lama dipertahankan.';
            }
        } catch (e) {
            status.textContent = 'Pencarian sampul gagal. Sampul lama dipertahankan.';
        }
    }
    [judul, pengarang].forEach(el => el.addEventListener('blur', () => { clearTimeout(timer); timer=setTimeout(cari, 150); }));

    const coverFile = document.getElementById('coverFile');
    coverFile.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        img.src = URL.createObjectURL(file);
        status.textContent = 'Sampul lokal dipilih. Sampul ini akan menjadi sampul utama setelah disimpan.';
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
