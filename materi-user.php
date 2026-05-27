<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$user        = current_user();
$filter_cat  = $_GET['category'] ?? '';
$valid_cats  = ['listening','structure','reading','grammar','vocabulary','speaking'];

$sql    = 'SELECT m.*, u.name AS author,
                  (SELECT COUNT(*) FROM user_materi_progress ump WHERE ump.materi_id = m.id AND ump.user_id = ? AND ump.is_completed = 1) AS done
           FROM materi m JOIN users u ON u.id = m.created_by';
$params = [$user['id']];
if ($filter_cat && in_array($filter_cat, $valid_cats)) {
    $sql   .= ' WHERE m.category = ?';
    $params[] = $filter_cat;
}
$sql .= ' ORDER BY m.sort_order, m.created_at DESC';
$items = db_all($sql, $params);

$active_page = 'materi';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Materi — EngLight</title>
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
      <h1 class="font-bold text-gray-900">Materi Pembelajaran</h1>
      <span class="text-sm text-gray-500"><?= e($user['name']) ?></span>
    </header>
    <main class="p-6 lg:p-8 space-y-6">
      <?php render_flash(); ?>

      <!-- Category Filter -->
      <div class="flex gap-2 flex-wrap">
        <a href="<?= APP_URL ?>/materi-user.php" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all <?= !$filter_cat ? 'bg-brand text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' ?>">Semua</a>
        <?php foreach ($valid_cats as $cat): ?>
          <a href="<?= APP_URL ?>/materi-user.php?category=<?= urlencode($cat) ?>" class="px-4 py-2 rounded-xl text-sm font-semibold capitalize transition-all <?= $filter_cat === $cat ? 'bg-brand text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' ?>"><?= e(ucfirst($cat)) ?></a>
        <?php endforeach; ?>
      </div>

      <!-- Materi Grid -->
      <?php if (empty($items)): ?>
        <div class="text-center py-20 text-gray-400">
          <p class="text-lg font-semibold">Belum ada materi tersedia.</p>
        </div>
      <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($items as $m): ?>
          <?php
            $is_locked  = $m['is_premium'] && $user['plan'] === 'free';
            $is_done    = (bool)$m['done'];
            $cat_colors = ['listening'=>'blue','structure'=>'green','reading'=>'purple','grammar'=>'orange','vocabulary'=>'teal','speaking'=>'red'];
            $cc         = $cat_colors[$m['category']] ?? 'gray';
            $has_file   = !empty($m['file_path']);
            $ext        = strtolower(pathinfo($m['file_path'] ?? '', PATHINFO_EXTENSION));
            $btn_label  = match($m['type']) {
                'video' => '▶ Tonton Video',
                'pdf'   => '📄 Baca PDF',
                default => '📖 Buka Materi',
            };
          ?>
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-shadow">
            <div class="h-3 bg-<?= $cc ?>-500"></div>
            <div class="p-5 flex-1 flex flex-col">
              <!-- Header badges -->
              <div class="flex items-start justify-between mb-3">
                <span class="text-xs font-bold uppercase bg-<?= $cc ?>-100 text-<?= $cc ?>-700 px-2 py-1 rounded-lg"><?= e($m['category']) ?></span>
                <?php if ($is_done): ?>
                  <span class="text-xs font-bold text-green-600 bg-green-100 px-2 py-1 rounded-lg">✓ Selesai</span>
                <?php elseif ($is_locked): ?>
                  <span class="text-xs font-bold text-yellow-600 bg-yellow-100 px-2 py-1 rounded-lg">🔒 Premium</span>
                <?php endif; ?>
              </div>

              <!-- Title & description -->
              <h3 class="font-bold text-gray-900 mb-2"><?= e($m['title']) ?></h3>
              <p class="text-xs text-gray-500 mb-4 leading-relaxed flex-1">
                <?= e(mb_substr($m['description'] ?? '', 0, 100)) ?><?= strlen($m['description'] ?? '') > 100 ? '…' : '' ?>
              </p>

              <!-- Action buttons -->
              <div class="flex items-center justify-between gap-2">
                <span class="text-xs text-gray-400 uppercase font-medium"><?= e($m['type']) ?></span>

                <?php if ($is_locked): ?>
                  <a href="<?= APP_URL ?>/membership-user.php"
                     class="text-xs bg-yellow-500 text-white px-3 py-1.5 rounded-lg font-semibold hover:bg-yellow-600 transition">
                    🔒 Upgrade
                  </a>

                <?php elseif ($has_file): ?>
                  <!-- Open in viewer -->
                  <a href="<?= APP_URL ?>/baca-materi.php?id=<?= (int)$m['id'] ?>"
                     class="text-xs bg-brand text-white px-3 py-1.5 rounded-lg font-semibold hover:bg-blue-700 transition">
                    <?= $btn_label ?>
                  </a>

                <?php else: ?>
                  <!-- No file yet -->
                  <span class="text-xs text-gray-400 italic">File belum tersedia</span>

                <?php endif; ?>
              </div>
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
