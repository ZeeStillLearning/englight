# EngLight — Platform Pembelajaran Bahasa Inggris

Platform pembelajaran bahasa Inggris berbasis **PHP Native + MySQL** dengan fitur materi, latihan soal, tryout TOEFL, forum diskusi, e-book, dan speaking practice.

---

## Requirements

| Software        | Versi Minimum |
| --------------- | ------------- |
| PHP             | 8.0+          |
| MySQL           | 5.7+          |
| Apache          | 2.4+          |
| XAMPP / Laragon | Versi terbaru |

---

## Setup — XAMPP (Windows)

### Step 1 — Clone Repository

Buka **CMD** atau **Git Bash**, jalankan:

```bash
cd C:\xampp\htdocs
git clone https://github.com/USERNAME/englight.git
```

Pastikan struktur foldernya:

```
C:\xampp\htdocs\englight\index.php
```

---

### Step 2 — Buat Database

1. Buka **XAMPP Control Panel** → klik **Start** pada **Apache** dan **MySQL**
2. Buka browser → pergi ke `http://localhost/phpmyadmin`
3. Klik **New** di sidebar kiri
4. Isi nama database: `englight_db` → klik **Create**
5. Klik database `englight_db` yang baru dibuat
6. Klik tab **Import** → klik **Choose File**
7. Pilih file `C:\xampp\htdocs\englight\database.sql`
8. Scroll bawah → klik **Go**

Tunggu hingga muncul pesan sukses hijau

---

### Step 3 — Konfigurasi Koneksi

Buka file `C:\xampp\htdocs\englight\config.php` dengan text editor:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'englight_db');
define('DB_USER', 'root');
define('DB_PASS', '');        // XAMPP default: kosong
define('APP_URL', 'http://localhost/englight');
```

> **Catatan XAMPP:** Password MySQL default XAMPP adalah **kosong** (tidak ada password). Jika kamu sudah set password MySQL sendiri, isi di `DB_PASS`.

---

### Step 4 — Buat Folder Upload

Buka **CMD** dan jalankan:

```bash
cd C:\xampp\htdocs\englight
mkdir uploads\materi
mkdir uploads\ebook
```

---

### Step 5 — Buka Aplikasi

```
http://localhost/englight
```

---

## Setup — Laragon (Windows)

### Step 1 — Clone Repository

```bash
cd C:\laragon\www
git clone https://github.com/USERNAME/englight.git
```

---

### Step 2 — Buat Database

1. Buka **Laragon** → klik **Start All**
2. Klik ikon Laragon di tray → **Database** → **phpMyAdmin**
3. Klik **New** → nama: `englight_db` → **Create**
4. Klik `englight_db` → tab **Import** → pilih `database.sql` → **Go**

---

### Step 3 — Konfigurasi Koneksi

Buka `C:\laragon\www\englight\config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'englight_db');
define('DB_USER', 'root');
define('DB_PASS', '');        // Laragon default: kosong
define('APP_URL', 'http://localhost/englight');
```

---

### Step 4 — Buat Folder Upload

```bash
cd C:\laragon\www\englight
mkdir uploads\materi
mkdir uploads\ebook
```

---

### Step 5 — Buka Aplikasi

```
http://localhost/englight
```

---

## Akun Default

Setelah import database, jalankan script reset password dulu:

**Buka browser:**

```
http://localhost/englight/reset-password.php
```

Tunggu muncul pesan sukses, lalu **hapus file** `reset-password.php`.

Kemudian login dengan:

| Role      | Email               | Password      |
| --------- | ------------------- | ------------- |
| **Admin** | `admin@englight.id` | `Admin@12345` |
| **User**  | `budi@student.com`  | `User@12345`  |

---

## Struktur Folder

```
englight/
├── config.php              ← Konfigurasi database & app
├── database.sql            ← Import ini ke phpMyAdmin
├── reset-password.php      ← Jalankan sekali lalu hapus!
├── index.php               ← Landing page
├── login.php               ← Halaman login & register
├── logout.php              ← Proses logout
├── dashboard.php           ← Dashboard user
├── materi-user.php         ← Halaman materi
├── baca-materi.php         ← PDF/Video viewer
├── latihansoal-user.php    ← Latihan soal
├── tryout-user.php         ← Tryout TOEFL
├── tryout-start.php        ← Sesi tryout aktif
├── forum-user.php          ← Forum diskusi
├── ebook-user.php          ← E-book
├── speaking-user.php       ← Speaking practice
├── membership-user.php     ← Halaman membership
├── includes/
│   ├── auth.php            ← Session & cookie timeout
│   ├── header_public.php   ← Navbar publik
│   ├── footer_public.php   ← Footer publik
│   ├── sidebar_user.php    ← Sidebar user
│   └── sidebar_admin.php   ← Sidebar admin
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
    ├── materi/             ← Buat folder ini manual!
    └── ebook/              ← Buat folder ini manual!
```

---

## Fitur Keamanan

| Fitur            | Keterangan                                   |
| ---------------- | -------------------------------------------- |
| Password Hashing | BCrypt cost 12 via `password_hash()`         |
| SQL Injection    | Prepared Statements PDO di semua query       |
| XSS Prevention   | `htmlspecialchars()` di semua output         |
| Session Security | `session_regenerate_id()` saat login         |
| Cookie Timeout   | Auto logout setelah **5 menit** tidak aktif  |
| Access Control   | RBAC — `require_login()` & `require_admin()` |

---

## Troubleshooting

**Error: Table not found / Base table doesn't exist**
→ Database belum diimport. Ulangi Step 2.

**Error: Access denied for user 'root'**
→ Cek `DB_PASS` di `config.php`. XAMPP/Laragon default kosong.

**Error: No such file or directory (uploads)**
→ Folder `uploads/materi` dan `uploads/ebook` belum dibuat. Ulangi Step 4.

**Halaman putih / blank**
→ Pastikan Apache & MySQL sudah **Start** di XAMPP/Laragon.

**Login tidak bisa / password salah**
→ Jalankan `http://localhost/englight/reset-password.php` dulu.

**URL Not Found**
→ Pastikan folder project bernama `englight` (huruf kecil semua).

---
