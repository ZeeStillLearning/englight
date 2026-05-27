<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$user = current_user();

// ── Handle new post ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_post'])) {
    $title    = trim($_POST['title']    ?? '');
    $content  = trim($_POST['content']  ?? '');
    $category = trim($_POST['category'] ?? '');
    if (strlen($title) >= 5 && strlen($content) >= 10) {
        db_run('INSERT INTO forum_posts (user_id, title, content, category) VALUES (?, ?, ?, ?)',
               [$user['id'], $title, $content, $category]);
        db_run('UPDATE users SET xp = xp + 5 WHERE id = ?', [$user['id']]);
        $_SESSION['user_xp'] = ($_SESSION['user_xp'] ?? 0) + 5;
        set_flash('success', 'Postingan berhasil dibuat! +5 XP 🎉');
    } else {
        set_flash('error', 'Judul minimal 5 karakter dan konten minimal 10 karakter.');
    }
    header('Location: ' . APP_URL . '/forum-user.php'); exit;
}

// ── Handle reply ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reply'])) {
    $post_id = (int)($_POST['post_id'] ?? 0);
    $content = trim($_POST['reply_content'] ?? '');
    if ($post_id > 0 && strlen($content) >= 3) {
        db_run('INSERT INTO forum_replies (post_id, user_id, content) VALUES (?, ?, ?)',
               [$post_id, $user['id'], $content]);
        db_run('UPDATE users SET xp = xp + 2 WHERE id = ?', [$user['id']]);
        $_SESSION['user_xp'] = ($_SESSION['user_xp'] ?? 0) + 2;
        set_flash('success', 'Balasan berhasil dikirim! +2 XP');
    }
    $redirect_post = $post_id ? '#post-' . $post_id : '';
    header('Location: ' . APP_URL . '/forum-user.php' . $redirect_post); exit;
}

// ── Handle upvote (1x per user per post) ─────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upvote_post'])) {
    $pid = (int)$_POST['upvote_post'];
    // Check if already upvoted using session
    $upvoted_key = 'upvoted_post_' . $pid;
    if (empty($_SESSION[$upvoted_key])) {
        db_run('UPDATE forum_posts SET upvotes = upvotes + 1 WHERE id = ?', [$pid]);
        $_SESSION[$upvoted_key] = true;
    } else {
        // Un-upvote
        db_run('UPDATE forum_posts SET upvotes = GREATEST(upvotes - 1, 0) WHERE id = ?', [$pid]);
        unset($_SESSION[$upvoted_key]);
    }
    header('Location: ' . APP_URL . '/forum-user.php#post-' . $pid); exit;
}

// ── Delete own post ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {
    $pid = (int)$_POST['delete_post'];
    $post = db_row('SELECT user_id FROM forum_posts WHERE id = ?', [$pid]);
    if ($post && ($post['user_id'] == $user['id'] || $user['role'] === 'admin')) {
        db_run('DELETE FROM forum_posts WHERE id = ?', [$pid]);
        set_flash('success', 'Postingan berhasil dihapus.');
    }
    header('Location: ' . APP_URL . '/forum-user.php'); exit;
}

// ── Fetch posts with replies ──────────────────────────────────
$search      = trim($_GET['q']        ?? '');
$filter_cat  = trim($_GET['category'] ?? '');

$sql  = 'SELECT fp.*, u.name AS author_name,
                (SELECT COUNT(*) FROM forum_replies fr WHERE fr.post_id = fp.id) AS reply_count
         FROM forum_posts fp
         JOIN users u ON u.id = fp.user_id
         WHERE 1=1';
$params = [];

if ($search) {
    $sql     .= ' AND (fp.title LIKE ? OR fp.content LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filter_cat) {
    $sql     .= ' AND fp.category = ?';
    $params[] = $filter_cat;
}
$sql   .= ' ORDER BY fp.is_pinned DESC, fp.created_at DESC LIMIT 40';
$posts  = db_all($sql, $params);

