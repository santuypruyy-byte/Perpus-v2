<?php
<<<<<<< HEAD
/**
 * ============================================================================
 * FILE: config/connection.php (Koneksi Database MySQLi)
 * ============================================================================
 * FUNGSI UTAMA:
 * Membuka jalur komunikasi (koneksi) antara aplikasi PHP dengan database MySQL.
 *
 * ALUR LOGIKA:
 * 1. Mengambil kredensial database (host, user, password, nama database) dari environment
 *    atau nilai bawaan.
 * 2. Membuat objek koneksi baru menggunakan kelas `new mysqli(...)`.
 * 3. Mengecek jika koneksi gagal ($conn->connect_error), catat ke log dan tampilkan pesan aman.
 * 4. Mengatur charset menjadi `utf8mb4` agar mendukung seluruh karakter teks dan emoji.
 * ============================================================================
 */
=======
>>>>>>> d3a474d9f86ca513d111ac9b325518e17d65db66
require_once __DIR__ . '/app.php';

// Sesuaikan dengan konfigurasi server lokal. Untuk produksi, gunakan environment variables.
$host = getenv('DB_HOST') ?: "localhost";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : "";
$db   = getenv('DB_NAME') ?: "buku_ukk_v1";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    error_log("Koneksi database gagal: " . $conn->connect_error);
    http_response_code(500);
    exit("Layanan sedang mengalami gangguan. Silakan coba lagi nanti.");
}

$conn->set_charset("utf8mb4");
