<?php
<<<<<<< HEAD
/**
 * ============================================================================
 * FILE: includes/auth.php (Sistem Autentikasi, Hak Akses & Keamanan CSRF)
 * ============================================================================
 * FUNGSI UTAMA:
 * Mengelola sesi login (Session), membatasi hak akses antara Admin dan User,
 * serta melindungi form dari serangan CSRF (Cross-Site Request Forgery).
 *
 * ALUR LOGIKA:
 * 1. Memulai session PHP jika belum aktif (session_start()).
 * 2. Fungsi isLogin(), isAdmin(), isUser() mengecek status dan level pengguna di $_SESSION.
 * 3. Fungsi requireLogin(), requireAdmin(), requireUser() bertindak sebagai "satpam" halaman:
 *    jika tidak memenuhi syarat, user otomatis dilempar ke halaman login atau dashboard.
 * 4. Fungsi csrfToken(), csrfField(), requireValidCsrf() membuat dan memvalidasi token acak
 *    agar form POST hanya bisa dikirim dari halaman aplikasi yang sah.
 * ============================================================================
 */
=======
>>>>>>> d3a474d9f86ca513d111ac9b325518e17d65db66
require_once __DIR__ . '/../config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLogin(): bool
{
    return isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] > 0;
}

function isAdmin(): bool
{
    return isLogin() && ($_SESSION['level'] ?? '') === 'admin';
}

function isUser(): bool
{
    return isLogin() && ($_SESSION['level'] ?? '') === 'user';
}

function requireLogin(): void
{
    if (!isLogin()) {
        header('Location: ' . url('auth/login.php'));
        exit;
    }
}

function requireAdmin(): void
{
    requireLogin();

    if (!isAdmin()) {
        header('Location: ' . url('dashboard/dashboard.php'));
        exit;
    }
}

function requireUser(): void
{
    requireLogin();

    if (!isUser()) {
        header('Location: ' . url('dashboard/dashboard.php'));
        exit;
    }
}


function csrfToken(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

function requireValidCsrf(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Metode permintaan tidak diizinkan.');
    }
    $sent = $_POST['csrf_token'] ?? '';
    $known = $_SESSION['csrf_token'] ?? '';
    if (!is_string($sent) || !is_string($known) || $known === '' || !hash_equals($known, $sent)) {
        http_response_code(403);
        exit('Permintaan ditolak. Muat ulang halaman lalu coba lagi.');
    }
}
