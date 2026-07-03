<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$user    = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_session'])) {
    header('Content-Type: application/json');

    $topic    = trim($_POST['topic'] ?? '');
    $mode     = trim($_POST['mode'] ?? 'free'); // 'free' atau 'sentence'
    $score    = $mode === 'sentence' ? (int)($_POST['score'] ?? 0) : null;
    $feedback = trim($_POST['feedback'] ?? '');

    if ($topic === '') {
        echo json_encode(['ok' => false, 'error' => 'Topik tidak boleh kosong.']);
        exit;
    }

    db_run('INSERT INTO speaking_sessions (user_id, topic, score, feedback, created_at) VALUES (?, ?, ?, ?, NOW())',
           [$user['id'], $topic, $score, $feedback ?: null]);

    // XP hanya diberikan untuk mode "Latihan Kalimat" yang punya skor terukur
    $xp = 0;
    if ($mode === 'sentence' && $score !== null) {
        $xp = $score >= 80 ? 15 : ($score >= 50 ? 8 : 3);
        db_run('UPDATE users SET xp = xp + ? WHERE id = ?', [$xp, $user['id']]);
        $_SESSION['user_xp'] = ($_SESSION['user_xp'] ?? 0) + $xp;
    }

    echo json_encode(['ok' => true, 'xp' => $xp]);
    exit;
}

$history = db_all('SELECT * FROM speaking_sessions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10', [$user['id']]);

// ── Topik latihan bebas (tanpa skor) ──────────────────────────
$free_topics = [
    ['Introduce yourself', 'Dasar'],
    ['Describe your daily routine', 'Dasar'],
    ['Talk about your hobbies', 'Menengah'],
    ['Describe a place you visited', 'Menengah'],
    ['Discuss current events', 'Lanjutan'],
    ['Express your opinion on education', 'Lanjutan'],
];

// ── Kalimat latihan untuk mode skor otomatis ──────────────────
// ingin mengelola kalimat ini lewat panel admin.
$sentence_drills = [
    ['text' => 'The weather is nice today.', 'level' => 'Dasar'],
    ['text' => 'I would like to order a cup of coffee.', 'level' => 'Dasar'],
    ['text' => 'She has been working here for three years.', 'level' => 'Menengah'],
    ['text' => 'Could you please repeat the question?', 'level' => 'Menengah'],
    ['text' => 'The committee has reached a unanimous decision.', 'level' => 'Lanjutan'],
    ['text' => 'Despite the challenges, the team managed to finish on time.', 'level' => 'Lanjutan'],
];

