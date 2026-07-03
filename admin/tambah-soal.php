<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); 



$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

$item = $edit_id
    ? db_row('SELECT * FROM questions WHERE id = ?', [$edit_id])
    : null;

if ($edit_id && !$item) {
    set_flash('error', 'Soal tidak ditemukan.');
    header('Location: banksoal-admin.php');
    exit;
}



$errors = []; // Tampung semua error validasi

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $question_text  = trim($_POST['question_text']  ?? '');
    $option_a       = trim($_POST['option_a']       ?? '');
    $option_b       = trim($_POST['option_b']       ?? '');
    $option_c       = trim($_POST['option_c']       ?? '');
    $option_d       = trim($_POST['option_d']       ?? '');
    $option_e       = trim($_POST['option_e']       ?? ''); // Pilihan E (opsional)
    $correct_answer = strtolower(trim($_POST['correct_answer'] ?? ''));
    $explanation    = trim($_POST['explanation']    ?? '');
    $category       = trim($_POST['category']       ?? '');
    $difficulty     = trim($_POST['difficulty']     ?? '');


    if (strlen($question_text) < 10) {
        $errors[] = 'Teks pertanyaan minimal 10 karakter.';
    }

    // Pilihan A–D wajib diisi (E opsional)
    if (empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d)) {
        $errors[] = 'Pilihan jawaban A, B, C, dan D wajib diisi.';
    }

    // Tentukan opsi jawaban yang valid berdasarkan apakah E diisi
    if ($correct_answer === 'e' && empty($option_e)) {
    $errors[] = 'Pilihan E belum diisi, tidak bisa dijadikan jawaban benar.';
}

    $valid_answers = !empty($option_e) ? ['a','b','c','d','e'] : ['a','b','c','d'];
    if (!in_array($correct_answer, $valid_answers)) {
    $errors[] = 'Pilih jawaban benar yang valid (A–' . strtoupper(end($valid_answers)) . ').';
}

    // Kategori harus salah satu dari enum yang tersedia
    if (!in_array($category, ['listening', 'structure', 'reading'])) {
        $errors[] = 'Pilih kategori soal yang valid.';
    }

    // Tingkat kesulitan wajib dipilih
    if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
        $errors[] = 'Pilih tingkat kesulitan soal.';
    }

    // === 3. Simpan ke database jika tidak ada error ===
    if (empty($errors)) {

        if ($edit_id && $item) {
            db_run(
                'UPDATE questions SET
                    question_text  = ?,
                    option_a       = ?,
                    option_b       = ?,
                    option_c       = ?,
                    option_d       = ?,
                    option_e       = ?,
                    correct_answer = ?,
                    explanation    = ?,
                    category       = ?,
                    difficulty     = ?
                 WHERE id = ?',
                [
                    $question_text, $option_a, $option_b, $option_c,
                    $option_d, $option_e ?: null, $correct_answer,
                    $explanation ?: null, $category, $difficulty,
                    $edit_id
                ]
            );

            // Catat aktivitas admin ke log
            log_admin_action('EDIT_SOAL', 'questions', $edit_id, 'Edit soal: ' . mb_substr($question_text, 0, 80));

            set_flash('success', 'Soal berhasil diperbarui di Bank Soal.');

        } else {
            db_run(
                'INSERT INTO questions
                    (question_text, option_a, option_b, option_c, option_d, option_e,
                     correct_answer, explanation, category, difficulty, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $question_text, $option_a, $option_b, $option_c,
                    $option_d, $option_e ?: null, $correct_answer,
                    $explanation ?: null, $category, $difficulty,
                    $_SESSION['user_id']
                ]
            );

            $new_id = (int) db()->lastInsertId();

            // Catat aktivitas admin ke log
            log_admin_action('ADD_SOAL', 'questions', $new_id, 'Tambah soal: ' . mb_substr($question_text, 0, 80));

            set_flash('success', 'Soal berhasil ditambahkan ke Bank Soal.');
        }

        // Redirect ke halaman daftar Bank Soal setelah berhasil
        header('Location: banksoal-admin.php');
        exit;
    }

}



$v = fn(string $key) => e($_POST[$key] ?? $item[$key] ?? '');

// Label kategori dalam Bahasa Indonesia untuk tampilan
$kategori_labels = [
    'listening' => 'Listening Comprehension',
    'structure' => 'Structure & Written Expression',
    'reading'   => 'Reading Comprehension',
];

// Label tingkat kesulitan
$difficulty_labels = [
    'easy'   => 'Mudah',
    'medium' => 'Sedang',
    'hard'   => 'Sulit',
];

