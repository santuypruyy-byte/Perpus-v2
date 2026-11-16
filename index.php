<?php
/**
 * ============================================================================
 * FILE: index.php (Halaman Pintu Masuk / Entry Point)
 * ============================================================================
 * FUNGSI UTAMA:
 * Mengarahkan pengunjung pertama kali langsung ke halaman login (auth/login.php).
 *
 * ALUR LOGIKA:
 * 1. Memanggil config/app.php untuk mendapatkan fungsi pembantu url().
 * 2. Mengirim header redirect HTTP menuju halaman auth/login.php lalu menghentikan eksekusi.
 * ============================================================================
 */
require_once __DIR__ . '/config/app.php';
header('Location: ' . url('auth/login.php'));
exit;
