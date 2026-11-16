<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../config/connection.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare('SELECT judul, ebook_file FROM buku WHERE id=? AND status_aktif=1 LIMIT 1');
$stmt->bind_param('i', $id); $stmt->execute(); $book = $stmt->get_result()->fetch_assoc();
if (!$book || empty($book['ebook_file'])) { http_response_code(404); exit('E-book tidak tersedia.'); }
$file = __DIR__ . '/../assets/ebooks/' . basename($book['ebook_file']);
if (!is_file($file)) { http_response_code(404); exit('File e-book tidak ditemukan.'); }
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($book['ebook_file']) . '"');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;
