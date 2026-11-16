<?php
/**
 * ============================================================================
 * FILE: config/app.php (Konfigurasi Dasar & Helper Global Aplikasi)
 * ============================================================================
 * FUNGSI UTAMA:
 * Menentukan konstanta path dasar (BASE_URL), tarif denda, serta fungsi helper
 * global seperti url() untuk membuat tautan dinamis dan e() untuk mencegah XSS.
 *
 * ALUR LOGIKA:
 * 1. Mengatur error_reporting agar error dicatat di log server, bukan ditampilkan ke user.
 * 2. Mendeteksi lokasi folder proyek secara otomatis agar aplikasi fleksibel dijalankan
 *    baik di subfolder htdocs maupun di root domain.
 * 3. Mendefinisikan konstanta DENDA_PER_BUKU_PER_HARI = Rp 1.000.
 * 4. Menyediakan fungsi e() sebagai pembungkus htmlspecialchars() untuk keamanan tampilan HTML.
 * ============================================================================
 */
// Konfigurasi dasar aplikasi.
// Path dibuat otomatis agar project tetap bisa dijalankan
// dari root AWebServer maupun dari dalam subfolder.

error_reporting(E_ALL);
ini_set("display_errors", "0");
ini_set("log_errors", "1");

$scriptName = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "");
$scriptDir = str_replace("\\", "/", dirname($scriptName));

// Folder fitur yang berada satu tingkat di bawah folder utama project.
$appFolders = [
  "auth",
  "buku",
  "anggota",
  "transaksi",
  "pencarian",
  "dashboard",
  "kategori",
  "rak",
  "penerbit",
  "user",
  "bookmark",
  "aksi",
  "notifikasi",
];

$currentFolder = basename(rtrim($scriptDir, "/"));

if (in_array($currentFolder, $appFolders, true)) {
  $baseUrl = dirname($scriptDir);
} else {
  // Dipakai ketika file berada di root project, misalnya index.php.
  $baseUrl = $scriptDir;
}

$baseUrl = rtrim(str_replace("\\", "/", $baseUrl), "/");
if ($baseUrl === ".") {
  $baseUrl = "";
}

define("BASE_URL", $baseUrl);

// Denda keterlambatan: Rp1.000 per buku untuk setiap hari keterlambatan.
define("DENDA_PER_BUKU_PER_HARI", 1000);

function url($path = "")
{
  $path = ltrim($path, "/");
  return BASE_URL . ($path !== "" ? "/" . $path : "");
}

// Fungsi escape dipusatkan di sini agar bisa dipakai semua halaman.
if (!function_exists("e")) {
  function e($value)
  {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
  }
}
