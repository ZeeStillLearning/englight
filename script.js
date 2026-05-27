/* EngLight — Shared JavaScript
   Contains:
   1. Lucide icons initialisation
   2. Landing page: hamburger menu + smooth scroll + fade-up observer
   3. Login/Register page: tab switching + static login logic
   4. Forum page: new-post modal helpers
*/

(function () {
  'use strict';

  /* ── 1. Lucide icons ── */
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }

  /* ── 2. Landing page ── */
  const hamburger  = document.getElementById('hamburger');
  const mobileMenu = document.getElementById('mobile-menu');

  if (hamburger && mobileMenu) {
    hamburger.addEventListener('click', function () {
      mobileMenu.classList.toggle('open');
      const icon = hamburger.querySelector('[data-lucide]');
      if (icon) {
        icon.setAttribute('data-lucide', mobileMenu.classList.contains('open') ? 'x' : 'menu');
        if (typeof lucide !== 'undefined') lucide.createIcons();
      }
    });

    mobileMenu.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () {
        mobileMenu.classList.remove('open');
        const icon = hamburger.querySelector('[data-lucide]');
        if (icon) {
          icon.setAttribute('data-lucide', 'menu');
          if (typeof lucide !== 'undefined') lucide.createIcons();
        }
      });
    });
  }

  /* Smooth scroll for in-page anchor links */
  document.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      const href = a.getAttribute('href');
      if (href === '#' || href === '') return;
      const target = document.querySelector(href);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  /* Fade-up intersection observer */
  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.style.animationPlayState = 'running';
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15 });

  document.querySelectorAll('.fade-up').forEach(function (el) {
    if (!el.style.animationPlayState) {
      el.style.animationPlayState = 'paused';
    }
    observer.observe(el);
  });

  /* ── 3. Login / Register tab switching ── */
  window.showTab = function (tab) {
    const isLogin = tab === 'login';
    const formLogin    = document.getElementById('form-login');
    const formRegister = document.getElementById('form-register');
    const tabLogin     = document.getElementById('tab-login');
    const tabRegister  = document.getElementById('tab-register');

    if (!formLogin || !formRegister) return;

    formLogin.classList.toggle('hidden', !isLogin);
    formRegister.classList.toggle('hidden', isLogin);

    const activeClass   = 'flex-1 py-2 rounded-lg text-sm font-bold transition-all bg-white text-[#1B3F8B] shadow-sm';
    const inactiveClass = 'flex-1 py-2 rounded-lg text-sm font-bold transition-all text-gray-500';

    if (tabLogin)    tabLogin.className    = isLogin ? activeClass : inactiveClass;
    if (tabRegister) tabRegister.className = !isLogin ? activeClass : inactiveClass;

    if (typeof lucide !== 'undefined') lucide.createIcons();
  };

  /* ── Static login logic ── */
  const loginForm = document.getElementById('login-form');
  if (loginForm) {
    loginForm.addEventListener('submit', function (event) {
      event.preventDefault();

      const email    = (loginForm.querySelector('input[name="email"]') || {}).value || '';
      const password = (loginForm.querySelector('input[name="password"]') || {}).value || '';

      if (email === 'admin@englight.id' && password === 'password') {
        window.location.href = 'admin-dashboard.html';
      } else if (email === 'budi@student.com' && password === 'password') {
        window.location.href = 'dashboard.html';
      } else {
        alert('Email atau password salah. Silakan coba lagi.');
      }
    });
  }

  /* ── 4. Forum new-post modal ── */
  window.openNewPost = function () {
    const modal = document.getElementById('new-post-modal');
    if (modal) modal.classList.remove('hidden');
  };

  window.closeNewPost = function () {
    const modal = document.getElementById('new-post-modal');
    if (modal) modal.classList.add('hidden');
  };

})();
