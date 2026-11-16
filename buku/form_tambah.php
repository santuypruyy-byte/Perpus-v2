<?php
<<<<<<< HEAD
/**
 * ============================================================================
 * FILE: buku/form_tambah.php (Form Input Tambah Buku Baru)
 * ============================================================================
 * FUNGSI UTAMA:
 * Menampilkan formulir bagi Admin untuk menambahkan koleksi buku baru ke database.
 *
 * ALUR LOGIKA:
 * 1. Mengambil data master kategori, penerbit, dan rak aktif dari database untuk mengisi
 *    pilihan dropdown pada formulir.
 * 2. Mengirimkan data formulir dengan metode POST dan `enctype="multipart/form-data"`
 *    (karena mendukung upload file gambar sampul dan PDF e-book) ke file `buku/tambah.php`.
 * ============================================================================
 */
=======
>>>>>>> d3a474d9f86ca513d111ac9b325518e17d65db66
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

require_once __DIR__ . '/../config/connection.php';

$kategori = $conn->query(
    'SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori'
);

$penerbit = $conn->query(
    'SELECT id, nama_penerbit FROM penerbit ORDER BY nama_penerbit'
);

$rakRows = $conn->query(
    'SELECT id, kategori_id, kode_rak, nama_rak FROM rak WHERE status_aktif = 1 ORDER BY kode_rak'
);

$pageTitle = 'Tambah Buku';
$pageDescription = 'Tambahkan koleksi baru ke perpustakaan.';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<section class="panel form-panel">
    <div class="form-title">
        <span class="page-label">DATA BUKU</span>
        <h1>Tambah Buku</h1>
        <p>Lengkapi informasi buku di bawah ini.</p>
    </div>

    <form
        action="<?= e(url('buku/tambah.php')) ?>"
        method="post"
        enctype="multipart/form-data"
        class="form-grid"
    >
        <div>
            <label>Judul Buku</label>
            <input
                type="text"
                name="judul"
                placeholder="Masukkan judul buku"
                required
            >
        </div>

        <div>
            <label>Kategori</label>
            <select name="kategori_id" required>
                <option value="">Pilih kategori</option>

                <?php while ($row = $kategori->fetch_assoc()): ?>
                    <option value="<?= (int) $row['id'] ?>">
                        <?= e($row['nama_kategori']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label>Penerbit</label>
            <select name="penerbit_id" required>
                <option value="">Pilih penerbit</option>

                <?php while ($row = $penerbit->fetch_assoc()): ?>
                    <option value="<?= (int) $row['id'] ?>">
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
                placeholder="Nama pengarang"
            >
        </div>

        <div>
            <label>Jumlah Halaman</label>
            <input
                type="number"
                name="jumlah_halaman"
                min="1"
                placeholder="Contoh: 300"
            >
        </div>

        <div>
            <label>Jumlah Stok</label>
            <input
                type="number"
                name="jumlah_stok"
                min="0"
                value="1"
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
                placeholder="2026"
            >
        </div>

        <div>
            <label>Rak</label>
            <select name="rak_id" id="rakSelect" required disabled>
                <option value="">Pilih kategori terlebih dahulu</option>
            </select>
            <small class="file-help">Rak ditentukan berdasarkan kategori yang dipilih.</small>
        </div>

        <div class="full">
            <label>Sampul Buku</label>
            <div class="cover-online-preview" id="coverPreviewBox">
                <img id="coverPreview" src="<?= e(url('assets/images/default.svg')) ?>" alt="Pratinjau sampul buku">
                <div>
                    <strong id="coverStatus">Sampul akan dicari otomatis</strong>
                    <small class="file-help">Jika sampul online tidak ditemukan, pilih gambar sampul sendiri. Sampul lokal akan disimpan dan menjadi sampul utama buku.</small>
                </div>
            </div>
            <input type="file" name="cover_file" id="coverFile" accept="image/jpeg,image/png,image/webp">
            <small class="file-help">JPG, PNG, atau WEBP. Maksimal 5 MB. Opsional.</small>
            <input type="hidden" name="gambar" id="gambarUrl" value="">
        </div>

        <div>
            <label>E-Book (PDF)</label>
            <input type="file" name="ebook_file" accept="application/pdf">
            <small class="file-help">PDF maksimal 10 MB. Opsional.</small>
        </div>

        <div class="full">
            <label>Sinopsis</label>
            <textarea
                name="sinopsis"
                rows="6"
                placeholder="Masukkan sinopsis buku"
            ></textarea>
        </div>

        <div class="full form-actions">
            <button type="submit" class="btn primary">
                Simpan Buku
            </button>

            <a
                href="<?= e(url('buku/tampil.php')) ?>"
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

    function isiRak() {
        const kategoriId = kategori.value;
        rakSelect.innerHTML = '';
        if (!kategoriId) {
            rakSelect.disabled = true;
            rakSelect.required = true;
            rakSelect.innerHTML = '<option value="">Pilih kategori terlebih dahulu</option>';
            return;
        }
        const rows = rakData.filter(function (rak) { return String(rak.kategori_id) === String(kategoriId); });
        rakSelect.disabled = false;
        rakSelect.innerHTML = '<option value="">Pilih rak</option>';
        rows.forEach(function (rak) {
            const option = document.createElement('option');
            option.value = rak.id;
            option.textContent = rak.kode_rak + ' - ' + rak.nama_rak;
            rakSelect.appendChild(option);
        });
        if (!rows.length) {
            rakSelect.innerHTML = '<option value="">Belum ada rak untuk kategori ini</option>';
            rakSelect.disabled = true;
        }
    }

    kategori.addEventListener('change', isiRak);
    isiRak();

    const judul = document.querySelector('[name="judul"]');
    const pengarang = document.querySelector('[name="pengarang"]');
    const penerbit = document.querySelector('[name="penerbit_id"]');
    const img = document.getElementById('coverPreview');
    const status = document.getElementById('coverStatus');
    const hidden = document.getElementById('gambarUrl');
    let timer;
    async function cari() {
        const q = new URLSearchParams({judul: judul.value, pengarang: pengarang.value, penerbit_id: penerbit.value});
        if (judul.value.trim().length < 3) return;
        status.textContent = 'Mencari sampul online...';
        try {
            const r = await fetch('<?= e(url('buku/cari_cover.php')) ?>?' + q.toString(), {headers: {'Accept':'application/json'}});
            const data = await r.json();
            if (data.cover) {
                img.src = data.cover; hidden.value = data.cover; status.textContent = 'Sampul ditemukan otomatis.';
            } else {
                img.src = '<?= e(url('assets/images/default.svg')) ?>'; hidden.value = ''; status.textContent = 'Sampul tidak ditemukan. Default digunakan.';
            }
        } catch (e) {
            status.textContent = 'Pencarian sampul gagal. Sistem tetap dapat menyimpan buku.';
        }
    }
    [judul, pengarang].forEach(el => el.addEventListener('blur', () => { clearTimeout(timer); timer=setTimeout(cari, 150); }));

    const coverFile = document.getElementById('coverFile');
    coverFile.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        img.src = URL.createObjectURL(file);
        hidden.value = '';
        status.textContent = 'Sampul lokal dipilih. Sampul ini akan digunakan saat buku disimpan.';
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