// Fetch replies for all visible posts
$post_ids = array_column($posts, 'id');
$replies_map = [];
if (!empty($post_ids)) {
    $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
    $all_replies  = db_all(
        "SELECT fr.*, u.name AS author_name
         FROM forum_replies fr
         JOIN users u ON u.id = fr.user_id
         WHERE fr.post_id IN ($placeholders)
         ORDER BY fr.created_at ASC",
        $post_ids
    );
    foreach ($all_replies as $r) {
        $replies_map[$r['post_id']][] = $r;
    }
}

// Stats
$total_posts   = db_row('SELECT COUNT(*) AS cnt FROM forum_posts')['cnt'] ?? 0;
$total_members = db_row('SELECT COUNT(*) AS cnt FROM users WHERE role = "user"')['cnt'] ?? 0;
$total_replies = db_row('SELECT COUNT(*) AS cnt FROM forum_replies')['cnt'] ?? 0;

$categories = ['Grammar','Listening','Structure','Reading','TOEFL Tips','Umum'];

$active_page = 'forum';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forum Diskusi — EngLight</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{brand:{DEFAULT:'#1B3F8B',light:'#2952B3',dark:'#122D6B'}}}}}</script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;900&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; }
    .post-card { transition: box-shadow .2s ease, transform .15s ease; }
    .post-card:hover { box-shadow: 0 8px 30px rgba(27,63,139,.10); }
    .reply-area { display: none; }
    .reply-area.open { display: block; }
    .replies-list { display: none; }
    .replies-list.open { display: block; }
    .upvoted svg { fill: #1B3F8B; stroke: #1B3F8B; }
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:50; align-items:center; justify-content:center; }
    .modal-overlay.open { display:flex; }
  </style>
  <link rel="stylesheet" href="style.css">
