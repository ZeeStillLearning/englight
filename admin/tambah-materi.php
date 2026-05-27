<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$item    = $edit_id ? db_row('SELECT * FROM materi WHERE id = ?', [$edit_id]) : null;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title']      ?? '');
    $desc       = trim($_POST['description'] ?? '');
    $category   = trim($_POST['category']   ?? '');
    $type       = trim($_POST['type']       ?? '');
    $is_premium = isset($_POST['is_premium']) ? 1 : 0;
    $sort_order = (int)($_POST['order'] ?? 0);

    $valid_cats  = ['listening','structure','reading','grammar','vocabulary','speaking'];
    $valid_types = ['video','pdf','text'];

    if (strlen($title) < 3)                         $errors[] = 'Judul minimal 3 karakter.';
    if (!in_array($category, $valid_cats))           $errors[] = 'Pilih kategori yang valid.';
    if (!in_array($type, $valid_types))              $errors[] = 'Pilih tipe konten yang valid.';

    // Handle file upload
    $file_path = $item['file_path'] ?? null;
    if (!empty($_FILES['file']['name'])) {
        $ext      = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowed  = ['mp4','pdf','mov','webm'];
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Format file tidak didukung. Gunakan: MP4, PDF, MOV, WEBM.';
        } elseif ($_FILES['file']['size'] > 100 * 1024 * 1024) {
            $errors[] = 'Ukuran file maksimal 100 MB.';
        } else {
            $fname     = uniqid('materi_') . '.' . $ext;
            $dest      = __DIR__ . '/../uploads/materi/' . $fname;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
                $file_path = 'uploads/materi/' . $fname;
            } else {
                $errors[] = 'Gagal mengunggah file.';
            }
        }
    }

    if (empty($errors)) {
        if ($edit_id && $item) {
            db_run('UPDATE materi SET title=?,description=?,category=?,type=?,file_path=?,is_premium=?,sort_order=? WHERE id=?',
                   [$title, $desc, $category, $type, $file_path, $is_premium, $sort_order, $edit_id]);
            log_admin_action('EDIT_MATERI', 'materi', $edit_id, 'Edit: ' . $title);
            set_flash('success', 'Materi berhasil diperbarui.');
        } else {
            db_run('INSERT INTO materi (title,description,category,type,file_path,is_premium,sort_order,created_by) VALUES (?,?,?,?,?,?,?,?)',
                   [$title, $desc, $category, $type, $file_path, $is_premium, $sort_order, $_SESSION['user_id']]);
            $new_id = (int)db()->lastInsertId();
            log_admin_action('ADD_MATERI', 'materi', $new_id, 'Tambah: ' . $title);
            set_flash('success', 'Materi berhasil ditambahkan.');
        }
        header('Location: materi-admin.php'); exit;
    }
}

$active_page = 'materi';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $edit_id ? 'Edit' : 'Tambah' ?> Materi — EngLight</title>
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
      <h1 class="font-bold text-gray-900"><?= $edit_id ? 'Edit' : 'Tambah' ?> Materi</h1>
      <a href="materi-admin.php" class="text-sm text-gray-500 hover:text-brand">← Kembali</a>
    </header>
    <main class="p-8">
      <?php if (!empty($errors)): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl px-5 py-4 text-sm text-red-700">
          <?php foreach ($errors as $e): ?><p><?= e($e) ?></p><?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 max-w-2xl">
        <form method="POST" enctype="multipart/form-data" class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Judul Materi <span class="text-red-500">*</span></label>
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
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
              <select name="category" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand" required>
                <option value="">Pilih kategori</option>
                <?php foreach (['listening','structure','reading','grammar','vocabulary','speaking'] as $cat): ?>
                  <option value="<?= $cat ?>" <?= (($_POST['category'] ?? $item['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tipe Konten <span class="text-red-500">*</span></label>
              <select name="type" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand" required>
                <option value="">Pilih tipe</option>
                <?php foreach (['video','pdf','text'] as $t): ?>
                  <option value="<?= $t ?>" <?= (($_POST['type'] ?? $item['type'] ?? '') === $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Urutan Tampil</label>
            <input type="number" name="order" value="<?= (int)($_POST['order'] ?? $item['sort_order'] ?? 0) ?>" min="0"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">File Materi</label>
            <input type="file" name="file" accept=".mp4,.pdf,.mov,.webm"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            <?php if (!empty($item['file_path'])): ?>
              <p class="text-xs text-green-600 mt-1">File saat ini: <?= e($item['file_path']) ?></p>
            <?php endif; ?>
            <p class="text-xs text-gray-400 mt-1">Format: MP4, PDF, MOV, WEBM. Maks 100 MB.</p>
          </div>
          <div class="flex items-center gap-3">
            <input type="checkbox" name="is_premium" id="is_premium" value="1"
                   <?= (isset($_POST['is_premium']) || (!empty($item['is_premium']))) ? 'checked' : '' ?>
                   class="w-4 h-4 text-brand rounded">
            <label for="is_premium" class="text-sm font-semibold text-gray-700">Hanya untuk anggota Premium/Pro</label>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-brand text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition">
              <?= $edit_id ? 'Simpan Perubahan' : 'Tambah Materi' ?>
            </button>
            <a href="materi-admin.php" class="px-8 py-3 rounded-xl font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Batal</a>
          </div>
        </form>
      </div>
    </main>
  </div>
</div>
</body>
</html>
