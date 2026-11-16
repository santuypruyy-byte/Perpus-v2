<?php
/**
 * ============================================================================
 * FILE: transaksi/peminjaman.php (Proses Transaksi Peminjaman Buku)
 * ============================================================================
 * FUNGSI UTAMA:
 * Mencatat transaksi peminjaman buku baik yang diajukan oleh Anggota (User) maupun yang
 * diinput langsung oleh Admin.
 *
 * ALUR LOGIKA:
 * 1. Jika yang login adalah 'user', sistem otomatis mengambil `anggota_id` milik user tersebut.
 *    Jika 'admin', admin memilih anggota dari daftar dropdown.
 * 2. Memulai Database Transaction (`begin_transaction()`) untuk menjaga keakuratan stok:
 *    - Mengunci baris buku (`FOR UPDATE`) dan menghitung sisa stok tersedia (`jumlah_stok - dipinjam`).
 *    - Mengambil data snapshot anggota (kode & nama anggota) agar riwayat peminjaman tetap
 *      mencatat nama peminjam meski data anggota dihapus di kemudian hari.
 *    - Menyimpan header transaksi ke tabel `peminjaman` dan detail buku ke `peminjaman_detail`.
 *      Status awal adalah 'diproses' (jika diajukan user) atau langsung 'dipinjam' (jika oleh admin).
 * 3. Menyimpan permanen dengan `commit()` dan mengirim notifikasi ke admin/user.
 * ============================================================================
 */
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../includes/notification_helper.php';

$msg = '';
$err = '';

$userId = (int) $_SESSION['user_id'];
$level = $_SESSION['level'] ?? '';

// Buku yang dipilih dari Koleksi Dashboard.
$selectedBukuId = (int) ($_GET['buku_id'] ?? $_POST['buku_id'] ?? 0);


/*
|--------------------------------------------------------------------------
| Cari anggota berdasarkan akun login
|--------------------------------------------------------------------------
*/

$anggotaLogin = null;