$active_page = 'speaking';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Speaking Practice — EngLight</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{brand:{DEFAULT:'#1B3F8B'}}}}}</script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;900&display=swap" rel="stylesheet">
  <style>
    body{font-family:'Poppins',sans-serif}
    .tab-btn.active { background:#1B3F8B; color:#fff; }
    .word-match  { color:#16a34a; font-weight:600; }
    .word-miss   { color:#ef4444; text-decoration: line-through; }
    @keyframes pulseRec { 0%,100%{ box-shadow:0 0 0 0 rgba(255,255,255,.4) } 50%{ box-shadow:0 0 0 12px rgba(255,255,255,0) } }
    .recording { animation: pulseRec 1.4s infinite; }
  </style>
  <link rel="stylesheet" href="style.css">
</head>
<body class="h-full">
<div class="flex h-full min-h-screen">
  <?php require_once __DIR__ . '/includes/sidebar_user.php'; ?>
  <div class="flex-1 overflow-y-auto">
    <header class="bg-white border-b px-6 py-4 flex items-center justify-between">
      <h1 class="font-bold text-gray-900">Speaking Practice</h1>
      <span class="text-sm text-gray-500"><?= e($user['name']) ?></span>
    </header>
    <main class="p-6 lg:p-8 space-y-6">
      <?php render_flash(); ?>

      <!-- Browser compatibility notice (disuntikkan via JS jika tidak didukung) -->
      <div id="browserWarning" class="hidden bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm text-amber-700">
        ⚠️ Mode "Latihan Kalimat" memakai fitur pengenalan suara browser yang saat ini hanya didukung optimal di <strong>Google Chrome</strong> atau <strong>Microsoft Edge</strong> di desktop/Android. Kamu masih bisa pakai mode "Topik Bebas" di browser apapun.
      </div>

      <!-- Tab Switcher -->
      <div class="flex gap-2 bg-white p-1.5 rounded-2xl border border-gray-100 shadow-sm w-fit">
        <button type="button" id="tabFreeBtn" onclick="switchTab('free')"
                class="tab-btn active px-5 py-2.5 rounded-xl text-sm font-semibold transition-all">
          🗣️ Topik Bebas
        </button>
        <button type="button" id="tabSentenceBtn" onclick="switchTab('sentence')"
                class="tab-btn px-5 py-2.5 rounded-xl text-sm font-semibold transition-all text-gray-500">
          📝 Latihan Kalimat
        </button>
      </div>

      <div id="panelFree" class="space-y-6">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <h2 class="font-bold text-gray-900 mb-1">Latihan Bebas</h2>
          <p class="text-sm text-gray-500 mb-6">Pilih topik, rekam suaramu, lalu dengarkan ulang untuk evaluasi mandiri. Tidak ada skor otomatis di mode ini.</p>

          <p class="text-xs font-semibold text-gray-400 uppercase mb-2" id="freeTopicLabel">Pilih topik di bawah untuk mulai</p>

          <div class="bg-gradient-to-br from-brand to-blue-600 rounded-2xl p-8 text-center text-white mb-2">
            <div id="freeRecBtn" class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 cursor-pointer hover:bg-white/30 transition opacity-40 pointer-events-none">
              <svg id="freeMicIcon" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="9" y="2" width="6" height="13" rx="3"></rect>
                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                <path d="M12 19v3"></path>
              </svg>
            </div>
            <p id="freeRecStatus" class="font-semibold">Pilih topik terlebih dahulu</p>
            <p class="text-xs text-white/60 mt-1" id="freeRecTimer">00:00</p>
          </div>

          <!-- Playback rekaman -->
          <div id="freePlaybackWrap" class="hidden mt-4 flex items-center gap-3 bg-gray-50 rounded-xl p-4">
            <audio id="freeAudioPlayer" controls class="flex-1"></audio>
            <button type="button" onclick="saveFreeSession()" class="bg-brand text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition flex-shrink-0">
              Simpan Sesi
            </button>
          </div>
        </div>

        <!-- Daftar Topik -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <h2 class="font-bold text-gray-900 mb-4">Topik Latihan</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <?php foreach ($free_topics as [$topic, $level]):
              $lc = ['Dasar'=>'blue','Menengah'=>'orange','Lanjutan'=>'red'][$level];
            ?>
            <div class="free-topic-card flex items-center justify-between p-4 rounded-xl border border-gray-200 hover:border-brand hover:bg-blue-50 transition cursor-pointer"
                 onclick="selectFreeTopic('<?= e(addslashes($topic)) ?>', this)">
              <div>
                <p class="font-semibold text-sm text-gray-800"><?= e($topic) ?></p>
                <span class="text-xs bg-<?= $lc ?>-100 text-<?= $lc ?>-700 px-2 py-0.5 rounded font-semibold"><?= e($level) ?></span>
              </div>
              <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div><!-- /panelFree -->

      <div id="panelSentence" class="hidden space-y-6">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <h2 class="font-bold text-gray-900 mb-1">Latihan Kalimat</h2>
          <p class="text-sm text-gray-500 mb-6">Baca kalimat target dengan suara nyaring. Sistem akan mentranskrip ucapanmu dan menghitung skor kecocokan kata secara otomatis.</p>

          <!-- Kalimat target -->
          <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 mb-5 text-center">
            <p class="text-xs font-semibold text-blue-500 uppercase mb-2">Ucapkan kalimat ini</p>
            <p id="targetSentence" class="text-lg font-bold text-blue-900">Pilih kalimat di bawah untuk mulai</p>
          </div>

          <div class="bg-gradient-to-br from-brand to-blue-600 rounded-2xl p-8 text-center text-white mb-4">
            <div id="sentRecBtn" class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 cursor-pointer hover:bg-white/30 transition opacity-40 pointer-events-none">
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="9" y="2" width="6" height="13" rx="3"></rect>
                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                <path d="M12 19v3"></path>
              </svg>
            </div>
            <p id="sentRecStatus" class="font-semibold">Pilih kalimat terlebih dahulu</p>
          </div>

          <!-- Hasil transkrip & skor -->
          <div id="sentResultWrap" class="hidden space-y-4">
            <div class="bg-gray-50 rounded-xl p-4">
              <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Yang Sistem Dengar</p>
              <p id="transcriptResult" class="text-sm text-gray-700">—</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
              <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Perbandingan Kata</p>
              <p id="wordCompareResult" class="text-sm leading-relaxed">—</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div class="bg-green-50 rounded-xl p-4 text-center">
                <p id="sentScoreVal" class="text-3xl font-black text-green-600">0%</p>
                <p class="text-xs text-gray-500 mt-1">Skor Kecocokan</p>
              </div>
              <div class="flex flex-col gap-2 justify-center">
                <button type="button" onclick="retrySentence()" class="bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
                  Coba Lagi
                </button>
                <button type="button" onclick="saveSentenceSession()" class="bg-brand text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
                  Simpan Hasil
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Daftar Kalimat -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <h2 class="font-bold text-gray-900 mb-4">Pilih Kalimat</h2>
          <div class="space-y-2">
            <?php foreach ($sentence_drills as $s):
              $lc = ['Dasar'=>'blue','Menengah'=>'orange','Lanjutan'=>'red'][$s['level']];
            ?>
            <div class="sentence-card flex items-center justify-between p-4 rounded-xl border border-gray-200 hover:border-brand hover:bg-blue-50 transition cursor-pointer"
                 onclick="selectSentence('<?= e(addslashes($s['text'])) ?>', this)">
              <div>
                <p class="font-medium text-sm text-gray-800">"<?= e($s['text']) ?>"</p>
                <span class="text-xs bg-<?= $lc ?>-100 text-<?= $lc ?>-700 px-2 py-0.5 rounded font-semibold"><?= e($s['level']) ?></span>
              </div>
              <svg class="w-5 h-5 text-brand flex-shrink-0 ml-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div><!-- /panelSentence -->

      <!-- History -->
      <?php if (!empty($history)): ?>
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="font-bold text-gray-900">Riwayat Sesi</h2>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
            <tr>
              <th class="px-6 py-3 text-left">Tanggal</th>
              <th class="px-6 py-3 text-left">Topik / Kalimat</th>
              <th class="px-6 py-3 text-left">Skor</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($history as $h): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-3 text-gray-500"><?= date('d M Y H:i', strtotime($h['created_at'])) ?></td>
              <td class="px-6 py-3 text-gray-700"><?= e(mb_substr($h['topic'] ?? '—', 0, 60)) ?></td>
              <td class="px-6 py-3 font-bold text-brand"><?= $h['score'] !== null ? $h['score'] . '%' : '— (latihan bebas)' ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

    </main>
  </div>
</div>

<script>
function switchTab(tab) {
    const panelFree = document.getElementById('panelFree');
    const panelSentence = document.getElementById('panelSentence');
    const tabFree = document.getElementById('tabFreeBtn');
    const tabSentence = document.getElementById('tabSentenceBtn');

    if (tab === 'free') {
        panelFree.classList.remove('hidden');
        panelSentence.classList.add('hidden');
        tabFree.classList.add('active');
        tabSentence.classList.remove('active');
        tabSentence.classList.add('text-gray-500');
    } else {
        panelFree.classList.add('hidden');
        panelSentence.classList.remove('hidden');
        tabSentence.classList.add('active');
        tabFree.classList.remove('active');
        tabFree.classList.add('text-gray-500');
    }
}

let mediaRecorder = null;
let audioChunks   = [];
let freeRecording = false;
let freeTimer = null, freeSeconds = 0;
let currentFreeTopic = null;
let lastFreeAudioBlob = null;

async function selectFreeTopic(topic, cardEl) {
    currentFreeTopic = topic;
    document.querySelectorAll('.free-topic-card').forEach(c => c.classList.remove('border-brand', 'bg-blue-50'));
    cardEl.classList.add('border-brand', 'bg-blue-50');

    document.getElementById('freeTopicLabel').textContent = 'Topik: ' + topic;
    document.getElementById('freeRecStatus').textContent = 'Klik mikrofon untuk mulai merekam';
    document.getElementById('freeRecBtn').classList.remove('opacity-40', 'pointer-events-none');
    document.getElementById('freePlaybackWrap').classList.add('hidden');

    document.getElementById('freeRecBtn').onclick = toggleFreeRecording;
}

async function toggleFreeRecording() {
    if (!freeRecording) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            audioChunks = [];
            mediaRecorder = new MediaRecorder(stream);
            mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
            mediaRecorder.onstop = () => {
                lastFreeAudioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                const url = URL.createObjectURL(lastFreeAudioBlob);
                document.getElementById('freeAudioPlayer').src = url;
                document.getElementById('freePlaybackWrap').classList.remove('hidden');
                stream.getTracks().forEach(t => t.stop());
            };
            mediaRecorder.start();
            freeRecording = true;

            document.getElementById('freeRecBtn').classList.add('recording');
            document.getElementById('freeRecStatus').textContent = '🔴 Merekam... klik lagi untuk berhenti';
            freeSeconds = 0;
            freeTimer = setInterval(() => {
                freeSeconds++;
                const m = String(Math.floor(freeSeconds/60)).padStart(2,'0');
                const s = String(freeSeconds%60).padStart(2,'0');
                document.getElementById('freeRecTimer').textContent = m + ':' + s;
            }, 1000);
        } catch (err) {
            alert('Tidak bisa mengakses mikrofon. Pastikan kamu mengizinkan akses mikrofon di browser.');
        }
    } else {
        mediaRecorder.stop();
        freeRecording = false;
        clearInterval(freeTimer);
        document.getElementById('freeRecBtn').classList.remove('recording');
        document.getElementById('freeRecStatus').textContent = 'Rekaman selesai. Dengarkan ulang di bawah.';
    }
}

function saveFreeSession() {
    if (!currentFreeTopic) return;
    const fd = new FormData();
    fd.append('save_session', '1');
    fd.append('mode', 'free');
    fd.append('topic', currentFreeTopic);
    fd.append('feedback', 'Latihan topik bebas (tidak dinilai otomatis).');

    fetch(window.location.href, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                alert('Sesi latihan bebas berhasil disimpan!');
                location.reload();
            }
        });
}

