<?php
/**
 * ============================================================================
 * FILE: auth/daftar.php (Registrasi Anggota & Akun Baru Sekaligus)
 * ============================================================================
 * FUNGSI UTAMA:
 * Mendaftarkan pengunjung menjadi anggota perpustakaan sekaligus membuatkan akun login.
 *
 * ALUR LOGIKA:
 * 1. Memvalidasi seluruh input form (nama, tanggal lahir, format nomor telepon, kekuatan password)
 *    serta memeriksa token CSRF.
 * 2. Mengecek apakah username sudah dipakai atau data anggota (nama + tgl lahir) sudah terdaftar.
 * 3. Memulai Database Transaction (`begin_transaction()`):
 *    - Membuat kode anggota baru otomatis (contoh: AG005).
 *    - Menyimpan biodata ke tabel `anggota`.
 *    - Meng-hash password dengan `password_hash()` dan menyimpan akun ke tabel `user`
 *      yang terhubung dengan `anggota_id` baru tersebut.
 * 4. Jika kedua proses berhasil, simpan permanen dengan `commit()`. Jika gagal, batalkan (`rollback()`).
 * ============================================================================
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/connection.php';

// Pastikan error MySQL dilempar sebagai exception (default PHP 8.1+, eksplisit untuk versi lama).
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (isLogin()) {
    header('Location: ' . url('dashboard/dashboard.php'));
    exit;
}

/*
 * Pendaftaran membuat DUA data sekaligus (dalam satu transaksi):
 *
 * 1. Tabel `anggota`: kode_anggota (otomatis AG001, AG002, ...), data diri, .
 * 2. Tabel `user`: akun level user yang terhubung ke anggota tersebut.
 * Jika salah satu gagal, keduanya dibatalkan (rollback).
 */

function buatKodeAnggota(mysqli $conn): string
{
    // FOR UPDATE menahan pendaftar lain sampai transaksi ini selesai,
    // supaya dua orang tidak mendapat kode yang sama.
    $res = $conn->query(
        "SELECT COALESCE(MAX(CAST(SUBSTRING(kode_anggota, 3) AS UNSIGNED)), 0) AS terakhir
         FROM anggota
         WHERE kode_anggota REGEXP '^AG[0-9]+$'
         FOR UPDATE"
    );
    $terakhir = (int) $res->fetch_assoc()['terakhir'];

    return 'AG' . str_pad((string) ($terakhir + 1), 3, '0', STR_PAD_LEFT);
}

$error = '';
$success = '';
$kodeBaru = '';

