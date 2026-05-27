<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$user   = current_user();
$ebooks = db_all('SELECT e.*, u.name AS author FROM ebooks e JOIN users u ON u.id = e.created_by ORDER BY e.created_at DESC');

$active_page = 'ebook';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-Book — EngLight</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{brand:{DEFAULT:'#1B3F8B'}}}}}</script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;900&display=swap" rel="stylesheet">
  <style>body{font-family:'Poppins',sans-serif}</style>
  <link rel="stylesheet" href="style.css">
</head>
<body class="h-full">
<div class="flex h-full min-h-screen">
  <?php require_once __DIR__ . '/includes/sidebar_user.php'; ?>
  <div class="flex-1 overflow-y-auto">
    <header class="bg-white border-b px-6 py-4 flex items-center justify-between">
      <h1 class="font-bold text-gray-900">E-Book Premium</h1>
      <span class="text-sm text-gray-500"><?= e($user['name']) ?></span>
    </header>
    <main class="p-6 lg:p-8 space-y-6">
      <?php render_flash(); ?>

      <?php if (empty($ebooks)): ?>
        <div class="bg-white rounded-2xl p-12 text-center text-gray-400 border border-gray-100 shadow-sm">
          <p class="text-lg font-semibold mb-2">Belum ada e-book tersedia.</p>
        </div>
      <?php else: ?>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        <?php foreach ($ebooks as $eb): ?>
          <?php $locked = $eb['is_premium'] && $user['plan'] === 'free'; ?>
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <!-- Cover -->
            <div class="h-40 flex items-center justify-center relative" style="background: <?= e($eb['cover_color_hex'] ?? '#1B3F8B') ?>">
              <div class="text-center text-white px-4">
                <p class="font-black text-sm leading-tight"><?= e($eb['title']) ?></p>
                <?php if ($eb['pages']): ?><p class="text-xs text-white/70 mt-1"><?= $eb['pages'] ?> hlm</p><?php endif; ?>
              </div>
              <?php if ($locked): ?>
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                  <span class="text-2xl">🔒</span>
                </div>
              <?php endif; ?>
            </div>
            <div class="p-4 flex flex-col flex-1 gap-2">
              <?php if ($eb['category']): ?>
                <span class="text-xs bg-blue-100 text-blue-700 font-semibold px-2 py-0.5 rounded self-start"><?= e($eb['category']) ?></span>
              <?php endif; ?>
              <p class="text-xs text-gray-500 flex-1 leading-relaxed"><?= e(mb_substr($eb['description'] ?? '', 0, 80)) ?><?= strlen($eb['description'] ?? '') > 80 ? '…' : '' ?></p>
              <?php if ($locked): ?>
                <a href="membership-user.php" class="text-center text-xs bg-yellow-500 text-white py-2 rounded-xl font-semibold hover:bg-yellow-600 transition">Upgrade</a>
              <?php elseif ($eb['file_path']): ?>
                <a href="<?= e($eb['file_path']) ?>" target="_blank" class="text-center text-xs bg-brand text-white py-2 rounded-xl font-semibold hover:bg-blue-700 transition">Unduh PDF</a>
              <?php else: ?>
                <span class="text-center text-xs text-gray-400">Segera hadir</span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </main>
  </div>
</div>
</body>
</html>
