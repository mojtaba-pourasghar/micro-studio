// Renders transparent 1280x720 overlay PNGs (Persian text, brand style) for each trailer scene.
const { chromium } = require('playwright');
const path = require('path');
const ROOT = '/home/user/micro-studio/public/assets';
const OUT = __dirname;

const cards = {
  intro: { big: 'آماده‌ای مهارتت را<br>نشان بدهی؟', small: 'دنیای بازی‌های میکرو استودیو', center: true },
  'two-skies': { tag: 'ماجراجویی نقش‌آفرینی', title: 'دو آسمان', sub: 'کایرو و ایلارا' },
  razino: { tag: 'معمایی و داستانی · ۸ تا ۱۲ سال', title: 'رازینو', sub: '۲۹ معما، ۲۱ پایان' },
  pilo: { tag: 'پلتفرمر معمایی · ۵ تا ۹ سال', title: 'سفر پیلو', sub: 'در رنگارستان' },
  pashmak: { tag: 'خلاقیت و نقاشی · ۳ تا ۸ سال', title: 'دنیای پشمک', sub: 'نقاشی‌ات جان می‌گیرد' },
  'popo-goshyar': { tag: 'کودک خردسال · ۲ تا ۸ سال', title: 'پوپو و گوش‌یار', sub: 'بساز، گوش بده، کشف کن' },
  hoho: { tag: 'آموزشی · کلاس سوم دبستان', title: 'هوهو', sub: 'فارسی، ریاضی و علوم' },
  outro: { outro: true },
};

const css = `
@font-face { font-family: Lalezar; src: url(file://${ROOT}/fonts/Lalezar-Regular.ttf); }
@font-face { font-family: Vaz; src: url(file://${ROOT}/fonts/Vazirmatn-var.woff2); font-weight: 100 900; }
html, body { margin: 0; width: 1280px; height: 720px; background: transparent; direction: rtl; overflow: hidden; }
.lt { position: absolute; right: 70px; bottom: 70px; color: #fff; font-family: Vaz; }
.tag { display: inline-block; padding: 6px 16px; border-radius: 10px; background: #7457ff; font-weight: 800; font-size: 22px; box-shadow: 0 10px 30px rgba(116,87,255,.5); }
.title { font-family: Lalezar; font-size: 96px; line-height: 1.1; margin-top: 10px; text-shadow: 0 6px 24px rgba(0,0,0,.65); }
.sub { font-size: 30px; font-weight: 700; opacity: .95; text-shadow: 0 3px 14px rgba(0,0,0,.8); }
.shade { position: absolute; inset: 0; background: linear-gradient(0deg, rgba(10,10,28,.75), rgba(10,10,28,0) 55%); }
.center { position: absolute; inset: 0; display: grid; place-content: center; text-align: center; color: #fff; }
.big { font-family: Lalezar; font-size: 104px; line-height: 1.2; text-shadow: 0 8px 30px rgba(0,0,0,.7); }
.big + .small { font-family: Vaz; font-weight: 800; font-size: 30px; margin-top: 10px; color: #d9d0ff; text-shadow: 0 3px 14px rgba(0,0,0,.8); }
.vignette { position: absolute; inset: 0; background: radial-gradient(ellipse at center, rgba(10,10,28,.25), rgba(10,10,28,.75)); }
.bug { position: absolute; left: 34px; top: 28px; width: 150px; opacity: .92; filter: drop-shadow(0 4px 10px rgba(0,0,0,.5)); }
.outro { position: absolute; inset: 0; display: grid; place-content: center; justify-items: center; gap: 26px; text-align: center; color: #fff; font-family: Vaz; }
.outro img { width: 560px; }
.pills { display: flex; gap: 14px; }
.pills span { padding: 8px 20px; border-radius: 12px; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18); font-weight: 800; font-size: 26px; }
.site { font-size: 26px; font-weight: 700; color: #bfb3ff; letter-spacing: .02em; direction: ltr; }
`;

(async () => {
  const b = await chromium.launch({ args: ['--allow-file-access-from-files'] });
  const p = await b.newPage({ viewport: { width: 1280, height: 720 } });
  for (const [key, c] of Object.entries(cards)) {
    let html;
    if (c.outro) {
      html = `<div class="outro"><img src="file://${ROOT}/brand/logo-horizontal-dark.png"><div class="pills"><span>بدون تبلیغ</span><span>کاملاً فارسی</span><span>امن برای کودکان</span></div><div class="site">instagram.com/micro_studio.ir</div></div>`;
    } else if (c.center) {
      html = `<div class="vignette"></div><div class="center"><div class="big">${c.big}</div><div class="small">${c.small}</div></div>`;
    } else {
      html = `<div class="shade"></div><div class="lt"><span class="tag">${c.tag}</span><div class="title">${c.title}</div><div class="sub">${c.sub}</div></div><img class="bug" src="file://${ROOT}/brand/logo-horizontal-dark.png">`;
    }
    const f = path.join(OUT, '_card.html');
    require('fs').writeFileSync(f, `<!doctype html><meta charset="utf-8"><style>${css}</style>${html}`);
    await p.goto('file://' + f);
    await p.evaluate(() => document.fonts.ready);
    await p.waitForTimeout(150);
    await p.screenshot({ path: path.join(OUT, `card-${key}.png`), omitBackground: true });
  }
  await b.close();
})();