// Nilai form (dikembalikan ke form jika ada error; password tidak pernah dikembalikan).
$nama = trim($_POST['nama'] ?? '');
$jenisKelamin = $_POST['jenis_kelamin'] ?? '';
$tempatLahir = trim($_POST['tempat_lahir'] ?? '');
$tanggalLahir = trim($_POST['tanggal_lahir'] ?? '');
$telpon = trim($_POST['telpon'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$username = trim($_POST['username'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf();
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    $tgl = DateTime::createFromFormat('Y-m-d', $tanggalLahir);
    $tglValid = $tgl && $tgl->format('Y-m-d') === $tanggalLahir;

    if (
        $nama === '' || $jenisKelamin === '' || $tempatLahir === '' ||
        $tanggalLahir === '' || $telpon === '' || $alamat === '' ||
        $username === '' || $password === '' || $konfirmasi === ''
    ) {
        $error = 'Semua data wajib diisi.';
    } elseif (mb_strlen($nama) > 100) {
        $error = 'Nama maksimal 100 karakter.';
    } elseif (!in_array($jenisKelamin, ['pria', 'wanita'], true)) {
        $error = 'Jenis kelamin tidak valid.';
    } elseif (mb_strlen($tempatLahir) > 100) {
        $error = 'Tempat lahir maksimal 100 karakter.';
    } elseif (!$tglValid || $tanggalLahir > date('Y-m-d')) {
        $error = 'Tanggal lahir tidak valid.';
    } elseif (!preg_match('/^[0-9]{8,12}$/', $telpon)) {
        // Kolom anggota.telpon = varchar(12) pada database.
        $error = 'Nomor telpon harus 8-12 digit angka.';
    } elseif (!preg_match('/^[A-Za-z0-9._-]{3,100}$/', $username)) {
        $error = 'Username 3-100 karakter, hanya huruf, angka, titik, garis bawah, atau strip.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = 'Password harus memiliki huruf kecil, huruf besar, dan angka.';
    } elseif (strlen($password) > 72) {
        // Batas aman bcrypt (password_hash) agar tidak terpotong diam-diam.
        $error = 'Password maksimal 72 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        // Username harus unik.
        $cek = $conn->prepare('SELECT id FROM user WHERE username = ? LIMIT 1');
        $cek->bind_param('s', $username);
        $cek->execute();
        $usernameDipakai = $cek->get_result()->num_rows > 0;
        $cek->close();

        // Cegah anggota ganda: nama + tanggal lahir yang sama sudah terdaftar.
        $cekAnggota = $conn->prepare(
            'SELECT id FROM anggota WHERE nama = ? AND tanggal_lahir = ? LIMIT 1'
        );
        $cekAnggota->bind_param('ss', $nama, $tanggalLahir);
        $cekAnggota->execute();
        $anggotaAda = $cekAnggota->get_result()->num_rows > 0;
        $cekAnggota->close();

        if ($usernameDipakai) {
            $error = 'Username sudah digunakan.';
        } elseif ($anggotaAda) {
            $error = 'Anggota dengan nama dan tanggal lahir tersebut sudah terdaftar. Hubungi admin jika Anda belum memiliki akun.';
        } else {
            try {
                $conn->begin_transaction();

                // 1. Tambah anggota baru.
                $kode = buatKodeAnggota($conn);
                $stmtA = $conn->prepare(
                    'INSERT INTO anggota
                        (kode_anggota, nama, jenis_kelamin, tempat_lahir, tanggal_lahir, telpon, alamat)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $stmtA->bind_param(
                    'sssssss',
                    $kode, $nama, $jenisKelamin, $tempatLahir, $tanggalLahir, $telpon, $alamat
                );
                $stmtA->execute();
                $anggotaId = (int) $conn->insert_id;
                $stmtA->close();

                // 2. Tambah akun user yang terhubung ke anggota tersebut.
                $level = 'user';
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmtU = $conn->prepare(
                    'INSERT INTO user (nama, username, password, level, anggota_id)
                     VALUES (?, ?, ?, ?, ?)'
                );
                $stmtU->bind_param('ssssi', $nama, $username, $passwordHash, $level, $anggotaId);
                $stmtU->execute();
                $stmtU->close();

                $conn->commit();

                $success = 'Pendaftaran berhasil. Kode anggota Anda: ' . $kode . '. Silakan login.';
                $nama = $jenisKelamin = $tempatLahir = $tanggalLahir = '';
                $telpon = $alamat = $username = '';
            } catch (Throwable $ex) {
                if ($conn->errno || $conn->connect_errno === 0) { @ $conn->rollback(); }
                // 1062 = duplicate entry (username bentrok saat dua orang daftar bersamaan).
                $error = $ex->getCode() === 1062
                    ? 'Username sudah digunakan.'
                    : 'Pendaftaran gagal, silakan coba lagi.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Daftar - Perpustakaan</title>
    <link rel="stylesheet" href="<?= e(url('assets/css/login.css')) ?>">
</head>
<body class="login-body">
    <div class="login-card" style="width:min(560px,100%)">
        <div class="brand">
            <div class="brand-icon">📚</div>
            <div>
                <b>Perpustakaan</b>
                <small>Daftar Anggota Baru</small>
            </div>
        </div>

        <h1>Buat Akun</h1>
        <p class="muted">Isi data diri Anda untuk menjadi anggota dan mengakses sistem perpustakaan.</p>

        <?php if ($error): ?>
            <div class="alert danger" style="margin-top:16px"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success" style="margin-top:16px"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="form-grid">
                <div class="full">
                    <label for="nama">Nama Lengkap</label>
                    <input id="nama" name="nama" placeholder="Masukkan nama lengkap"
                           value="<?= e($nama) ?>" maxlength="100" required>
                </div>

                <div>
                    <label for="jenis_kelamin">Jenis Kelamin</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="">-- Pilih --</option>
                        <option value="pria" <?= $jenisKelamin === 'pria' ? 'selected' : '' ?>>Pria</option>
                        <option value="wanita" <?= $jenisKelamin === 'wanita' ? 'selected' : '' ?>>Wanita</option>
                    </select>
                </div>

                <div>
                    <label for="telpon">Telpon</label>
                    <input id="telpon" name="telpon" placeholder="Contoh: 081234567890"
                           value="<?= e($telpon) ?>" inputmode="numeric"
                           pattern="[0-9]{8,12}" maxlength="12" required>
                </div>

                <div>
                    <label for="tempat_lahir">Tempat Lahir</label>
                    <input id="tempat_lahir" name="tempat_lahir" placeholder="Kota kelahiran"
                           value="<?= e($tempatLahir) ?>" maxlength="100" required>
                </div>

                <div>
                    <label for="tanggal_lahir">Tanggal Lahir</label>
                    <input id="tanggal_lahir" type="date" name="tanggal_lahir"
                           value="<?= e($tanggalLahir) ?>" max="<?= e(date('Y-m-d')) ?>" required>
                </div>

                <div class="full">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat" rows="3"
                              placeholder="Masukkan alamat lengkap" required><?= e($alamat) ?></textarea>
                </div>

                <div class="full">
                    <label for="username">Username</label>
                    <input id="username" name="username" placeholder="Masukkan username"
                           value="<?= e($username) ?>" maxlength="100" required autocomplete="username">
                </div>

                <div>
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" placeholder="Minimal 8 karakter, kombinasi huruf dan angka"
                           minlength="8" maxlength="72" required autocomplete="new-password">
                </div>

                <div>
                    <label for="konfirmasi_password">Konfirmasi Password</label>
                    <input id="konfirmasi_password" type="password" name="konfirmasi_password"
                           placeholder="Ulangi password" minlength="8" maxlength="72" required
                           autocomplete="new-password">
                </div>
            </div>

            <button class="btn primary full" type="submit">Daftar</button>
        </form>

        <p class="login-note">
            Sudah memiliki akun?
            <a href="<?= e(url('auth/login.php')) ?>">Login di sini</a>
        </p>
    </div>
</body>
</html>