</head>
<body class="h-full">
<div class="flex h-full min-h-screen">
  <?php require_once __DIR__ . '/includes/sidebar_user.php'; ?>

  <div class="flex-1 overflow-y-auto">

    <!-- Header -->
    <header class="bg-white border-b px-6 py-4 flex items-center justify-between">
      <div>
        <h1 class="font-bold text-gray-900">Forum Diskusi</h1>
        <p class="text-xs text-gray-500">Diskusikan, tanya, dan bantu sesama pelajar</p>
      </div>
      <button onclick="openModal()"
              class="flex items-center gap-2 bg-brand text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Buat Postingan
      </button>
    </header>

    <main class="p-6 lg:p-8 space-y-6">
      <?php render_flash(); ?>

      <!-- Stats Bar -->
      <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
          <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
          </div>
          <div>
            <p class="text-xl font-black text-brand"><?= number_format($total_posts) ?></p>
            <p class="text-xs text-gray-500">Total Diskusi</p>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
          <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
          </div>
          <div>
            <p class="text-xl font-black text-green-600"><?= number_format($total_replies) ?></p>
            <p class="text-xs text-gray-500">Total Balasan</p>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
          <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
          <div>
            <p class="text-xl font-black text-purple-600"><?= number_format($total_members) ?></p>
            <p class="text-xs text-gray-500">Anggota Aktif</p>
          </div>
        </div>
      </div>

      <!-- Search & Filter -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
          <!-- Search input -->
          <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="q" value="<?= e($search) ?>"
                   placeholder="Cari topik diskusi..."
                   class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
          </div>
          <!-- Category filter chips -->
          <div class="flex gap-2 flex-wrap">
            <a href="<?= APP_URL ?>/forum-user.php<?= $search ? '?q=' . urlencode($search) : '' ?>"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition <?= !$filter_cat ? 'bg-brand text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
              Semua
            </a>
            <?php foreach ($categories as $cat):
              $active_cls = $filter_cat === $cat ? 'bg-brand text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200';
              $href = APP_URL . '/forum-user.php?category=' . urlencode($cat) . ($search ? '&q=' . urlencode($search) : '');
            ?>
            <a href="<?= $href ?>" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition <?= $active_cls ?>">
              <?= e($cat) ?>
            </a>
            <?php endforeach; ?>
          </div>
          <button type="submit" class="bg-brand text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
            Cari
          </button>
        </form>
      </div>

      <!-- Posts List -->
      <?php if (empty($posts)): ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
            </svg>
          </div>
          <p class="font-bold text-gray-500 text-lg mb-1">Belum ada diskusi</p>
          <p class="text-gray-400 text-sm mb-6">Jadilah yang pertama memulai diskusi!</p>
          <button onclick="openModal()" class="bg-brand text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
            Buat Postingan Pertama
          </button>
        </div>

      <?php else: ?>
      <div class="space-y-4">
        <?php foreach ($posts as $p):
          $replies      = $replies_map[$p['id']] ?? [];
          $reply_count  = count($replies);
          $is_upvoted   = !empty($_SESSION['upvoted_post_' . $p['id']]);
          $is_own       = $p['user_id'] == $user['id'];
          $avatar_char  = strtoupper(mb_substr($p['author_name'], 0, 1));

          // Category color
          $cat_cls_map = [
            'Grammar'    => 'bg-orange-100 text-orange-700',
            'Listening'  => 'bg-blue-100 text-blue-700',
            'Structure'  => 'bg-green-100 text-green-700',
            'Reading'    => 'bg-purple-100 text-purple-700',
            'TOEFL Tips' => 'bg-red-100 text-red-700',
            'Umum'       => 'bg-gray-100 text-gray-600',
          ];
          $cat_cls = $cat_cls_map[$p['category']] ?? 'bg-gray-100 text-gray-600';

          // Time ago
          $diff = time() - strtotime($p['created_at']);
          if ($diff < 60)          $time_ago = 'Baru saja';
          elseif ($diff < 3600)    $time_ago = floor($diff/60) . ' menit lalu';
          elseif ($diff < 86400)   $time_ago = floor($diff/3600) . ' jam lalu';
          elseif ($diff < 604800)  $time_ago = floor($diff/86400) . ' hari lalu';
          else                     $time_ago = date('d M Y', strtotime($p['created_at']));
        ?>

        <div class="post-card bg-white rounded-2xl border <?= $p['is_pinned'] ? 'border-yellow-300 bg-yellow-50/30' : 'border-gray-100' ?> shadow-sm overflow-hidden"
             id="post-<?= $p['id'] ?>">

          <!-- Post Header -->
          <div class="p-5 pb-4">
            <?php if ($p['is_pinned']): ?>
              <div class="flex items-center gap-1.5 text-xs text-yellow-600 font-bold mb-3">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M16 12V4a1 1 0 00-1-1H9a1 1 0 00-1 1v8l-2 2v1h12v-1l-2-2zm-5 6a2 2 0 004 0h-4z"/></svg>
                DISEMATKAN
              </div>
            <?php endif; ?>

            <div class="flex items-start gap-4">
              <!-- Avatar -->
              <div class="w-10 h-10 rounded-full bg-brand flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                <?= $avatar_char ?>
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2 mb-1">
                  <h3 class="font-bold text-gray-900 text-base leading-snug"><?= e($p['title']) ?></h3>
                  <?php if ($is_own || $user['role'] === 'admin'): ?>
                    <form method="POST" class="flex-shrink-0" onsubmit="return confirm('Hapus postingan ini?')">
                      <input type="hidden" name="delete_post" value="<?= (int)$p['id'] ?>">
                      <button type="submit" class="text-gray-300 hover:text-red-400 transition" title="Hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>

                <!-- Meta -->
                <div class="flex items-center flex-wrap gap-2 mb-3">
                  <span class="text-xs font-semibold text-gray-600"><?= e($p['author_name']) ?></span>
                  <span class="text-gray-300">·</span>
                  <span class="text-xs text-gray-400"><?= $time_ago ?></span>
                  <?php if ($p['category']): ?>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full <?= $cat_cls ?>"><?= e($p['category']) ?></span>
                  <?php endif; ?>
                </div>

                <!-- Content Preview -->
                <p class="text-sm text-gray-600 leading-relaxed" id="content-preview-<?= $p['id'] ?>">
                  <?= nl2br(e(mb_substr($p['content'], 0, 250))) ?><?= mb_strlen($p['content']) > 250 ? '…' : '' ?>
                </p>
                <?php if (mb_strlen($p['content']) > 250): ?>
                  <button onclick="toggleContent(<?= $p['id'] ?>)"
                          id="read-more-<?= $p['id'] ?>"
                          class="text-xs text-brand font-semibold mt-1 hover:underline">
                    Baca selengkapnya
                  </button>
                  <p class="text-sm text-gray-600 leading-relaxed hidden" id="content-full-<?= $p['id'] ?>">
                    <?= nl2br(e($p['content'])) ?>
                    <button onclick="toggleContent(<?= $p['id'] ?>)" class="text-xs text-brand font-semibold ml-1 hover:underline">Lebih sedikit</button>
                  </p>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Post Footer: actions -->
          <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between bg-gray-50/50">
            <div class="flex items-center gap-4">
              <!-- Upvote -->
              <form method="POST">
                <input type="hidden" name="upvote_post" value="<?= (int)$p['id'] ?>">
                <button type="submit"
                        class="flex items-center gap-1.5 text-xs font-semibold transition <?= $is_upvoted ? 'text-brand upvoted' : 'text-gray-400 hover:text-brand' ?>">
                  <svg class="w-4 h-4" fill="<?= $is_upvoted ? '#1B3F8B' : 'none' ?>" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                  </svg>
                  <span><?= $p['upvotes'] ?></span>
                </button>
              </form>

              <!-- Reply count toggle -->
              <button onclick="toggleReplies(<?= $p['id'] ?>)"
                      class="flex items-center gap-1.5 text-xs font-semibold text-gray-400 hover:text-brand transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span id="reply-label-<?= $p['id'] ?>"><?= $reply_count ?> Balasan</span>
              </button>
            </div>

            <!-- Write reply button -->
            <button onclick="toggleReplyForm(<?= $p['id'] ?>)"
                    class="flex items-center gap-1.5 text-xs font-semibold text-brand border border-brand/30 bg-brand/5 hover:bg-brand hover:text-white px-3 py-1.5 rounded-lg transition">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
              </svg>
              Balas
            </button>
          </div>

          <!-- Replies List (collapsible) -->
          <?php if (!empty($replies)): ?>
          <div class="replies-list <?= $reply_count > 0 ? '' : '' ?>" id="replies-<?= $p['id'] ?>">
            <div class="border-t border-gray-100 divide-y divide-gray-50">
              <?php foreach ($replies as $r):
                $r_avatar = strtoupper(mb_substr($r['author_name'], 0, 1));
                $r_diff   = time() - strtotime($r['created_at']);
                if ($r_diff < 60)         $r_time = 'Baru saja';
                elseif ($r_diff < 3600)   $r_time = floor($r_diff/60) . ' mnt lalu';
                elseif ($r_diff < 86400)  $r_time = floor($r_diff/3600) . ' jam lalu';
                else                      $r_time = date('d M', strtotime($r['created_at']));
              ?>
              <div class="flex items-start gap-3 px-5 py-3 bg-gray-50/50 hover:bg-gray-100/50 transition">
                <div class="w-7 h-7 rounded-full bg-brand/20 flex items-center justify-center text-brand font-bold text-xs flex-shrink-0 mt-0.5">
                  <?= $r_avatar ?>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-xs font-bold text-gray-700"><?= e($r['author_name']) ?></span>
                    <span class="text-xs text-gray-400"><?= $r_time ?></span>
                  </div>
                  <p class="text-sm text-gray-600 leading-relaxed"><?= nl2br(e($r['content'])) ?></p>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- Reply Form (collapsible) -->
          <div class="reply-area" id="reply-form-<?= $p['id'] ?>">
            <div class="border-t border-gray-100 p-4 bg-blue-50/30">
              <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-brand flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                  <?= strtoupper(mb_substr($user['name'], 0, 1)) ?>
                </div>
                <form method="POST" class="flex-1 flex gap-2">
                  <input type="hidden" name="submit_reply" value="1">
                  <input type="hidden" name="post_id" value="<?= (int)$p['id'] ?>">
                  <textarea name="reply_content" rows="2"
                            placeholder="Tulis balasanmu di sini..."
                            class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand resize-none bg-white"></textarea>
                  <button type="submit"
                          class="bg-brand text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition self-end">
                    Kirim
                  </button>
                </form>
              </div>
            </div>
          </div>

        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </main>
  </div>
