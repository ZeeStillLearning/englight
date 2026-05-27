<?php
// includes/footer_public.php — shared footer for public pages
?>
<footer class="bg-gray-950 text-gray-400 pt-16 pb-8">
  <div class="max-w-7xl mx-auto px-4 md:px-8 lg:px-12">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-10 mb-12">
      <div class="col-span-2 md:col-span-1">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-8 h-8 bg-[#1B3F8B] rounded-lg flex items-center justify-center"><span class="text-white font-black text-sm">E</span></div>
          <span class="text-white font-black text-xl tracking-tight">EngLight</span>
        </div>
        <p class="text-sm leading-relaxed mb-5">Platform pembelajaran bahasa Inggris berbasis AI yang fleksibel, terstruktur, dan terjangkau untuk semua kalangan.</p>
        <div class="flex gap-3">
          <a href="#" class="w-9 h-9 bg-white/10 rounded-lg flex items-center justify-center hover:bg-[#1B3F8B] transition-colors"><i data-lucide="instagram" class="w-4 h-4 text-white"></i></a>
          <a href="#" class="w-9 h-9 bg-white/10 rounded-lg flex items-center justify-center hover:bg-[#1B3F8B] transition-colors"><i data-lucide="twitter" class="w-4 h-4 text-white"></i></a>
          <a href="#" class="w-9 h-9 bg-white/10 rounded-lg flex items-center justify-center hover:bg-[#1B3F8B] transition-colors"><i data-lucide="youtube" class="w-4 h-4 text-white"></i></a>
        </div>
      </div>
      <div>
        <h4 class="text-white font-bold text-sm mb-4">Fitur</h4>
        <ul class="space-y-2 text-sm">
          <li><a href="#" class="hover:text-white transition-colors">Video Pembelajaran</a></li>
          <li><a href="#" class="hover:text-white transition-colors">Latihan Soal Adaptif</a></li>
          <li><a href="#" class="hover:text-white transition-colors">Simulasi TOEFL</a></li>
          <li><a href="#" class="hover:text-white transition-colors">Speaking Practice AI</a></li>
          <li><a href="#" class="hover:text-white transition-colors">Forum Diskusi</a></li>
        </ul>
      </div>
      <div>
        <h4 class="text-white font-bold text-sm mb-4">Platform</h4>
        <ul class="space-y-2 text-sm">
          <li><a href="index.php#pricing" class="hover:text-white transition-colors">Harga Paket</a></li>
          <li><a href="#" class="hover:text-white transition-colors">E-Book Premium</a></li>
          <li><a href="#" class="hover:text-white transition-colors">Blog</a></li>
        </ul>
      </div>
      <div>
        <h4 class="text-white font-bold text-sm mb-4">Support</h4>
        <ul class="space-y-2 text-sm">
          <li><a href="#" class="hover:text-white transition-colors">Kontak</a></li>
          <li><a href="#" class="hover:text-white transition-colors">Pusat Bantuan</a></li>
          <li><a href="#" class="hover:text-white transition-colors">Kebijakan Privasi</a></li>
          <li><a href="#" class="hover:text-white transition-colors">Syarat &amp; Ketentuan</a></li>
        </ul>
      </div>
    </div>
    <div class="border-t border-white/10 pt-8 text-center text-sm">
      &copy; <?= date('Y') ?> EngLight — Universitas Sebelas Maret. Dibuat dengan ❤️ untuk pelajar Indonesia.
    </div>
  </div>
</footer>

<script>
  lucide.createIcons();
  const btn  = document.getElementById('hamburger');
  const menu = document.getElementById('mobile-menu');
  if (btn && menu) {
    btn.addEventListener('click', () => {
      menu.classList.toggle('open');
      const icon = btn.querySelector('[data-lucide]');
      if (icon) {
        icon.setAttribute('data-lucide', menu.classList.contains('open') ? 'x' : 'menu');
        lucide.createIcons();
      }
    });
    menu.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => {
        menu.classList.remove('open');
        const icon = btn.querySelector('[data-lucide]');
        if (icon) { icon.setAttribute('data-lucide','menu'); lucide.createIcons(); }
      });
    });
  }
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) { e.preventDefault(); target.scrollIntoView({ behavior:'smooth', block:'start' }); }
    });
  });
  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.style.animationPlayState='running'; observer.unobserve(e.target); } });
  }, { threshold: 0.15 });
  document.querySelectorAll('.fade-up').forEach(el => { el.style.animationPlayState='paused'; observer.observe(el); });
</script>
<script src="script.js"></script>
</body>
</html>
