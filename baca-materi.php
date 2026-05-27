<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$user      = current_user();
$materi_id = (int)($_GET['id'] ?? 0);

if (!$materi_id) {
    header('Location: ' . APP_URL . '/materi-user.php'); exit;
}

$materi = db_row('SELECT * FROM materi WHERE id = ?', [$materi_id]);

if (!$materi) {
    header('Location: ' . APP_URL . '/materi-user.php'); exit;
}

// Access control: premium content
if ($materi['is_premium'] && $user['plan'] === 'free') {
    set_flash('error', 'Konten ini khusus untuk anggota Premium/Pro.');
    header('Location: ' . APP_URL . '/materi-user.php'); exit;
}

// Build the correct file path
$file_path     = $materi['file_path'] ?? '';
$full_path     = __DIR__ . '/' . ltrim($file_path, '/');
$file_exists   = !empty($file_path) && file_exists($full_path);
$is_pdf        = strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) === 'pdf';
$is_video      = in_array(strtolower(pathinfo($file_path, PATHINFO_EXTENSION)), ['mp4','webm','mov']);
$file_url      = APP_URL . '/' . ltrim($file_path, '/');

// Auto-mark as completed when opened
$already_done = db_row('SELECT id FROM user_materi_progress WHERE user_id = ? AND materi_id = ?', [$user['id'], $materi_id]);
if (!$already_done && $file_exists) {
    db_run('INSERT INTO user_materi_progress (user_id, materi_id, is_completed, completed_at)
            VALUES (?, ?, 1, NOW())
            ON DUPLICATE KEY UPDATE is_completed = 1, completed_at = NOW()',
           [$user['id'], $materi_id]);
    db_run('UPDATE users SET xp = xp + 10 WHERE id = ?', [$user['id']]);
    $_SESSION['user_xp'] = ($_SESSION['user_xp'] ?? 0) + 10;
}

// Category color map
$cat_colors = [
    'listening'   => 'blue',
    'structure'   => 'green',
    'reading'     => 'purple',
    'grammar'     => 'orange',
    'vocabulary'  => 'teal',
    'speaking'    => 'red',
];
$cc = $cat_colors[$materi['category']] ?? 'gray';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($materi['title']) ?> — EngLight</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{brand:{DEFAULT:'#1B3F8B'}}}}}</script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;900&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; }
    .pdf-container { height: calc(100vh - 140px); }
    .video-container { max-height: calc(100vh - 200px); }
  </style>
  <link rel="stylesheet" href="style.css">
</head>
<body class="h-full bg-gray-50">

<!-- Top Bar -->
<div class="bg-white border-b shadow-sm px-6 py-3 flex items-center justify-between sticky top-0 z-40">
  <div class="flex items-center gap-4">
    <a href="<?= APP_URL ?>/materi-user.php"
       class="flex items-center gap-2 text-gray-500 hover:text-brand transition text-sm font-medium">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
      </svg>
      Kembali
    </a>
    <div class="h-5 w-px bg-gray-200"></div>
    <div>
      <span class="text-xs font-bold uppercase bg-<?= $cc ?>-100 text-<?= $cc ?>-700 px-2 py-0.5 rounded mr-2"><?= e($materi['category']) ?></span>
      <span class="font-semibold text-gray-900 text-sm"><?= e($materi['title']) ?></span>
    </div>
  </div>
  <div class="flex items-center gap-3">
    <?php if (!$already_done): ?>
      <span class="text-xs bg-green-100 text-green-700 font-semibold px-3 py-1 rounded-full">+10 XP didapat!</span>
    <?php else: ?>
      <span class="text-xs bg-green-100 text-green-700 font-semibold px-3 py-1 rounded-full">✓ Selesai</span>
    <?php endif; ?>
    <?php if ($file_exists): ?>
      <a href="<?= e($file_url) ?>" download
         class="flex items-center gap-1.5 text-xs bg-gray-100 text-gray-600 hover:bg-gray-200 px-3 py-1.5 rounded-lg font-semibold transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Download
      </a>
    <?php endif; ?>
  </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-4">

  <!-- Info Bar -->
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 flex items-center justify-between flex-wrap gap-3">
    <div class="flex items-center gap-6 text-sm text-gray-500">
      <span class="flex items-center gap-1.5">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span class="uppercase font-semibold text-xs"><?= e($materi['type']) ?></span>
      </span>
      <?php if ($materi['description']): ?>
      <span><?= e(mb_substr($materi['description'], 0, 120)) ?><?= mb_strlen($materi['description']) > 120 ? '…' : '' ?></span>
      <?php endif; ?>
    </div>
    <?php if ($materi['is_premium']): ?>
      <span class="text-xs bg-yellow-100 text-yellow-700 font-bold px-3 py-1 rounded-full">⭐ Premium</span>
    <?php endif; ?>
  </div>

  <!-- Content Viewer -->
  <?php if (!$file_exists): ?>

    <!-- No file uploaded yet -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
      <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
      </div>
      <p class="font-bold text-gray-700 text-lg mb-2">File Belum Tersedia</p>
      <p class="text-gray-400 text-sm">Admin belum mengunggah file untuk materi ini.</p>
      <a href="<?= APP_URL ?>/materi-user.php" class="inline-block mt-6 bg-brand text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
        Kembali ke Materi
      </a>
    </div>

  <?php elseif ($is_pdf): ?>

    <!-- PDF Viewer -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="bg-gray-800 px-4 py-2 flex items-center gap-2">
        <div class="w-3 h-3 rounded-full bg-red-400"></div>
        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
        <div class="w-3 h-3 rounded-full bg-green-400"></div>
        <span class="text-gray-400 text-xs ml-2">📄 <?= e(basename($file_path)) ?></span>
      </div>
      <div class="pdf-container w-full">
        <!-- Primary: browser native PDF embed -->
        <embed
          src="<?= e($file_url) ?>#toolbar=1&navpanes=1&scrollbar=1&view=FitH"
          type="application/pdf"
          class="w-full h-full"
          id="pdf-embed"
        />
      </div>
      <!-- Fallback if embed doesn't work -->
      <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-500">Jika PDF tidak tampil, gunakan tombol di sebelah kanan.</p>
        <div class="flex gap-2">
          <a href="<?= e($file_url) ?>" target="_blank"
             class="text-xs bg-brand text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
            Buka di Tab Baru
          </a>
          <a href="<?= e($file_url) ?>" download
             class="text-xs bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300 transition">
            Download PDF
          </a>
        </div>
      </div>
    </div>

  <?php elseif ($is_video): ?>

    <!-- Video Player -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="bg-gray-900 p-4">
        <video
          controls
          class="video-container w-full rounded-xl"
          controlsList="nodownload"
          oncontextmenu="return false;"
        >
          <source src="<?= e($file_url) ?>" type="video/<?= strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) ?>">
          Browser kamu tidak mendukung pemutar video.
        </video>
      </div>
      <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
        <p class="text-xs text-gray-500">Klik tombol play untuk mulai menonton. Pastikan koneksi internetmu stabil.</p>
      </div>
    </div>

  <?php else: ?>

    <!-- Other file types — download only -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
      <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-brand" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
      </div>
      <p class="font-bold text-gray-700 text-lg mb-2"><?= e($materi['title']) ?></p>
      <p class="text-gray-400 text-sm mb-6">File ini tidak bisa ditampilkan langsung. Silakan download untuk membukanya.</p>
      <a href="<?= e($file_url) ?>" download
         class="inline-block bg-brand text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition shadow-md">
        Download File
      </a>
    </div>

  <?php endif; ?>

</div>

</body>
</html>
