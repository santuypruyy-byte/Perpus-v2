<?php
/**
 * ============================================================================
 * FILE: auth/login.php (Proses Autentikasi / Masuk ke Sistem)
 * ============================================================================
 * FUNGSI UTAMA:
 * Memverifikasi username dan password pengguna, lalu membuat sesi login (Session).
 *
 * ALUR LOGIKA:
 * 1. Jika user sudah login (isLogin()), langsung arahkan ke dashboard.
 * 2. Saat form dikirim (POST), ambil username dan password lalu cari di database
 *    menggunakan Prepared Statement (`prepare` & `bind_param`) untuk mencegah SQL Injection.
 * 3. Periksa kecocokan password dengan `password_verify()` (untuk password ter-hash).
 *    Jika akun masih memakai password lama (plain text), sistem otomatis meng-upgrade
 *    password tersebut menjadi hash modern (`password_hash`) saat berhasil login.
 * 4. Panggil `session_regenerate_id(true)` untuk mencegah serangan Session Fixation,
 *    simpan data user ke $_SESSION, lalu arahkan ke dashboard.
 * ============================================================================
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/connection.php';

if (isLogin()) {
    header('Location: ' . url('dashboard/dashboard.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $conn->prepare("
            SELECT
                id,
                nama,
                username,
                password,
                level,
                anggota_id
            FROM user
            WHERE username = ?
            LIMIT 1
        ");

        if (!$stmt) {
            $error = 'Terjadi kesalahan pada database.';
        } else {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $u = $stmt->get_result()->fetch_assoc();

            /*
             * Database lama berisi password biasa (contoh: 12345),
             * sedangkan user baru dari menu admin dapat berisi password_hash().
             * Karena itu login mendukung keduanya.
             */
            $passwordValid = false;
            if ($u) {
                $storedPassword = (string) $u['password'];
                $isHashed = password_get_info($storedPassword)['algo'] !== null
                    && password_get_info($storedPassword)['algo'] !== 0;

                // Akun lama yang masih menyimpan password biasa tetap bisa login satu kali,
                // lalu password langsung diubah menjadi hash yang aman.
                $passwordValid = $isHashed
                    ? password_verify($password, $storedPassword)
                    : hash_equals($storedPassword, $password);

                if ($passwordValid && (!$isHashed || password_needs_rehash($storedPassword, PASSWORD_DEFAULT))) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upgrade = $conn->prepare('UPDATE user SET password = ? WHERE id = ?');
                    $upgrade->bind_param('si', $newHash, $u['id']);
                    $upgrade->execute();
                    $upgrade->close();
                }
            }

            if ($passwordValid) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = (int) $u['id'];
                $_SESSION['nama'] = $u['nama'];
                $_SESSION['level'] = $u['level'];
                $_SESSION['anggota_id'] = $u['anggota_id'] !== null
                    ? (int) $u['anggota_id']
                    : null;

                header('Location: ' . url('dashboard/dashboard.php'));
                exit;
            }

            $error = 'Username atau password salah.';
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login Perpustakaan</title>
    <link rel="stylesheet" href="<?= e(url('assets/css/login.css')) ?>">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="brand">
            <div class="brand-icon">📚</div>
            <div>
                <b>Perpustakaan</b>
                <small>Sistem Peminjaman Buku</small>
            </div>
        </div>

        <h1>Selamat Datang</h1>
        <p class="muted">Silakan masuk ke akun Anda.</p>

        <?php if ($error): ?>
            <div class="alert danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">            <?= csrfField() ?>
            <label>Username</label>
            <input
                name="username"
                required
                autocomplete="username"
                value="<?= e($_POST['username'] ?? '') ?>"
            >

            <label>Password</label>
            <input
                type="password"
                name="password"
                required
                autocomplete="current-password"
            >

            <button class="btn primary full" type="submit">Login</button>
        </form>

        <p class="login-note">
            Belum punya akun?
            <a href="<?= e(url('auth/daftar.php')) ?>">Daftar sebagai user</a>
        </p>
    </div>
</body>
</html>
