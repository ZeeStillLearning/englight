<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $m  = db_row('SELECT title FROM materi WHERE id = ?', [$id]);
    if ($m) {
        db_run('DELETE FROM materi WHERE id = ?', [$id]);
        log_admin_action('DELETE_MATERI', 'materi', $id, 'Hapus: ' . $m['title']);
        set_flash('success', 'Materi "' . $m['title'] . '" berhasil dihapus.');
    }
    header('Location: materi-admin.php'); exit;
}

$materi = db_all('SELECT m.*, u.name AS author FROM materi m JOIN users u ON u.id = m.created_by ORDER BY m.sort_order, m.created_at DESC');
$active_page = 'materi';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Materi</title>
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
      <h1 class="font-bold text-gray-900">Manajemen Materi</h1>
      <a href="tambah-materi.php" class="bg-brand text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">+ Tambah Materi</a>
    </header>
    <main class="p-8 space-y-6">
      <?php render_flash(); ?>

      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
            <tr>
              <th class="px-6 py-3 text-left">Judul</th>
              <th class="px-6 py-3 text-left">Kategori</th>
              <th class="px-6 py-3 text-left">Tipe</th>
              <th class="px-6 py-3 text-left">Premium</th>
              <th class="px-6 py-3 text-left">Oleh</th>
              <th class="px-6 py-3 text-left">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php if (empty($materi)): ?>
              <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Belum ada materi. <a href="tambah-materi.php" class="text-brand hover:underline">Tambah sekarang</a></td></tr>
            <?php else: ?>
              <?php foreach ($materi as $m): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <p class="font-semibold text-gray-800"><?= e($m['title']) ?></p>
                  <p class="text-xs text-gray-400 mt-0.5"><?= e(mb_substr($m['description'] ?? '', 0, 60)) ?></p>
                </td>
                <td class="px-6 py-4 capitalize text-gray-600"><?= e($m['category']) ?></td>
                <td class="px-6 py-4 uppercase text-xs font-semibold text-gray-500"><?= e($m['type']) ?></td>
                <td class="px-6 py-4">
                  <span class="text-xs font-semibold px-2 py-0.5 rounded <?= $m['is_premium'] ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' ?>">
                    <?= $m['is_premium'] ? 'Premium' : 'Gratis' ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-gray-500 text-xs"><?= e($m['author']) ?></td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <a href="tambah-materi.php?edit=<?= (int)$m['id'] ?>" class="text-xs text-brand font-semibold hover:underline">Edit</a>
                    <form method="POST" onsubmit="return confirm('Hapus materi ini?')">
                      <input type="hidden" name="delete_id" value="<?= (int)$m['id'] ?>">
                      <button type="submit" class="text-xs text-red-500 font-semibold hover:underline">Hapus</button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</div>
</body>
</html>
