/* Micro Studio — home page interactions (vanilla JS, no dependencies) */
(() => {
  'use strict';

  const doc = document.documentElement;
  doc.classList.add('js');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const $ = (s, root = document) => root.querySelector(s);
  const $$ = (s, root = document) => Array.from(root.querySelectorAll(s));
  const faDigits = (n) => String(n).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);

  /* ---------- header state + back-to-top ---------- */
  const header = $('.site-header');
  const toTop = $('.to-top');
  const onScroll = () => {
    const y = window.scrollY;
    header.classList.toggle('scrolled', y > 40);
    toTop.classList.toggle('show', y > 700);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ---------- mobile navigation (off-canvas, Esc + backdrop close, focus return) ---------- */
  const nav = $('#main-nav');
  const toggle = $('.nav-toggle');
  let backdrop = null;
  const setNav = (open) => {
    nav.classList.toggle('open', open);
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'بستن منو' : 'باز کردن منو');
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) {
      backdrop = document.createElement('div');
      backdrop.className = 'nav-backdrop';
      backdrop.addEventListener('click', () => setNav(false));
      document.body.appendChild(backdrop);
      $('a', nav).focus();
    } else if (backdrop) {
      backdrop.remove();
      backdrop = null;
    }
  };
  toggle.addEventListener('click', () => setNav(!nav.classList.contains('open')));
  $$('a', nav).forEach((a) => a.addEventListener('click', () => nav.classList.contains('open') && setNav(false)));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && nav.classList.contains('open')) { setNav(false); toggle.focus(); }
  });
  window.matchMedia('(min-width: 901px)').addEventListener('change', (m) => m.matches && setNav(false));

  /* ---------- active menu item while scrolling ---------- */
  const navLinks = $$('.main-nav ul a');
  const sections = navLinks.map((a) => a.hash && $(a.hash)).filter(Boolean);
  if ('IntersectionObserver' in window && sections.length) {
    const spy = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (!en.isIntersecting) return;
        navLinks.forEach((a) => a.removeAttribute('aria-current'));
        const hit = navLinks.find((a) => a.hash === '#' + en.target.id);
        if (hit) hit.setAttribute('aria-current', 'true');
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    sections.forEach((s) => spy.observe(s));
    new IntersectionObserver(([en]) => {
      if (en.isIntersecting) {
        navLinks.forEach((a) => a.removeAttribute('aria-current'));
        navLinks[0].setAttribute('aria-current', 'page');
      }
    }, { threshold: 0.4 }).observe($('.hero'));
  }

  /* ---------- reveal on scroll ---------- */
  const revealEls = $$('.reveal, .reveal-up');
  if (reduceMotion || !('IntersectionObserver' in window)) {
    revealEls.forEach((el) => el.classList.add('in'));
  } else {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });
    revealEls.forEach((el) => io.observe(el));
  }

  /* ---------- count-up numbers (Persian digits) ---------- */
  const counters = $$('[data-count]');
  if (!reduceMotion && 'IntersectionObserver' in window) {
    const cio = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (!en.isIntersecting) return;
        const el = en.target;
        const end = Number(el.dataset.count);
        const t0 = performance.now();
        const dur = 1400;
        const step = (t) => {
          const p = Math.min(1, (t - t0) / dur);
          el.textContent = faDigits(Math.round(end * (1 - Math.pow(1 - p, 3))));
          if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
        cio.unobserve(el);
      });
    }, { threshold: 0.6 });
    counters.forEach((el) => { el.textContent = faDigits(0); cio.observe(el); });
  }

  /* ---------- parallax backgrounds ---------- */
  const parallax = $$('[data-parallax]');
  if (!reduceMotion && parallax.length) {
    let ticking = false;
    const update = () => {
      const vh = window.innerHeight;
      parallax.forEach((el) => {
        const r = el.parentElement.getBoundingClientRect();
        if (r.bottom < 0 || r.top > vh) return;
        const offset = (r.top + r.height / 2 - vh / 2) * Number(el.dataset.parallax);
        el.style.transform = `translate3d(0, ${offset.toFixed(1)}px, 0)`;
      });
      ticking = false;
    };
    window.addEventListener('scroll', () => { if (!ticking) { ticking = true; requestAnimationFrame(update); } }, { passive: true });
    update();
  }

  /* ---------- horizontal scrollers (services slider + works rail) ----------
     In RTL, scrollLeft runs from 0 (start, right edge) to negative values in modern browsers.
     We work with the absolute distance from the start so the logic is direction-agnostic. */
  const initScroller = (root, trackSel) => {
    const track = $(trackSel, root);
    if (!track) return;
    const prev = $('[data-dir="prev"]', root);
    const next = $('[data-dir="next"]', root);
    const bar = $('.rail-progress span', root);
    const isRTL = getComputedStyle(track).direction === 'rtl';
    const sign = isRTL ? -1 : 1;
    const maxScroll = () => track.scrollWidth - track.clientWidth;
    const pos = () => Math.abs(track.scrollLeft);
    const stepSize = () => {
      const card = track.firstElementChild;
      const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
      return card ? card.getBoundingClientRect().width + gap : track.clientWidth * 0.8;
    };
    const refresh = () => {
      const max = maxScroll();
      if (prev) prev.disabled = pos() <= 2;
      if (next) next.disabled = pos() >= max - 2;
      if (bar) {
        const visible = track.clientWidth / track.scrollWidth;
        bar.style.width = `${Math.max(12, (visible + (1 - visible) * (max ? pos() / max : 0)) * 100)}%`;
      }
    };
    const go = (dir) => track.scrollBy({ left: dir * sign * stepSize(), behavior: reduceMotion ? 'auto' : 'smooth' });
    if (prev) prev.addEventListener('click', () => go(-1));
    if (next) next.addEventListener('click', () => go(1));
    track.addEventListener('scroll', refresh, { passive: true });
    window.addEventListener('resize', refresh);
    track.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') { e.preventDefault(); go(isRTL ? 1 : -1); }
      if (e.key === 'ArrowRight') { e.preventDefault(); go(isRTL ? -1 : 1); }
    });

    /* mouse drag-to-scroll (touch already scrolls natively) */
    let startX = 0, startLeft = 0, dragging = false, moved = false;
    track.addEventListener('pointerdown', (e) => {
      if (e.pointerType !== 'mouse' || e.button !== 0) return;
      dragging = true; moved = false; startX = e.clientX; startLeft = track.scrollLeft;
    });
    window.addEventListener('pointermove', (e) => {
      if (!dragging) return;
      const dx = e.clientX - startX;
      if (!moved && Math.abs(dx) > 5) { moved = true; track.classList.add('dragging'); }
      if (moved) track.scrollLeft = startLeft - dx;
    });
    window.addEventListener('pointerup', () => {
      if (!dragging) return;
      dragging = false;
      track.classList.remove('dragging');
    });
    track.addEventListener('click', (e) => { if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; } }, true);
    track.addEventListener('dragstart', (e) => e.preventDefault());
    refresh();
  };
  $$('[data-slider]').forEach((el) => initScroller(el, '.slider-track'));
  $$('[data-rail]').forEach((el) => initScroller(el, '.rail-track'));

  /* ---------- testimonial marquee: duplicate cards for a seamless loop ---------- */
  if (!reduceMotion) {
    $$('[data-marquee] .testi-track').forEach((track) => {
      Array.from(track.children).forEach((card) => {
        const clone = card.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        track.appendChild(clone);
      });
    });
  }

  /* ---------- video lightbox with focus trap ---------- */
  const lb = $('#lightbox');
  const video = $('video', lb);
  const closeBtn = $('.lb-close', lb);
  let lastFocus = null;
  const openLB = (src) => {
    lastFocus = document.activeElement;
    video.src = src;
    lb.hidden = false;
    document.body.style.overflow = 'hidden';
    closeBtn.focus();
    video.play().catch(() => {});
  };
  const closeLB = () => {
    video.pause();
    video.removeAttribute('src');
    video.load();
    lb.hidden = true;
    document.body.style.overflow = '';
    if (lastFocus) lastFocus.focus();
  };
  $$('[data-video]').forEach((b) => b.addEventListener('click', () => openLB(b.dataset.video)));
  closeBtn.addEventListener('click', closeLB);
  lb.addEventListener('click', (e) => { if (e.target === lb) closeLB(); });
  document.addEventListener('keydown', (e) => {
    if (lb.hidden) return;
    if (e.key === 'Escape') closeLB();
    if (e.key === 'Tab') {
      const items = [closeBtn, video];
      const i = items.indexOf(document.activeElement);
      e.preventDefault();
      items[(i + (e.shiftKey ? items.length - 1 : 1)) % items.length].focus();
    }
  });
  /* ---------- contact form: inline validation (no backend connected yet) ---------- */
  const form = $('#contact-form');
  if (form) {
    const status = $('.form-status', form);
    const rules = {
      name: (v) => v.trim().length >= 2 || 'لطفاً نام خود را وارد کنید.',
      email: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()) || 'ایمیل معتبر نیست؛ مثال: name@mail.com',
      phone: (v) => !v.trim() || /^[0-9۰-۹+\s()-]{8,}$/.test(v.trim()) || 'شماره تماس معتبر نیست.',
      message: (v) => v.trim().length >= 10 || 'پیام باید دست‌کم ۱۰ حرف باشد.',
    };
    const check = (input) => {
      const rule = rules[input.name];
      if (!rule) return true;
      const res = rule(input.value);
      const field = input.closest('.field');
      const ok = res === true;
      field.classList.toggle('invalid', !ok);
      input.setAttribute('aria-invalid', String(!ok));
      $('.error', field).textContent = ok ? '' : res;
      return ok;
    };
    $$('input[name], textarea[name]', form).forEach((input) => {
      input.addEventListener('blur', () => { if (input.value) check(input); });
      input.addEventListener('input', () => {
        const field = input.closest('.field');
        if (field && field.classList.contains('invalid')) check(input);
      });
    });
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const bad = $$('input[name], textarea[name]', form).filter((i) => rules[i.name] && !check(i));
      if (bad.length) {
        status.className = 'form-status err';
        status.textContent = 'لطفاً موارد مشخص‌شده را اصلاح کنید.';
        bad[0].focus();
        return;
      }
      const btn = $('button[type="submit"]', form);
      btn.disabled = true;
      status.className = 'form-status';
      status.textContent = 'در حال ارسال…';
      // Placeholder: wire this to the real endpoint once the backend/contact page exists.
      setTimeout(() => {
        status.textContent = 'درخواست شما ثبت شد؛ به‌زودی با شما تماس می‌گیریم.';
        form.reset();
        btn.disabled = false;
      }, 700);
    });
  }

  /* ---------- footer year (Solar Hijri, Persian digits) ---------- */
  const year = $('#year');
  if (year) {
    try {
      year.textContent = new Intl.DateTimeFormat('fa-IR-u-ca-persian', { year: 'numeric' }).format(new Date());
    } catch (_) { /* keep static fallback */ }
  }
})();
