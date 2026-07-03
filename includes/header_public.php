<?php
// includes/header_public.php — shared <head> + navbar for public (landing) pages
// Variables expected: $page_title (string)
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($page_title ?? APP_NAME) ?> — <?= APP_NAME ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;900&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: { DEFAULT: '#1B3F8B', light: '#2952B3', dark: '#122D6B' },
            gold:  { DEFAULT: '#F5A623', light: '#FFB84D' },
          },
          fontFamily: { poppins: ['Poppins', 'sans-serif'] },
        }
      }
    }
  </script>
  <style>
    * { font-family: 'Poppins', sans-serif; }
    .hero-bg { background: linear-gradient(135deg, #0f2460 0%, #1B3F8B 45%, #2952B3 100%); }
    .cta-bg  { background: linear-gradient(135deg, #0f2460 0%, #1B3F8B 60%, #2952B3 100%); }
    .step-line { background: linear-gradient(90deg, #1B3F8B, #F5A623); }
    @keyframes float   { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
    @keyframes fadeUp  { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
    @keyframes badgePulse { 0%,100%{box-shadow:0 0 0 0 rgba(245,166,35,.5)} 50%{box-shadow:0 0 0 8px rgba(245,166,35,0)} }
    .floating    { animation: float 4s ease-in-out infinite; }
    .fade-up     { opacity:0; animation: fadeUp .6s ease forwards; }
    .d1{animation-delay:.1s} .d2{animation-delay:.2s} .d3{animation-delay:.3s}
    .d4{animation-delay:.4s} .d5{animation-delay:.5s} .d6{animation-delay:.6s}
    .badge-pulse { animation: badgePulse 2s ease infinite; }
    .price-card  { transition: transform .25s ease, box-shadow .25s ease; }
    .price-card:hover { transform:translateY(-8px) scale(1.02); box-shadow:0 24px 60px rgba(27,63,139,.18); }
    .feat-card   { transition: transform .2s ease, box-shadow .2s ease; }
    .feat-card:hover { transform:translateY(-6px); box-shadow:0 16px 40px rgba(27,63,139,.12); }
    .nav-glass   { background:rgba(255,255,255,.97); backdrop-filter:blur(12px); border-bottom:1px solid rgba(27,63,139,.08); }
    #mobile-menu { transition:max-height .3s ease,opacity .3s ease; max-height:0; opacity:0; overflow:hidden; }
    #mobile-menu.open { max-height:400px; opacity:1; }
    .gradient-text {
      background: linear-gradient(135deg,#fff 30%,#F5A623 100%);
      -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
    }
    ::-webkit-scrollbar{width:6px} ::-webkit-scrollbar-track{background:#f1f1f1} ::-webkit-scrollbar-thumb{background:#1B3F8B;border-radius:3px}
  </style>
  <link rel="stylesheet" href="style.css">
</head>
<body class="bg-white text-gray-800 antialiased">

<nav class="nav-glass sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 md:px-8 lg:px-12">
    <div class="flex items-center justify-between h-16">
      <a href="index.php" class="flex items-center gap-2">
        <div class="w-8 h-8 bg-[#1B3F8B] rounded-lg flex items-center justify-center">
          <span class="text-white font-black text-sm">E</span>
        </div>
        <span class="text-[#1B3F8B] font-black text-xl tracking-tight">EngLight</span>
      </a>
      <div class="hidden md:flex items-center gap-8">
        <a href="index.php#features" class="text-sm font-medium text-gray-600 hover:text-[#1B3F8B] transition-colors">Fitur</a>
        <a href="index.php#about" class="text-sm font-medium text-gray-600 hover:text-[#1B3F8B] transition-colors">tentang</a>
        <a href="index.php#how"      class="text-sm font-medium text-gray-600 hover:text-[#1B3F8B] transition-colors">Cara Kerja</a>
        <a href="index.php#pricing"  class="text-sm font-medium text-gray-600 hover:text-[#1B3F8B] transition-colors">Harga</a>
      </div>
      <div class="hidden md:flex items-center gap-3">
        <a href="login.php"    class="px-5 py-2 text-sm font-semibold text-[#1B3F8B] border-2 border-[#1B3F8B] rounded-xl hover:bg-[#1B3F8B] hover:text-white transition-all">Login</a>
        <a href="register.php" class="px-5 py-2 text-sm font-semibold text-white bg-[#1B3F8B] rounded-xl hover:bg-[#122D6B] transition-all shadow-md">Daftar</a>
      </div>
      <button id="hamburger" class="md:hidden p-2 rounded-lg hover:bg-[#F4F7FF] transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="menu" class="w-6 h-6 text-[#1B3F8B]"></svg>
      </button>
    </div>
  </div>
  <div id="mobile-menu" class="md:hidden bg-white border-t border-gray-100">
    <div class="px-4 py-4 space-y-3">
      <a href="index.php#features" class="block text-sm font-medium text-gray-700 py-2 hover:text-[#1B3F8B]">Fitur</a>
      <a href="index.php#how"      class="block text-sm font-medium text-gray-700 py-2 hover:text-[#1B3F8B]">Cara Kerja</a>
      <a href="index.php#pricing"  class="block text-sm font-medium text-gray-700 py-2 hover:text-[#1B3F8B]">Harga</a>
      <div class="flex gap-3 pt-2">
        <a href="login.php"    class="flex-1 text-center px-4 py-2.5 text-sm font-semibold text-[#1B3F8B] border-2 border-[#1B3F8B] rounded-xl">Login</a>
        <a href="register.php" class="flex-1 text-center px-4 py-2.5 text-sm font-semibold text-white bg-[#1B3F8B] rounded-xl">Daftar</a>
      </div>
    </div>
  </div>
</nav>
