# EngLight

EngLight adalah platform belajar bahasa Inggris berbasis web yang dibangun dengan PHP native dan MySQL. Aplikasi ini mencakup modul materi pembelajaran, latihan soal, tryout TOEFL, forum diskusi antar pengguna, koleksi e-book, dan sesi latihan speaking.

Dibuat sebagai aplikasi PHP murni tanpa framework, sehingga cukup ringan untuk dijalankan di local server seperti XAMPP atau Laragon.

## Kebutuhan Sistem

Sebelum instalasi, pastikan environment berikut sudah terpasang:

- PHP 8.0 ke atas
- MySQL 5.7 ke atas
- Apache 2.4 ke atas
- XAMPP atau Laragon versi terbaru

## Instalasi dengan XAMPP

Clone repository ke folder htdocs:

```bash
cd C:\xampp\htdocs
git clone https://github.com/USERNAME/englight.git
```

Pastikan file `index.php` berada langsung di `C:\xampp\htdocs\englight\index.php`, bukan di dalam subfolder tambahan.

Jalankan Apache dan MySQL dari XAMPP Control Panel, lalu buka `http://localhost/phpmyadmin`. Buat database baru dengan nama `englight_db`, masuk ke database tersebut, buka tab Import, dan pilih file `database.sql` yang ada di root folder project. Klik Go dan tunggu sampai muncul konfirmasi bahwa import berhasil.

Selanjutnya buka `config.php` dan sesuaikan kredensial database:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'englight_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('APP_URL', 'http://localhost/englight');
```

Password MySQL default XAMPP kosong, jadi baris `DB_PASS` bisa dibiarkan seperti di atas kecuali sudah pernah diubah sendiri.

Buat folder upload yang dibutuhkan aplikasi:

```bash
cd C:\xampp\htdocs\englight
mkdir uploads\materi
mkdir uploads\ebook
```

Setelah itu aplikasi bisa diakses lewat `http://localhost/englight`.

## Instalasi dengan Laragon

Langkahnya hampir sama seperti XAMPP. Clone project ke folder www:

```bash
cd C:\laragon\www
git clone https://github.com/USERNAME/englight.git
```

Start semua service dari Laragon, lalu buka phpMyAdmin melalui ikon Laragon di tray. Buat database `englight_db`, import `database.sql` dari tab Import.

Isi `config.php` dengan konfigurasi yang sama seperti pada setup XAMPP, lalu buat folder upload:

```bash
cd C:\laragon\www\englight
mkdir uploads\materi
mkdir uploads\ebook
```

Akses aplikasi melalui `http://localhost/englight`.

## Akun Awal

Setelah database berhasil diimport, jalankan dulu script berikut untuk mengatur ulang password akun default:

```
http://localhost/englight/reset-password.php
```

Setelah muncul pesan sukses, hapus file `reset-password.php` dari server karena tidak lagi dibutuhkan dan berisiko jika dibiarkan.

Akun yang tersedia setelah proses ini:

Admin — email `admin@englight.id`, password `Admin@12345`
User — email `budi@student.com`, password `User@12345`

Disarankan mengganti password ini setelah login pertama kali.

## Struktur Project

```
englight/
├── config.php
├── database.sql
├── reset-password.php
├── index.php
├── login.php
├── logout.php
├── dashboard.php
├── materi-user.php
├── baca-materi.php
├── latihansoal-user.php
├── tryout-user.php
├── tryout-start.php
├── forum-user.php
├── ebook-user.php
├── speaking-user.php
├── membership-user.php
├── includes/
│   ├── auth.php
│   ├── header_public.php
│   ├── footer_public.php
│   ├── sidebar_user.php
│   └── sidebar_admin.php
├── admin/
│   ├── admin-dashboard.php
│   ├── materi-admin.php
│   ├── tambah-materi.php
│   ├── banksoal-admin.php
│   ├── tambah-soal.php
│   ├── tryout-admin.php
│   ├── tambah-tryout.php
│   ├── kelola-soal-tryout.php
│   ├── ebook-admin.php
│   ├── tambah-ebook.php
│   ├── pengguna-admin.php
│   └── log-admin.php
└── uploads/
    ├── materi/
    └── ebook/
```

Folder `uploads/materi` dan `uploads/ebook` tidak ikut ter-tracking di repository dan harus dibuat manual setelah clone.

## Keamanan

Beberapa hal yang sudah diterapkan di sisi backend:

Password pengguna disimpan dalam bentuk hash menggunakan BCrypt cost 12 lewat `password_hash()`. Semua query database memakai prepared statement melalui PDO untuk mencegah SQL injection. Output ke halaman dibersihkan dengan `htmlspecialchars()` guna menghindari XSS. Session ID diregenerasi setiap kali user login, dan sesi akan otomatis logout setelah 5 menit tidak ada aktivitas. Pembatasan akses halaman admin dan user diatur lewat fungsi `require_login()` dan `require_admin()`.

## Kendala Umum

Jika muncul error terkait tabel tidak ditemukan, kemungkinan besar database belum diimport dengan benar — ulangi proses import `database.sql`.

Error access denied pada user root biasanya disebabkan oleh `DB_PASS` di `config.php` yang tidak sesuai dengan setting MySQL di komputer masing-masing.

Jika ada error terkait folder uploads yang tidak ditemukan, artinya folder `uploads/materi` dan `uploads/ebook` belum dibuat secara manual.

Halaman yang muncul blank biasanya karena Apache atau MySQL belum dijalankan dari XAMPP/Laragon.

Jika login gagal terus meski data sudah benar, coba jalankan ulang `reset-password.php` sebelum mencoba login kembali.

Untuk error URL Not Found, periksa apakah nama folder project sudah benar yaitu `englight` dengan huruf kecil semua.