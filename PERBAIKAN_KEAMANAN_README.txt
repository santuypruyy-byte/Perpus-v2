PERPUSTAKAAN - PAKET PERBAIKAN KEAMANAN
=======================================

Perubahan utama:
- Menambahkan helper token CSRF di includes/auth.php.
- Menambahkan validasi CSRF pada aksi bookmark, rating, pengarsipan buku, penghapusan anggota, dan pengajuan peminjaman.
- Mengubah pengarsipan buku dan penghapusan anggota dari GET menjadi POST.
- Mematikan display_errors dan mengaktifkan error logging.
- Menyamarkan detail kegagalan koneksi database kepada pengguna.
- Mendukung konfigurasi koneksi melalui DB_HOST, DB_USER, DB_PASS, DB_NAME.
- Mempertahankan session_regenerate_id saat login.

PENTING:
1. Default DB_PASS sekarang kosong agar tidak mengasumsikan password root tertentu. Jika konfigurasi lokal Anda memakai password, set DB_PASS atau ubah config/connection.php.
2. Paket ini belum diuji terhadap server/database hidup. Uji semua fitur setelah ekstraksi.
3. Tidak semua formulir CRUD di proyek lama sudah diintegrasikan CSRF secara menyeluruh. Lakukan uji regresi; README ini tidak menyatakan aplikasi bebas kesalahan.
4. Untuk produksi, gunakan user database khusus dengan hak minimum, HTTPS, backup, dan log yang terlindungi.

PERBAIKAN LANJUTAN (REGISTRASI & BOOKMARK)
- Semua kolom registrasi wajib diisi dan divalidasi di server.
- Password baru minimal 8 karakter dan wajib mengandung huruf kecil, huruf besar, dan angka.
- Password disimpan menggunakan password_hash(), bukan teks biasa.
- Akun lama yang password-nya masih teks biasa otomatis di-hash saat berhasil login.
- Form bookmark pada Dashboard dan Koleksi Buku sekarang menyertakan token CSRF.
  Sebelumnya token tidak dikirim sehingga server menampilkan:
  "Permintaan ditolak. Muat ulang halaman lalu coba lagi."
- Registrasi juga dilindungi token CSRF.
