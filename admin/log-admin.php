<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// Purge old logs (older than 90 days)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purge_logs'])) {
    db_run('DELETE FROM admin_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)');
    log_admin_action('PURGE_LOGS', null, null, 'Log lebih dari 90 hari dihapus');
    set_flash('success', 'Log lama berhasil dihapus.');
    header('Location: log-admin.php'); exit;
}

$filter_action = trim($_GET['action'] ?? '');
$filter_admin  = (int)($_GET['admin_id'] ?? 0);

$sql  = 'SELECT al.*, u.name AS admin_name FROM admin_logs al JOIN users u ON u.id = al.admin_id WHERE 1=1';
$p    = [];
if ($filter_action) { $sql .= ' AND al.action LIKE ?'; $p[] = "%$filter_action%"; }
if ($filter_admin)  { $sql .= ' AND al.admin_id = ?';  $p[] = $filter_admin; }
$sql .= ' ORDER BY al.created_at DESC LIMIT 200';
$logs = db_all($sql, $p);

$admins      = db_all('SELECT id, name FROM users WHERE role = "admin" ORDER BY name');
$total_logs  = db_row('SELECT COUNT(*) AS cnt FROM admin_logs')['cnt'] ?? 0;

$active_page = 'log';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Log Aktivitas</title>
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
        <h1 class="font-bold text-gray-900">Log Aktivitas Admin</h1>
        <p class="text-xs text-gray-500">Total <?= number_format($total_logs) ?> log tersimpan</p>
      </div>
      <form method="POST" onsubmit="return confirm('Hapus semua log lebih dari 90 hari? Tindakan ini tidak dapat dibatalkan.')">
        <input type="hidden" name="purge_logs" value="1">
        <button type="submit" class="text-xs text-red-500 border border-red-200 px-4 py-2 rounded-xl font-semibold hover:bg-red-50 transition">Hapus Log Lama (>90 hari)</button>
      </form>
    </header>
    <main class="p-8 space-y-6">
      <?php render_flash(); ?>

      <!-- Filters -->
      <form method="GET" class="flex flex-wrap gap-3">
        <input type="text" name="action" value="<?= e($filter_action) ?>" placeholder="Filter aksi (cth: ADD, DELETE)"
               class="border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand w-56">
        <select name="admin_id" class="border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
          <option value="">Semua Admin</option>
          <?php foreach ($admins as $a): ?>
            <option value="<?= (int)$a['id'] ?>" <?= $filter_admin === (int)$a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-brand text-white px-5 py-2 rounded-xl text-sm font-semibold">Filter</button>
        <?php if ($filter_action || $filter_admin): ?>
          <a href="log-admin.php" class="px-4 py-2 border border-gray-200 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Reset</a>
        <?php endif; ?>
      </form>

      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-3 border-b border-gray-100 text-xs text-gray-500">
          Menampilkan <?= count($logs) ?> log (maks. 200)
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
            <tr>
              <th class="px-6 py-3 text-left">Waktu</th>
              <th class="px-6 py-3 text-left">Admin</th>
              <th class="px-6 py-3 text-left">Aksi</th>
              <th class="px-6 py-3 text-left">Target</th>
              <th class="px-6 py-3 text-left">Detail</th>
              <th class="px-6 py-3 text-left">IP</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php if (empty($logs)): ?>
              <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Tidak ada log ditemukan.</td></tr>
            <?php else: ?>
              <?php foreach ($logs as $l): ?>
                <?php
                // Color-code action badge
                $action_lc = strtolower($l['action'] ?? '');
                $badge_cls = str_contains($action_lc, 'delete') || str_contains($action_lc, 'purge') ? 'bg-red-100 text-red-700'
                    : (str_contains($action_lc, 'add') || str_contains($action_lc, 'create') ? 'bg-green-100 text-green-700'
                    : (str_contains($action_lc, 'edit') || str_contains($action_lc, 'update') || str_contains($action_lc, 'change') ? 'bg-blue-100 text-blue-700'
                    : 'bg-gray-100 text-gray-600'));
                ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 text-gray-400 text-xs whitespace-nowrap"><?= date('d M Y H:i:s', strtotime($l['created_at'])) ?></td>
                <td class="px-6 py-3 font-medium text-gray-700 text-xs"><?= e($l['admin_name']) ?></td>
                <td class="px-6 py-3">
                  <span class="text-xs font-bold px-2 py-0.5 rounded <?= $badge_cls ?>"><?= e($l['action']) ?></span>
                </td>
                <td class="px-6 py-3 text-xs text-gray-500">
                  <?php if ($l['target_type']): ?>
                    <?= e($l['target_type']) ?><?= $l['target_id'] ? ' #' . $l['target_id'] : '' ?>
                  <?php else: ?>—<?php endif; ?>
                </td>
                <td class="px-6 py-3 text-xs text-gray-500 max-w-xs truncate"><?= $l['detail'] ? e($l['detail']) : '—' ?></td>
                <td class="px-6 py-3 text-xs text-gray-400 font-mono"><?= e($l['ip_address'] ?? '—') ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</div>
</body>
</html>
