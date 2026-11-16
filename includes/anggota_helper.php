<<<<<<< HEAD
<?php
/**
 * ============================================================================
 * FILE: includes/anggota_helper.php (Helper Pembuatan Kode Anggota Otomatis)
 * ============================================================================
 * FUNGSI UTAMA:
 * Menghasilkan nomor kode anggota baru secara berurutan (contoh: AG001, AG002, dst).
 *
 * ALUR LOGIKA:
 * 1. Mengambil angka terbesar dari kode_anggota yang berawalan 'AG' di tabel anggota.
 * 2. Menggunakan klausa SQL `FOR UPDATE` untuk mengunci baris sementara selama transaksi
 *    agar tidak terjadi kode ganda jika dua orang mendaftar bersamaan.
 * 3. Menambahkan 1 pada nomor terakhir dan memformatnya dengan 3 digit nol di depan (str_pad).
 * ============================================================================
 */
/*
 * Helper anggota yang dipakai bersama oleh form admin (user/form.php).
 * Isinya sama dengan buatKodeAnggota() di auth/daftar.php.
 * Dibungkus function_exists agar tidak bentrok jika suatu saat
 * daftar.php ikut memakai file ini.
 */

if (!function_exists('buatKodeAnggota')) {
    function buatKodeAnggota(mysqli $conn): string
    {
        // FOR UPDATE menahan proses lain sampai transaksi ini selesai,
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
=======
<?php

/*
 * Helper anggota yang dipakai bersama oleh form admin (user/form.php).
 * Isinya sama dengan buatKodeAnggota() di auth/daftar.php.
 * Dibungkus function_exists agar tidak bentrok jika suatu saat
 * daftar.php ikut memakai file ini.
 */

if (!function_exists('buatKodeAnggota')) {
    function buatKodeAnggota(mysqli $conn): string
    {
        // FOR UPDATE menahan proses lain sampai transaksi ini selesai,
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
>>>>>>> d3a474d9f86ca513d111ac9b325518e17d65db66
}