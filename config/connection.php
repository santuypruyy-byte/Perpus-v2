<?php
require_once __DIR__ . '/app.php';

// Sesuaikan dengan konfigurasi server lokal. Untuk produksi, gunakan environment variables.
$host = getenv('DB_HOST') ?: "sql103.infinityfree.com";
$user = getenv('DB_USER') ?: "if0_43230179";
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : "JjYhwrrFtthpJ5";
$db   = getenv('DB_NAME') ?: "if0_43230179_buku_ukk_v1";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    error_log("Koneksi database gagal: " . $conn->connect_error);
    http_response_code(500);
    exit("Layanan sedang mengalami gangguan. Silakan coba lagi nanti.");
}

$conn->set_charset("utf8mb4");
