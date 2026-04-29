/* ============================================================
   KABAYAN MUNICIPALITY — Global JavaScript
   ============================================================ */

(function () {
  'use strict';

  // ─── Navbar: scroll state + mobile toggle ──────────────────
  const navbar = document.querySelector('.navbar');

  function updateNavbar() {
    if (!navbar) return;
    navbar.classList.toggle('scrolled', window.scrollY > 40);
  }

  if (navbar) {
    window.addEventListener('scroll', updateNavbar, { passive: true });
    updateNavbar();

    // Mobile toggle
    const toggle = navbar.querySelector('.navbar-toggle');
    const nav    = navbar.querySelector('.navbar-nav');
    if (toggle && nav) {
      toggle.addEventListener('click', () => {
        const open = nav.classList.toggle('open');
        toggle.setAttribute('aria-expanded', open);
        toggle.innerHTML = open
          ? '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>'
          : '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>';
      });
    }

    // Highlight active nav link
    const currentPath = window.location.pathname.split('/').pop() || 'index.php';
    navbar.querySelectorAll('.navbar-nav a').forEach(a => {
      const href = a.getAttribute('href') || '';
      if (href === currentPath || (currentPath === '' && href === 'index.php')) {
        a.classList.add('active');
      }
    });
  }

  // ─── Scroll-reveal ─────────────────────────────────────────
  const revealTargets = document.querySelectorAll('.reveal');

  if (revealTargets.length && 'IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry, i) => {
        if (entry.isIntersecting) {
          // Stagger siblings by 80ms
          const siblings = [...entry.target.parentElement.querySelectorAll('.reveal')];
          const idx = siblings.indexOf(entry.target);
          setTimeout(() => entry.target.classList.add('visible'), idx * 80);
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });

    revealTargets.forEach(el => io.observe(el));
  } else {
    // Fallback for old browsers
    revealTargets.forEach(el => el.classList.add('visible'));
  }

  // ─── Auto-reveal cards & sections ──────────────────────────
  // Add .reveal to common elements that weren't hand-coded with it
  document.addEventListener('DOMContentLoaded', () => {
    const autoReveal = document.querySelectorAll(
      '.destination-card, .service-card, .faq-item, .mv-card, .history-block, .chart-card'
    );
    if ('IntersectionObserver' in window) {
      const io2 = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.animationPlayState = 'running';
            entry.target.classList.add('in-view');
            io2.unobserve(entry.target);
          }
        });
      }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

      autoReveal.forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(24px)';
        el.style.transition = `opacity .55s cubic-bezier(.25,.1,.25,1) ${(i % 4) * 90}ms, transform .55s cubic-bezier(.25,.1,.25,1) ${(i % 4) * 90}ms`;
        io2.observe(el);
      });

      document.addEventListener('scroll', () => {}, { passive: true });
    }

    // CSS class toggle for in-view
    document.querySelectorAll('.in-view').forEach(el => {
      el.style.opacity = '1';
      el.style.transform = 'none';
    });
  });

  // ─── Fix: apply in-view styles via MutationObserver ────────
  const inViewObserver = new MutationObserver(mutations => {
    mutations.forEach(m => {
      m.addedNodes.forEach(node => {
        if (node.classList && node.classList.contains('in-view')) {
          node.style.opacity = '1';
          node.style.transform = 'none';
        }
      });
    });
  });

  const bodyEl = document.querySelector('body');
  if (bodyEl) inViewObserver.observe(bodyEl, { subtree: true, childList: true, attributes: true, attributeFilter: ['class'] });

  // Actually apply in-view changes through a simpler approach
  if ('IntersectionObserver' in window) {
    const cardIO = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
          cardIO.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });

    document.querySelectorAll('.destination-card, .service-card, .faq-item, .mv-card, .history-block').forEach((el, i) => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(22px)';
      el.style.transition = `opacity .5s ease ${(i % 4) * 85}ms, transform .5s ease ${(i % 4) * 85}ms`;
      cardIO.observe(el);
    });
  }

  // ─── FAQ accordion ──────────────────────────────────────────
  // (Already handled via CSS + onclick in faqs.php;
  //  this provides keyboard accessibility as a bonus)
  document.querySelectorAll('.faq-item').forEach(item => {
    item.setAttribute('tabindex', '0');
    item.setAttribute('role', 'button');
    item.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        item.classList.toggle('open');
      }
    });
  });

  // ─── Gallery carousel (supports index.php carousel) ────────
  // Handled inline in index.php; this adds touch/swipe support
  const track = document.getElementById('galleryTrack');
  if (track) {
    let startX = 0;
    let isDragging = false;

    track.addEventListener('touchstart', e => {
      startX = e.touches[0].clientX;
      isDragging = true;
    }, { passive: true });

    track.addEventListener('touchend', e => {
      if (!isDragging) return;
      const diff = startX - e.changedTouches[0].clientX;
      if (Math.abs(diff) > 40) {
        const btn = diff > 0
          ? document.getElementById('galNext')
          : document.getElementById('galPrev');
        if (btn) btn.click();
      }
      isDragging = false;
    }, { passive: true });
  }

  // ─── Smooth anchor scroll ───────────────────────────────────
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) {
        e.preventDefault();
        const offset = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--nav-h')) || 72;
        const top = target.getBoundingClientRect().top + window.scrollY - offset - 12;
        window.scrollTo({ top, behavior: 'smooth' });
      }
    });
  });

  // ─── Hero search: autofocus on load if query present ────────
  const searchInput = document.querySelector('.hero-search input, .search-form input');
  if (searchInput && searchInput.value) {
    searchInput.focus();
    searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
  }

  // ─── Image lazy-load fallback ────────────────────────────────
  document.querySelectorAll('img').forEach(img => {
    img.setAttribute('loading', 'lazy');
    img.addEventListener('error', () => {
      img.style.background = 'var(--ivory-2)';
      img.alt = img.alt || '';
    });
  });

  // ─── Announcement band auto-marquee on mobile ────────────────
  const annText = document.querySelector('.announcement-text');
  if (annText && window.innerWidth < 600) {
    annText.style.overflow = 'hidden';
  }

})();