$difficulty_colors = [
    'easy'   => 'bg-green-100 text-green-700',
    'medium' => 'bg-yellow-100 text-yellow-700',
    'hard'   => 'bg-red-100 text-red-700',
];

// Judul halaman dinamis
$page_title  = $edit_id ? 'Edit Soal' : 'Tambah Soal ke Bank Soal';
$active_page = 'banksoal'; // Digunakan sidebar untuk highlight menu aktif

?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($page_title) ?> — EngLight Admin</title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              DEFAULT: '#1B3F8B',
              light:   '#2952B3',
              dark:    '#122D6B',
            },
            gold: { DEFAULT: '#F5A623' },
          },
          fontFamily: {
            poppins: ['Poppins', 'sans-serif'],
          },
        }
      }
    }
  </script>

  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    * { font-family: 'Poppins', sans-serif; }

    @keyframes fadeInScale {
      from { opacity: 0; transform: scale(0.95) translateY(10px); }
      to   { opacity: 1; transform: scale(1)    translateY(0); }
    }
    .modal-content { animation: fadeInScale 0.2s ease forwards; }

    .answer-option {
      transition: all 0.15s ease;
    }
    .answer-option:hover {
      transform: translateX(4px);
    }
    .answer-option.correct {
      background: #f0fdf4;
      border-color: #22c55e;
      color: #15803d;
    }
  </style>
</head>

