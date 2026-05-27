<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$total_users   = db_row('SELECT COUNT(*) AS cnt FROM users WHERE role = "user"')['cnt'] ?? 0;
$premium_users = db_row('SELECT COUNT(*) AS cnt FROM users WHERE plan IN ("premium","pro")')['cnt'] ?? 0;
$revenue       = db_row('SELECT COALESCE(SUM(amount),0) AS total FROM memberships WHERE status = "active"')['total'] ?? 0;
$total_materi  = db_row('SELECT COUNT(*) AS cnt FROM materi')['cnt'] ?? 0;
$total_soal    = db_row('SELECT COUNT(*) AS cnt FROM questions')['cnt'] ?? 0;
$total_tryout  = db_row('SELECT COUNT(*) AS cnt FROM tryouts')['cnt'] ?? 0;
$total_ebook   = db_row('SELECT COUNT(*) AS cnt FROM ebooks')['cnt'] ?? 0;
$total_forum   = db_row('SELECT COUNT(*) AS cnt FROM forum_posts')['cnt'] ?? 0;

$recent_users  = db_all('SELECT id, name, email, plan, created_at FROM users WHERE role = "user" ORDER BY created_at DESC LIMIT 5');
$recent_logs   = db_all('SELECT al.*, u.name AS admin_name FROM admin_logs al JOIN users u ON u.id = al.admin_id ORDER BY al.created_at DESC LIMIT 5');

$active_page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — EngLight</title>
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
      <h1 class="font-bold text-gray-900">Admin Dashboard</h1>
      <span class="text-sm text-gray-500">Admin EngLight</span>
    </header>
    <main class="p-8 space-y-6">
      <?php render_flash(); ?>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
          <div class="bg-blue-50 w-10 h-10 rounded-xl mb-3"></div>
          <p class="text-2xl font-bold text-blue-600"><?= number_format($total_users) ?></p>
          <p class="text-sm text-gray-500">Total Pengguna</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
          <div class="bg-green-50 w-10 h-10 rounded-xl mb-3"></div>
          <p class="text-2xl font-bold text-green-600"><?= number_format($premium_users) ?></p>
          <p class="text-sm text-gray-500">Pengguna Premium</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
          <div class="bg-purple-50 w-10 h-10 rounded-xl mb-3"></div>
          <p class="text-2xl font-bold text-purple-600">Rp <?= number_format($revenue, 0, ',', '.') ?></p>
          <p class="text-sm text-gray-500">Total Revenue</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
          <div class="bg-orange-50 w-10 h-10 rounded-xl mb-3"></div>
          <p class="text-2xl font-bold text-orange-600"><?= number_format($total_forum) ?></p>
          <p class="text-sm text-gray-500">Postingan Forum</p>
        </div>
      </div>

      <!-- Content Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <?php
        $content_stats = [
          ['Materi', $total_materi, 'text-blue-600', 'bg-blue-50'],
          ['Bank Soal', $total_soal, 'text-green-600', 'bg-green-50'],
          ['Tryout', $total_tryout, 'text-orange-600', 'bg-orange-50'],
          ['E-Book', $total_ebook, 'text-purple-600', 'bg-purple-50'],
        ];
        foreach ($content_stats as [$label, $cnt, $tc, $bg]):
        ?>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
          <div class="<?= $bg ?> w-10 h-10 rounded-xl flex-shrink-0"></div>
          <div>
            <p class="text-xl font-bold <?= $tc ?>"><?= number_format($cnt) ?></p>
            <p class="text-xs text-gray-500"><?= e($label) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Users -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-gray-900">Pengguna Terbaru</h2>
            <a href="pengguna-admin.php" class="text-xs text-brand hover:underline">Lihat semua →</a>
          </div>
          <div class="divide-y divide-gray-100">
            <?php if (empty($recent_users)): ?>
              <p class="px-6 py-8 text-sm text-gray-400 text-center">Belum ada pengguna.</p>
            <?php else: ?>
              <?php foreach ($recent_users as $u): ?>
              <div class="px-6 py-3 flex items-center justify-between">
                <div>
                  <p class="font-semibold text-sm text-gray-800"><?= e($u['name']) ?></p>
                  <p class="text-xs text-gray-400"><?= e($u['email']) ?></p>
                </div>
                <span class="text-xs font-bold uppercase bg-blue-100 text-blue-700 px-2 py-0.5 rounded"><?= e($u['plan']) ?></span>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Recent Logs -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-gray-900">Log Aktivitas</h2>
            <a href="log-admin.php" class="text-xs text-brand hover:underline">Lihat semua →</a>
          </div>
          <div class="divide-y divide-gray-100">
            <?php if (empty($recent_logs)): ?>
              <p class="px-6 py-8 text-sm text-gray-400 text-center">Belum ada log.</p>
            <?php else: ?>
              <?php foreach ($recent_logs as $l): ?>
              <div class="px-6 py-3">
                <p class="text-sm font-semibold text-gray-800"><?= e($l['action']) ?></p>
                <p class="text-xs text-gray-400"><?= e($l['admin_name']) ?> &bull; <?= date('d M Y H:i', strtotime($l['created_at'])) ?></p>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>
</body>
</html>
