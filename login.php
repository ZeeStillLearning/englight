<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

redirect_if_logged_in();

$errors   = [];
$success  = '';
$active   = isset($_GET['tab']) && $_GET['tab'] === 'register' ? 'register' : 'login';

// ── Handle LOGIN ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $active = 'login';
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } elseif (empty($password)) {
        $errors[] = 'Password wajib diisi.';
    } else {
        $user = db_row('SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1', [$email]);
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role']  = $user['role'];
            $_SESSION['user_plan']  = $user['plan'];
            $_SESSION['user_xp']    = $user['xp'];

            if ($user['role'] === 'admin') {
                header('Location: admin/admin-dashboard.php'); exit;
            } else {
                header('Location: dashboard.php'); exit;
            }
        } else {
            $errors[] = 'Email atau password salah.';
        }
    }
}

// ── Handle REGISTER ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $active   = 'register';
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    if (empty($name) || strlen($name) < 2)                     $errors[] = 'Nama lengkap minimal 2 karakter.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))            $errors[] = 'Format email tidak valid.';
    if (strlen($password) < 8)                                 $errors[] = 'Password minimal 8 karakter.';
    if ($password !== $confirm)                                $errors[] = 'Konfirmasi password tidak cocok.';

    if (empty($errors)) {
        $existing = db_row('SELECT id FROM users WHERE email = ? LIMIT 1', [$email]);
        if ($existing) {
            $errors[] = 'Email sudah terdaftar. Silakan login.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            db_run('INSERT INTO users (name, email, password, role, plan) VALUES (?, ?, ?, ?, ?)',
                   [$name, $email, $hash, 'user', 'free']);
            $success = 'Akun berhasil dibuat! Silakan login.';
            $active  = 'login';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk / Daftar — EngLight</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;900&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <style>
    * { font-family: 'Poppins', sans-serif; }
    body { background-color: #F4F7FF; }
    .hero-panel { background: linear-gradient(135deg, #0f2460 0%, #1B3F8B 50%, #2952B3 100%); }
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
    .floating { animation: float 3s ease-in-out infinite; }
  </style>
  <link rel="stylesheet" href="style.css">
</head>
<body class="min-h-screen flex items-center justify-center p-4">

  <div class="bg-white w-full max-w-5xl rounded-[24px] shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[600px]">

    <!-- Form Panel -->
    <section class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
      <a href="index.php" class="flex items-center gap-2 mb-8">
        <div class="w-7 h-7 bg-[#1B3F8B] rounded-lg flex items-center justify-center">
          <span class="text-white font-black text-xs">E</span>
        </div>
        <span class="text-[#1B3F8B] font-black text-lg tracking-tight">EngLight</span>
      </a>

      <!-- Tab Switcher -->
      <div class="flex bg-[#F4F7FF] rounded-xl p-1 mb-6">
        <button id="tab-login"    onclick="showTab('login')"    class="flex-1 py-2 rounded-lg text-sm font-bold transition-all <?= $active === 'login'    ? 'bg-white text-[#1B3F8B] shadow-sm' : 'text-gray-500' ?>">Masuk</button>
        <button id="tab-register" onclick="showTab('register')" class="flex-1 py-2 rounded-lg text-sm font-bold transition-all <?= $active === 'register' ? 'bg-white text-[#1B3F8B] shadow-sm' : 'text-gray-500' ?>">Daftar</button>
      </div>

      <!-- Alerts -->
      <?php if (!empty($errors)): ?>
        <div class="mb-4 px-4 py-3 rounded-xl text-sm bg-red-100 text-red-700 border border-red-200">
          <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="mb-4 px-4 py-3 rounded-xl text-sm bg-green-100 text-green-700 border border-green-200"><?= e($success) ?></div>
      <?php endif; ?>

      <!-- LOGIN FORM -->
      <div id="form-login" class="<?= $active === 'login' ? '' : 'hidden' ?>">
        <h1 class="text-2xl font-black text-gray-900 mb-1">Selamat Datang Kembali!</h1>
        <p class="text-gray-500 text-sm mb-6">Silakan masuk ke akun EngLight kamu.</p>
        <form method="POST" action="login.php" class="space-y-4">
          <input type="hidden" name="action" value="login">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
            <div class="relative">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="mail" class="absolute left-3 top-3.5 w-4 h-4 text-gray-400"></svg>
              <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="nama@email.com"
                     class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#1B3F8B]/30 focus:border-[#1B3F8B] outline-none text-sm transition-all" required>
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
            <div class="relative">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="lock" class="absolute left-3 top-3.5 w-4 h-4 text-gray-400"></svg>
              <input type="password" name="password" placeholder="Masukkan password"
                     class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#1B3F8B]/30 focus:border-[#1B3F8B] outline-none text-sm transition-all" required>
            </div>
          </div>
          <div class="flex justify-end">
            <a href="#" class="text-xs text-[#1B3F8B] font-semibold hover:underline">Lupa password?</a>
          </div>
          <button type="submit" class="w-full bg-[#1B3F8B] text-white py-3 rounded-xl font-bold hover:bg-[#122D6B] transition-all shadow-md hover:shadow-lg">
            Masuk Sekarang
          </button>
        </form>
      </div>

      <!-- REGISTER FORM -->
      <div id="form-register" class="<?= $active === 'register' ? '' : 'hidden' ?>">
        <h1 class="text-2xl font-black text-gray-900 mb-1">Buat Akun Baru</h1>
        <p class="text-gray-500 text-sm mb-6">Mulai perjalanan belajarmu hari ini — gratis!</p>
        <form method="POST" action="login.php?tab=register" class="space-y-4">
          <input type="hidden" name="action" value="register">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Lengkap</label>
            <div class="relative">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="user" class="absolute left-3 top-3.5 w-4 h-4 text-gray-400"></svg>
              <input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>" placeholder="Nama lengkap kamu"
                     class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#1B3F8B]/30 focus:border-[#1B3F8B] outline-none text-sm transition-all" required>
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
            <div class="relative">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="mail" class="absolute left-3 top-3.5 w-4 h-4 text-gray-400"></svg>
              <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="nama@email.com"
                     class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#1B3F8B]/30 focus:border-[#1B3F8B] outline-none text-sm transition-all" required>
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
            <div class="relative">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="lock" class="absolute left-3 top-3.5 w-4 h-4 text-gray-400"></svg>
              <input type="password" name="password" placeholder="Min. 8 karakter"
                     class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#1B3F8B]/30 focus:border-[#1B3F8B] outline-none text-sm transition-all" required>
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Konfirmasi Password</label>
            <div class="relative">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="shield-check" class="absolute left-3 top-3.5 w-4 h-4 text-gray-400"></svg>
              <input type="password" name="confirm" placeholder="Ulangi password"
                     class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#1B3F8B]/30 focus:border-[#1B3F8B] outline-none text-sm transition-all" required>
            </div>
          </div>
          <button type="submit" class="w-full bg-[#1B3F8B] text-white py-3 rounded-xl font-bold hover:bg-[#122D6B] transition-all shadow-md hover:shadow-lg">
            Daftar Sekarang
          </button>
        </form>
      </div>
    </section>

    <!-- Hero Panel -->
    <section class="hidden md:flex hero-panel w-1/2 flex-col items-center justify-center p-12 relative overflow-hidden">
      <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full blur-3xl -translate-y-1/3 translate-x-1/3"></div>
      <div class="absolute bottom-0 left-0 w-48 h-48 bg-[#F5A623]/10 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3"></div>
      <div class="relative z-10 text-center">
        <div class="floating mb-8">
          <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-6 text-white">
            <div class="grid grid-cols-3 gap-4 mb-6">
              <div class="bg-white/10 rounded-xl p-3 text-center">
                <div class="text-2xl font-black text-[#F5A623]">550</div>
                <div class="text-xs text-blue-200 mt-1">TOEFL Score</div>
              </div>
              <div class="bg-white/10 rounded-xl p-3 text-center">
                <div class="text-2xl font-black text-green-300">92%</div>
                <div class="text-xs text-blue-200 mt-1">Akurasi</div>
              </div>
              <div class="bg-white/10 rounded-xl p-3 text-center">
                <div class="text-2xl font-black">30h</div>
                <div class="text-xs text-blue-200 mt-1">Belajar</div>
              </div>
            </div>
            <div class="space-y-2">
              <div class="text-xs text-blue-200 flex justify-between"><span>Listening</span><span class="font-bold text-white">88%</span></div>
              <div class="bg-white/20 rounded-full h-1.5"><div class="bg-[#F5A623] h-1.5 rounded-full w-[88%]"></div></div>
              <div class="text-xs text-blue-200 flex justify-between"><span>Structure</span><span class="font-bold text-white">75%</span></div>
              <div class="bg-white/20 rounded-full h-1.5"><div class="bg-green-400 h-1.5 rounded-full w-[75%]"></div></div>
              <div class="text-xs text-blue-200 flex justify-between"><span>Reading</span><span class="font-bold text-white">92%</span></div>
              <div class="bg-white/20 rounded-full h-1.5"><div class="bg-blue-400 h-1.5 rounded-full w-[92%]"></div></div>
            </div>
          </div>
        </div>
        <h2 class="text-2xl font-black text-white mb-3">Raih Skor TOEFL Impianmu</h2>
        <p class="text-blue-200 text-sm leading-relaxed">Bergabung dengan 25.000+ pelajar yang telah meningkatkan kemampuan bahasa Inggris mereka.</p>
        <div class="mt-6 flex items-center justify-center gap-2">
          <div class="flex -space-x-2">
            <div class="w-8 h-8 rounded-full bg-[#F5A623] border-2 border-white flex items-center justify-center text-white text-xs font-bold">A</div>
            <div class="w-8 h-8 rounded-full bg-green-400 border-2 border-white flex items-center justify-center text-white text-xs font-bold">B</div>
            <div class="w-8 h-8 rounded-full bg-blue-400 border-2 border-white flex items-center justify-center text-white text-xs font-bold">C</div>
          </div>
          <span class="text-white/80 text-xs">+25K pengguna aktif</span>
        </div>
      </div>
    </section>
  </div>

<script>
  lucide.createIcons();
  function showTab(tab) {
    const formLogin    = document.getElementById('form-login');
    const formRegister = document.getElementById('form-register');
    const tabLogin     = document.getElementById('tab-login');
    const tabRegister  = document.getElementById('tab-register');
    if (tab === 'login') {
      formLogin.classList.remove('hidden');
      formRegister.classList.add('hidden');
      tabLogin.classList.add('bg-white','text-[#1B3F8B]','shadow-sm');
      tabLogin.classList.remove('text-gray-500');
      tabRegister.classList.remove('bg-white','text-[#1B3F8B]','shadow-sm');
      tabRegister.classList.add('text-gray-500');
    } else {
      formRegister.classList.remove('hidden');
      formLogin.classList.add('hidden');
      tabRegister.classList.add('bg-white','text-[#1B3F8B]','shadow-sm');
      tabRegister.classList.remove('text-gray-500');
      tabLogin.classList.remove('bg-white','text-[#1B3F8B]','shadow-sm');
      tabLogin.classList.add('text-gray-500');
    }
  }
</script>
</body>
</html>