<body class="h-full">
<div class="flex h-full min-h-screen">

  <
  <?php require_once __DIR__ . '/../includes/sidebar_admin.php'; ?>


  <div class="flex-1 overflow-y-auto">

    <!-- Header halaman -->
    <header class="bg-white border-b border-gray-100 px-8 py-4 flex items-center justify-between sticky top-0 z-10">
      <div class="flex items-center gap-3">
        <!-- Ikon buku -->
        <div class="w-9 h-9 rounded-xl bg-brand/10 flex items-center justify-center">
          <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
          </svg>
        </div>
        <div>
          <h1 class="font-bold text-gray-900 leading-tight"><?= e($page_title) ?></h1>
          <p class="text-xs text-gray-400 leading-tight">
            <?= $edit_id ? 'Perbarui soal #' . $edit_id . ' di Bank Soal' : 'Soal baru akan masuk ke master Bank Soal' ?>
          </p>
        </div>
      </div>
      <!-- Tombol kembali -->
      <a href="banksoal-admin.php"
         class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Bank Soal
      </a>
    </header>

    <!-- Area konten utama -->
    <main class="p-8">

      
      <?php if (!empty($errors)): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl px-5 py-4">
          <div class="flex items-start gap-3">
            <!-- Ikon warning -->
            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
              <p class="font-semibold text-red-700 text-sm mb-1">Terdapat <?= count($errors) ?> kesalahan:</p>
              <ul class="space-y-0.5">
                <?php foreach ($errors as $err): ?>
                  <li class="text-sm text-red-600">• <?= e($err) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
      <?php endif; ?>

     
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

      
        <div class="xl:col-span-2">
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

            <!-- Header form card -->
            <div class="px-8 py-5 border-b border-gray-50 bg-gradient-to-r from-brand/5 to-transparent">
              <h2 class="font-semibold text-gray-800">Data Soal</h2>
              <p class="text-xs text-gray-400 mt-0.5">Isi semua field bertanda * dengan lengkap dan benar</p>
            </div>

            <!-- Form utama -->
            <form id="formSoal" method="POST" class="p-8 space-y-6">

              <!-- ---- TEKS PERTANYAAN ---- -->
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                  Teks Pertanyaan
                  <span class="text-red-500">*</span>
                </label>
                <textarea
                  id="inp_question"
                  name="question_text"
                  rows="4"
                  placeholder="Tulis pertanyaan di sini... (minimal 10 karakter)"
                  required
                  class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand resize-none transition-colors"><?= $v('question_text') ?></textarea>
              </div>

              <!-- ---- PILIHAN JAWABAN A-D (wajib) ---- -->
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Pilihan Jawaban A–D
                  <span class="text-red-500">*</span>
                </label>
                <div class="space-y-3">
                  <?php
                  // Loop untuk pilihan A, B, C, D (wajib)
                  $wajib_opts = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'];
                  foreach ($wajib_opts as $key => $label):
                  ?>
                  <div class="flex items-center gap-3">
                    <!-- Badge huruf pilihan -->
                    <span class="w-8 h-8 rounded-lg bg-brand/10 text-brand font-bold text-sm flex items-center justify-center flex-shrink-0">
                      <?= $label ?>
                    </span>
                    <input
                      id="inp_option_<?= $key ?>"
                      type="text"
                      name="option_<?= $key ?>"
                      value="<?= $v('option_' . $key) ?>"
                      placeholder="Isi pilihan jawaban <?= $label ?>..."
                      required
                      class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand transition-colors">
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- ---- PILIHAN E (OPSIONAL) ---- -->
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Pilihan Jawaban E
                  <span class="text-xs font-normal text-gray-400 ml-1">(opsional)</span>
                </label>
                <div class="flex items-center gap-3">
                  <span class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 font-bold text-sm flex items-center justify-center flex-shrink-0">
                    E
                  </span>
                  <input
                    id="inp_option_e"
                    type="text"
                    name="option_e"
                    value="<?= $v('option_e') ?>"
                    placeholder="Isi pilihan E jika ada (biarkan kosong jika tidak ada)..."
                    class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand transition-colors">
                </div>
              </div>

              <!-- ---- GRID: JAWABAN BENAR, KATEGORI, KESULITAN ---- -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <!-- Jawaban Benar -->
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Jawaban Benar <span class="text-red-500">*</span>
                  </label>
                  <select
                    id="inp_correct"
                    name="correct_answer"
                    required
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand transition-colors bg-white">
                    <option value="">-- Pilih --</option>
                    <?php foreach (['a','b','c','d','e'] as $opt): ?>
                      <option value="<?= $opt ?>"
                        <?= ($v('correct_answer') === $opt) ? 'selected' : '' ?>>
                        Pilihan <?= strtoupper($opt) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Kategori Soal -->
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Kategori <span class="text-red-500">*</span>
                  </label>
                  <select
                    id="inp_category"
                    name="category"
                    required
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand transition-colors bg-white">
                    <option value="">-- Pilih --</option>
                    <?php foreach ($kategori_labels as $val => $label): ?>
                      <option value="<?= $val ?>"
                        <?= ($v('category') === $val) ? 'selected' : '' ?>>
                        <?= e($label) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Tingkat Kesulitan -->
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Kesulitan <span class="text-red-500">*</span>
                  </label>
                  <select
                    id="inp_difficulty"
                    name="difficulty"
                    required
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand transition-colors bg-white">
                    <option value="">-- Pilih --</option>
                    <?php foreach ($difficulty_labels as $val => $label): ?>
                      <option value="<?= $val ?>"
                        <?= ($v('difficulty') === $val) ? 'selected' : '' ?>>
                        <?= e($label) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                  Pembahasan / Penjelasan
                  <span class="text-xs font-normal text-gray-400 ml-1">(opsional, tapi sangat direkomendasikan)</span>
                </label>
                <textarea
                  id="inp_explanation"
                  name="explanation"
                  rows="3"
                  placeholder="Jelaskan mengapa jawaban tersebut benar, serta konteks yang relevan..."
                  class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand resize-none transition-colors"><?= $v('explanation') ?></textarea>
              </div>

              <!-- ---- TOMBOL AKSI ---- -->
              <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-gray-50">

                <!-- Tombol Simpan (submit form) -->
                <button
                  type="submit"
                  class="flex items-center gap-2 bg-brand text-white px-7 py-3 rounded-xl font-bold hover:bg-brand-dark transition-colors shadow-sm shadow-brand/20">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 13l4 4L19 7"/>
                  </svg>
                  <?= $edit_id ? 'Simpan Perubahan' : 'Tambah ke Bank Soal' ?>
                </button>

                <!-- Tombol Preview — TIDAK submit form, buka modal JS -->
                <button
                  type="button"
                  onclick="bukaModalPreview()"
                  class="flex items-center gap-2 bg-amber-50 text-amber-700 border border-amber-200 px-7 py-3 rounded-xl font-bold hover:bg-amber-100 transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  Preview Soal
                </button>

                <!-- Tombol Batal -->
                <a href="banksoal-admin.php"
                   class="px-7 py-3 rounded-xl font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition-colors">
                  Batal
                </a>
              </div>

            </form><!-- /form -->
          </div><!-- /card -->
        </div><!-- /kolom kiri -->

        <div class="space-y-4">

          <!-- Card: Tips penulisan soal -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 text-sm mb-3 flex items-center gap-2">
              <span class="w-6 h-6 rounded-lg bg-brand/10 text-brand flex items-center justify-center text-xs">💡</span>
              Tips Penulisan Soal
            </h3>
            <ul class="space-y-2.5 text-xs text-gray-500 leading-relaxed">
              <li class="flex gap-2">
                <span class="text-brand font-bold mt-0.5">•</span>
                Tulis pertanyaan yang jelas dan tidak ambigu.
              </li>
              <li class="flex gap-2">
                <span class="text-brand font-bold mt-0.5">•</span>
                Pastikan hanya ada SATU jawaban yang benar-benar tepat.
              </li>
              <li class="flex gap-2">
                <span class="text-brand font-bold mt-0.5">•</span>
                Buat pilihan pengecoh (distractor) yang masuk akal agar soal tidak terlalu mudah.
              </li>
              <li class="flex gap-2">
                <span class="text-brand font-bold mt-0.5">•</span>
                Isi pembahasan agar siswa bisa belajar dari kesalahan.
              </li>
              <li class="flex gap-2">
                <span class="text-brand font-bold mt-0.5">•</span>
                Pilihan E bersifat opsional — aktif jika diisi.
              </li>
            </ul>
          </div>

          <!-- Card: Info kategori -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 text-sm mb-3 flex items-center gap-2">
              <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-xs"></span>
              Kategori Soal
            </h3>
            <div class="space-y-2">
              <?php foreach ($kategori_labels as $val => $label): ?>
              <div class="flex items-start gap-2 text-xs text-gray-500">
                <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-medium flex-shrink-0"><?= strtoupper($val[0]) ?></span>
                <span><?= e($label) ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Card: Info alur Bank Soal → Tryout -->
          <div class="bg-gradient-to-br from-brand to-brand-light rounded-2xl p-5 text-white">
            <h3 class="font-semibold text-sm mb-2 opacity-90">Alur Sistem CBT</h3>
            <div class="space-y-2 text-xs opacity-80 leading-relaxed">
              <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center font-bold flex-shrink-0">1</span>
                <span>Tambah soal → masuk <strong>Bank Soal</strong></span>
              </div>
              <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center font-bold flex-shrink-0">2</span>
                <span>Buat Tryout → pilih soal dari Bank</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center font-bold flex-shrink-0">3</span>
                <span>Siswa kerjakan Tryout sesuai jadwal</span>
              </div>
            </div>
          </div>

        </div><!-- /kolom kanan -->
      </div><!-- /grid -->

    </main>
  </div><!-- /konten utama -->
