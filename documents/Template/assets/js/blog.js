/* Micro Studio — blog interactions (blog.html + blog-single.html). Vanilla JS, no dependencies.
   Every block is guarded: it only runs if its elements exist on the current page. */
(() => {
  'use strict';

  const $ = (s, root = document) => root.querySelector(s);
  const $$ = (s, root = document) => Array.from(root.querySelectorAll(s));
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- category filter (blog.html + games.html) — client-side preview; PHP will use ?cat=slug ---------- */
  const chips = $$('.cat-chip');
  const cards = $$('.post-grid [data-cat], .games-grid [data-cat]');
  const empty = $('.filter-empty');
  if (chips.length && cards.length) {
    const apply = (cat) => {
      let shown = 0;
      cards.forEach((card) => {
        const match = cat === 'all' || card.dataset.cat === cat;
        card.hidden = !match;
        if (match) shown += 1;
      });
      chips.forEach((c) => {
        const on = c.dataset.filter === cat;
        c.classList.toggle('is-active', on);
        c.setAttribute('aria-pressed', String(on));
      });
      if (empty) empty.hidden = shown > 0;
      const counter = $('[data-shown]');
      if (counter) counter.textContent = String(shown).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
    };
    chips.forEach((chip) => chip.addEventListener('click', () => apply(chip.dataset.filter)));
    const initial = new URLSearchParams(window.location.search).get('cat');
    if (initial && chips.some((c) => c.dataset.filter === initial)) apply(initial);
  }

  /* ---------- newsletter form (blog.html) — validation only, no backend yet ---------- */
  const nl = $('.nl-form');
  if (nl) {
    const input = $('input[type="email"]', nl);
    const status = $('.nl-status', nl);
    nl.addEventListener('submit', (e) => {
      e.preventDefault();
      const ok = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(input.value.trim());
      input.setAttribute('aria-invalid', String(!ok));
      status.className = 'nl-status' + (ok ? ' ok' : ' err');
      status.textContent = ok ? 'عضویت شما ثبت شد؛ یادداشت بعدی را برایتان می‌فرستیم.' : 'لطفاً یک ایمیل معتبر وارد کنید.';
      if (ok) nl.reset(); else input.focus();
    });
  }

  /* ---------- reading progress (blog-single.html) ---------- */
  const prose = $('.prose');
  const bar = $('.read-progress span');
  if (prose && bar) {
    const update = () => {
      const r = prose.getBoundingClientRect();
      const total = r.height - window.innerHeight * 0.6;
      const p = total > 0 ? Math.min(1, Math.max(0, -r.top / total)) : 0;
      bar.style.transform = `scaleX(${p.toFixed(4)})`;
    };
    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    update();
  }

  /* ---------- table of contents: build from .prose h2 + highlight current section ---------- */
  const tocList = $('.toc-list');
  if (prose && tocList) {
    const heads = $$('h2', prose);
    heads.forEach((h, i) => { if (!h.id) h.id = 'section-' + (i + 1); });
    tocList.innerHTML = '';
    heads.forEach((h) => {
      const li = document.createElement('li');
      const a = document.createElement('a');
      a.href = '#' + h.id;
      a.textContent = h.textContent;
      li.appendChild(a);
      tocList.appendChild(li);
    });
    const links = $$('a', tocList);
    const setActive = () => {
      let current = heads[0];
      heads.forEach((h) => { if (h.getBoundingClientRect().top < window.innerHeight * 0.35) current = h; });
      links.forEach((a) => {
        const on = a.hash === '#' + current.id;
        a.classList.toggle('is-active', on);
        if (on) a.setAttribute('aria-current', 'location'); else a.removeAttribute('aria-current');
      });
    };
    window.addEventListener('scroll', setActive, { passive: true });
    setActive();
    links.forEach((a) => a.addEventListener('click', (e) => {
      const target = document.getElementById(a.hash.slice(1));
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
      history.replaceState(null, '', a.hash);
    }));
  }

  /* ---------- share buttons (blog-single.html) ---------- */
  const shareStatus = $('.share-status');
  $$('[data-share]').forEach((btn) => {
    const url = window.location.href.split('#')[0];
    const title = document.title;
    if (btn.dataset.share === 'telegram') {
      btn.href = 'https://t.me/share/url?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title);
    }
    if (btn.dataset.share === 'copy') {
      btn.addEventListener('click', async () => {
        try {
          if (navigator.share && window.matchMedia('(pointer: coarse)').matches) {
            await navigator.share({ title, url });
            return;
          }
          await navigator.clipboard.writeText(url);
          if (shareStatus) shareStatus.textContent = 'پیوند کپی شد.';
        } catch (_) {
          if (shareStatus) shareStatus.textContent = 'کپی نشد؛ پیوند را از نوار آدرس بردارید.';
        }
        setTimeout(() => { if (shareStatus) shareStatus.textContent = ''; }, 2500);
      });
    }
  });
})();
