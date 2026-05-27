<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$tryout_id = (int)($_GET['tryout_id'] ?? 0);
if (!$tryout_id) { header('Location: tryout-admin.php'); exit; }

$tryout = db_row('SELECT * FROM tryouts WHERE id = ?', [$tryout_id]);
if (!$tryout) { header('Location: tryout-admin.php'); exit; }

// Add questions to tryout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_questions'])) {
    $qids = $_POST['question_ids'] ?? [];
    foreach ($qids as $qid) {
        $qid = (int)$qid;
        // Ignore duplicates via INSERT IGNORE pattern
        try {
            db_run('INSERT INTO tryout_questions (tryout_id, question_id) VALUES (?, ?)', [$tryout_id, $qid]);
        } catch (\PDOException $e) { /* duplicate key, skip */ }
    }
    // Update total_questions count
    $cnt = db_row('SELECT COUNT(*) AS c FROM tryout_questions WHERE tryout_id = ?', [$tryout_id])['c'];
    db_run('UPDATE tryouts SET total_questions = ? WHERE id = ?', [$cnt, $tryout_id]);
    log_admin_action('ADD_SOAL_TRYOUT', 'tryouts', $tryout_id, count($qids) . ' soal ditambahkan');
    set_flash('success', count($qids) . ' soal berhasil ditambahkan ke tryout.');
    header('Location: kelola-soal-tryout.php?tryout_id=' . $tryout_id); exit;
}

// Remove a question from tryout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_question_id'])) {
    $qid = (int)$_POST['remove_question_id'];
    db_run('DELETE FROM tryout_questions WHERE tryout_id = ? AND question_id = ?', [$tryout_id, $qid]);
    $cnt = db_row('SELECT COUNT(*) AS c FROM tryout_questions WHERE tryout_id = ?', [$tryout_id])['c'];
    db_run('UPDATE tryouts SET total_questions = ? WHERE id = ?', [$cnt, $tryout_id]);
    set_flash('success', 'Soal berhasil dihapus dari tryout.');
    header('Location: kelola-soal-tryout.php?tryout_id=' . $tryout_id); exit;
}

// Soal already in this tryout
$assigned_ids = array_column(
    db_all('SELECT question_id FROM tryout_questions WHERE tryout_id = ?', [$tryout_id]),
    'question_id'
);

// All questions NOT yet assigned to this tryout
$filter_cat = $_GET['cat'] ?? '';
$sql_avail  = 'SELECT * FROM questions';
$pav        = [];
if (!empty($assigned_ids)) {
    $placeholders = implode(',', array_fill(0, count($assigned_ids), '?'));
    $sql_avail   .= ' WHERE id NOT IN (' . $placeholders . ')';
    $pav          = $assigned_ids;
    if ($filter_cat) {
        $sql_avail .= ' AND category = ?';
        $pav[]      = $filter_cat;
    }
} elseif ($filter_cat) {
    $sql_avail .= ' WHERE category = ?';
    $pav[]      = $filter_cat;
}
$sql_avail   .= ' ORDER BY category, difficulty';
$available    = db_all($sql_avail, $pav);

// Assigned questions with detail
$assigned = empty($assigned_ids) ? [] : db_all(
    'SELECT q.* FROM questions q
     JOIN tryout_questions tq ON tq.question_id = q.id
     WHERE tq.tryout_id = ?
     ORDER BY tq.sort_order, q.id',
    [$tryout_id]
);

$active_page = 'tryout';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Soal Tryout — EngLight</title>
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
      <div>
        <h1 class="font-bold text-gray-900">Kelola Soal: <?= e($tryout['title']) ?></h1>
        <p class="text-xs text-gray-500"><?= count($assigned_ids) ?> soal terpilih &bull; <?= $tryout['duration_minutes'] ?> menit &bull; Passing <?= $tryout['passing_score'] ?>%</p>
      </div>
      <a href="tryout-admin.php" class="text-sm text-gray-500 hover:text-brand">← Kembali</a>
    </header>
    <main class="p-8 space-y-6">
      <?php render_flash(); ?>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Available Questions -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-gray-900">Soal Tersedia</h2>
            <div class="flex gap-2">
              <?php foreach (['','listening','structure','reading'] as $cat): ?>
                <a href="?tryout_id=<?= $tryout_id ?>&cat=<?= $cat ?>"
                   class="text-xs px-2 py-1 rounded-lg font-semibold <?= $filter_cat === $cat ? 'bg-brand text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
                   <?= $cat ? ucfirst($cat) : 'Semua' ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
          <?php if (empty($available)): ?>
            <p class="px-6 py-10 text-sm text-gray-400 text-center">Semua soal sudah ditambahkan ke tryout ini.</p>
          <?php else: ?>
          <form method="POST">
            <input type="hidden" name="add_questions" value="1">
            <div class="divide-y divide-gray-100 max-h-[460px] overflow-y-auto">
              <?php foreach ($available as $q): ?>
              <label class="flex items-start gap-3 px-6 py-3 cursor-pointer hover:bg-blue-50 transition">
                <input type="checkbox" name="question_ids[]" value="<?= (int)$q['id'] ?>" class="mt-0.5 w-4 h-4 text-brand rounded">
                <div class="flex-1 min-w-0">
                  <p class="text-sm text-gray-800 leading-relaxed"><?= e(mb_substr($q['question_text'], 0, 90)) ?><?= mb_strlen($q['question_text']) > 90 ? '…' : '' ?></p>
                  <div class="flex gap-2 mt-1">
                    <span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded capitalize"><?= e($q['category']) ?></span>
                    <span class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded capitalize"><?= e($q['difficulty']) ?></span>
                  </div>
                </div>
              </label>
              <?php endforeach; ?>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
              <button type="submit" class="bg-brand text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
                Tambahkan Soal Terpilih →
              </button>
            </div>
          </form>
          <?php endif; ?>
        </div>

        <!-- Assigned Questions -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-900">Soal dalam Tryout <span class="text-gray-400 font-normal text-sm">(<?= count($assigned) ?>)</span></h2>
          </div>
          <?php if (empty($assigned)): ?>
            <p class="px-6 py-10 text-sm text-gray-400 text-center">Belum ada soal ditambahkan.</p>
          <?php else: ?>
          <div class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
            <?php foreach ($assigned as $i => $q): ?>
            <div class="flex items-start gap-3 px-6 py-3">
              <span class="text-xs font-bold text-brand mt-0.5 w-5 flex-shrink-0"><?= $i + 1 ?>.</span>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-800"><?= e(mb_substr($q['question_text'], 0, 80)) ?><?= mb_strlen($q['question_text']) > 80 ? '…' : '' ?></p>
                <div class="flex gap-2 mt-1">
                  <span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded capitalize"><?= e($q['category']) ?></span>
                  <span class="text-xs text-green-600 font-semibold">✓ <?= strtoupper($q['correct_answer']) ?></span>
                </div>
              </div>
              <form method="POST" class="flex-shrink-0">
                <input type="hidden" name="remove_question_id" value="<?= (int)$q['id'] ?>">
                <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition font-semibold">✕</button>
              </form>
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
