<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$user    = current_user();
$tryouts = db_all('SELECT t.*,
                          (SELECT COUNT(*) FROM tryout_sessions ts WHERE ts.tryout_id = t.id AND ts.user_id = ?) AS attempt_count,
                          (SELECT MAX(ts.score) FROM tryout_sessions ts WHERE ts.tryout_id = t.id AND ts.user_id = ?) AS best_score
                   FROM tryouts t ORDER BY t.created_at DESC',
                  [$user['id'], $user['id']]);

$history = db_all('SELECT ts.*, t.title FROM tryout_sessions ts
                   JOIN tryouts t ON t.id = ts.tryout_id
                   WHERE ts.user_id = ? ORDER BY ts.started_at DESC LIMIT 10',
                  [$user['id']]);

$active_page = 'tryout';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tryout TOEFL — EngLight</title>
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
      <h1 class="font-bold text-gray-900">Tryout TOEFL</h1>
      <span class="text-sm text-gray-500"><?= e($user['name']) ?></span>
    </header>
    <main class="p-6 lg:p-8 space-y-6">
      <?php render_flash(); ?>

      <?php if (empty($tryouts)): ?>
        <div class="bg-white rounded-2xl p-12 text-center text-gray-400 border border-gray-100 shadow-sm">
          <p class="text-lg font-semibold mb-2">Belum ada tryout tersedia.</p>
          <p class="text-sm">Admin belum menambahkan tryout.</p>
        </div>
      <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($tryouts as $t): ?>
          <?php $locked = $t['is_premium'] && $user['plan'] === 'free'; ?>
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4">
            <div class="flex items-start justify-between">
              <h3 class="font-bold text-gray-900 flex-1"><?= e($t['title']) ?></h3>
              <?php if ($locked): ?>
                <span class="text-xs bg-yellow-100 text-yellow-700 font-bold px-2 py-1 rounded-lg ml-2">🔒</span>
              <?php endif; ?>
            </div>
            <p class="text-sm text-gray-500 leading-relaxed"><?= e($t['description'] ?? '') ?></p>
            <div class="grid grid-cols-3 gap-2 text-center text-xs">
              <div class="bg-blue-50 rounded-xl py-2">
                <p class="font-bold text-blue-700"><?= $t['total_questions'] ?></p>
                <p class="text-gray-500">Soal</p>
              </div>
              <div class="bg-orange-50 rounded-xl py-2">
                <p class="font-bold text-orange-600"><?= $t['duration_minutes'] ?> min</p>
                <p class="text-gray-500">Durasi</p>
              </div>
              <div class="bg-green-50 rounded-xl py-2">
                <p class="font-bold text-green-600"><?= $t['best_score'] ?? '—' ?><?= $t['best_score'] !== null ? '%' : '' ?></p>
                <p class="text-gray-500">Best</p>
              </div>
            </div>
            <?php if ($locked): ?>
              <a href="membership-user.php" class="block text-center py-2.5 bg-yellow-500 text-white rounded-xl text-sm font-semibold hover:bg-yellow-600 transition">Upgrade untuk Akses</a>
            <?php else: ?>
              <a href="tryout-start.php?id=<?= (int)$t['id'] ?>" class="block text-center py-2.5 bg-brand text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
                <?= $t['attempt_count'] > 0 ? 'Ulangi Tryout' : 'Mulai Tryout' ?>
              </a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($history)): ?>
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="font-bold text-gray-900">Riwayat Tryout</h2>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
            <tr>
              <th class="px-6 py-3 text-left">Tryout</th>
              <th class="px-6 py-3 text-left">Tanggal</th>
              <th class="px-6 py-3 text-left">Skor</th>
              <th class="px-6 py-3 text-left">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($history as $h): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-3 font-medium text-gray-800"><?= e($h['title']) ?></td>
              <td class="px-6 py-3 text-gray-500"><?= date('d M Y H:i', strtotime($h['started_at'])) ?></td>
              <td class="px-6 py-3 font-bold <?= $h['is_passed'] ? 'text-green-600' : 'text-red-500' ?>"><?= $h['score'] ?>%</td>
              <td class="px-6 py-3">
                <span class="text-xs font-semibold px-2 py-1 rounded-lg <?= $h['is_passed'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                  <?= $h['is_passed'] ? 'Lulus' : 'Belum Lulus' ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

    </main>
  </div>
</div>
</body>
</html>
