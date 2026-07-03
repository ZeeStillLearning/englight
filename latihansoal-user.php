<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$user       = current_user();
$valid_cats = ['listening','structure','reading'];
$filter     = $_GET['category'] ?? '';

// ── Handle quiz submission ───────────────────────────────────
// Sekarang validasi berdasarkan TEKS jawaban, bukan huruf a/b/c/d
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $answer_texts = $_POST['answer_text'] ?? []; // [question_id => teks jawaban yang dipilih]
    $question_ids = array_keys($answer_texts);
    $correct      = 0;

    foreach ($question_ids as $qid) {
        $q = db_row('SELECT correct_answer, option_a, option_b, option_c, option_d FROM questions WHERE id = ?', [(int)$qid]);
        if (!$q) continue;

        $correct_text = $q['option_' . $q['correct_answer']];
        $given_text   = $answer_texts[$qid];

        // Bandingkan TEKS jawaban, bukan huruf — karena posisi sudah diacak di tampilan
        if (trim($given_text) === trim($correct_text)) {
            $correct++;
        }
    }

    $total = count($question_ids);
    $score = $total > 0 ? round($correct / $total * 100) : 0;
    $cat   = $_POST['quiz_category'] ?? '';

    db_run('INSERT INTO latihan_sessions (user_id, category, total_soal, correct_count, score, finished_at)
            VALUES (?, ?, ?, ?, ?, NOW())',
           [$user['id'], $cat, $total, $correct, $score]);

    $xp = $correct * 5;
    if ($xp > 0) {
        db_run('UPDATE users SET xp = xp + ? WHERE id = ?', [$xp, $user['id']]);
        $_SESSION['user_xp'] = ($_SESSION['user_xp'] ?? 0) + $xp;
    }

    set_flash('success', "Latihan selesai! Skor kamu: {$score}% ({$correct}/{$total} benar). +{$xp} XP!");
    header('Location: ' . APP_URL . '/latihansoal-user.php' . ($filter ? '?category=' . urlencode($filter) : ''));
    exit;
}

// Load questions
$sql_q  = 'SELECT * FROM questions';
$pq     = [];
if ($filter && in_array($filter, $valid_cats)) {
    $sql_q .= ' WHERE category = ?';
    $pq[]   = $filter;
}
$sql_q  .= ' ORDER BY RAND() LIMIT 10';
$questions = db_all($sql_q, $pq);

// ── Acak urutan pilihan jawaban untuk tiap soal (server-side) ──
// Supaya konsisten meski form di-render PHP, kita acak di sini lalu
// simpan urutan teks ke dalam array untuk dirender.
foreach ($questions as &$q) {
    $opts = [
        ['text' => $q['option_a'], 'is_correct' => $q['correct_answer'] === 'a'],
        ['text' => $q['option_b'], 'is_correct' => $q['correct_answer'] === 'b'],
        ['text' => $q['option_c'], 'is_correct' => $q['correct_answer'] === 'c'],
        ['text' => $q['option_d'], 'is_correct' => $q['correct_answer'] === 'd'],
    ];
    shuffle($opts);
    $q['shuffled_options'] = $opts;
}
unset($q);

// Recent sessions
$history = db_all('SELECT * FROM latihan_sessions WHERE user_id = ? ORDER BY started_at DESC LIMIT 5', [$user['id']]);

$active_page = 'latihan';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Latihan Soal — EngLight</title>
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
      <h1 class="font-bold text-gray-900">Latihan Soal</h1>
      <span class="text-sm text-gray-500">⚡ <?= number_format($user['xp']) ?> XP</span>
    </header>
    <main class="p-6 lg:p-8 space-y-6">
      <?php render_flash(); ?>

      <!-- Category Filter -->
      <div class="flex gap-2 flex-wrap">
        <a href="<?= APP_URL ?>/latihansoal-user.php" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all <?= !$filter ? 'bg-brand text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' ?>">Semua</a>
        <?php foreach ($valid_cats as $cat): ?>
          <a href="<?= APP_URL ?>/latihansoal-user.php?category=<?= urlencode($cat) ?>" class="px-4 py-2 rounded-xl text-sm font-semibold capitalize transition-all <?= $filter === $cat ? 'bg-brand text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' ?>"><?= e(ucfirst($cat)) ?></a>
        <?php endforeach; ?>
      </div>

      <?php if (empty($questions)): ?>
        <div class="bg-white rounded-2xl p-12 text-center text-gray-400 border border-gray-100 shadow-sm">
          <p class="text-lg font-semibold mb-2">Belum ada soal tersedia.</p>
          <p class="text-sm">Tunggu admin menambahkan soal.</p>
        </div>
      <?php else: ?>
      <form method="POST" class="space-y-5">
        <input type="hidden" name="submit_quiz" value="1">
        <input type="hidden" name="quiz_category" value="<?= e($filter) ?>">

        <?php foreach ($questions as $i => $q): ?>
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
          <p class="font-semibold text-gray-900 mb-4">
            <span class="text-brand font-bold mr-2"><?= $i + 1 ?>.</span>
            <?= e($q['question_text']) ?>
          </p>
          <div class="space-y-3">
            <?php foreach ($q['shuffled_options'] as $idx => $opt):
              $opt_label = chr(97 + $idx); // a, b, c, d — hanya untuk label tampilan
            ?>
              <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-blue-50 hover:border-brand transition-all">
                <input type="radio"
                       name="answer_text[<?= (int)$q['id'] ?>]"
                       value="<?= e($opt['text']) ?>"
                       class="w-4 h-4 text-brand" required>
                <span class="text-sm text-gray-700">
                  <strong class="text-brand uppercase"><?= strtoupper($opt_label) ?>.</strong>
                  <?= e($opt['text']) ?>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
          <div class="mt-2 flex justify-between text-xs text-gray-400">
            <span class="capitalize"><?= e($q['category']) ?></span>
            <span class="capitalize"><?= e($q['difficulty']) ?></span>
          </div>
        </div>
        <?php endforeach; ?>

        <div class="flex justify-end">
          <button type="submit" class="bg-brand text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition shadow-md">
            Kumpulkan Jawaban
          </button>
        </div>
      </form>
      <?php endif; ?>

      <!-- History -->
      <?php if (!empty($history)): ?>
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="font-bold text-gray-900">Riwayat Latihan</h2>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
            <tr>
              <th class="px-6 py-3 text-left">Tanggal</th>
              <th class="px-6 py-3 text-left">Kategori</th>
              <th class="px-6 py-3 text-left">Soal</th>
              <th class="px-6 py-3 text-left">Skor</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($history as $h): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-3 text-gray-500"><?= date('d M Y H:i', strtotime($h['started_at'])) ?></td>
              <td class="px-6 py-3 capitalize text-gray-700"><?= $h['category'] ?: 'Semua' ?></td>
              <td class="px-6 py-3 text-gray-700"><?= $h['correct_count'] ?>/<?= $h['total_soal'] ?></td>
              <td class="px-6 py-3 font-bold <?= $h['score'] >= 70 ? 'text-green-600' : 'text-red-500' ?>"><?= $h['score'] ?>%</td>
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
