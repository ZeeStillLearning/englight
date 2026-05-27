<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$item    = $edit_id ? db_row('SELECT * FROM tryouts WHERE id = ?', [$edit_id]) : null;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title            = trim($_POST['title']           ?? '');
    $description      = trim($_POST['description']     ?? '');
    $duration_minutes = (int)($_POST['duration_minutes'] ?? 60);
    $total_questions  = (int)($_POST['total_questions']  ?? 0);
    $passing_score    = (int)($_POST['passing_score']    ?? 60);
    $is_premium       = isset($_POST['is_premium']) ? 1 : 0;

    if (strlen($title) < 3)              $errors[] = 'Judul minimal 3 karakter.';
    if ($duration_minutes < 1)           $errors[] = 'Durasi harus lebih dari 0 menit.';
    if ($passing_score < 0 || $passing_score > 100) $errors[] = 'Skor kelulusan harus antara 0–100.';

    if (empty($errors)) {
        if ($edit_id && $item) {
            db_run('UPDATE tryouts SET title=?,description=?,duration_minutes=?,total_questions=?,passing_score=?,is_premium=? WHERE id=?',
                   [$title, $description, $duration_minutes, $total_questions, $passing_score, $is_premium, $edit_id]);
            log_admin_action('EDIT_TRYOUT', 'tryouts', $edit_id, 'Edit: ' . $title);
            set_flash('success', 'Tryout berhasil diperbarui.');
        } else {
            db_run('INSERT INTO tryouts (title,description,duration_minutes,total_questions,passing_score,is_premium,created_by) VALUES (?,?,?,?,?,?,?)',
                   [$title, $description, $duration_minutes, $total_questions, $passing_score, $is_premium, $_SESSION['user_id']]);
            $new_id = (int)db()->lastInsertId();
            log_admin_action('ADD_TRYOUT', 'tryouts', $new_id, 'Tambah: ' . $title);
            set_flash('success', 'Tryout berhasil dibuat. Sekarang tambahkan soal ke tryout ini.');
            header('Location: kelola-soal-tryout.php?tryout_id=' . $new_id); exit;
        }
        header('Location: tryout-admin.php'); exit;
    }
}

$active_page = 'tryout';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $edit_id ? 'Edit' : 'Tambah' ?> Tryout — EngLight</title>
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
      <h1 class="font-bold text-gray-900"><?= $edit_id ? 'Edit' : 'Tambah' ?> Tryout</h1>
      <a href="tryout-admin.php" class="text-sm text-gray-500 hover:text-brand">← Kembali</a>
    </header>
    <main class="p-8">
      <?php if (!empty($errors)): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl px-5 py-4 text-sm text-red-700">
          <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 max-w-2xl">
        <form method="POST" class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Judul Tryout <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="<?= e($_POST['title'] ?? $item['title'] ?? '') ?>" maxlength="255"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand" required>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi</label>
            <textarea name="description" rows="3"
                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand resize-none"><?= e($_POST['description'] ?? $item['description'] ?? '') ?></textarea>
          </div>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Durasi (menit) <span class="text-red-500">*</span></label>
              <input type="number" name="duration_minutes" value="<?= (int)($_POST['duration_minutes'] ?? $item['duration_minutes'] ?? 60) ?>" min="1" max="360"
                     class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand" required>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jumlah Soal</label>
              <input type="number" name="total_questions" value="<?= (int)($_POST['total_questions'] ?? $item['total_questions'] ?? 0) ?>" min="0"
                     class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Skor Kelulusan (%) <span class="text-red-500">*</span></label>
              <input type="number" name="passing_score" value="<?= (int)($_POST['passing_score'] ?? $item['passing_score'] ?? 60) ?>" min="0" max="100"
                     class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand" required>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <input type="checkbox" name="is_premium" id="is_premium" value="1"
                   <?= (isset($_POST['is_premium']) || (!empty($item['is_premium']))) ? 'checked' : '' ?>
                   class="w-4 h-4 text-brand rounded">
            <label for="is_premium" class="text-sm font-semibold text-gray-700">Hanya untuk anggota Premium/Pro</label>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-brand text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition">
              <?= $edit_id ? 'Simpan Perubahan' : 'Buat Tryout' ?>
            </button>
            <a href="tryout-admin.php" class="px-8 py-3 rounded-xl font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Batal</a>
          </div>
        </form>
      </div>
    </main>
  </div>
</div>
</body>
</html>