</div><!-- /flex wrapper -->


<div id="modalPreview"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm hidden"
     onclick="if(event.target===this) tutupModalPreview()">

  <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">

    <!-- Header modal -->
    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
      <div class="flex items-center gap-3">
        <!-- Ikon preview -->
        <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center">
          <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
        </div>
        <div>
          <h3 class="font-bold text-gray-900 text-sm">Preview Soal</h3>
          <p class="text-xs text-gray-400">Tampilan sebagaimana dilihat oleh siswa</p>
        </div>
      </div>
      <!-- Tombol tutup modal -->
      <button onclick="tutupModalPreview()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Body modal — diisi oleh JavaScript -->
    <div id="previewBody" class="p-6"></div>

    <!-- Footer modal -->
    <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center bg-gray-50/50 rounded-b-2xl">
      <p class="text-xs text-gray-400">* Ini adalah tampilan preview. Soal belum tersimpan.</p>
      <button onclick="tutupModalPreview()"
              class="px-5 py-2 text-sm font-semibold bg-brand text-white rounded-xl hover:bg-brand-dark transition-colors">
        Tutup
      </button>
    </div>

  </div>
</div>



<script>

const KATEGORI_LABELS = <?= json_encode($kategori_labels) ?>;
const DIFFICULTY_LABELS = <?= json_encode($difficulty_labels) ?>;

const DIFFICULTY_COLORS = {
    easy:   'bg-green-100 text-green-700',
    medium: 'bg-yellow-100 text-yellow-700',
    hard:   'bg-red-100 text-red-700',
};

const OPTION_LABELS = { a:'A', b:'B', c:'C', d:'D', e:'E' };


