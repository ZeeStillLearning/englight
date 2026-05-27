<?php
// includes/sidebar_admin.php — left sidebar for admin pages
// Variable expected: $active_page (string)
$links = [
    'dashboard' => ['href' => APP_URL . '/admin/admin-dashboard.php', 'label' => 'Dashboard'],
    'materi'    => ['href' => APP_URL . '/admin/materi-admin.php',    'label' => 'Materi'],
    'banksoal'  => ['href' => APP_URL . '/admin/banksoal-admin.php',  'label' => 'Bank Soal'],
    'tryout'    => ['href' => APP_URL . '/admin/tryout-admin.php',    'label' => 'Tryout'],
    'ebook'     => ['href' => APP_URL . '/admin/ebook-admin.php',     'label' => 'E-Book'],
    'pengguna'  => ['href' => APP_URL . '/admin/pengguna-admin.php',  'label' => 'Pengguna'],
    'log'       => ['href' => APP_URL . '/admin/log-admin.php',       'label' => 'Admin Log'],
];
?>
<aside class="w-64 bg-gray-900 text-white flex flex-col flex-shrink-0">
    <div class="px-6 py-5 border-b border-white/10">
        <p class="text-xl font-bold">EngLight</p>
        <p class="text-xs text-gray-400">Admin Panel</p>
    </div>
    <nav class="flex-1 px-4 py-4 space-y-1">
        <?php foreach ($links as $key => $link): ?>
            <a href="<?= $link['href'] ?>" class="flex items-center px-3 py-2.5 rounded-xl text-sm transition <?= $active_page === $key ? 'bg-white/10 text-white font-semibold' : 'text-gray-400 hover:bg-white/5 hover:text-white' ?>">
                <?= e($link['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="px-4 py-4 border-t border-white/10">
        <div class="mt-2">
            <a href="<?= APP_URL ?>/logout.php" class="block w-full text-left px-3 py-2 text-sm text-white/60 hover:text-white hover:bg-white/10 rounded-xl transition-all">
                Keluar
            </a>
        </div>
    </div>
</aside>
