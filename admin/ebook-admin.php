<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $eb = db_row('SELECT title FROM ebooks WHERE id = ?', [$id]);
    if ($eb) {
        db_run('DELETE FROM ebooks WHERE id = ?', [$id]);
        log_admin_action('DELETE_EBOOK', 'ebooks', $id, 'Hapus: ' . $eb['title']);
        set_flash('success', 'E-Book "' . $eb['title'] . '" berhasil dihapus.');
    }
    header('Location: ebook-admin.php'); exit;
}

$ebooks = db_all('SELECT e.*, u.name AS author FROM ebooks e JOIN users u ON u.id = e.created_by ORDER BY e.created_at DESC');
$active_page = 'ebook';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — E-Book</title>
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
      <h1 class="font-bold text-gray-900">Manajemen E-Book</h1>
      <a href="tambah-ebook.php" class="bg-brand text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">+ Tambah E-Book</a>
    </header>
    <main class="p-8 space-y-6">
      <?php render_flash(); ?>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        <?php if (empty($ebooks)): ?>
          <div class="col-span-4 text-center py-16 text-gray-400">
            <p class="text-lg font-semibold">Belum ada e-book.</p>
            <a href="tambah-ebook.php" class="text-brand hover:underline text-sm">Tambah sekarang</a>
          </div>
        <?php else: ?>
          <?php foreach ($ebooks as $eb): ?>
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div class="h-32 flex items-center justify-center" style="background: <?= e($eb['cover_color_hex'] ?? '#1B3F8B') ?>">
              <p class="text-white font-black text-sm text-center px-3 leading-tight"><?= e($eb['title']) ?></p>
            </div>
            <div class="p-4 flex flex-col gap-2 flex-1">
              <?php if ($eb['category']): ?>
                <span class="text-xs bg-blue-100 text-blue-700 font-semibold px-2 py-0.5 rounded self-start"><?= e($eb['category']) ?></span>
              <?php endif; ?>
              <p class="text-xs text-gray-400"><?= $eb['pages'] ?> halaman &bull; <?= $eb['is_premium'] ? '<span class="text-yellow-600 font-semibold">Premium</span>' : '<span class="text-green-600 font-semibold">Gratis</span>' ?></p>
              <p class="text-xs text-gray-400 flex-1">oleh <?= e($eb['author']) ?></p>
              <div class="flex gap-2 mt-1">
                <a href="tambah-ebook.php?edit=<?= (int)$eb['id'] ?>" class="flex-1 text-center text-xs text-brand border border-brand font-semibold py-1.5 rounded-lg hover:bg-brand hover:text-white transition">Edit</a>
                <form method="POST" onsubmit="return confirm('Hapus e-book ini?')" class="flex-1">
                  <input type="hidden" name="delete_id" value="<?= (int)$eb['id'] ?>">
                  <button type="submit" class="w-full text-xs text-red-500 border border-red-200 font-semibold py-1.5 rounded-lg hover:bg-red-50 transition">Hapus</button>
                </form>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>
</body>
</html>
