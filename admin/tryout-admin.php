<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $t  = db_row('SELECT title FROM tryouts WHERE id = ?', [$id]);
    if ($t) {
        db_run('DELETE FROM tryouts WHERE id = ?', [$id]);
        log_admin_action('DELETE_TRYOUT', 'tryouts', $id, 'Hapus: ' . $t['title']);
        set_flash('success', 'Tryout "' . $t['title'] . '" berhasil dihapus.');
    }
    header('Location: tryout-admin.php'); exit;
}

$tryouts = db_all('SELECT t.*, u.name AS author,
                          (SELECT COUNT(*) FROM tryout_questions tq WHERE tq.tryout_id = t.id) AS soal_count,
                          (SELECT COUNT(*) FROM tryout_sessions ts WHERE ts.tryout_id = t.id) AS attempt_count
                   FROM tryouts t JOIN users u ON u.id = t.created_by
                   ORDER BY t.created_at DESC');

$active_page = 'tryout';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Tryout</title>
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
      <h1 class="font-bold text-gray-900">Manajemen Tryout</h1>
      <a href="tambah-tryout.php" class="bg-brand text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">+ Tambah Tryout</a>
    </header>
    <main class="p-8 space-y-6">
      <?php render_flash(); ?>

      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
            <tr>
              <th class="px-6 py-3 text-left">Judul</th>
              <th class="px-6 py-3 text-left">Durasi</th>
              <th class="px-6 py-3 text-left">Soal</th>
              <th class="px-6 py-3 text-left">Passing</th>
              <th class="px-6 py-3 text-left">Peserta</th>
              <th class="px-6 py-3 text-left">Premium</th>
              <th class="px-6 py-3 text-left">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php if (empty($tryouts)): ?>
              <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Belum ada tryout. <a href="tambah-tryout.php" class="text-brand hover:underline">Tambah sekarang</a></td></tr>
            <?php else: ?>
              <?php foreach ($tryouts as $t): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <p class="font-semibold text-gray-800"><?= e($t['title']) ?></p>
                  <p class="text-xs text-gray-400 mt-0.5">oleh <?= e($t['author']) ?></p>
                </td>
                <td class="px-6 py-4 text-gray-600"><?= $t['duration_minutes'] ?> menit</td>
                <td class="px-6 py-4 text-gray-600"><?= $t['soal_count'] ?> / <?= $t['total_questions'] ?></td>
                <td class="px-6 py-4 text-gray-600"><?= $t['passing_score'] ?>%</td>
                <td class="px-6 py-4 text-gray-600"><?= number_format($t['attempt_count']) ?></td>
                <td class="px-6 py-4">
                  <span class="text-xs font-semibold px-2 py-0.5 rounded <?= $t['is_premium'] ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' ?>">
                    <?= $t['is_premium'] ? 'Premium' : 'Gratis' ?>
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <a href="tambah-tryout.php?edit=<?= (int)$t['id'] ?>" class="text-xs text-brand font-semibold hover:underline">Edit</a>
                    <a href="kelola-soal-tryout.php?tryout_id=<?= (int)$t['id'] ?>" class="text-xs text-green-600 font-semibold hover:underline">Kelola Soal</a>
                    <form method="POST" onsubmit="return confirm('Hapus tryout ini? Semua sesi terkait akan ikut terhapus.')">
                      <input type="hidden" name="delete_id" value="<?= (int)$t['id'] ?>">
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
