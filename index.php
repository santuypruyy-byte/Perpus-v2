<?php
<<<<<<< HEAD
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
=======
>>>>>>> d3a474d9f86ca513d111ac9b325518e17d65db66
require_once __DIR__ . '/config/app.php';
header('Location: ' . url('auth/login.php'));
exit;