let recognition = null;
let currentTargetSentence = null;
let lastSentenceScore = null;
let lastTranscript = '';

const SpeechRecognitionAPI = window.SpeechRecognition || window.webkitSpeechRecognition;

if (!SpeechRecognitionAPI) {
    document.getElementById('browserWarning').classList.remove('hidden');
} else {
    recognition = new SpeechRecognitionAPI();
    recognition.lang = 'en-US';
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;
}

function selectSentence(sentence, cardEl) {
    currentTargetSentence = sentence;
    document.querySelectorAll('.sentence-card').forEach(c => c.classList.remove('border-brand', 'bg-blue-50'));
    cardEl.classList.add('border-brand', 'bg-blue-50');

    document.getElementById('targetSentence').textContent = '"' + sentence + '"';
    document.getElementById('sentRecStatus').textContent = 'Klik mikrofon, lalu ucapkan kalimat di atas';
    document.getElementById('sentResultWrap').classList.add('hidden');

    if (SpeechRecognitionAPI) {
        document.getElementById('sentRecBtn').classList.remove('opacity-40', 'pointer-events-none');
        document.getElementById('sentRecBtn').onclick = startSentenceRecognition;
    } else {
        document.getElementById('sentRecStatus').textContent = 'Browser ini tidak mendukung pengenalan suara. Coba pakai Chrome.';
    }
}

