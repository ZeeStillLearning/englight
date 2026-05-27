# EngLight — PHP Native Application

Versi dinamis berbasis PHP + MySQL dari proyek statis "EngLight".

---

## 📁 Struktur Direktori

```
englight/
├── .htaccess                   # Apache security rules
├── config.php                  # PDO connection & helpers (db, e, db_row, db_all)
├── database.sql                # Schema + seed data — import ke phpMyAdmin
│
├── index.php                   # Landing page (publik)
├── login.php                   # Login + Register (tab switching, satu file)
├── register.php                # Redirect → login.php?tab=register
├── logout.php                  # Hapus sesi & redirect ke login
│
├── dashboard.php               # User: halaman utama setelah login
├── materi-user.php             # User: daftar & tandai selesai materi
├── latihansoal-user.php        # User: latihan soal adaptif + riwayat
├── tryout-user.php             # User: daftar tryout + riwayat
├── tryout-start.php            # User: sesi tryout aktif dengan countdown timer
├── speaking-user.php           # User: speaking practice (UI + recorder)
├── forum-user.php              # User: forum diskusi (CRUD post + reply + upvote)
├── ebook-user.php              # User: galeri e-book
├── membership-user.php         # User: paket & riwayat pembayaran
│
├── style.css                   # Stylesheet asli (tidak diubah)
├── script.js                   # JavaScript asli (tidak diubah)
│
├── includes/
│   ├── auth.php                # Session helpers (require_login, require_admin, flash)
│   ├── header_public.php       # <head> + navbar untuk halaman publik
│   ├── footer_public.php       # Footer publik + JS init
│   ├── sidebar_user.php        # Sidebar navigasi pengguna
│   └── sidebar_admin.php       # Sidebar navigasi admin
│
├── admin/
│   ├── admin-dashboard.php     # Admin: ringkasan statistik
│   ├── materi-admin.php        # Admin: CRUD materi
│   ├── tambah-materi.php       # Admin: form tambah/edit materi (dengan upload file)
│   ├── banksoal-admin.php      # Admin: CRUD bank soal
│   ├── tambah-soal.php         # Admin: form tambah/edit soal
│   ├── tryout-admin.php        # Admin: CRUD tryout
│   ├── tambah-tryout.php       # Admin: form tambah/edit tryout
│   ├── kelola-soal-tryout.php  # Admin: pivot — assign/remove soal ke tryout
│   ├── ebook-admin.php         # Admin: CRUD e-book (dengan upload PDF)
│   ├── tambah-ebook.php        # Admin: form tambah/edit e-book (color picker)
│   ├── pengguna-admin.php      # Admin: CRUD pengguna (toggle aktif, ubah plan)
│   └── log-admin.php           # Admin: audit log dengan filter & purge
│
└── uploads/
    ├── materi/                 # Video & PDF materi yang diunggah
    └── ebook/                  # PDF e-book yang diunggah
```

---

## ⚙️ Instalasi

### 1. Import Database
- Buka **phpMyAdmin** → tab **Import**
- Pilih file `database.sql` → klik **Go**
- Database `englight_db` otomatis terbuat beserta tabelnya

### 2. Konfigurasi Koneksi
Edit `config.php` sesuaikan:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'englight_db');
define('DB_USER', 'root');       // ← sesuaikan
define('DB_PASS', '');           // ← sesuaikan
define('APP_URL', 'http://localhost/englight');  // ← sesuaikan
```

### 3. Permission Upload Folder
```bash
chmod 755 uploads/materi
chmod 755 uploads/ebook
```

### 4. Jalankan Aplikasi
Letakkan folder `englight/` di dalam `htdocs/` (XAMPP) atau `www/` (WAMP).
Buka: `http://localhost/englight`

---

## 🔑 Akun Default

| Role  | Email                 | Password     |
|-------|-----------------------|--------------|
| Admin | admin@englight.id     | Admin@12345  |
| User  | budi@student.com      | User@12345   |

> **Penting:** Ganti password default setelah instalasi pertama!

---

## 🛡️ Keamanan yang Diterapkan

| Metode             | Implementasi |
|--------------------|--------------|
| SQL Injection      | PDO Prepared Statements di semua query |
| XSS                | `htmlspecialchars()` via helper `e()` di semua output |
| CSRF (basic)       | Session-based redirect setelah setiap POST |
| Password Hashing   | `password_hash()` dengan `PASSWORD_BCRYPT`, cost 12 |
| Session Fixation   | `session_regenerate_id(true)` saat login |
| Akses Admin        | `require_admin()` di setiap halaman admin |
| File Upload        | Validasi ekstensi + ukuran + `move_uploaded_file()` |
| Directory Listing  | Dinonaktifkan via `Options -Indexes` di `.htaccess` |
| Security Headers   | X-Frame-Options, X-XSS-Protection, NOSNIFF via `.htaccess` |
| Sensitive File     | `config.php` dan `includes/` diblokir dari akses langsung |

---

## 🗄️ Skema Database (14 Tabel)

| Tabel                   | Fungsi |
|-------------------------|--------|
| `users`                 | Akun pengguna & admin |
| `memberships`           | Riwayat langganan & pembayaran |
| `materi`                | Konten pembelajaran (video/pdf/text) |
| `user_materi_progress`  | Tracking progres tiap user per materi |
| `questions`             | Bank soal MCQ |
| `latihan_sessions`      | Riwayat sesi latihan soal |
| `tryouts`               | Paket tryout TOEFL |
| `tryout_questions`      | Pivot: soal yang masuk ke tryout |
| `tryout_sessions`       | Riwayat percobaan tryout user |
| `ebooks`                | Koleksi e-book |
| `forum_posts`           | Postingan forum |
| `forum_replies`         | Balasan forum |
| `speaking_sessions`     | Riwayat sesi speaking AI |
| `admin_logs`            | Audit trail semua aksi admin |
