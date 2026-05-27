<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$item    = $edit_id ? db_row('SELECT * FROM ebooks WHERE id = ?', [$edit_id]) : null;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title           = trim($_POST['title']           ?? '');
    $description     = trim($_POST['description']     ?? '');
    $category        = trim($_POST['category']        ?? '');
    $cover_color     = trim($_POST['cover_color']     ?? '');
    $cover_color_hex = trim($_POST['cover_color_hex'] ?? '#1B3F8B');
    $pages           = (int)($_POST['pages']          ?? 0);
    $is_premium      = isset($_POST['is_premium']) ? 1 : 0;

    if (strlen($title) < 3) $errors[] = 'Judul minimal 3 karakter.';

    // Validate hex color
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $cover_color_hex)) {
        $cover_color_hex = '#1B3F8B';
    }

    // Handle file upload
    $file_path = $item['file_path'] ?? null;
    if (!empty($_FILES['file']['name'])) {
        $ext     = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            $errors[] = 'File e-book harus dalam format PDF.';
        } elseif ($_FILES['file']['size'] > 50 * 1024 * 1024) {
            $errors[] = 'Ukuran file maksimal 50 MB.';
        } else {
            $fname = uniqid('ebook_') . '.pdf';
            $dest  = __DIR__ . '/../uploads/ebook/' . $fname;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
                $file_path = 'uploads/ebook/' . $fname;
            } else {
                $errors[] = 'Gagal mengunggah file PDF.';
            }
        }
    }

    if (empty($errors)) {
        if ($edit_id && $item) {
            db_run('UPDATE ebooks SET title=?,description=?,category=?,cover_color=?,cover_color_hex=?,pages=?,is_premium=?,file_path=? WHERE id=?',
                   [$title,$description,$category,$cover_color,$cover_color_hex,$pages,$is_premium,$file_path,$edit_id]);
            log_admin_action('EDIT_EBOOK', 'ebooks', $edit_id, 'Edit: ' . $title);
            set_flash('success', 'E-Book berhasil diperbarui.');
        } else {
            db_run('INSERT INTO ebooks (title,description,category,cover_color,cover_color_hex,pages,is_premium,file_path,created_by) VALUES (?,?,?,?,?,?,?,?,?)',
                   [$title,$description,$category,$cover_color,$cover_color_hex,$pages,$is_premium,$file_path,$_SESSION['user_id']]);
            $new_id = (int)db()->lastInsertId();
            log_admin_action('ADD_EBOOK', 'ebooks', $new_id, 'Tambah: ' . $title);
            set_flash('success', 'E-Book berhasil ditambahkan.');
        }
        header('Location: ebook-admin.php'); exit;
    }
}

$active_page = 'ebook';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $edit_id ? 'Edit' : 'Tambah' ?> E-Book — EngLight</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{brand:{DEFAULT:'#1B3F8B'}}}}}</script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;900&display=swap" rel="stylesheet">
  <style>body{font-family:'Poppins',sans-serif}</style>
  <link rel="stylesheet" href="../style.css">
</head>
<body class="h-full">
<div class="flex h-full min-h-screen">
  <?php require_once __DIR__ . '/../includes/sidebar_admin.php'; ?>
  <div class="flex-1 overflow-y-auto">
    <header class="bg-white border-b px-8 py-4 flex items-center justify-between">
      <h1 class="font-bold text-gray-900"><?= $edit_id ? 'Edit' : 'Tambah' ?> E-Book</h1>
      <a href="ebook-admin.php" class="text-sm text-gray-500 hover:text-brand">← Kembali</a>
    </header>
    <main class="p-8">
      <?php if (!empty($errors)): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl px-5 py-4 text-sm text-red-700">
          <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 max-w-2xl">
        <form method="POST" enctype="multipart/form-data" class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Judul E-Book <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="<?= e($_POST['title'] ?? $item['title'] ?? '') ?>" maxlength="255"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand" required>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi</label>
            <textarea name="description" rows="3"
                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand resize-none"><?= e($_POST['description'] ?? $item['description'] ?? '') ?></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kategori</label>
              <select name="category" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
                <option value="">Pilih kategori</option>
                <?php foreach (['Grammar','Listening','Reading','Vocabulary','TOEFL Tips','Speaking','General'] as $cat): ?>
                  <option value="<?= $cat ?>" <?= (($_POST['category'] ?? $item['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jumlah Halaman</label>
              <input type="number" name="pages" value="<?= (int)($_POST['pages'] ?? $item['pages'] ?? 0) ?>" min="0"
                     class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Warna Cover</label>
              <input type="text" name="cover_color" value="<?= e($_POST['cover_color'] ?? $item['cover_color'] ?? '') ?>" placeholder="cth: Navy Blue"
                     class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kode Warna Cover (HEX)</label>
              <div class="flex gap-2">
                <input type="color" name="cover_color_hex" value="<?= e($_POST['cover_color_hex'] ?? $item['cover_color_hex'] ?? '#1B3F8B') ?>"
                       class="w-12 h-10 border border-gray-200 rounded-xl cursor-pointer" id="colorPicker">
                <input type="text" id="colorHex" value="<?= e($_POST['cover_color_hex'] ?? $item['cover_color_hex'] ?? '#1B3F8B') ?>"
                       class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand" readonly>
              </div>
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">File PDF E-Book</label>
            <input type="file" name="file" accept=".pdf"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm">
            <?php if (!empty($item['file_path'])): ?>
              <p class="text-xs text-green-600 mt-1">File saat ini: <?= e($item['file_path']) ?></p>
            <?php endif; ?>
            <p class="text-xs text-gray-400 mt-1">Format: PDF. Maks 50 MB.</p>
          </div>
          <div class="flex items-center gap-3">
            <input type="checkbox" name="is_premium" id="is_premium" value="1"
                   <?= (isset($_POST['is_premium']) || (!empty($item['is_premium']))) ? 'checked' : '' ?>
                   class="w-4 h-4 text-brand rounded">
            <label for="is_premium" class="text-sm font-semibold text-gray-700">Hanya untuk anggota Premium/Pro</label>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-brand text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition">
              <?= $edit_id ? 'Simpan Perubahan' : 'Tambah E-Book' ?>
            </button>
            <a href="ebook-admin.php" class="px-8 py-3 rounded-xl font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Batal</a>
          </div>
        </form>
      </div>
    </main>
  </div>
</div>
<script>
  const picker = document.getElementById('colorPicker');
  const hexIn  = document.getElementById('colorHex');
  if (picker && hexIn) {
    picker.addEventListener('input', () => { hexIn.value = picker.value; });
  }
</script>
</body>
</html>