</div>

<!-- ═══════════════════════════════════════════════
     MODAL: Buat Postingan Baru
═══════════════════════════════════════════════ -->
<div class="modal-overlay" id="post-modal" onclick="handleModalClick(event)">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden" onclick="event.stopPropagation()">
    <!-- Modal Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-brand">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-sm">
          <?= strtoupper(mb_substr($user['name'], 0, 1)) ?>
        </div>
        <div>
          <p class="font-bold text-white text-sm"><?= e($user['name']) ?></p>
          <p class="text-xs text-blue-200">Buat Diskusi Baru • +5 XP</p>
        </div>
      </div>
      <button onclick="closeModal()" class="text-white/70 hover:text-white transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Modal Body -->
    <form method="POST" class="p-6 space-y-4">
      <input type="hidden" name="submit_post" value="1">
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Judul Diskusi <span class="text-red-500">*</span></label>
        <input type="text" name="title" placeholder="Tulis judul pertanyaan atau topikmu..." maxlength="255"
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand" required autofocus>
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kategori</label>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($categories as $cat):
            $cat_map = ['Grammar'=>'orange','Listening'=>'blue','Structure'=>'green','Reading'=>'purple','TOEFL Tips'=>'red','Umum'=>'gray'];
            $c = $cat_map[$cat] ?? 'gray';
          ?>
          <label class="cursor-pointer">
            <input type="radio" name="category" value="<?= $cat ?>" class="sr-only peer">
            <span class="inline-block px-3 py-1.5 rounded-xl text-xs font-semibold border-2 border-gray-200 text-gray-500 peer-checked:border-brand peer-checked:bg-brand peer-checked:text-white transition cursor-pointer hover:border-brand/50">
              <?= $cat ?>
            </span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Isi Diskusi <span class="text-red-500">*</span></label>
        <textarea name="content" rows="5"
                  placeholder="Jelaskan pertanyaan atau topik diskusimu secara detail..."
                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand resize-none" required></textarea>
        <p class="text-xs text-gray-400 mt-1">Minimal 10 karakter. Semakin detail, semakin mudah dijawab!</p>
      </div>
      <div class="flex gap-3 pt-1">
        <button type="submit"
                class="flex-1 bg-brand text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition">
          Posting Sekarang
        </button>
        <button type="button" onclick="closeModal()"
                class="px-5 py-3 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
          Batal
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// Modal
function openModal() {
  document.getElementById('post-modal').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal() {
  document.getElementById('post-modal').classList.remove('open');
  document.body.style.overflow = '';
}
function handleModalClick(e) {
  if (e.target === document.getElementById('post-modal')) closeModal();
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

// Toggle replies visibility
function toggleReplies(id) {
  const el = document.getElementById('replies-' + id);
  if (!el) return;
  el.classList.toggle('open');
  const label = document.getElementById('reply-label-' + id);
  if (label) {
    const count = label.textContent.match(/\d+/)[0];
    label.textContent = el.classList.contains('open')
      ? count + ' Balasan ▲'
      : count + ' Balasan';
  }
}

// Toggle reply form
function toggleReplyForm(id) {
  const form = document.getElementById('reply-form-' + id);
  if (!form) return;
  form.classList.toggle('open');
  if (form.classList.contains('open')) {
    // Also open replies when writing
    const replies = document.getElementById('replies-' + id);
    if (replies) replies.classList.add('open');
    setTimeout(() => form.querySelector('textarea')?.focus(), 100);
  }
}

// Read more toggle
function toggleContent(id) {
  const preview = document.getElementById('content-preview-' + id);
  const full    = document.getElementById('content-full-' + id);
  if (!preview || !full) return;
  preview.classList.toggle('hidden');
  full.classList.toggle('hidden');
}

// Auto-open replies if there are any (first 3 posts)
document.querySelectorAll('[id^="replies-"]').forEach((el, i) => {
  if (i < 3) el.classList.add('open');
});
</script>

</body>
</html>
