<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$item    = $edit_id ? db_row('SELECT * FROM questions WHERE id = ?', [$edit_id]) : null;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text  = trim($_POST['question_text']  ?? '');
    $option_a       = trim($_POST['option_a']        ?? '');
    $option_b       = trim($_POST['option_b']        ?? '');
    $option_c       = trim($_POST['option_c']        ?? '');
    $option_d       = trim($_POST['option_d']        ?? '');
    $correct_answer = strtolower(trim($_POST['correct_answer'] ?? ''));
    $explanation    = trim($_POST['explanation']     ?? '');
    $category       = trim($_POST['category']        ?? '');
    $difficulty     = trim($_POST['difficulty']      ?? '');

    if (strlen($question_text) < 10)                    $errors[] = 'Pertanyaan minimal 10 karakter.';
    if (empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d)) $errors[] = 'Semua pilihan jawaban wajib diisi.';
    if (!in_array($correct_answer, ['a','b','c','d']))  $errors[] = 'Pilih jawaban benar yang valid.';
    if (!in_array($category, ['listening','structure','reading'])) $errors[] = 'Pilih kategori yang valid.';
    if (!in_array($difficulty, ['easy','medium','hard']))          $errors[] = 'Pilih tingkat kesulitan.';

    if (empty($errors)) {
        if ($edit_id && $item) {
            db_run('UPDATE questions SET question_text=?,option_a=?,option_b=?,option_c=?,option_d=?,correct_answer=?,explanation=?,category=?,difficulty=? WHERE id=?',
                   [$question_text,$option_a,$option_b,$option_c,$option_d,$correct_answer,$explanation,$category,$difficulty,$edit_id]);
            log_admin_action('EDIT_SOAL', 'questions', $edit_id);
            set_flash('success', 'Soal berhasil diperbarui.');
        } else {
            db_run('INSERT INTO questions (question_text,option_a,option_b,option_c,option_d,correct_answer,explanation,category,difficulty,created_by) VALUES (?,?,?,?,?,?,?,?,?,?)',
                   [$question_text,$option_a,$option_b,$option_c,$option_d,$correct_answer,$explanation,$category,$difficulty,$_SESSION['user_id']]);
            $new_id = (int)db()->lastInsertId();
            log_admin_action('ADD_SOAL', 'questions', $new_id);
            set_flash('success', 'Soal berhasil ditambahkan.');
        }
        header('Location: banksoal-admin.php'); exit;
    }
}

$active_page = 'banksoal';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $edit_id ? 'Edit' : 'Tambah' ?> Soal — EngLight</title>
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
      <h1 class="font-bold text-gray-900"><?= $edit_id ? 'Edit' : 'Tambah' ?> Soal</h1>
      <a href="banksoal-admin.php" class="text-sm text-gray-500 hover:text-brand">← Kembali</a>
    </header>
    <main class="p-8">
      <?php if (!empty($errors)): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl px-5 py-4 text-sm text-red-700">
          <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 max-w-2xl">
        <form method="POST" class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Teks Pertanyaan <span class="text-red-500">*</span></label>
            <textarea name="question_text" rows="4"
                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand resize-none" required><?= e($_POST['question_text'] ?? $item['question_text'] ?? '') ?></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <?php foreach (['a','b','c','d'] as $opt): ?>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pilihan <?= strtoupper($opt) ?> <span class="text-red-500">*</span></label>
              <input type="text" name="option_<?= $opt ?>" value="<?= e($_POST['option_'.$opt] ?? $item['option_'.$opt] ?? '') ?>"
                     class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand" required>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jawaban Benar <span class="text-red-500">*</span></label>
              <select name="correct_answer" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand" required>
                <option value="">Pilih</option>
                <?php foreach (['a','b','c','d'] as $opt): ?>
                  <option value="<?= $opt ?>" <?= (($_POST['correct_answer'] ?? $item['correct_answer'] ?? '') === $opt) ? 'selected' : '' ?>><?= strtoupper($opt) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
              <select name="category" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand" required>
                <option value="">Pilih</option>
                <?php foreach (['listening','structure','reading'] as $cat): ?>
                  <option value="<?= $cat ?>" <?= (($_POST['category'] ?? $item['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kesulitan <span class="text-red-500">*</span></label>
              <select name="difficulty" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand" required>
                <option value="">Pilih</option>
                <?php foreach (['easy','medium','hard'] as $d): ?>
                  <option value="<?= $d ?>" <?= (($_POST['difficulty'] ?? $item['difficulty'] ?? '') === $d) ? 'selected' : '' ?>><?= ucfirst($d) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Penjelasan (opsional)</label>
            <textarea name="explanation" rows="3"
                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand resize-none"><?= e($_POST['explanation'] ?? $item['explanation'] ?? '') ?></textarea>
          </div>

          <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-brand text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition">
              <?= $edit_id ? 'Simpan Perubahan' : 'Tambah Soal' ?>
            </button>
            <a href="banksoal-admin.php" class="px-8 py-3 rounded-xl font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Batal</a>
          </div>
        </form>
      </div>
    </main>
  </div>
</div>
</body>
</html>
