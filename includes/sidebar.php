<?php
<<<<<<< HEAD
/**
 * ============================================================================
 * FILE: includes/sidebar.php (Menu Navigasi Samping / Sidebar Dinamis)
 * ============================================================================
 * FUNGSI UTAMA:
 * Menampilkan daftar menu navigasi yang menyesuaikan hak akses (level) pengguna.
 *
 * ALUR LOGIKA:
 * 1. Mendeteksi nama halaman aktif ($currentPage) untuk memberi tanda highlight ('active').
 * 2. Mengecek apakah pengguna adalah 'admin' atau 'user' biasa.
 * 3. Admin melihat menu manajemen lengkap (Buku, Anggota, Kategori, Penerbit, Rak, User).
 * 4. User biasa hanya melihat menu Dashboard, Koleksi Buku, Peminjaman, Riwayat, dan Bookmark.
 * ============================================================================
 */
=======
>>>>>>> d3a474d9f86ca513d111ac9b325518e17d65db66
$currentPage = basename($_SERVER['PHP_SELF']);
$level = $_SESSION['level'] ?? '';
$isAdmin = $level === 'admin';
?>
<div class="sidebar-inner">
<div class="sidebar-head">
    <div class="brand side-brand">
        <div class="brand-icon">📚</div>
    <div>
        <b>Perpustakaan</b>
        <small><?= e(ucfirst($level ?: 'User')) ?></small>
        </div>
    </div>
    <button class="sidebar-close" type="button" onclick="toggleSidebar()" aria-label="Tutup menu">×</button>
</div>

<nav class="sidebar-nav">
    <div class="nav-label">MENU UTAMA</div>

    <a class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"
       href="<?= e(url('dashboard/dashboard.php')) ?>">
        <span class="nav-icon">⌂</span><span>Dashboard</span>
    </a>

    <?php if ($isAdmin): ?>
        <a class="<?= in_array($currentPage, ['tampil.php','form_tambah.php','edit.php','detail.php'], true) ? 'active' : '' ?>"
           href="<?= e(url('buku/tampil.php')) ?>">
            <span class="nav-icon">▣</span><span>Data Buku</span>
        </a>

        <a class="<?= in_array($currentPage, ['anggota.php','anggota_form.php'], true) ? 'active' : '' ?>"
           href="<?= e(url('anggota/anggota.php')) ?>">
            <span class="nav-icon">♙</span><span>Data Anggota</span>
        </a>

        <a class="<?= in_array($currentPage, ['index.php','form.php'], true) && str_contains($_SERVER['SCRIPT_NAME'], '/kategori/') ? 'active' : '' ?>"
           href="<?= e(url('kategori/index.php')) ?>">
            <span class="nav-icon">▤</span><span>Data Kategori</span>
        </a>

        <a class="<?= in_array($currentPage, ['index.php','form.php'], true) && str_contains($_SERVER['SCRIPT_NAME'], '/penerbit/') ? 'active' : '' ?>"
           href="<?= e(url('penerbit/index.php')) ?>">
            <span class="nav-icon">▥</span><span>Data Penerbit</span>
        </a>

        <a class="<?= in_array($currentPage, ['index.php','form.php'], true) && str_contains($_SERVER['SCRIPT_NAME'], '/rak/') ? 'active' : '' ?>"
           href="<?= e(url('rak/index.php')) ?>">
            <span class="nav-icon">▤</span><span>Data Rak</span>
        </a>

        <a class="<?= in_array($currentPage, ['index.php','form.php'], true) && str_contains($_SERVER['SCRIPT_NAME'], '/user/') ? 'active' : '' ?>"
           href="<?= e(url('user/index.php')) ?>">
            <span class="nav-icon">♙</span><span>Data User</span>
        </a>
    <?php endif; ?>

    <a class="<?= $currentPage === 'peminjaman.php' ? 'active' : '' ?>"
       href="<?= e(url('transaksi/peminjaman.php')) ?>">
        <span class="nav-icon">↗</span><span>Peminjaman</span>
    </a>

    <?php if ($isAdmin): ?>
        <a class="<?= $currentPage === 'pengembalian.php' ? 'active' : '' ?>"
           href="<?= e(url('transaksi/pengembalian.php')) ?>">
            <span class="nav-icon">↙</span><span>Pengembalian</span>
        </a>
    <?php else: ?>
        <a class="<?= $currentPage === 'riwayat_buku.php' ? 'active' : '' ?>"
           href="<?= e(url('transaksi/riwayat_buku.php')) ?>">
            <span class="nav-icon">↙</span><span>Riwayat Buku</span>
        </a>
    <?php endif; ?>

    <a class="<?= $currentPage === 'cari_buku.php' ? 'active' : '' ?>"
       href="<?= e(url('pencarian/cari_buku.php')) ?>">
        <span class="nav-icon">📚</span><span>Koleksi Buku</span>
    </a>

    <?php
    $notifCount = 0;
    if (isset($conn) && isset($_SESSION['user_id'])) {
        $nq = $conn->prepare('SELECT COUNT(*) total FROM notifikasi WHERE user_id=? AND dibaca=0');
        if ($nq) { $uid = (int)$_SESSION['user_id']; $nq->bind_param('i', $uid); $nq->execute(); $notifCount = (int)$nq->get_result()->fetch_assoc()['total']; }
    }
    ?>
    <a class="<?= $currentPage === 'index.php' && str_contains($_SERVER['SCRIPT_NAME'], '/notifikasi/') ? 'active' : '' ?>" href="<?= e(url('notifikasi/index.php')) ?>">
        <span class="nav-icon">🔔</span><span>Notifikasi</span><?= $notifCount > 0 ? '<span class="nav-badge">' . $notifCount . '</span>' : '' ?>
    </a>

    <a class="<?= $currentPage === 'index.php' && str_contains($_SERVER['SCRIPT_NAME'], '/bookmark/') ? 'active' : '' ?>"
       href="<?= e(url($isAdmin ? 'bookmark/admin.php' : 'bookmark/index.php')) ?>">
        <span class="nav-icon">🔖</span><span>Bookmark Saya</span>
    </a>

    <div class="nav-label nav-label-bottom">AKUN</div>

    <a href="<?= e(url('auth/logout.php')) ?>" class="logout">
        <span class="nav-icon">⇥</span><span>Logout</span>
    </a>
</nav>
</div>
