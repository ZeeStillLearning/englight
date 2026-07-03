<?php
// includes/sidebar_user.php — left sidebar for user dashboard pages
// Variable expected: $active_page (string, e.g. 'dashboard')
$user  = current_user();
$links = [
    'dashboard'  => ['href' => APP_URL . '/dashboard.php',         'label' => 'Dashboard',    'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    'materi'     => ['href' => APP_URL . '/materi-user.php',        'label' => 'Materi',       'icon' => 'M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z'],
    'latihan'    => ['href' => APP_URL . '/latihansoal-user.php',   'label' => 'Latihan Soal', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
    'tryout'     => ['href' => APP_URL . '/tryout-user.php',        'label' => 'Tryout Englight', 'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
    'speaking'   => ['href' => APP_URL . '/speaking-user.php',      'label' => 'Speaking AI',  'icon' => 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z'],
    'forum'      => ['href' => APP_URL . '/forum-user.php',         'label' => 'Forum',        'icon' => 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z'],
    'ebook'      => ['href' => APP_URL . '/ebook-user.php',         'label' => 'E-Book',       'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
    'membership' => ['href' => APP_URL . '/membership-user.php',    'label' => 'Membership',   'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
];
?>
<aside class="hidden lg:flex lg:flex-col lg:w-64 bg-brand text-white flex-shrink-0" style="background-color:#1B3F8B">
    <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
        <div class="w-9 h-9 bg-white rounded-xl flex items-center justify-center">
            <span class="font-black text-sm" style="color:#1B3F8B">E</span>
        </div>
        <span class="text-xl font-bold tracking-wide">EngLight</span>
    </div>
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <?php foreach ($links as $key => $link): ?>
            <a href="<?= $link['href'] ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= $active_page === $key ? 'bg-white/20 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' ?>">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="<?= $link['icon'] ?>"></path>
                </svg>
                <?= e($link['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="px-4 py-4 border-t border-white/10">
        <div class="flex items-center gap-3 px-3 py-2 mb-2">
            <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center text-xs font-bold">
                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold truncate"><?= e($user['name'] ?? '') ?></p>
                <p class="text-xs text-white/60 uppercase"><?= e($user['plan'] ?? 'free') ?></p>
            </div>
        </div>
        <a href="<?= APP_URL ?>/logout.php" class="block w-full text-left px-3 py-2 text-sm text-white/60 hover:text-white hover:bg-white/10 rounded-xl transition-all">
            Keluar
        </a>
    </div>
</aside>
