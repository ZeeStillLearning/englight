<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

// If user is already logged in, redirect to their area
if (!empty($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: admin/admin-dashboard.php'); exit;
    } else {
        header('Location: dashboard.php'); exit;
    }
}

$page_title = 'Kuasai Bahasa Inggris Lebih Cepat';
require_once __DIR__ . '/includes/header_public.php';
?>

<section class="hero-bg relative overflow-hidden">
  <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
  <div class="absolute bottom-0 left-0 w-80 h-80 bg-[#F5A623]/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/3 pointer-events-none"></div>

  <div class="flex justify-center pt-10 px-4">
    <div class="badge-pulse bg-[#F5A623]/20 border border-[#F5A623]/40 text-[#F5A623] font-bold text-sm px-6 py-2 rounded-full backdrop-blur-sm">
      🎉 7 HARI Tryout Gratis!!
    </div>
  </div>

  <div class="max-w-7xl mx-auto px-4 md:px-8 lg:px-12 py-16 md:py-24">
    <div class="flex flex-col md:flex-row items-center gap-12 md:gap-16">
      <div class="flex-1 text-center md:text-left fade-up">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-6">
          <span class="gradient-text">Kuasai Bahasa<br>Inggris Lebih Cepat</span><br>
          <span class="text-white">&amp; Lebih Mudah</span>
        </h1>
        <p class="text-blue-200 text-base md:text-lg leading-relaxed mb-8 max-w-xl mx-auto md:mx-0">
          Platform pembelajaran bahasa Inggris berbasis AI yang fleksibel dan terstruktur. Materi lengkap, tryout TOEFL, forum diskusi aktif — semua dalam satu platform.
        </p>
        <div class="flex flex-wrap justify-center md:justify-start gap-6 mb-10">
          <div class="flex items-center gap-2 bg-white/10 rounded-2xl px-4 py-2.5 backdrop-blur-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="users" class="w-5 h-5 text-[#F5A623]"></svg>
            <div><div class="text-white font-bold text-sm">25.000+</div><div class="text-blue-300 text-xs">Pengguna Aktif</div></div>
          </div>
          <div class="flex items-center gap-2 bg-white/10 rounded-2xl px-4 py-2.5 backdrop-blur-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="book-open" class="w-5 h-5 text-[#F5A623]"></svg>
            <div><div class="text-white font-bold text-sm">180+</div><div class="text-blue-300 text-xs">Materi Tersedia</div></div>
          </div>
          <div class="flex items-center gap-2 bg-white/10 rounded-2xl px-4 py-2.5 backdrop-blur-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" class="w-5 h-5 text-[#F5A623]"></svg>
            <div><div class="text-white font-bold text-sm">4.9/5</div><div class="text-blue-300 text-xs">Rating Pengguna</div></div>
          </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
          <a href="register.php" class="w-full sm:w-auto px-8 py-4 bg-[#F5A623] text-white font-bold rounded-xl text-center hover:bg-[#FFB84D] transition-all shadow-lg hover:-translate-y-0.5">
            Mulai Belajar Gratis
          </a>
          <a href="index.php#features" class="w-full sm:w-auto px-8 py-4 bg-white/10 border border-white/30 text-white font-semibold rounded-xl text-center hover:bg-white/20 transition-all backdrop-blur-sm flex items-center justify-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="play-circle" class="w-5 h-5"></svg> Lihat Demo
          </a>
        </div>
      </div>
      <div class="flex-1 flex justify-center md:justify-end fade-up d2">
        <div class="floating relative">
          <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-6 w-72 md:w-80 shadow-2xl">
            <div class="bg-white/20 rounded-2xl h-40 mb-4 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="graduation-cap" class="w-20 h-20 text-white/80"></svg>
            </div>
            <div class="space-y-2">
              <div class="h-3 bg-white/30 rounded-full w-3/4"></div>
              <div class="h-3 bg-white/20 rounded-full w-full"></div>
              <div class="h-3 bg-white/20 rounded-full w-5/6"></div>
            </div>
            <div class="mt-4">
              <div class="flex justify-between text-xs text-white/70 mb-1"><span>Progress Belajar</span><span>72%</span></div>
              <div class="bg-white/20 rounded-full h-2"><div class="bg-[#F5A623] h-2 rounded-full w-[72%]"></div></div>
            </div>
          </div>
          <div class="absolute -top-4 -right-4 bg-[#F5A623] text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg">🎯 TOEFL Ready!</div>
          <div class="absolute -bottom-4 -left-4 bg-white text-[#1B3F8B] text-xs font-bold px-3 py-2 rounded-xl shadow-xl flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="trending-up" class="w-4 h-4 text-green-500"></svg> Skor naik 40 pts!
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="relative -mb-1">
    <svg viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="w-full h-12 md:h-16">
      <path d="M0,30 C360,60 1080,0 1440,30 L1440,60 L0,60 Z" fill="#F4F7FF"></path>
    </svg>
  </div>
</section>

<section id="features" class="bg-[#F4F7FF] py-20 md:py-28">
  <div class="max-w-7xl mx-auto px-4 md:px-8 lg:px-12">
    <div class="text-center mb-16">
      <span class="inline-block bg-[#1B3F8B]/10 text-[#1B3F8B] font-semibold text-sm px-4 py-1.5 rounded-full mb-4">Fitur Unggulan</span>
      <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">Semua Yang Kamu Butuhkan</h2>
      <p class="text-gray-500 max-w-xl mx-auto text-base">Satu platform lengkap untuk perjalanan belajar bahasa Inggrismu dari nol hingga TOEFL-ready.</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
      <article class="feat-card bg-white rounded-[24px] p-6 border border-gray-100 fade-up d1">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="play-circle" class="w-6 h-6 text-[#1B3F8B]"></svg>
        </div>
        <h3 class="font-bold text-gray-900 text-lg mb-2">Video Pembelajaran</h3>
        <p class="text-gray-500 text-sm leading-relaxed">Tonton video materi Listening, Structure &amp; Reading kapan saja, di mana saja dengan kualitas HD.</p>
        <div class="mt-4 flex items-center gap-1 text-[#1B3F8B] text-sm font-semibold">
          <span>100+ video tersedia</span>
        </div>
      </article>
      <article class="feat-card bg-white rounded-[24px] p-6 border border-gray-100 fade-up d2">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="clipboard-list" class="w-6 h-6 text-green-600"></svg>
        </div>
        <h3 class="font-bold text-gray-900 text-lg mb-2">Latihan Soal Adaptif</h3>
        <p class="text-gray-500 text-sm leading-relaxed">Ribuan soal latihan dengan Adaptive Learning — tingkat kesulitan menyesuaikan kemampuanmu secara otomatis.</p>
        <div class="mt-4 flex items-center gap-1 text-green-600 text-sm font-semibold"><span>5.000+ soal latihan</span></div>
      </article>
      <article class="feat-card bg-white rounded-[24px] p-6 border border-gray-100 fade-up d3">
        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="target" class="w-6 h-6 text-orange-500"></svg>
        </div>
        <h3 class="font-bold text-gray-900 text-lg mb-2">Tryout Simulasi TOEFL</h3>
        <p class="text-gray-500 text-sm leading-relaxed">Simulasi ujian nyata dengan timer otomatis, skor instan, sertifikat digital, dan analisis mendalam.</p>
        <div class="mt-4 flex items-center gap-1 text-orange-500 text-sm font-semibold"><span>Simulasi realistis</span></div>
      </article>
      <article class="feat-card bg-white rounded-[24px] p-6 border border-gray-100 fade-up d4">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="mic" class="w-6 h-6 text-red-500"></svg>
        </div>
        <h3 class="font-bold text-gray-900 text-lg mb-2">Speaking Practice AI</h3>
        <p class="text-gray-500 text-sm leading-relaxed">Latihan berbicara dengan AI yang menganalisis pronunciation &amp; grammar-mu secara real-time.</p>
        <div class="mt-4 flex items-center gap-1 text-red-500 text-sm font-semibold"><span>Feedback instan dari AI</span></div>
      </article>
      <article class="feat-card bg-white rounded-[24px] p-6 border border-gray-100 fade-up d5">
        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="message-circle" class="w-6 h-6 text-purple-600"></svg>
        </div>
        <h3 class="font-bold text-gray-900 text-lg mb-2">Forum Diskusi</h3>
        <p class="text-gray-500 text-sm leading-relaxed">Diskusikan materi, tanya jawab, dan belajar bersama komunitas aktif dengan sistem upvote &amp; moderasi admin.</p>
        <div class="mt-4 flex items-center gap-1 text-purple-600 text-sm font-semibold"><span>Komunitas aktif 24/7</span></div>
      </article>
      <article class="feat-card bg-white rounded-[24px] p-6 border border-gray-100 fade-up d6">
        <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="book-open" class="w-6 h-6 text-teal-600"></svg>
        </div>
        <h3 class="font-bold text-gray-900 text-lg mb-2">E-Book &amp; Progress Tracking</h3>
        <p class="text-gray-500 text-sm leading-relaxed">Akses e-book premium eksklusif dan pantau perkembangan belajarmu dengan XP, badge, dan laporan performa.</p>
        <div class="mt-4 flex items-center gap-1 text-teal-600 text-sm font-semibold"><span>50+ e-book eksklusif</span></div>
      </article>
    </div>
  </div>
</section>

<section id="how" class="bg-white py-20 md:py-28">
  <div class="max-w-7xl mx-auto px-4 md:px-8 lg:px-12">
    <div class="text-center mb-16">
      <span class="inline-block bg-[#1B3F8B]/10 text-[#1B3F8B] font-semibold text-sm px-4 py-1.5 rounded-full mb-4">Cara Kerja</span>
      <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">Mulai Belajar dalam 4 Langkah</h2>
      <p class="text-gray-500 max-w-xl mx-auto">Proses yang mudah dan terstruktur dari registrasi akun hingga meraih sertifikat resmi.</p>
    </div>
    <div class="relative">
      <div class="hidden md:block absolute top-12 left-[16%] right-[16%] h-1 step-line rounded-full z-0"></div>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 md:gap-4 relative z-10">
        <div class="flex flex-col items-center text-center group">
          <div class="w-24 h-24 bg-[#1B3F8B] rounded-2xl flex flex-col items-center justify-center mb-5 shadow-lg group-hover:scale-105 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="user-plus" class="w-10 h-10 text-white mb-1"></svg>
            <span class="text-white/70 text-xs font-bold">01</span>
          </div>
          <h3 class="font-bold text-gray-900 text-lg mb-2">Daftar Gratis</h3>
          <p class="text-gray-500 text-sm leading-relaxed">Buat akun dalam 1 menit — tanpa kartu kredit, tanpa komitmen.</p>
        </div>
        <div class="flex flex-col items-center text-center group">
          <div class="w-24 h-24 bg-white border-2 border-[#1B3F8B] rounded-2xl flex flex-col items-center justify-center mb-5 shadow-md group-hover:bg-[#1B3F8B] transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="layout-grid" class="w-10 h-10 text-[#1B3F8B] group-hover:text-white transition-colors mb-1"></svg>
            <span class="text-[#1B3F8B]/70 group-hover:text-white/70 text-xs font-bold transition-colors">02</span>
          </div>
          <h3 class="font-bold text-gray-900 text-lg mb-2">Pilih Materi</h3>
          <p class="text-gray-500 text-sm leading-relaxed">Pilih sesuai levelmu — dari Grammar dasar hingga TOEFL advanced.</p>
        </div>
        <div class="flex flex-col items-center text-center group">
          <div class="w-24 h-24 bg-white border-2 border-[#1B3F8B] rounded-2xl flex flex-col items-center justify-center mb-5 shadow-md group-hover:bg-[#1B3F8B] transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="pencil-ruler" class="w-10 h-10 text-[#1B3F8B] group-hover:text-white transition-colors mb-1"></svg>
            <span class="text-[#1B3F8B]/70 group-hover:text-white/70 text-xs font-bold transition-colors">03</span>
          </div>
          <h3 class="font-bold text-gray-900 text-lg mb-2">Berlatih &amp; Tryout</h3>
          <p class="text-gray-500 text-sm leading-relaxed">Kerjakan soal dan simulasi TOEFL untuk mengukur kemampuanmu.</p>
        </div>
        <div class="flex flex-col items-center text-center group">
          <div class="w-24 h-24 bg-[#F5A623] rounded-2xl flex flex-col items-center justify-center mb-5 shadow-lg group-hover:scale-105 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="award" class="w-10 h-10 text-white mb-1"></svg>
            <span class="text-white/70 text-xs font-bold">04</span>
          </div>
          <h3 class="font-bold text-gray-900 text-lg mb-2">Raih Sertifikat</h3>
          <p class="text-gray-500 text-sm leading-relaxed">Dapatkan sertifikat digital resmi yang bisa kamu share ke LinkedIn!</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="pricing" class="bg-white py-20 md:py-28">
  <div class="max-w-7xl mx-auto px-4 md:px-8 lg:px-12">
    <div class="text-center mb-16">
      <span class="inline-block bg-[#1B3F8B]/10 text-[#1B3F8B] font-semibold text-sm px-4 py-1.5 rounded-full mb-4">Harga Paket</span>
      <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">Pilih Paket Sesuai Kebutuhanmu</h2>
      <p class="text-gray-500 max-w-xl mx-auto">Mulai gratis, upgrade kapan saja. Tidak ada biaya tersembunyi.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div class="price-card bg-[#F4F7FF] border border-gray-200 rounded-[24px] p-8 flex flex-col">
        <div class="mb-6">
          <div class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Free</div>
          <div class="text-4xl font-black text-gray-900 mb-1">Rp 0</div>
          <div class="text-gray-500 text-sm">/ Bulan — Selamanya Gratis</div>
        </div>
        <ul class="space-y-3 mb-8 flex-1 text-sm">
          <li class="flex items-center gap-2 text-gray-700"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></svg> Akses materi dasar (full)</li>
          <li class="flex items-center gap-2 text-gray-700"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></svg> 50 soal latihan/bulan</li>
          <li class="flex items-center gap-2 text-gray-700"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></svg> Forum diskusi</li>
          <li class="flex items-center gap-2 text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="x" class="w-4 h-4 text-gray-300 shrink-0"></svg> Materi lanjutan</li>
          <li class="flex items-center gap-2 text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="x" class="w-4 h-4 text-gray-300 shrink-0"></svg> Tryout TOEFL (terbatas)</li>
        </ul>
        <a href="register.php" class="block text-center py-3 px-6 border-2 border-[#1B3F8B] text-[#1B3F8B] font-semibold rounded-xl hover:bg-[#1B3F8B] hover:text-white transition-all">Mulai Gratis</a>
      </div>
      <div class="price-card bg-[#1B3F8B] rounded-[24px] p-8 flex flex-col relative shadow-2xl shadow-[#1B3F8B]/30">
        <div class="absolute -top-4 left-1/2 -translate-x-1/2">
          <span class="bg-[#F5A623] text-white text-xs font-bold px-4 py-1.5 rounded-full whitespace-nowrap shadow-lg">⭐ Paling Populer</span>
        </div>
        <div class="mb-6">
          <div class="text-sm font-semibold text-blue-300 uppercase tracking-wider mb-2">Premium</div>
          <div class="text-4xl font-black text-white mb-1">Rp 50.000</div>
          <div class="text-blue-300 text-sm">/ Bulan (dasar)</div>
        </div>
        <ul class="space-y-3 mb-8 flex-1 text-sm">
          <li class="flex items-center gap-2 text-white"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" class="w-4 h-4 text-[#F5A623] shrink-0"></svg> Akses materi dasar &amp; lanjutan</li>
          <li class="flex items-center gap-2 text-white"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" class="w-4 h-4 text-[#F5A623] shrink-0"></svg> Latihan soal tak terbatas</li>
          <li class="flex items-center gap-2 text-white"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" class="w-4 h-4 text-[#F5A623] shrink-0"></svg> Progress tracking lengkap</li>
          <li class="flex items-center gap-2 text-white"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" class="w-4 h-4 text-[#F5A623] shrink-0"></svg> Sertifikat kelulusan</li>
        </ul>
        <a href="register.php" class="block text-center py-3 px-6 bg-[#F5A623] text-white font-bold rounded-xl hover:bg-[#FFB84D] transition-all shadow-lg shadow-[#F5A623]/40">Mulai Premium</a>
      </div>
      <div class="price-card bg-[#F4F7FF] border border-gray-200 rounded-[24px] p-8 flex flex-col relative">
        <div class="absolute -top-4 left-1/2 -translate-x-1/2">
          <span class="bg-green-500 text-white text-xs font-bold px-4 py-1.5 rounded-full whitespace-nowrap shadow-lg">💰 Hemat 30%</span>
        </div>
        <div class="mb-6">
          <div class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Pro (Add-on)</div>
          <div class="text-4xl font-black text-gray-900 mb-1">Rp 199.000</div>
          <div class="text-gray-500 text-sm">/ Bulan <span class="line-through text-gray-400">Rp 300.000</span></div>
        </div>
        <ul class="space-y-3 mb-8 flex-1 text-sm">
          <li class="flex items-center gap-2 text-gray-700"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></svg> Semua fitur Premium</li>
          <li class="flex items-center gap-2 text-gray-700"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></svg> Speaking AI 10 sesi/bulan</li>
          <li class="flex items-center gap-2 text-gray-700"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></svg> Tryout TOEFL 10 sesi/bulan</li>
          <li class="flex items-center gap-2 text-gray-700"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></svg> Semua E-book premium</li>
          <li class="flex items-center gap-2 text-gray-700"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></svg> Prioritas dukungan 24/7</li>
        </ul>
        <a href="register.php" class="block text-center py-3 px-6 border-2 border-[#1B3F8B] text-[#1B3F8B] font-semibold rounded-xl hover:bg-[#1B3F8B] hover:text-white transition-all">Mulai Pro</a>
      </div>
    </div>
  </div>
</section>

<section class="cta-bg py-20 md:py-24 relative overflow-hidden">
  <div class="absolute inset-0 overflow-hidden pointer-events-none">
    <div class="absolute -top-20 -right-20 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-[#F5A623]/10 rounded-full blur-3xl"></div>
  </div>
  <div class="max-w-3xl mx-auto px-4 md:px-8 text-center relative z-10">
    <h2 class="text-3xl md:text-5xl font-black text-white mb-5 leading-tight">Siap Meningkatkan Skill<br>Bahasa Inggrismu?</h2>
    <p class="text-blue-200 text-base md:text-lg mb-10 max-w-xl mx-auto">Bergabunglah dengan 25.000+ pengguna EngLight dan mulai perjalanan belajarmu hari ini. Daftar gratis tanpa kartu kredit!</p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="register.php" class="px-10 py-4 bg-[#F5A623] text-white font-bold rounded-xl hover:bg-[#FFB84D] transition-all shadow-lg hover:-translate-y-0.5 hover:shadow-xl">Daftar Sekarang Gratis</a>
      <a href="index.php#features" class="px-10 py-4 bg-white/10 border border-white/30 text-white font-semibold rounded-xl hover:bg-white/20 transition-all backdrop-blur-sm">Lihat Fitur Platform</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer_public.php'; ?>
