<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$user = current_user();

// Fetch stats
$total_materi    = db_row('SELECT COUNT(*) AS cnt FROM materi')['cnt'] ?? 0;
$total_completed = db_row('SELECT COUNT(*) AS cnt FROM user_materi_progress WHERE user_id = ? AND is_completed = 1', [$user['id']])['cnt'] ?? 0;
$total_tryout    = db_row('SELECT COUNT(*) AS cnt FROM tryout_sessions WHERE user_id = ?', [$user['id']])['cnt'] ?? 0;
$avg_score       = db_row('SELECT AVG(score) AS avg FROM tryout_sessions WHERE user_id = ? AND finished_at IS NOT NULL', [$user['id']])['avg'] ?? 0;

$recent_materi   = db_all('SELECT m.title, m.category, ump.completed_at
                            FROM user_materi_progress ump
                            JOIN materi m ON m.id = ump.materi_id
                            WHERE ump.user_id = ? AND ump.is_completed = 1
                            ORDER BY ump.completed_at DESC LIMIT 5', [$user['id']]);

$recent_tryouts  = db_all('SELECT ts.score, ts.is_passed, ts.finished_at, t.title
                            FROM tryout_sessions ts
                            JOIN tryouts t ON t.id = ts.tryout_id
                            WHERE ts.user_id = ?
                            ORDER BY ts.started_at DESC LIMIT 5', [$user['id']]);

$active_page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — EngLight</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { theme: { extend: { colors: { brand: { DEFAULT: '#1B3F8B', light: '#2951b3', dark: '#142e66' } } } } }</script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;900&display=swap" rel="stylesheet">
  <style>body { font-family: 'Poppins', sans-serif; }</style>
  <link rel="stylesheet" href="style.css">
</head>
<body class="h-full">

<div class="flex h-full min-h-screen">
  <?php require_once __DIR__ . '/includes/sidebar_user.php'; ?>

  <div class="flex-1 overflow-y-auto">
    <header class="bg-white border-b px-6 py-4 flex items-center justify-between">
      <div>
        <h1 class="font-bold text-gray-900">Dashboard</h1>
        <p class="text-xs text-gray-500">Selamat datang kembali, <?= e($user['name']) ?>!</p>
      </div>
      <div class="flex items-center gap-3">
        <span class="text-xs bg-yellow-100 text-yellow-700 font-semibold px-3 py-1 rounded-full">⚡ <?= number_format($user['xp']) ?> XP</span>
        <span class="text-xs bg-blue-100 text-blue-700 font-semibold px-3 py-1 rounded-full uppercase"><?= e($user['plan']) ?></span>
      </div>
    </header>
    <main class="p-6 lg:p-8 space-y-8">

      <!-- Stat Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-brand to-brand-light rounded-2xl p-5 text-white">
          <p class="text-white/70 text-xs font-medium uppercase mb-1">Materi Selesai</p>
          <p class="text-2xl font-bold"><?= $total_completed ?> <span class="text-sm font-normal text-white/70">/ <?= $total_materi ?></span></p>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-2xl p-5 text-white">
          <p class="text-white/70 text-xs font-medium uppercase mb-1">Tryout Dilakukan</p>
          <p class="text-2xl font-bold"><?= $total_tryout ?></p>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-5 text-white">
          <p class="text-white/70 text-xs font-medium uppercase mb-1">Avg Tryout Score</p>
          <p class="text-2xl font-bold"><?= $avg_score > 0 ? number_format($avg_score, 1) : '—' ?></p>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-2xl p-5 text-white">
          <p class="text-white/70 text-xs font-medium uppercase mb-1">XP Points</p>
          <p class="text-2xl font-bold"><?= number_format($user['xp']) ?></p>
        </div>
      </div>

      <!-- Progress -->
      <?php if ($total_materi > 0): ?>
      <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
        <h2 class="font-bold text-gray-900 mb-3">Progress Materi</h2>
        <?php $pct = round($total_completed / $total_materi * 100); ?>
        <div class="flex justify-between text-sm text-gray-500 mb-2">
          <span><?= $total_completed ?> dari <?= $total_materi ?> materi</span>
          <span class="font-bold text-brand"><?= $pct ?>%</span>
        </div>
        <div class="bg-gray-100 rounded-full h-3">
          <div class="bg-brand rounded-full h-3 transition-all" style="width:<?= $pct ?>%"></div>
        </div>
      </div>
      <?php endif; ?>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
          <h2 class="font-bold text-gray-900 mb-4">Aksi Cepat</h2>
          <div class="space-y-3">
            <a href="materi-user.php"     class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
              <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-brand transition">
                <svg class="w-5 h-5 text-brand group-hover:text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
              </div>
              <div><p class="font-semibold text-sm text-gray-800">Lanjutkan Materi</p><p class="text-xs text-gray-400">Video &amp; bahan ajar</p></div>
            </a>
            <a href="latihansoal-user.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
              <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center group-hover:bg-green-500 transition">
                <svg class="w-5 h-5 text-green-600 group-hover:text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
              </div>
              <div><p class="font-semibold text-sm text-gray-800">Latihan Soal</p><p class="text-xs text-gray-400">Adaptive learning</p></div>
            </a>
            <a href="tryout-user.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
              <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center group-hover:bg-orange-500 transition">
                <svg class="w-5 h-5 text-orange-600 group-hover:text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
              </div>
              <div><p class="font-semibold text-sm text-gray-800">Tryout TOEFL</p><p class="text-xs text-gray-400">Simulasi ujian</p></div>
            </a>
          </div>
        </div>

        <!-- Recent Tryouts -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
          <h2 class="font-bold text-gray-900 mb-4">Hasil Tryout Terakhir</h2>
          <?php if (empty($recent_tryouts)): ?>
            <p class="text-sm text-gray-400 text-center py-8">Belum ada tryout. <a href="tryout-user.php" class="text-brand hover:underline">Mulai sekarang →</a></p>
          <?php else: ?>
            <div class="divide-y divide-gray-100">
              <?php foreach ($recent_tryouts as $t): ?>
              <div class="flex items-center justify-between py-3">
                <div>
                  <p class="text-sm font-semibold text-gray-800 truncate max-w-[160px]"><?= e($t['title']) ?></p>
                  <p class="text-xs text-gray-400"><?= $t['finished_at'] ? date('d M Y', strtotime($t['finished_at'])) : 'Belum selesai' ?></p>
                </div>
                <span class="text-sm font-bold <?= $t['is_passed'] ? 'text-green-600' : 'text-red-500' ?>">
                  <?= $t['score'] ?>%
                </span>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </main>
  </div>
</div>
</body>
</html>
