<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $q  = db_row('SELECT id FROM questions WHERE id = ?', [$id]);
    if ($q) {
        db_run('DELETE FROM questions WHERE id = ?', [$id]);
        log_admin_action('DELETE_SOAL', 'questions', $id);
        set_flash('success', 'Soal berhasil dihapus.');
    }
    header('Location: banksoal-admin.php'); exit;
}

$filter_cat = $_GET['category'] ?? '';
$sql = 'SELECT q.*, u.name AS author FROM questions q JOIN users u ON u.id = q.created_by';
$p   = [];
if ($filter_cat) { $sql .= ' WHERE q.category = ?'; $p[] = $filter_cat; }
$sql .= ' ORDER BY q.created_at DESC';
$questions = db_all($sql, $p);

$active_page = 'banksoal';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Bank Soal</title>
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
      <h1 class="font-bold text-gray-900">Bank Soal</h1>
      <a href="tambah-soal.php" class="bg-brand text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">+ Tambah Soal</a>
    </header>
    <main class="p-8 space-y-6">
      <?php render_flash(); ?>

      <!-- Filter -->
      <div class="flex gap-2 flex-wrap">
        <a href="banksoal-admin.php" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all <?= !$filter_cat ? 'bg-brand text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' ?>">Semua</a>
        <?php foreach (['listening','structure','reading'] as $cat): ?>
          <a href="banksoal-admin.php?category=<?= $cat ?>" class="px-4 py-2 rounded-xl text-sm font-semibold capitalize transition-all <?= $filter_cat === $cat ? 'bg-brand text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' ?>"><?= ucfirst($cat) ?></a>
        <?php endforeach; ?>
      </div>

      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-3 border-b border-gray-100 text-xs text-gray-500">
          <?= count($questions) ?> soal ditemukan
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
            <tr>
              <th class="px-6 py-3 text-left">Pertanyaan</th>
              <th class="px-6 py-3 text-left">Kategori</th>
              <th class="px-6 py-3 text-left">Kesulitan</th>
              <th class="px-6 py-3 text-left">Jawaban</th>
              <th class="px-6 py-3 text-left">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php if (empty($questions)): ?>
              <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">Belum ada soal. <a href="tambah-soal.php" class="text-brand hover:underline">Tambah sekarang</a></td></tr>
            <?php else: ?>
              <?php foreach ($questions as $q): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 max-w-xs">
                  <p class="font-medium text-gray-800 truncate"><?= e(mb_substr($q['question_text'], 0, 80)) ?><?= mb_strlen($q['question_text']) > 80 ? '…' : '' ?></p>
                </td>
                <td class="px-6 py-4 capitalize text-gray-600"><?= e($q['category']) ?></td>
                <td class="px-6 py-4">
                  <?php $dc = ['easy'=>'green','medium'=>'yellow','hard'=>'red'][$q['difficulty']] ?? 'gray'; ?>
                  <span class="text-xs font-semibold bg-<?= $dc ?>-100 text-<?= $dc ?>-700 px-2 py-0.5 rounded capitalize"><?= e($q['difficulty']) ?></span>
                </td>
                <td class="px-6 py-4 uppercase font-bold text-brand"><?= e($q['correct_answer']) ?></td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <a href="tambah-soal.php?edit=<?= (int)$q['id'] ?>" class="text-xs text-brand font-semibold hover:underline">Edit</a>
                    <form method="POST" onsubmit="return confirm('Hapus soal ini?')">
                      <input type="hidden" name="delete_id" value="<?= (int)$q['id'] ?>">
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
