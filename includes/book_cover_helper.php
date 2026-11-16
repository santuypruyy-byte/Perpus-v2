<?php
/**
 * ============================================================================
 * FILE: includes/book_cover_helper.php (Helper Sampul Buku & API Eksternal)
 * ============================================================================
 * FUNGSI UTAMA:
 * Mengatur URL gambar sampul buku (lokal maupun link internet) serta mencari gambar
 * sampul buku secara otomatis melalui Google Books API dan Open Library API.
 *
 * ALUR LOGIKA:
 * 1. normalizeBookCoverUrl(): Menentukan apakah gambar berasal dari URL luar (http/https),
 *    folder lokal (assets/covers/), atau gambar bawaan (default.svg).
 * 2. httpGetJson() / httpGetJsonCurl(): Mengambil data JSON dari API luar dengan batas waktu (timeout).
 * 3. fetchBookCoverOnline(): Mencari sampul buku berdasarkan judul, pengarang, dan penerbit.
 * ============================================================================
 */
/**
 * Helper cover buku dan upload e-book.
 * Cover buku sekarang diambil otomatis dari Google Books API
 * berdasarkan judul + pengarang. Jika Google Books tidak menemukan
 * cover, helper mencoba Open Library dan akhirnya memakai default.svg.
 */
function normalizeBookCoverUrl(?string $gambar): string
{
    $gambar = trim((string) $gambar);
    if ($gambar === '') {
        return url('assets/images/default.svg');
    }

    if (preg_match('~^https?://~i', $gambar)) {
        return $gambar;
    }

    // Sampul lokal yang diunggah Admin disimpan di assets/covers/.
    if (preg_match('~^covers/~i', ltrim($gambar, '/'))) {
        return url('assets/' . ltrim($gambar, '/'));
    }

    return url('assets/images/' . ltrim($gambar, '/'));
}

function httpGetJson(string $url): ?array
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 8,
            'ignore_errors' => true,
            'header' => "User-Agent: Perpustakaan-SMK/1.0\r\nAccept: application/json\r\n",
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    if ($body === false || trim($body) === '') {
        return null;
    }

    $data = json_decode($body, true);
    return is_array($data) ? $data : null;
}

function httpGetJsonCurl(string $url): ?array
{
    if (!function_exists('curl_init')) {
        return null;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_USERAGENT => 'Perpustakaan-SMK/1.0',
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $body = curl_exec($ch);
    curl_close($ch);

    if ($body === false || trim((string) $body) === '') {
        return null;
    }

    $data = json_decode($body, true);
    return is_array($data) ? $data : null;
}

function fetchJson(string $url): ?array
{
    $data = httpGetJsonCurl($url);
    if ($data !== null) {
        return $data;
    }

    return httpGetJson($url);
}

function normalizeBookText(string $text): string
{
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text);
    return trim(preg_replace('/\s+/u', ' ', $text));
}

function coverCandidateScore(array $item, string $judul, string $pengarang): int
{
    $info = $item['volumeInfo'] ?? [];
    $apiTitle = normalizeBookText((string) ($info['title'] ?? ''));
    $apiAuthors = normalizeBookText(implode(' ', $info['authors'] ?? []));
    $targetTitle = normalizeBookText($judul);
    $targetAuthor = normalizeBookText($pengarang);

    $score = 0;
    if ($apiTitle !== '' && $apiTitle === $targetTitle) {
        $score += 100;
    } elseif ($apiTitle !== '' && ($apiTitle === $targetTitle || str_contains($apiTitle, $targetTitle) || str_contains($targetTitle, $apiTitle))) {
        $score += 60;
    }

    if ($targetAuthor !== '' && $apiAuthors !== '') {
        if ($apiAuthors === $targetAuthor) {
            $score += 50;
        } elseif (str_contains($apiAuthors, $targetAuthor) || str_contains($targetAuthor, $apiAuthors)) {
            $score += 30;
        }
    }

    if (!empty($info['imageLinks'])) {
        $score += 10;
    }

    return $score;
}

function extractGoogleBooksCover(array $items, string $judul, string $pengarang): ?string
{
    $bestUrl = null;
    $bestScore = -1;

    foreach ($items as $item) {
        $info = $item['volumeInfo'] ?? [];
        $links = $info['imageLinks'] ?? [];
        if (!$links) {
            continue;
        }

        $url = $links['extraLarge'] ?? $links['large'] ?? $links['medium'] ?? $links['thumbnail'] ?? $links['smallThumbnail'] ?? null;
        if (!$url) {
            continue;
        }

        $url = preg_replace('~^http://~i', 'https://', $url);
        $score = coverCandidateScore($item, $judul, $pengarang);
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestUrl = $url;
        }
    }

    return $bestUrl;
}

function fetchBookCoverOnline(string $judul, string $pengarang = '', string $penerbit = ''): ?string
{
    $judul = trim($judul);
    $pengarang = trim($pengarang);
    $penerbit = trim($penerbit);

    if ($judul === '') {
        return null;
    }

    $parts = ['intitle:' . $judul];
    if ($pengarang !== '') {
        $parts[] = 'inauthor:' . $pengarang;
    }
    if ($penerbit !== '') {
        $parts[] = 'inpublisher:' . $penerbit;
    }

    $query = rawurlencode(implode(' ', $parts));
    $googleUrl = 'https://www.googleapis.com/books/v1/volumes?q=' . $query . '&maxResults=10&printType=books';
    $google = fetchJson($googleUrl);
    $cover = extractGoogleBooksCover($google['items'] ?? [], $judul, $pengarang);

    if ($cover) {
        return $cover;
    }

    // Fallback: Open Library.
    $openUrl = 'https://openlibrary.org/search.json?title=' . rawurlencode($judul)
        . ($pengarang !== '' ? '&author=' . rawurlencode($pengarang) : '')
        . '&limit=10&fields=title,author_name,cover_i';
    $open = fetchJson($openUrl);

    $best = null;
    $bestScore = -1;
    foreach (($open['docs'] ?? []) as $doc) {
        if (empty($doc['cover_i'])) {
            continue;
        }

        $apiTitle = normalizeBookText((string) ($doc['title'] ?? ''));
        $targetTitle = normalizeBookText($judul);
        $score = 0;
        if ($apiTitle === $targetTitle) {
            $score += 100;
        } elseif ($apiTitle !== '' && (str_contains($apiTitle, $targetTitle) || str_contains($targetTitle, $apiTitle))) {
            $score += 60;
        }
        if ($pengarang !== '' && !empty($doc['author_name'])) {
            $authors = normalizeBookText(implode(' ', $doc['author_name']));
            $targetAuthor = normalizeBookText($pengarang);
            if ($authors === $targetAuthor) {
                $score += 50;
            } elseif (str_contains($authors, $targetAuthor) || str_contains($targetAuthor, $authors)) {
                $score += 30;
            }
        }

        if ($score > $bestScore) {
            $bestScore = $score;
            $best = 'https://covers.openlibrary.org/b/id/' . (int) $doc['cover_i'] . '-L.jpg';
        }
    }

    return $best;
}

