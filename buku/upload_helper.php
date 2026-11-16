<?php
<<<<<<< HEAD
/**
 * ============================================================================
 * FILE: buku/upload_helper.php (Helper Validasi & Keamanan Upload File)
 * ============================================================================
 * FUNGSI UTAMA:
 * Memastikan file sampul (gambar) dan file e-book (PDF) yang diunggah aman sebelum disimpan.
 *
 * ALUR LOGIKA:
 * 1. Mengecek ukuran maksimal file agar server tidak kelebihan beban.
 * 2. Memeriksa tipe file asli menggunakan `finfo_file()` (MIME Type) agar file berbahaya
 *    (seperti skrip PHP yang diganti ekstensinya menjadi .jpg/.pdf) ditolak.
 * 3. Mengubah nama file menjadi kode acak (`random_bytes`) sebelum dipindahkan ke folder tujuan.
 * ============================================================================
 */
=======
>>>>>>> d3a474d9f86ca513d111ac9b325518e17d65db66
require_once __DIR__ . '/../includes/book_cover_helper.php';

function uploadFileBuku($fieldName, $allowedMime, $maxBytes, $prefix = 'file_', $required = false, $folderName = 'ebooks')
{
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return $required
            ? ['success' => false, 'error' => 'Silakan pilih file.']
            : ['success' => true, 'filename' => null, 'path' => null];
    }

    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload file gagal. Kode error: ' . $file['error']];
    }
    if ($file['size'] > $maxBytes) {
        return ['success' => false, 'error' => 'Ukuran file terlalu besar.'];
    }

    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    } else {
        $mime = mime_content_type($file['tmp_name']);
    }

    if (!isset($allowedMime[$mime])) {
        return ['success' => false, 'error' => 'Format file tidak diperbolehkan.'];
    }

    $folder = __DIR__ . '/../assets/' . $folderName . '/';
    if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
        return ['success' => false, 'error' => 'Folder penyimpanan file tidak dapat dibuat.'];
    }

    $filename = $prefix . bin2hex(random_bytes(8)) . '.' . $allowedMime[$mime];
    $destination = $folder . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'File gagal disimpan ke server.'];
    }

    return ['success' => true, 'filename' => $filename, 'path' => $destination];
}


function uploadCover($fieldName = 'cover_file', $required = false)
{
    return uploadFileBuku(
        $fieldName,
        [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ],
        5 * 1024 * 1024,
        'cover_',
        $required,
        'covers'
    );
}

function uploadEbook($fieldName = 'ebook_file', $required = false)
{
    return uploadFileBuku(
        $fieldName,
        ['application/pdf' => 'pdf'],
        10 * 1024 * 1024,
        'ebook_',
        $required,
        'ebooks'
    );
}