function startSentenceRecognition() {
    if (!recognition || !currentTargetSentence) return;

    document.getElementById('sentRecBtn').classList.add('recording');
    document.getElementById('sentRecStatus').textContent = '🔴 Mendengarkan...';

    recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        lastTranscript = transcript;
        scoreSentence(currentTargetSentence, transcript);
    };

    recognition.onerror = (event) => {
        document.getElementById('sentRecBtn').classList.remove('recording');
        document.getElementById('sentRecStatus').textContent = 'Gagal menangkap suara. Coba lagi.';
        console.error('Speech recognition error:', event.error);
    };

    recognition.onend = () => {
        document.getElementById('sentRecBtn').classList.remove('recording');
    };

    recognition.start();
}

// Hitung skor kecocokan kata secara sederhana:
// bandingkan kata-per-kata (lowercase, tanpa tanda baca) antara
// kalimat target dan hasil transkrip.
function scoreSentence(target, transcript) {
    const normalize = (str) => str.toLowerCase().replace(/[^\w\s]/g, '').trim().split(/\s+/);

    const targetWords     = normalize(target);
    const transcriptWords = normalize(transcript);

    let matchedCount = 0;
    const compareHtml = targetWords.map(word => {
        const isMatch = transcriptWords.includes(word);
        if (isMatch) matchedCount++;
        return `<span class="${isMatch ? 'word-match' : 'word-miss'}">${escHtml(word)}</span>`;
    }).join(' ');

    const score = Math.round((matchedCount / targetWords.length) * 100);
    lastSentenceScore = score;

    document.getElementById('sentRecStatus').textContent = 'Selesai! Lihat hasil di bawah.';
    document.getElementById('transcriptResult').textContent = transcript || '(tidak terdeteksi)';
    document.getElementById('wordCompareResult').innerHTML = compareHtml;
    document.getElementById('sentScoreVal').textContent = score + '%';
    document.getElementById('sentResultWrap').classList.remove('hidden');
}

function retrySentence() {
    document.getElementById('sentResultWrap').classList.add('hidden');
    document.getElementById('sentRecStatus').textContent = 'Klik mikrofon, lalu ucapkan kalimat di atas';
}

function saveSentenceSession() {
    if (!currentTargetSentence || lastSentenceScore === null) return;
    const fd = new FormData();
    fd.append('save_session', '1');
    fd.append('mode', 'sentence');
    fd.append('topic', currentTargetSentence);
    fd.append('score', lastSentenceScore);
    fd.append('feedback', 'Transkrip: ' + lastTranscript);

    fetch(window.location.href, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                alert(`Hasil disimpan! Skor: ${lastSentenceScore}% (+${data.xp} XP)`);
                location.reload();
            }
        });
}

function escHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str || ''));
    return div.innerHTML;
}
</script>
</body>
</html>