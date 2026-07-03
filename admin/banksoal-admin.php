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
  <style>
    body{font-family:'Poppins',sans-serif}
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:50; align-items:center; justify-content:center; padding:1rem; }
    .modal-overlay.open { display:flex; }
  </style>
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
              <th class="px-6 py-3 text-left">Jawaban Benar</th>
              <th class="px-6 py-3 text-left">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php if (empty($questions)): ?>
              <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">Belum ada soal. <a href="tambah-soal.php" class="text-brand hover:underline">Tambah sekarang</a></td></tr>
            <?php else: ?>
              <?php foreach ($questions as $q):
                // Teks jawaban benar (bukan huruf) — untuk ditampilkan di tabel
                $correct_text = $q['option_' . $q['correct_answer']];
              ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 max-w-xs">
                  <p class="font-medium text-gray-800 truncate"><?= e(mb_substr($q['question_text'], 0, 80)) ?><?= mb_strlen($q['question_text']) > 80 ? '…' : '' ?></p>
                </td>
                <td class="px-6 py-4 capitalize text-gray-600"><?= e($q['category']) ?></td>
                <td class="px-6 py-4">
                  <?php $dc = ['easy'=>'green','medium'=>'yellow','hard'=>'red'][$q['difficulty']] ?? 'gray'; ?>
                  <span class="text-xs font-semibold bg-<?= $dc ?>-100 text-<?= $dc ?>-700 px-2 py-0.5 rounded capitalize"><?= e($q['difficulty']) ?></span>
                </td>
                <td class="px-6 py-4 text-green-700 font-semibold max-w-[160px] truncate" title="<?= e($correct_text) ?>">
                  <?= e(mb_substr($correct_text, 0, 30)) ?><?= mb_strlen($correct_text) > 30 ? '…' : '' ?>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <button type="button" onclick='openPreview(<?= json_encode($q, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                            class="text-xs text-purple-600 font-semibold hover:underline">
                      Preview
                    </button>
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

<!-- ═══════════════════════════════════════════
     MODAL: Preview Soal (tampilan seperti user)
     Pilihan jawaban (A-E, E opsional) diacak setiap
     dibuka / di-reshuffle, supaya posisi jawaban benar
     tidak selalu di huruf yang sama.
═══════════════════════════════════════════ -->
<div class="modal-overlay" id="preview-modal" onclick="handleModalClick(event)">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl mx-auto overflow-hidden" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-brand">
      <div>
        <p class="font-bold text-white text-sm">Preview Soal — Tampilan User</p>
        <p class="text-xs text-blue-200">Pilihan diacak setiap kali dibuka, seperti yang dilihat user</p>
      </div>
      <button onclick="closePreview()" class="text-white/70 hover:text-white transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <div class="p-6">
      <p class="font-semibold text-gray-900 mb-4 leading-relaxed" id="preview-question"></p>

      <div class="space-y-3" id="preview-options"></div>

      <div class="mt-4 flex items-center justify-between">
        <button type="button" onclick="checkPreviewAnswer()"
                class="bg-brand text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
          Cek Jawaban
        </button>
        <button type="button" onclick="reshufflePreview()"
                class="text-xs text-gray-500 hover:text-brand font-semibold flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          Acak Ulang
        </button>
      </div>

      <div id="preview-result" class="mt-4 hidden"></div>

      <div class="mt-4 pt-4 border-t border-gray-100" id="preview-explanation-wrap" style="display:none">
        <p class="text-xs font-bold text-gray-500 uppercase mb-1">Penjelasan</p>
        <p class="text-sm text-gray-600" id="preview-explanation"></p>
      </div>
    </div>
  </div>
</div>

<script>
let currentQuestion = null;
let shuffledOptions = []; // [{ text: '...', isCorrect: true/false }, ...]
let selectedIndex   = null;

function openPreview(q) {
    currentQuestion = q;
    document.getElementById('preview-result').classList.add('hidden');
    document.getElementById('preview-explanation-wrap').style.display = 'none';
    document.getElementById('preview-explanation').textContent = q.explanation || 'Tidak ada penjelasan.';
    shuffleAndRender();
    document.getElementById('preview-modal').classList.add('open');
}

