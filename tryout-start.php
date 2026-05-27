<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$user       = current_user();
$tryout_id  = (int)($_GET['id'] ?? 0);
if (!$tryout_id) { header('Location: tryout-user.php'); exit; }

$tryout = db_row('SELECT * FROM tryouts WHERE id = ?', [$tryout_id]);
if (!$tryout) { header('Location: tryout-user.php'); exit; }

// Access control: premium tryout
if ($tryout['is_premium'] && $user['plan'] === 'free') {
    set_flash('error', 'Tryout ini khusus untuk pengguna Premium atau Pro.');
    header('Location: tryout-user.php'); exit;
}

// Load questions for this tryout
$questions = db_all('SELECT q.* FROM questions q
                     JOIN tryout_questions tq ON tq.question_id = q.id
                     WHERE tq.tryout_id = ?
                     ORDER BY tq.sort_order, q.id',
                    [$tryout_id]);

if (empty($questions)) {
    set_flash('error', 'Tryout ini belum memiliki soal. Coba lagi nanti.');
    header('Location: tryout-user.php'); exit;
}

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_tryout'])) {
    $answers  = $_POST['answers'] ?? [];
    $correct  = 0;

    foreach ($questions as $q) {
        $given = strtolower($answers[$q['id']] ?? '');
        if ($given === strtolower($q['correct_answer'])) {
            $correct++;
        }
    }

    $total   = count($questions);
    $score   = $total > 0 ? round($correct / $total * 100) : 0;
    $passed  = $score >= (int)$tryout['passing_score'] ? 1 : 0;

    db_run('INSERT INTO tryout_sessions (user_id, tryout_id, score, is_passed, finished_at) VALUES (?, ?, ?, ?, NOW())',
           [$user['id'], $tryout_id, $score, $passed]);

    // XP reward
    $xp = $passed ? 50 : ($score >= 50 ? 20 : 10);
    db_run('UPDATE users SET xp = xp + ? WHERE id = ?', [$xp, $user['id']]);
    $_SESSION['user_xp'] = ($_SESSION['user_xp'] ?? 0) + $xp;

    $msg = "Tryout selesai! Skor: {$score}% ({$correct}/{$total} benar). " . ($passed ? '🎉 LULUS!' : 'Belum lulus. Terus semangat!') . " +{$xp} XP";
    set_flash($passed ? 'success' : 'error', $msg);
    header('Location: tryout-user.php'); exit;
}

$duration_seconds = (int)$tryout['duration_minutes'] * 60;
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($tryout['title']) ?> — EngLight</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{brand:{DEFAULT:'#1B3F8B'}}}}}</script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;900&display=swap" rel="stylesheet">
  <style>body{font-family:'Poppins',sans-serif}</style>
  <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-50 min-h-screen">

<!-- Sticky Header with Timer -->
<div class="sticky top-0 z-40 bg-white border-b shadow-sm px-6 py-3 flex items-center justify-between">
  <div>
    <p class="font-bold text-gray-900 text-sm"><?= e($tryout['title']) ?></p>
    <p class="text-xs text-gray-500"><?= count($questions) ?> soal &bull; Passing <?= $tryout['passing_score'] ?>%</p>
  </div>
  <div class="flex items-center gap-4">
    <div class="text-center">
      <p class="text-xs text-gray-500 uppercase font-semibold">Sisa Waktu</p>
      <p id="countdown" class="text-2xl font-black text-brand tabular-nums">—</p>
    </div>
    <button form="tryout-form" type="submit" name="submit_tryout" value="1"
            class="bg-brand text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-blue-700 transition"
            onclick="return confirm('Kumpulkan jawaban sekarang?')">
      Kumpulkan
    </button>
  </div>
</div>

<main class="max-w-3xl mx-auto px-4 py-8">
  <form id="tryout-form" method="POST" class="space-y-6">
    <input type="hidden" name="submit_tryout" value="1">

    <?php foreach ($questions as $i => $q): ?>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6" id="q<?= $i + 1 ?>">
      <p class="font-semibold text-gray-900 mb-4 leading-relaxed">
        <span class="text-brand font-bold mr-2"><?= $i + 1 ?>.</span>
        <?= e($q['question_text']) ?>
      </p>
      <div class="space-y-3">
        <?php foreach (['a','b','c','d'] as $opt): ?>
        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-blue-50 hover:border-brand transition-all group">
          <input type="radio" name="answers[<?= (int)$q['id'] ?>]" value="<?= e($opt) ?>"
                 class="mt-0.5 w-4 h-4 text-brand flex-shrink-0">
          <span class="text-sm text-gray-700">
            <span class="font-bold text-brand uppercase mr-1"><?= $opt ?>.</span>
            <?= e($q['option_' . $opt]) ?>
          </span>
        </label>
        <?php endforeach; ?>
      </div>
      <div class="mt-3 flex gap-2">
        <span class="text-xs bg-blue-50 text-blue-600 font-semibold px-2 py-0.5 rounded capitalize"><?= e($q['category']) ?></span>
        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded capitalize"><?= e($q['difficulty']) ?></span>
      </div>
    </div>
    <?php endforeach; ?>

    <div class="flex justify-center pb-8">
      <button type="submit" name="submit_tryout" value="1"
              class="bg-brand text-white px-12 py-4 rounded-xl font-bold text-base hover:bg-blue-700 transition shadow-lg"
              onclick="return confirm('Kumpulkan jawaban sekarang? Pastikan semua soal sudah dijawab.')">
        Kumpulkan Jawaban
      </button>
    </div>
  </form>
</main>

<script>
  // Countdown timer
  let remaining = <?= $duration_seconds ?>;
  const el = document.getElementById('countdown');
  function updateTimer() {
    if (remaining <= 0) {
      el.textContent = '00:00';
      el.classList.add('text-red-600');
      document.getElementById('tryout-form').submit();
      return;
    }
    const m = String(Math.floor(remaining / 60)).padStart(2, '0');
    const s = String(remaining % 60).padStart(2, '0');
    el.textContent = m + ':' + s;
    if (remaining <= 300) { el.classList.add('text-red-500'); }
    remaining--;
    setTimeout(updateTimer, 1000);
  }
  updateTimer();

  // Question navigation: warn on back button
  window.addEventListener('beforeunload', (e) => {
    e.preventDefault();
    e.returnValue = 'Tryout sedang berjalan. Keluar sekarang?';
  });
  document.getElementById('tryout-form').addEventListener('submit', () => {
    window.removeEventListener('beforeunload', () => {});
  });
</script>
</body>
</html>
