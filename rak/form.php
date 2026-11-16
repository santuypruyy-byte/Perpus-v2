<?php
/**
 * ============================================================================
 * FILE: rak/form.php (Form Tambah & Edit Data Rak Buku)
 * ============================================================================
 * FUNGSI UTAMA:
 * Menyediakan form untuk menambah atau mengubah kode rak, nama rak, serta menghubungkan
 * rak tersebut dengan kategori buku tertentu.
 * ============================================================================
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/connection.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$data = ['kode_rak' => '', 'nama_rak' => '', 'kategori_id' => ''];
$error = '';

if ($id > 0) {
    $stmt = $conn->prepare('SELECT * FROM rak WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc() ?: $data;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['kode_rak'] = trim($_POST['kode_rak'] ?? '');
    $data['nama_rak'] = trim($_POST['nama_rak'] ?? '');
    $data['kategori_id'] = (int)($_POST['kategori_id'] ?? 0);

    if ($data['kode_rak'] === '' || $data['nama_rak'] === '' || $data['kategori_id'] < 1) {
        $error = 'Kode rak, nama rak, dan kategori wajib diisi.';
    } else {
        try {
            if ($id > 0) {
                $stmt = $conn->prepare('UPDATE rak SET kode_rak = ?, nama_rak = ?, kategori_id = ? WHERE id = ?');
                $stmt->bind_param('ssii', $data['kode_rak'], $data['nama_rak'], $data['kategori_id'], $id);
            } else {
                $stmt = $conn->prepare('INSERT INTO rak (kode_rak, nama_rak, kategori_id, status_aktif) VALUES (?, ?, ?, 1)');
                $stmt->bind_param('ssi', $data['kode_rak'], $data['nama_rak'], $data['kategori_id']);
            }

            if ($stmt->execute()) {
                header('Location: ' . url('rak/index.php'));
                exit;
            }
            $error = 'Data rak gagal disimpan. Pastikan kode rak belum digunakan.';
        } catch (Throwable $e) {
            $error = 'Data rak gagal disimpan. Pastikan kode rak belum digunakan.';
        }
    }
}

$kategori = $conn->query('SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori ASC');

$pageTitle = $id > 0 ? 'Edit Rak' : 'Tambah Rak';
$pageDescription = $id > 0 ? 'Perbarui data rak perpustakaan.' : 'Tambahkan rak baru ke perpustakaan.';
require_once __DIR__ . '/../includes/layout_header.php';
?>

<?php if ($error): ?>
    <div class="alert danger"><?= e($error) ?></div>
<?php endif; ?>

<section class="panel form-panel">
    <div class="form-title">
        <span class="page-label">DATA MASTER</span>
        <h1><?= e($pageTitle) ?></h1>
        <p><?= e($pageDescription) ?></p>
    </div>

    <form method="post">
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="form-grid">
            <div>
                <label for="kode_rak">Kode Rak</label>
                <input type="text" id="kode_rak" name="kode_rak" value="<?= e($data['kode_rak']) ?>" placeholder="Contoh: A1" maxlength="30" required>
            </div>

            <div>
                <label for="nama_rak">Nama Rak</label>
                <input type="text" id="nama_rak" name="nama_rak" value="<?= e($data['nama_rak']) ?>" placeholder="Contoh: Rak Novel 1" maxlength="100" required>
            </div>

            <div class="full">
                <label for="kategori_id">Kategori</label>
                <select id="kategori_id" name="kategori_id" required>
                    <option value="">Pilih kategori</option>
                    <?php while ($row = $kategori->fetch_assoc()): ?>
                        <option value="<?= (int)$row['id'] ?>" <?= (int)$row['id'] === (int)$data['kategori_id'] ? 'selected' : '' ?>>
                            <?= e($row['nama_kategori']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="full form-actions">
                <button type="submit" class="btn primary">Simpan Rak</button>
                <a href="<?= e(url('rak/index.php')) ?>" class="btn">Kembali</a>
            </div>
        </div>
    </form>
</section>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>
