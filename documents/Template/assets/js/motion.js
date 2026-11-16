/* Micro Studio — motion engine (HTML5 + vanilla JS, no libraries)
   - [data-split]  headings: words fly in from outside, blur -> sharp, and assemble
   - .stagger      children cascade in one after another
   - .wipe         images revealed with a diagonal clip-path wipe
   - [data-speed]  scroll parallax on Y   (negative = moves up faster than the page)
   - [data-x]      starts offset on X and slides into place as it enters the viewport
   - [data-rot]    rotates with scroll
   - [data-sx]     big words drift horizontally with scroll
   - [data-depth]  follows the mouse (depth parallax)
   - [data-magnetic] buttons pulled toward the cursor
   - custom cursor, 3D card tilt, scroll progress bar, intro curtain
   Everything is disabled under prefers-reduced-motion; touch devices get scroll effects only. */
(() => {
  'use strict';

  const doc = document.documentElement;
  const $ = (s, root = document) => root.querySelector(s);
  const $$ = (s, root = document) => Array.from(root.querySelectorAll(s));
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  const clamp = (v, a, b) => Math.min(b, Math.max(a, v));

  /* ---------- intro curtain ---------- */
  const intro = $('.intro');
  let introDone = false;
  const endIntro = () => {
    if (introDone) return;
    introDone = true;
    doc.classList.add('loaded');
    if (intro) setTimeout(() => intro.remove(), 1300);
  };
  if (reduceMotion) endIntro();
  else if (document.readyState === 'complete') setTimeout(endIntro, 300);
  else {
    window.addEventListener('load', () => setTimeout(endIntro, 300));
    setTimeout(endIntro, 3000); /* never block the page on slow images */
  }

  /* ---------- split headings into words (keeps <br> and nested .outline/.grad spans) ---------- */
  const splitWords = (root) => {
    let i = 0;
    const walk = (node) => {
      Array.from(node.childNodes).forEach((child) => {
        if (child.nodeType === 3) {
          const frag = document.createDocumentFragment();
          child.textContent.split(/(\s+)/).forEach((part) => {
            if (!part) return;
            if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(' ')); return; }
            const w = document.createElement('span');
            w.className = 'w';
            const inner = document.createElement('span');
            inner.className = 'wi';
            inner.textContent = part;
            inner.style.setProperty('--i', i++);
            w.appendChild(inner);
            frag.appendChild(w);
          });
          child.replaceWith(frag);
        } else if (child.nodeType === 1 && child.tagName !== 'BR') {
          walk(child);
        }
      });
    };
    /* accessible name: join block-level line spans / <br> with spaces so words never run together */
    const label = Array.from(root.childNodes).map((n) => (n.nodeName === 'BR' ? ' ' : n.textContent)).join(' ');
    root.setAttribute('aria-label', label.replace(/\s+/g, ' ').trim());
    walk(root);
    root.classList.add('split');
  };
  const splitEls = $$('[data-split]');
  splitEls.forEach(splitWords);

  const staggerGroups = $$('.stagger');
  staggerGroups.forEach((g) => Array.from(g.children).forEach((c, idx) => c.style.setProperty('--si', idx)));
  const targets = [...splitEls, ...staggerGroups, ...$$('.wipe')];

  if (reduceMotion || !('IntersectionObserver' in window)) {
    targets.forEach((el) => el.classList.add('in'));
  } else {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.15 });
    targets.forEach((el) => io.observe(el));
  }

  /* ---------- safety net: rect-based reveal on scroll/load (covers browsers or embeds where
     IntersectionObserver callbacks are delayed). Applies to every animated block on the page. ---------- */
  const pendingReveal = () => $$('.reveal:not(.in), .reveal-up:not(.in), [data-split]:not(.in), .stagger:not(.in), .wipe:not(.in)');
  const revealCheck = () => {
    const vh = window.innerHeight;
    pendingReveal().forEach((el) => {
      const r = el.getBoundingClientRect();
      /* anything whose top has reached the lower part of the screen — including blocks already
         scrolled past (fast scroll, anchor jump, reload mid-page) — must end up visible */
      if (r.top < vh * 0.92) el.classList.add('in');
    });
  };
  /* time-throttled (not rAF-based) so it also runs where frames are not being produced */
  let lastCheck = 0, trailing = 0;
  window.addEventListener('scroll', () => {
    const now = Date.now();
    if (now - lastCheck > 120) { lastCheck = now; revealCheck(); }
    clearTimeout(trailing);
    trailing = setTimeout(revealCheck, 150);
  }, { passive: true });
  window.addEventListener('load', revealCheck);
  setTimeout(revealCheck, 400);

  /* ---------- scroll progress bar ---------- */
  const progressBar = $('.scroll-progress span');
  const setProgress = () => {
    if (!progressBar) return;
    const max = doc.scrollHeight - window.innerHeight;
    progressBar.style.transform = `scaleX(${max > 0 ? (window.scrollY / max).toFixed(4) : 0})`;
  };

  if (reduceMotion) {
    window.addEventListener('scroll', setProgress, { passive: true });
    setProgress();
    return; /* no transforms, cursor or tilt for users who asked for less motion */
  }
  /* ---------- scroll-linked + mouse-linked transforms (one rAF loop) ----------
     Uses the individual `translate` / `rotate` CSS properties so it never fights with
     CSS `transform` animations (float, reveal) already applied to the same element. */
  const num = (el, k) => parseFloat(el.dataset[k] || 0);
  const movers = $$('[data-speed],[data-x],[data-rot],[data-sx],[data-depth]').map((el) => ({
    el, speed: num(el, 'speed'), x: num(el, 'x'), rot: num(el, 'rot'), sx: num(el, 'sx'), depth: num(el, 'depth'),
  }));

  let mx = 0, my = 0, tmx = 0, tmy = 0;
  if (finePointer) {
    window.addEventListener('pointermove', (e) => {
      tmx = (e.clientX / window.innerWidth - 0.5) * 2;
      tmy = (e.clientY / window.innerHeight - 0.5) * 2;
      requestFrame();
    }, { passive: true });
  }

  let rafId = 0;
  const frame = () => {
    rafId = 0;
    setProgress();
    const vh = window.innerHeight;
    const k = window.innerWidth < 901 ? 0.45 : 1; /* calmer on phones/tablets */
    mx += (tmx - mx) * 0.08;
    my += (tmy - my) * 0.08;
    movers.forEach((m) => {
      const r = m.el.getBoundingClientRect();
      if (r.bottom < -300 || r.top > vh + 300) return;
      /* p: -1 when the element's centre is at the viewport bottom, 0 at the middle, +1 at the top */
      const p = clamp((vh / 2 - (r.top + r.height / 2)) / (vh / 2 + r.height / 2), -1, 1);
      /* enter: 0 as the element appears at the bottom, 1 once it is ~60% up the screen */
      const enter = clamp((vh - r.top) / (vh * 0.6), 0, 1);
      const ease = 1 - Math.pow(1 - enter, 3);
      const tx = (m.x * (1 - ease) + m.sx * p) * k - mx * m.depth * 9;
      const ty = m.speed * p * 120 * k + my * m.depth * 7;
      m.el.style.translate = `${tx.toFixed(1)}px ${ty.toFixed(1)}px`;
      if (m.rot) m.el.style.rotate = `${(m.rot * p).toFixed(2)}deg`;
      if (m.x) m.el.style.opacity = (0.1 + ease * 0.9).toFixed(3);
    });
    if (Math.abs(tmx - mx) > 0.002 || Math.abs(tmy - my) > 0.002) requestFrame();
  };
  function requestFrame() { if (!rafId) rafId = requestAnimationFrame(frame); }
  window.addEventListener('scroll', requestFrame, { passive: true });
  window.addEventListener('resize', requestFrame);
  requestFrame();

  if (!finePointer) return; /* the rest needs a real mouse */

  /* ---------- magnetic buttons ---------- */
  $$('[data-magnetic]').forEach((el) => {
    el.addEventListener('pointermove', (e) => {
      const r = el.getBoundingClientRect();
      const dx = e.clientX - (r.left + r.width / 2);
      const dy = e.clientY - (r.top + r.height / 2);
      el.style.translate = `${(dx * 0.22).toFixed(1)}px ${(dy * 0.3).toFixed(1)}px`;
    });
    el.addEventListener('pointerleave', () => { el.style.translate = ''; });
  });

  /* ---------- custom cursor ---------- */
  const cursor = $('.cursor');
  if (cursor) {
    doc.classList.add('has-cursor');
    const dot = $('.cursor-dot', cursor);
    const ring = $('.cursor-ring', cursor);
    let cx = -100, cy = -100, rx = -100, ry = -100, cRaf = 0;
    const loop = () => {
      rx += (cx - rx) * 0.2;
      ry += (cy - ry) * 0.2;
      dot.style.transform = `translate(${cx}px, ${cy}px)`;
      ring.style.transform = `translate(${rx.toFixed(1)}px, ${ry.toFixed(1)}px)`;
      cRaf = (Math.abs(cx - rx) > 0.2 || Math.abs(cy - ry) > 0.2) ? requestAnimationFrame(loop) : 0;
    };
    window.addEventListener('pointermove', (e) => {
      cx = e.clientX; cy = e.clientY;
      cursor.classList.add('on');
      if (!cRaf) cRaf = requestAnimationFrame(loop);
    }, { passive: true });
    document.documentElement.addEventListener('pointerleave', () => cursor.classList.remove('on'));
    const hoverSel = 'a, button, input, textarea, select, label, [tabindex], .work-card, .svc-card';
    document.addEventListener('pointerover', (e) => {
      const t = e.target.closest(hoverSel);
      cursor.classList.toggle('hover', !!t);
      cursor.classList.toggle('text', !!(t && t.matches('input, textarea')));
    });
    document.addEventListener('pointerdown', () => cursor.classList.add('down'));
    document.addEventListener('pointerup', () => cursor.classList.remove('down'));
  }

  /* ---------- 3D tilt + glare on cards ---------- */
  $$('.work-card, .svc-card, .t-card, .feature-card, .char-card, .post-card').forEach((card) => {
    card.classList.add('tilt');
    card.addEventListener('pointermove', (e) => {
      const r = card.getBoundingClientRect();
      const px = (e.clientX - r.left) / r.width - 0.5;
      const py = (e.clientY - r.top) / r.height - 0.5;
      const amt = 9;
      card.style.setProperty('--rx', `${(-py * amt).toFixed(2)}deg`);
      card.style.setProperty('--ry', `${(px * amt).toFixed(2)}deg`);
      card.style.setProperty('--gx', `${((px + 0.5) * 100).toFixed(1)}%`);
      card.style.setProperty('--gy', `${((py + 0.5) * 100).toFixed(1)}%`);
    });
    card.addEventListener('pointerleave', () => {
      card.style.setProperty('--rx', '0deg');
      card.style.setProperty('--ry', '0deg');
    });
  });
})();
