<?php
/**
 * ============================================================================
 * FILE: auth/logout.php (Proses Keluar / Hapus Sesi Login)
 * ============================================================================
 * FUNGSI UTAMA:
 * Menghapus seluruh data sesi pengguna dan mengarahkannya kembali ke halaman login.
 *
 * ALUR LOGIKA:
 * 1. Mengosongkan array $_SESSION.
 * 2. Menghapus cookie session di browser pengguna jika aktif.
 * 3. Menghancurkan sesi di server dengan `session_destroy()`.
 * 4. Mengarahkan (redirect) pengguna ke halaman auth/login.php.
 * ============================================================================
 */
require_once __DIR__ . '/../includes/auth.php';

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

header('Location: ' . url('auth/login.php'));
exit;