if ($level === 'user') {

    $q = $conn->prepare("
        SELECT
            u.id AS user_id,
            u.nama AS user_nama,
            u.anggota_id,
            a.kode_anggota,
            a.nama AS anggota_nama
        FROM user u

        LEFT JOIN anggota a
            ON a.id = u.anggota_id

        WHERE u.id = ?

        LIMIT 1
    ");

    $q->bind_param(
        'i',
        $userId
    );

    $q->execute();

    $anggotaLogin =
        $q->get_result()->fetch_assoc();


    if (
        !$anggotaLogin ||
        empty($anggotaLogin['anggota_id'])
    ) {

        $err =
            'Akun Anda belum terhubung '
            . 'dengan data anggota. '
            . 'Hubungi admin.';
    }
}


/*
|--------------------------------------------------------------------------
| Proses peminjaman
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $err === ''
) {
    requireValidCsrf();

    $buku =
        (int) ($_POST['buku_id'] ?? 0);

    $jumlah =
        (int) ($_POST['jumlah'] ?? 1);

    $lama =
        (int) ($_POST['lama_pinjam'] ?? 7);

    $ket =
        trim($_POST['keterangan'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Tentukan anggota
    |--------------------------------------------------------------------------
    */

    if ($level === 'admin') {

        $anggota =
            (int) ($_POST['anggota_id'] ?? 0);

    } else {

        $anggota =
            (int) $anggotaLogin['anggota_id'];
    }


    /*
    |--------------------------------------------------------------------------
    | Validasi
    |--------------------------------------------------------------------------
    */

    if (
        $anggota < 1 ||
        $buku < 1 ||
        $jumlah < 1 ||
        $lama < 1
    ) {

        $err =
            'Data peminjaman belum lengkap.';

    } else {

        $conn->begin_transaction();


        try {

            /*
            |--------------------------------------------------------------------------
            | Ambil data lengkap anggota untuk disimpan sebagai snapshot permanen
            |--------------------------------------------------------------------------
            */

            $q = $conn->prepare("
                SELECT kode_anggota, nama
                FROM anggota
                WHERE id = ?
                LIMIT 1
            ");

            $q->bind_param(
                'i',
                $anggota
            );

            $q->execute();

            $anggotaCheck =
                $q->get_result()->fetch_assoc();


            if (!$anggotaCheck) {

                throw new Exception(
                    'Data anggota tidak ditemukan.'
                );
            }

            $snapshotKode = $anggotaCheck['kode_anggota'];
            $snapshotNama = $anggotaCheck['nama'];


            /*
            |--------------------------------------------------------------------------
            | Cek buku
            |--------------------------------------------------------------------------
            */

            $q = $conn->prepare("
                SELECT
                    jumlah_stok
                FROM buku
                WHERE id = ?
                AND status_aktif = 1
                FOR UPDATE
            ");

            $q->bind_param(
                'i',
                $buku
            );

            $q->execute();

            $bk =
                $q->get_result()->fetch_assoc();


            if (!$bk) {

                throw new Exception(
                    'Buku tidak ditemukan.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Hitung buku yang sedang dipinjam
            |--------------------------------------------------------------------------
            */

            $q = $conn->prepare("
                SELECT
                    COALESCE(
                        SUM(pd.jumlah),
                        0
                    ) AS j

                FROM peminjaman_detail pd

                JOIN peminjaman p
                    ON p.id = pd.peminjaman_id

                WHERE pd.buku_id = ?

                AND p.status = 'dipinjam'
            ");

            $q->bind_param(
                'i',
                $buku
            );

            $q->execute();

            $dip =
                (int) $q
                    ->get_result()
                    ->fetch_assoc()['j'];


            $tersedia =
                (int) $bk['jumlah_stok'] - $dip;


            if ($tersedia < $jumlah) {

                throw new Exception(
                    'Stok tersedia tidak mencukupi.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Simpan peminjaman (Beserta Snapshot Nama & Kode Anggota)
            |--------------------------------------------------------------------------
            */

            $tanggal =
                date('Y-m-d');

            $status =
                ($level === 'user') ? 'diproses' : 'dipinjam';


            $q = $conn->prepare("
                INSERT INTO peminjaman
                (
                    tanggal_pinjam,
                    lama_pinjam,
                    keterangan,
                    status,
                    anggota_id,
                    user_id,
                    snapshot_kode_anggota,
                    snapshot_nama_anggota
                )

                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");

            $q->bind_param(
                'sissiiss',
                $tanggal,
                $lama,
                $ket,
                $status,
                $anggota,
                $userId,
                $snapshotKode,
                $snapshotNama
            );

            $q->execute();


            $pid =
                $conn->insert_id;


            /*
            |--------------------------------------------------------------------------
            | Simpan detail buku
            |--------------------------------------------------------------------------
            */

            $q = $conn->prepare("
                INSERT INTO peminjaman_detail
                (
                    peminjaman_id,
                    buku_id,
                    jumlah
                )

                VALUES
                (
                    ?,
                    ?,
                    ?
                )
            ");

            $q->bind_param(
                'iii',
                $pid,
                $buku,
                $jumlah
            );

            $q->execute();


            /*
            |--------------------------------------------------------------------------
            | Commit
            |--------------------------------------------------------------------------
            */

            $conn->commit();

            if ($level === 'user') {
                addNotificationToAdmins($conn, 'Pengajuan Peminjaman Baru', 'User ' . ($_SESSION['nama'] ?? 'User') . ' mengajukan peminjaman #' . $pid . '. Menunggu persetujuan admin.', 'peminjaman');
            }

            $msg = $level === 'user'
                ? 'Permintaan peminjaman berhasil diajukan dan menunggu persetujuan admin.'
                : 'Peminjaman berhasil disimpan.';

        } catch (Exception $e) {

            $conn->rollback();

            $err =
                $e->getMessage();
        }
    }
}


/*
|--------------------------------------------------------------------------
| Data anggota
|--------------------------------------------------------------------------
*/

$anggotaRs = null;

if ($level === 'admin') {

    $anggotaRs = $conn->query("
        SELECT
            id,
            kode_anggota,
            nama
        FROM anggota
        ORDER BY nama
    ");
}


/*
|--------------------------------------------------------------------------
| Data buku
|--------------------------------------------------------------------------
*/

$bukuRs = $conn->query("
    SELECT
        b.id,
        b.judul,

        b.jumlah_stok -
        COALESCE(
            x.dipinjam,
            0
        ) AS tersedia

    FROM buku b

    LEFT JOIN
    (
        SELECT
            pd.buku_id,
            SUM(pd.jumlah) AS dipinjam

        FROM peminjaman_detail pd

        JOIN peminjaman p
            ON p.id = pd.peminjaman_id

        WHERE p.status = 'dipinjam'

        GROUP BY pd.buku_id

    ) x
        ON x.buku_id = b.id

    WHERE b.status_aktif = 1

    ORDER BY b.judul
");


/*
|--------------------------------------------------------------------------
| Riwayat Peminjaman (Menggunakan snapshot_nama_anggota agar nama tetap tampil permanen)
|--------------------------------------------------------------------------
*/

if ($level === 'admin') {

    $rows = $conn->query("
        SELECT
            p.id,
            p.tanggal_pinjam,
            p.lama_pinjam,
            p.status,

            COALESCE(p.snapshot_nama_anggota, a.nama, 'Anggota Tidak Diketahui') AS anggota,

            GROUP_CONCAT(
                CONCAT(
                    b.judul,
                    ' (',
                    pd.jumlah,
                    ')'
                )
                SEPARATOR ', '
            ) AS buku

        FROM peminjaman p

        LEFT JOIN anggota a
            ON a.id = p.anggota_id

        JOIN peminjaman_detail pd
            ON pd.peminjaman_id = p.id

        JOIN buku b
            ON b.id = pd.buku_id

        GROUP BY p.id

        ORDER BY p.id DESC
    ");

} else {

    $rows = $conn->prepare("
        SELECT
            p.id,
            p.tanggal_pinjam,
            p.lama_pinjam,
            p.status,

            COALESCE(p.snapshot_nama_anggota, a.nama, 'Anggota Tidak Diketahui') AS anggota,

            GROUP_CONCAT(
                CONCAT(
                    b.judul,
                    ' (',
                    pd.jumlah,
                    ')'
                )
                SEPARATOR ', '
            ) AS buku

        FROM peminjaman p

        LEFT JOIN anggota a
            ON a.id = p.anggota_id

        JOIN peminjaman_detail pd
            ON pd.peminjaman_id = p.id

        JOIN buku b
            ON b.id = pd.buku_id

        WHERE p.user_id = ?

        GROUP BY p.id

        ORDER BY p.id DESC
    ");

    $rows->bind_param(
        'i',
        $userId
    );

    $rows->execute();

    $rows =
        $rows->get_result();
}


$pageTitle = 'Peminjaman';

$pageDescription =
    'Catat dan pantau transaksi '
    . 'peminjaman buku.';

require_once __DIR__ .
    '/../includes/layout_header.php';

?>


<?php if ($msg): ?>

    <div class="alert success">
        ✓ <?= e($msg) ?>
    </div>

<?php endif; ?>


<?php if ($err): ?>

    <div class="alert danger">
        ! <?= e($err) ?>
    </div>

<?php endif; ?>


<section class="panel form-panel">

    <div class="panel-head">

        <div>

            <h3>
                Transaksi Peminjaman
            </h3>


            <?php if (
                $level === 'user' &&
                $anggotaLogin
            ): ?>

                <p class="muted">

                    Anggota yang digunakan:

                    <strong>

                        <?= e(
                            $anggotaLogin['kode_anggota']
                            . ' - '
                            . $anggotaLogin['anggota_nama']
                        ) ?>

                    </strong>

                </p>

                <p class="muted">
                    Setelah diajukan, status menjadi <strong>Menunggu ACC Admin</strong>.
                    Buku baru berstatus dipinjam setelah admin menyetujui permintaan.
                </p>

            <?php elseif ($level === 'admin'): ?>

                <p class="muted">

                    Admin dapat memilih
                    anggota yang melakukan
                    peminjaman.

                </p>

            <?php endif; ?>

        </div>

    </div>


    <form
        method="post"
        class="form-grid"
    >
        <?= csrfField() ?>


        <?php if ($level === 'admin'): ?>

            <div>

                <label>
                    Anggota
                </label>

                <select
                    name="anggota_id"
                    required
                >

                    <option value="">
                        Pilih anggota
                    </option>


                    <?php while (
                        $a =
                        $anggotaRs->fetch_assoc()
                    ): ?>

                        <option
                            value="<?= (int) $a['id'] ?>"
                        >

                            <?= e(
                                $a['kode_anggota']
                                . ' - '
                                . $a['nama']
                            ) ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


        <?php else: ?>

            <div>

                <label>
                    Anggota
                </label>

                <input
                    type="text"
                    value="<?= $anggotaLogin
                        ? e(
                            $anggotaLogin['kode_anggota']
                            . ' - '
                            . $anggotaLogin['anggota_nama']
                        )
                        : 'Belum terhubung dengan anggota'
                    ?>"
                    readonly
                >

            </div>

        <?php endif; ?>


        <div>

            <label>
                Buku
            </label>

            <select
                name="buku_id"
                required
            >

                <option value="">
                    Pilih buku
                </option>


                <?php while (
                    $b =
                    $bukuRs->fetch_assoc()
                ): ?>

                    <option
                        value="<?= (int) $b['id'] ?>"
                        <?= $b['tersedia'] < 1
                            ? 'disabled'
                            : '' ?>
                        <?= $selectedBukuId === (int) $b['id']
                            ? 'selected'
                            : '' ?>
                    >

                        <?= e($b['judul']) ?>

                        —
                        tersedia
                        <?= (int) $b['tersedia'] ?>

                    </option>

                <?php endwhile; ?>

            </select>

            <?php if ($selectedBukuId > 0): ?>
                <?php
                $selectedBookName = '';
                $selectedCheck = $conn->prepare("SELECT judul FROM buku WHERE id = ? AND status_aktif = 1 LIMIT 1");
                $selectedCheck->bind_param('i', $selectedBukuId);
                $selectedCheck->execute();
                $selectedBookRow = $selectedCheck->get_result()->fetch_assoc();
                $selectedBookName = $selectedBookRow['judul'] ?? '';
                ?>
                <?php if ($selectedBookName !== ''): ?>
                    <small class="muted">
                        Buku dari koleksi dashboard: <strong><?= e($selectedBookName) ?></strong>
                    </small>
                <?php endif; ?>
            <?php endif; ?>

        </div>


        <div>

            <label>
                Jumlah
            </label>

            <input
                type="number"
                name="jumlah"
                min="1"
                value="1"
                required
            >

        </div>


        <div>

            <label>
                Lama Pinjam (hari)
            </label>

            <input
                type="number"
                name="lama_pinjam"
                min="1"
                value="7"
                required
            >

        </div>


        <div class="full">

            <label>
                Keterangan
            </label>

            <textarea
                name="keterangan"
                rows="4"
                placeholder="Keterangan tambahan (opsional)"
            ></textarea>

        </div>


        <div class="full form-actions">

            <button
                type="submit"
                class="btn primary"
                <?= (
                    $level === 'user' &&
                    !$anggotaLogin
                )
                    ? 'disabled'
                    : '' ?>
            >

                Ajukan Peminjaman

            </button>

        </div>

    </form>

</section>


<section class="panel">

    <div class="panel-head">

        <div>

            <h3>
                Riwayat Peminjaman
            </h3>

            <p class="muted">

                <?= $level === 'admin'
                    ? 'Semua transaksi peminjaman.'
                    : 'Riwayat peminjaman Anda.' ?>

            </p>

        </div>

    </div>


    <div class="table-wrap">

        <table>

            <thead>

                <tr>

                    <th>ID</th>
                    <th>Anggota</th>
                    <th>Buku</th>
                    <th>Tanggal</th>
                    <th>Lama</th>
                    <th>Status</th>

                </tr>

            </thead>


            <tbody>

                <?php while (
                    $r =
                    $rows->fetch_assoc()
                ): ?>

                    <tr>

                        <td data-label="ID">

                            #<?= (int) $r['id'] ?>

                        </td>


                        <td data-label="Anggota">

                            <?= e(
                                $r['anggota']
                            ) ?>

                        </td>


                        <td data-label="Buku">

                            <?= e(
                                $r['buku']
                            ) ?>

                        </td>


                        <td data-label="Tanggal">

                            <?= e(
                                $r['tanggal_pinjam']
                            ) ?>

                        </td>


                        <td data-label="Lama">

                            <?= (int)
                                $r['lama_pinjam'] ?>

                            hari

                        </td>


                        <td data-label="Status">

                            <?php
                            $statusClass = 'green';
                            $statusLabel = 'Sudah Dikembalikan';

                            if ($r['status'] === 'diproses') {
                                $statusClass = 'yellow';
                                $statusLabel = 'Sedang Diproses';
                            } elseif ($r['status'] === 'dipinjam') {
                                $statusClass = 'red';
                                $statusLabel = 'Sedang Dipinjam';
                            } elseif ($r['status'] === 'ditolak') {
                                $statusClass = 'gray';
                                $statusLabel = 'Ditolak';
                            }
                            ?>

                            <span class="badge <?= $statusClass ?>">
                                <?= e($statusLabel) ?>
                            </span>

                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</section>


<?php

require_once __DIR__ .
    '/../includes/layout_footer.php';

?>