function bukaModalPreview() {
    // Ambil nilai dari semua input form
    const pertanyaan = document.getElementById('inp_question').value.trim();
    const optA       = document.getElementById('inp_option_a').value.trim();
    const optB       = document.getElementById('inp_option_b').value.trim();
    const optC       = document.getElementById('inp_option_c').value.trim();
    const optD       = document.getElementById('inp_option_d').value.trim();
    const optE       = document.getElementById('inp_option_e').value.trim();
    const correct    = document.getElementById('inp_correct').value;
    const category   = document.getElementById('inp_category').value;
    const difficulty = document.getElementById('inp_difficulty').value;
    const explanation = document.getElementById('inp_explanation').value.trim();

    // Validasi minimal: pertanyaan dan pilihan A-D harus diisi
    if (!pertanyaan) {
        alert('Isi teks pertanyaan terlebih dahulu untuk melihat preview.');
        return;
    }
    if (correct === 'e' && !optE) {
    alert('Pilihan E belum diisi, tidak bisa dijadikan jawaban benar.');
    return;
    }

    // Susun daftar pilihan yang akan ditampilkan
    const pilihan = [
        { key: 'a', label: 'A', text: optA },
        { key: 'b', label: 'B', text: optB },
        { key: 'c', label: 'C', text: optC },
        { key: 'd', label: 'D', text: optD },
    ];
    // Tambahkan pilihan E hanya jika diisi
    if (optE) {
        pilihan.push({ key: 'e', label: 'E', text: optE });
    }

    // Render badge kategori dan kesulitan
    const katLabel  = KATEGORI_LABELS[category]  || category  || '—';
    const difLabel  = DIFFICULTY_LABELS[difficulty] || difficulty || '—';
    const difColor  = DIFFICULTY_COLORS[difficulty] || 'bg-gray-100 text-gray-600';

    // Bangun HTML pilihan jawaban
    let htmlPilihan = '';
    pilihan.forEach(p => {
        const isBenar = (p.key === correct);
        const baseClass = 'answer-option flex items-start gap-3 p-4 rounded-xl border-2 cursor-default mb-2.5';
        const stateClass = isBenar
            ? 'border-green-400 bg-green-50'  // Highlight jawaban benar dengan hijau
            : 'border-gray-100 bg-gray-50/50 hover:border-gray-200';

        htmlPilihan += `
            <div class="${baseClass} ${stateClass}">
                <span class="w-7 h-7 rounded-lg ${isBenar ? 'bg-green-500 text-white' : 'bg-white border border-gray-200 text-gray-600'}
                             font-bold text-sm flex items-center justify-center flex-shrink-0 mt-0.5">
                    ${escHtml(p.label)}
                </span>
                <span class="text-sm ${isBenar ? 'font-semibold text-green-800' : 'text-gray-700'} leading-relaxed">
                    ${escHtml(p.text)}
                    ${isBenar ? '<span class="ml-1 text-xs text-green-600 font-normal">← Jawaban Benar</span>' : ''}
                </span>
            </div>`;
    });

    // Bangun HTML pembahasan (jika ada)
    const htmlPembahasan = explanation ? `
        <div class="mt-5 p-4 bg-blue-50 border border-blue-100 rounded-xl">
            <p class="text-xs font-semibold text-blue-700 mb-1.5 uppercase tracking-wide">
                💡 Pembahasan
            </p>
            <p class="text-sm text-blue-800 leading-relaxed">${escHtml(explanation)}</p>
        </div>` : '';

    // Susun seluruh HTML konten modal
    const html = `
        <!-- Metadata soal -->
        <div class="flex flex-wrap items-center gap-2 mb-5">
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-brand/10 text-brand">
                ${escHtml(katLabel)}
            </span>
            <span class="px-3 py-1 text-xs font-semibold rounded-full ${difColor}">
                ${escHtml(difLabel)}
            </span>
            <span class="ml-auto text-xs text-gray-400">Preview mode — jawaban benar ditampilkan</span>
        </div>

        <!-- Teks pertanyaan -->
        <div class="mb-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Pertanyaan:</p>
            <p class="text-base font-medium text-gray-900 leading-relaxed">
                ${escHtml(pertanyaan)}
            </p>
        </div>

        <!-- Garis pemisah -->
        <hr class="border-gray-100 mb-4">

        <!-- Pilihan jawaban -->
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Pilihan Jawaban:</p>
            ${htmlPilihan}
        </div>

        <!-- Pembahasan (jika ada) -->
        ${htmlPembahasan}
    `;

    // Masukkan HTML ke dalam body modal dan tampilkan
    document.getElementById('previewBody').innerHTML = html;
    document.getElementById('modalPreview').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Kunci scroll halaman utama
}

function tutupModalPreview() {
    document.getElementById('modalPreview').classList.add('hidden');
    document.body.style.overflow = ''; // Kembalikan scroll halaman
}

function escHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str || ''));
    return div.innerHTML;
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') tutupModalPreview();
});
</script>

</body>
</html>
