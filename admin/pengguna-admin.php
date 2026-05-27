<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// Toggle active status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_active'])) {
    $uid     = (int)$_POST['toggle_active'];
    $current = db_row('SELECT is_active, name FROM users WHERE id = ? AND role = "user"', [$uid]);
    if ($current) {
        $new_status = $current['is_active'] ? 0 : 1;
        db_run('UPDATE users SET is_active = ? WHERE id = ?', [$new_status, $uid]);
        $action = $new_status ? 'ACTIVATE_USER' : 'DEACTIVATE_USER';
        log_admin_action($action, 'users', $uid, 'User: ' . $current['name']);
        set_flash('success', 'Status pengguna "' . $current['name'] . '" berhasil diubah.');
    }
    header('Location: pengguna-admin.php'); exit;
}

// Change plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_plan_uid'])) {
    $uid  = (int)$_POST['change_plan_uid'];
    $plan = $_POST['new_plan'] ?? '';
    if (in_array($plan, ['free','premium','pro'])) {
        db_run('UPDATE users SET plan = ? WHERE id = ? AND role = "user"', [$plan, $uid]);
        log_admin_action('CHANGE_PLAN', 'users', $uid, 'Plan → ' . $plan);
        set_flash('success', 'Plan pengguna berhasil diubah ke ' . strtoupper($plan) . '.');
    }
    header('Location: pengguna-admin.php'); exit;
}

// Delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $uid = (int)$_POST['delete_user'];
    $u   = db_row('SELECT name FROM users WHERE id = ? AND role = "user"', [$uid]);
    if ($u) {
        db_run('DELETE FROM users WHERE id = ? AND role = "user"', [$uid]);
        log_admin_action('DELETE_USER', 'users', $uid, 'Hapus: ' . $u['name']);
        set_flash('success', 'Pengguna "' . $u['name'] . '" berhasil dihapus.');
    }
    header('Location: pengguna-admin.php'); exit;
}

$search = trim($_GET['q'] ?? '');
$sql    = 'SELECT * FROM users WHERE role = "user"';
$p      = [];
if ($search) {
    $sql .= ' AND (name LIKE ? OR email LIKE ?)';
    $p[]  = "%$search%";
    $p[]  = "%$search%";
}
$sql  .= ' ORDER BY created_at DESC';
$users = db_all($sql, $p);

$active_page = 'pengguna';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Pengguna</title>
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
      <h1 class="font-bold text-gray-900">Manajemen Pengguna</h1>
      <span class="text-sm text-gray-500"><?= count($users) ?> pengguna</span>
    </header>
    <main class="p-8 space-y-6">
      <?php render_flash(); ?>

      <!-- Search -->
      <form method="GET" class="flex gap-3 max-w-md">
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama atau email..."
               class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
        <button type="submit" class="bg-brand text-white px-5 py-2.5 rounded-xl text-sm font-semibold">Cari</button>
        <?php if ($search): ?>
          <a href="pengguna-admin.php" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Reset</a>
        <?php endif; ?>
      </form>

      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
            <tr>
              <th class="px-6 py-3 text-left">Pengguna</th>
              <th class="px-6 py-3 text-left">Plan</th>
              <th class="px-6 py-3 text-left">XP</th>
              <th class="px-6 py-3 text-left">Status</th>
              <th class="px-6 py-3 text-left">Bergabung</th>
              <th class="px-6 py-3 text-left">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php if (empty($users)): ?>
              <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Tidak ada pengguna ditemukan.</td></tr>
            <?php else: ?>
              <?php foreach ($users as $u): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-brand/10 rounded-full flex items-center justify-center text-brand font-bold text-sm">
                      <?= strtoupper(substr($u['name'], 0, 1)) ?>
                    </div>
                    <div>
                      <p class="font-semibold text-gray-800"><?= e($u['name']) ?></p>
                      <p class="text-xs text-gray-400"><?= e($u['email']) ?></p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <form method="POST" class="inline-flex">
                    <input type="hidden" name="change_plan_uid" value="<?= (int)$u['id'] ?>">
                    <select name="new_plan" onchange="this.form.submit()"
                            class="text-xs border border-gray-200 rounded-lg px-2 py-1 focus:outline-none uppercase font-semibold">
                      <?php foreach (['free','premium','pro'] as $plan): ?>
                        <option value="<?= $plan ?>" <?= $u['plan'] === $plan ? 'selected' : '' ?>><?= strtoupper($plan) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </td>
                <td class="px-6 py-4 font-semibold text-blue-600"><?= number_format($u['xp']) ?></td>
                <td class="px-6 py-4">
                  <span class="text-xs font-semibold px-2 py-0.5 rounded-lg <?= $u['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                    <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-gray-500 text-xs"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <form method="POST">
                      <input type="hidden" name="toggle_active" value="<?= (int)$u['id'] ?>">
                      <button type="submit" class="text-xs font-semibold hover:underline <?= $u['is_active'] ? 'text-orange-500' : 'text-green-600' ?>">
                        <?= $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                      </button>
                    </form>
                    <form method="POST" onsubmit="return confirm('Hapus pengguna ini secara permanen?')">
                      <input type="hidden" name="delete_user" value="<?= (int)$u['id'] ?>">
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
</body>
</html>
