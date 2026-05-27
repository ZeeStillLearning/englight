<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$user = current_user();

// Handle upgrade simulation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upgrade_plan'])) {
    $plan = $_POST['upgrade_plan'];
    $valid_plans = ['premium' => 50000, 'pro' => 199000];
    if (array_key_exists($plan, $valid_plans)) {
        db_run('UPDATE users SET plan = ? WHERE id = ?', [$plan, $user['id']]);
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
        db_run('INSERT INTO memberships (user_id, plan, expires_at, amount, status) VALUES (?, ?, ?, ?, ?)',
               [$user['id'], $plan, $expires, $valid_plans[$plan], 'active']);
        $_SESSION['user_plan'] = $plan;
        set_flash('success', 'Upgrade berhasil! Selamat menikmati fitur ' . strtoupper($plan) . '.');
        header('Location: membership-user.php'); exit;
    }
}

$memberships = db_all('SELECT * FROM memberships WHERE user_id = ? ORDER BY created_at DESC LIMIT 5', [$user['id']]);

$active_page = 'membership';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Membership — EngLight</title>
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
      <h1 class="font-bold text-gray-900">Membership</h1>
      <span class="text-xs bg-blue-100 text-blue-700 font-semibold px-3 py-1 rounded-full uppercase"><?= e($user['plan']) ?></span>
    </header>
    <main class="p-6 lg:p-8 space-y-8">
      <?php render_flash(); ?>

      <!-- Current Plan Banner -->
      <div class="bg-gradient-to-r from-brand to-blue-600 rounded-2xl p-6 text-white">
        <p class="text-sm text-white/70 uppercase tracking-wide mb-1">Paket Aktif</p>
        <p class="text-3xl font-black uppercase"><?= e($user['plan']) ?></p>
        <p class="text-white/80 text-sm mt-2">XP Kamu: <strong><?= number_format($user['xp']) ?></strong></p>
      </div>

      <!-- Plans -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Free -->
        <div class="bg-[#F4F7FF] border border-gray-200 rounded-2xl p-6 flex flex-col <?= $user['plan'] === 'free' ? 'ring-2 ring-brand' : '' ?>">
          <div class="mb-4">
            <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Free</p>
            <p class="text-3xl font-black text-gray-900">Rp 0</p>
            <p class="text-xs text-gray-500">/ Selamanya</p>
          </div>
          <ul class="text-sm space-y-2 mb-6 flex-1 text-gray-600">
            <li>✓ Materi dasar</li>
            <li>✓ 50 soal/bulan</li>
            <li>✓ Forum diskusi</li>
            <li class="text-gray-300">✗ Tryout TOEFL premium</li>
            <li class="text-gray-300">✗ E-book premium</li>
          </ul>
          <?php if ($user['plan'] === 'free'): ?>
            <span class="block text-center py-2.5 border-2 border-brand text-brand font-semibold rounded-xl text-sm">Paket Aktif</span>
          <?php endif; ?>
        </div>

        <!-- Premium -->
        <div class="bg-brand rounded-2xl p-6 flex flex-col relative <?= $user['plan'] === 'premium' ? 'ring-4 ring-yellow-400' : '' ?>">
          <div class="absolute -top-3 left-1/2 -translate-x-1/2">
            <span class="bg-yellow-400 text-white text-xs font-black px-4 py-1 rounded-full">⭐ Populer</span>
          </div>
          <div class="mb-4">
            <p class="text-xs font-semibold text-blue-300 uppercase mb-1">Premium</p>
            <p class="text-3xl font-black text-white">Rp 50.000</p>
            <p class="text-xs text-blue-200">/ Bulan</p>
          </div>
          <ul class="text-sm space-y-2 mb-6 flex-1 text-white/80">
            <li>✓ Semua materi</li>
            <li>✓ Soal tak terbatas</li>
            <li>✓ Tryout TOEFL</li>
            <li>✓ Sertifikat kelulusan</li>
          </ul>
          <?php if ($user['plan'] === 'premium'): ?>
            <span class="block text-center py-2.5 bg-white/20 text-white font-semibold rounded-xl text-sm">Paket Aktif</span>
          <?php elseif ($user['plan'] === 'free'): ?>
            <form method="POST">
              <input type="hidden" name="upgrade_plan" value="premium">
              <button type="submit" class="w-full py-2.5 bg-yellow-400 text-white font-bold rounded-xl text-sm hover:bg-yellow-500 transition">Upgrade ke Premium</button>
            </form>
          <?php endif; ?>
        </div>

        <!-- Pro -->
        <div class="bg-[#F4F7FF] border border-gray-200 rounded-2xl p-6 flex flex-col <?= $user['plan'] === 'pro' ? 'ring-2 ring-green-500' : '' ?>">
          <div class="mb-4">
            <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Pro</p>
            <p class="text-3xl font-black text-gray-900">Rp 199.000</p>
            <p class="text-xs text-gray-500">/ Bulan <span class="line-through text-gray-300">Rp 300.000</span></p>
          </div>
          <ul class="text-sm space-y-2 mb-6 flex-1 text-gray-600">
            <li>✓ Semua fitur Premium</li>
            <li>✓ Speaking AI 10x/bulan</li>
            <li>✓ Tryout unlimited</li>
            <li>✓ Semua E-book</li>
            <li>✓ Laporan mingguan</li>
          </ul>
          <?php if ($user['plan'] === 'pro'): ?>
            <span class="block text-center py-2.5 border-2 border-green-500 text-green-600 font-semibold rounded-xl text-sm">Paket Aktif</span>
          <?php else: ?>
            <form method="POST">
              <input type="hidden" name="upgrade_plan" value="pro">
              <button type="submit" class="w-full py-2.5 bg-brand text-white font-bold rounded-xl text-sm hover:bg-blue-700 transition">Upgrade ke Pro</button>
            </form>
          <?php endif; ?>
        </div>
      </div>

      <!-- History -->
      <?php if (!empty($memberships)): ?>
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="font-bold text-gray-900">Riwayat Pembayaran</h2>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
            <tr>
              <th class="px-6 py-3 text-left">Tanggal</th>
              <th class="px-6 py-3 text-left">Paket</th>
              <th class="px-6 py-3 text-left">Jumlah</th>
              <th class="px-6 py-3 text-left">Berakhir</th>
              <th class="px-6 py-3 text-left">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($memberships as $m): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-3 text-gray-500"><?= date('d M Y', strtotime($m['created_at'])) ?></td>
              <td class="px-6 py-3 font-semibold uppercase text-gray-800"><?= e($m['plan']) ?></td>
              <td class="px-6 py-3 text-gray-700">Rp <?= number_format($m['amount'], 0, ',', '.') ?></td>
              <td class="px-6 py-3 text-gray-500"><?= $m['expires_at'] ? date('d M Y', strtotime($m['expires_at'])) : '—' ?></td>
              <td class="px-6 py-3">
                <span class="text-xs font-semibold px-2 py-1 rounded-lg <?= $m['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                  <?= e(ucfirst($m['status'])) ?>
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