function closePreview() {
    document.getElementById('preview-modal').classList.remove('open');
    currentQuestion = null;
}

function handleModalClick(e) {
    if (e.target === document.getElementById('preview-modal')) closePreview();
}

function shuffleAndRender() {
    if (!currentQuestion) return;
    selectedIndex = null;

    // Susun opsi sebagai array of {text, isCorrect}.
    // Opsi E hanya disertakan jika benar-benar terisi (tidak null/kosong),
    // sama seperti perilaku di form tambah-soal.php.
    const opts = [
        { key: 'a', text: currentQuestion.option_a, isCorrect: currentQuestion.correct_answer === 'a' },
        { key: 'b', text: currentQuestion.option_b, isCorrect: currentQuestion.correct_answer === 'b' },
        { key: 'c', text: currentQuestion.option_c, isCorrect: currentQuestion.correct_answer === 'c' },
        { key: 'd', text: currentQuestion.option_d, isCorrect: currentQuestion.correct_answer === 'd' },
    ];

    if (currentQuestion.option_e && currentQuestion.option_e.trim() !== '') {
        opts.push({ key: 'e', text: currentQuestion.option_e, isCorrect: currentQuestion.correct_answer === 'e' });
    }

    // Fisher-Yates shuffle — mengacak urutan tampilan opsi.
    // Properti isCorrect tetap menempel ke teks opsinya, jadi posisi
    // jawaban benar akan selalu berpindah-pindah setiap dipanggil.
    for (let i = opts.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [opts[i], opts[j]] = [opts[j], opts[i]];
    }

    shuffledOptions = opts;

    document.getElementById('preview-question').textContent = currentQuestion.question_text;

    // Label huruf dibuat dinamis sesuai jumlah opsi yang tampil (4 atau 5)
    const labels = ['A', 'B', 'C', 'D', 'E'];
    const container = document.getElementById('preview-options');
    container.innerHTML = '';

    shuffledOptions.forEach((opt, idx) => {
        const label = document.createElement('label');
        label.className = 'flex items-start gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-blue-50 hover:border-brand transition-all';
        label.id = 'preview-opt-' + idx;
        label.innerHTML = `
            <input type="radio" name="preview-answer" value="${idx}" class="mt-0.5 w-4 h-4 text-brand flex-shrink-0" onchange="selectedIndex=${idx}">
            <span class="text-sm text-gray-700"><span class="font-bold text-brand uppercase mr-1">${labels[idx]}.</span> ${escapeHtml(opt.text)}</span>
        `;
        container.appendChild(label);
    });

    document.getElementById('preview-result').classList.add('hidden');
    document.getElementById('preview-explanation-wrap').style.display = 'none';
}

function reshufflePreview() {
    shuffleAndRender();
}

function checkPreviewAnswer() {
    if (selectedIndex === null) {
        alert('Pilih salah satu jawaban dulu.');
        return;
    }
    const resultEl = document.getElementById('preview-result');
    const isCorrect = shuffledOptions[selectedIndex].isCorrect;

    // Reset semua border opsi
    shuffledOptions.forEach((opt, idx) => {
        const el = document.getElementById('preview-opt-' + idx);
        el.classList.remove('border-green-500','bg-green-50','border-red-500','bg-red-50');
        if (opt.isCorrect) {
            el.classList.add('border-green-500','bg-green-50');
        }
    });

    if (!isCorrect) {
        document.getElementById('preview-opt-' + selectedIndex).classList.add('border-red-500','bg-red-50');
    }

    resultEl.classList.remove('hidden');
    resultEl.innerHTML = isCorrect
        ? '<div class="px-4 py-2.5 rounded-xl bg-green-100 text-green-700 text-sm font-semibold">✓ Benar! Jawaban ini sudah tepat.</div>'
        : '<div class="px-4 py-2.5 rounded-xl bg-red-100 text-red-700 text-sm font-semibold">✗ Salah. Jawaban benar ditandai hijau di atas.</div>';

    document.getElementById('preview-explanation-wrap').style.display = 'block';
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closePreview(); });
</script>

</body>
</html>
