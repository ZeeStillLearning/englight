<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$user    = current_user();
$history = db_all('SELECT * FROM speaking_sessions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10', [$user['id']]);

$active_page = 'speaking';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Speaking AI — EngLight</title>
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
      <h1 class="font-bold text-gray-900">Speaking Practice AI</h1>
      <span class="text-sm text-gray-500"><?= e($user['name']) ?></span>
    </header>
    <main class="p-6 lg:p-8 space-y-6">
      <?php render_flash(); ?>

      <?php if ($user['plan'] === 'free'): ?>
      <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6 flex items-center gap-4">
        <span class="text-3xl">🔒</span>
        <div class="flex-1">
          <p class="font-bold text-yellow-800">Fitur Premium Diperlukan</p>
          <p class="text-sm text-yellow-600 mt-1">Upgrade ke Premium atau Pro untuk mengakses Speaking Practice AI dengan feedback real-time.</p>
        </div>
        <a href="membership-user.php" class="bg-yellow-500 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-yellow-600 transition">Upgrade</a>
      </div>
      <?php endif; ?>

      <!-- Speaking Practice Widget -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 mb-2">Mulai Sesi Speaking</h2>
        <p class="text-sm text-gray-500 mb-6">Rekam suaramu, AI akan menganalisis pronunciation dan grammar secara real-time.</p>

        <div class="bg-gradient-to-br from-brand to-blue-600 rounded-2xl p-8 text-center text-white mb-6">
          <div id="rec-btn" class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 cursor-pointer hover:bg-white/30 transition <?= $user['plan'] === 'free' ? 'opacity-50 pointer-events-none' : '' ?>" onclick="toggleRecording()">
            <svg id="mic-icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="9" y="2" width="6" height="13" rx="3"></rect>
              <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
              <path d="M12 19v3"></path>
            </svg>
          </div>
          <p id="rec-status" class="font-semibold">Klik untuk mulai merekam</p>
          <p class="text-xs text-white/60 mt-1" id="rec-timer">00:00</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-blue-50 rounded-xl p-4 text-center">
            <p class="text-2xl font-black text-brand">—</p>
            <p class="text-xs text-gray-500 mt-1">Pronunciation Score</p>
          </div>
          <div class="bg-green-50 rounded-xl p-4 text-center">
            <p class="text-2xl font-black text-green-600">—</p>
            <p class="text-xs text-gray-500 mt-1">Grammar Score</p>
          </div>
          <div class="bg-orange-50 rounded-xl p-4 text-center">
            <p class="text-2xl font-black text-orange-600">—</p>
            <p class="text-xs text-gray-500 mt-1">Fluency Score</p>
          </div>
        </div>
      </div>

      <!-- Practice Topics -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 mb-4">Topik Latihan</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <?php
          $topics = [
            ['Introduce yourself', 'Dasar'],
            ['Describe your daily routine', 'Dasar'],
            ['Talk about your hobbies', 'Menengah'],
            ['Describe a place you visited', 'Menengah'],
            ['Discuss current events', 'Lanjutan'],
            ['Express your opinion on education', 'Lanjutan'],
          ];
          foreach ($topics as [$topic, $level]):
            $lc = ['Dasar'=>'blue','Menengah'=>'orange','Lanjutan'=>'red'][$level];
          ?>
          <div class="flex items-center justify-between p-4 rounded-xl border border-gray-200 hover:border-brand hover:bg-blue-50 transition cursor-pointer <?= $user['plan'] === 'free' ? 'opacity-60' : '' ?>">
            <div>
              <p class="font-semibold text-sm text-gray-800"><?= e($topic) ?></p>
              <span class="text-xs bg-<?= $lc ?>-100 text-<?= $lc ?>-700 px-2 py-0.5 rounded font-semibold"><?= e($level) ?></span>
            </div>
            <?php if ($user['plan'] !== 'free'): ?>
              <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <?php else: ?>
              <span class="text-sm">🔒</span>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- History -->
      <?php if (!empty($history)): ?>
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="font-bold text-gray-900">Riwayat Sesi</h2>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
            <tr>
              <th class="px-6 py-3 text-left">Tanggal</th>
              <th class="px-6 py-3 text-left">Topik</th>
              <th class="px-6 py-3 text-left">Skor</th>
              <th class="px-6 py-3 text-left">Feedback</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($history as $h): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-3 text-gray-500"><?= date('d M Y', strtotime($h['created_at'])) ?></td>
              <td class="px-6 py-3 text-gray-700"><?= e($h['topic'] ?? '—') ?></td>
              <td class="px-6 py-3 font-bold text-brand"><?= $h['score'] ?? '—' ?><?= $h['score'] ? '/100' : '' ?></td>
              <td class="px-6 py-3 text-gray-500 text-xs"><?= e(mb_substr($h['feedback'] ?? '—', 0, 60)) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

    </main>
  </div>
</div>
<script>
let recording = false, timer = null, seconds = 0;
function toggleRecording() {
  recording = !recording;
  const status = document.getElementById('rec-status');
  const timerEl = document.getElementById('rec-timer');
  const btn = document.getElementById('rec-btn');
  if (recording) {
    status.textContent = '🔴 Merekam...';
    btn.classList.add('animate-pulse');
    seconds = 0;
    timer = setInterval(() => {
      seconds++;
      const m = String(Math.floor(seconds/60)).padStart(2,'0');
      const s = String(seconds%60).padStart(2,'0');
      timerEl.textContent = m + ':' + s;
    }, 1000);
  } else {
    clearInterval(timer);
    btn.classList.remove('animate-pulse');
    status.textContent = 'Klik untuk mulai merekam';
    timerEl.textContent = '00:00';
  }
}
</script>
</body>
</html>
