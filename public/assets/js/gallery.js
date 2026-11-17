/* Micro Studio — image lightbox for [data-lightbox] links (vanilla JS).
   Groups by the attribute value; keyboard (Esc, ←/→), swipe, focus trap and focus return. */
(() => {
  'use strict';
  const links = Array.from(document.querySelectorAll('[data-lightbox]'));
  if (!links.length) return;

  const box = document.createElement('div');
  box.className = 'gallery-lb';
  box.setAttribute('role', 'dialog');
  box.setAttribute('aria-modal', 'true');
  box.setAttribute('aria-label', 'گالری تصاویر');
  box.hidden = true;
  box.innerHTML = `
    <button class="glb-btn glb-close" type="button" aria-label="بستن"><svg aria-hidden="true"><use href="#i-close"/></svg></button>
    <button class="glb-btn glb-prev" type="button" aria-label="تصویر قبلی"><svg aria-hidden="true"><use href="#i-arrow"/></svg></button>
    <figure class="glb-figure"><img alt=""><figcaption></figcaption></figure>
    <button class="glb-btn glb-next" type="button" aria-label="تصویر بعدی"><svg class="flip" aria-hidden="true"><use href="#i-arrow"/></svg></button>
    <p class="glb-count" aria-live="polite"></p>`;
  document.body.appendChild(box);

  const img = box.querySelector('img');
  const cap = box.querySelector('figcaption');
  const count = box.querySelector('.glb-count');
  const btnClose = box.querySelector('.glb-close');
  const btnPrev = box.querySelector('.glb-prev');
  const btnNext = box.querySelector('.glb-next');
  const fa = (n) => String(n).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
  let group = [], index = 0, lastFocus = null;

  const show = (i) => {
    index = (i + group.length) % group.length;
    const a = group[index];
    img.classList.remove('in');
    img.src = a.href;
    img.alt = (a.querySelector('img') || {}).alt || '';
    cap.textContent = a.dataset.caption || '';
    cap.hidden = !a.dataset.caption;
    count.textContent = `${fa(index + 1)} از ${fa(group.length)}`;
    const single = group.length < 2;
    btnPrev.hidden = single; btnNext.hidden = single;
    // preload neighbours
    [index + 1, index - 1].forEach((j) => { const n = group[(j + group.length) % group.length]; if (n) new Image().src = n.href; });
  };
  img.addEventListener('load', () => img.classList.add('in'));

  const open = (a) => {
    group = links.filter((l) => l.dataset.lightbox === a.dataset.lightbox);
    lastFocus = document.activeElement;
    box.hidden = false;
    document.body.style.overflow = 'hidden';
    show(group.indexOf(a));
    btnClose.focus();
  };
  const close = () => {
    box.hidden = true;
    img.removeAttribute('src');
    document.body.style.overflow = '';
    if (lastFocus) lastFocus.focus();
  };

  links.forEach((a) => a.addEventListener('click', (e) => {
    if (e.metaKey || e.ctrlKey || e.shiftKey) return; // let users open in a new tab
    e.preventDefault();
    open(a);
  }));
  btnClose.addEventListener('click', close);
  // RTL: "previous" is on the right; arrows follow reading direction
  btnPrev.addEventListener('click', () => show(index - 1));
  btnNext.addEventListener('click', () => show(index + 1));
  box.addEventListener('click', (e) => { if (e.target === box) close(); });
  document.addEventListener('keydown', (e) => {
    if (box.hidden) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowLeft') show(index + 1);
    if (e.key === 'ArrowRight') show(index - 1);
    if (e.key === 'Tab') {
      const items = [btnClose, btnPrev, btnNext].filter((b) => !b.hidden);
      const i = items.indexOf(document.activeElement);
      e.preventDefault();
      items[(i + (e.shiftKey ? items.length - 1 : 1)) % items.length].focus();
    }
  });
  let x0 = null;
  box.addEventListener('touchstart', (e) => { x0 = e.touches[0].clientX; }, { passive: true });
  box.addEventListener('touchend', (e) => {
    if (x0 === null) return;
    const dx = e.changedTouches[0].clientX - x0;
    if (Math.abs(dx) > 50) show(index + (dx > 0 ? 1 : -1)); // swipe right = next in RTL
    x0 = null;
  });
})();
