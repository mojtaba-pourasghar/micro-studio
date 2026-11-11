# -*- coding: utf-8 -*-
"""صفحه‌ی اصلی — سبک استودیوی بازی: تیره، سینمایی و متحرک. فقط این صفحه از home.css و home.js استفاده می‌کند."""
from content import SITE, PRODUCTS, PRODUCT_BY_SLUG, POSTS


def build(ctx):
    head, header, footer, ic = ctx["head"], ctx["header"], ctx["footer"], ctx["ic"]
    org_ld, write, PAGES = ctx["org_ld"], ctx["write"], ctx["PAGES"]
    R = ""
    live = [p for p in PRODUCTS if p["status"] == "live"]
    p0 = PRODUCT_BY_SLUG["goshyar"]

    # ---- گالری محصول‌ها (کارت بزرگ رسانه‌ای)
    def card(p, i):
        feat = " card--feat" if i == 0 else ""
        badge = ('<span class="bdg bdg--soon">به‌زودی</span>' if p["status"] == "soon"
                 else '<span class="bdg">کافه‌بازار</span>')
        cta = (f'<a class="card-go" href="{p["bazaar"]}" target="_blank" rel="noopener">دانلود'
               f'<span class="sr-only"> {p["name"]} از کافه‌بازار</span>{ic("download", 18)}</a>'
               if p["status"] == "live" else "")
        return f"""<article class="card{feat} reveal" style="--accent:{p['accent']};--d:{i*70}ms">
  <a class="card-link" href="products/{p['slug']}/"><span class="sr-only">صفحه‌ی {p['name']}</span></a>
  <div class="card-media">
    <span class="card-aura" aria-hidden="true"></span>
    <img class="card-shot" src="assets/img/{p['slug']}-1.webp" width="540" height="1170" alt="" loading="lazy" decoding="async">
    <img class="card-icon" src="{p['icon']}" width="56" height="56" alt="" loading="lazy" decoding="async">
  </div>
  <div class="card-body">
    <div class="bdgs">{badge}<span class="bdg">{p['age']}</span><span class="bdg">{p['kind']}</span></div>
    <h3>{p['name']}</h3>
    <p class="card-tag">{p['tagline']}</p>
    <p class="card-txt">{p['short']}</p>
    <div class="card-foot"><span class="card-more">جزئیات {ic('arrow', 18)}</span>{cta}</div>
  </div>
</article>"""

    cards = "".join(card(p, i) for i, p in enumerate(PRODUCTS))

    mq_words = ["بازی", "یادگیری", "انیمیشن", "شخصیت", "فارسی", "صدا"]
    mq = "".join(f'<span>{w}</span><i aria-hidden="true">✶</i>' for w in mq_words)

    stats = [("6", "۶", "اپ و بازی"), ("5", "۵", "اپ روی کافه‌بازار"), ("100", "۱۰۰", "درصد فارسی"), ("0", "۰", "تبلیغ مزاحم")]
    stats_html = "".join(
        f'<li class="reveal" style="--d:{i*80}ms"><strong data-to="{n}">{fa}</strong><span>{l}</span></li>'
        for i, (n, fa, l) in enumerate(stats))

    rows = [("اپ و بازی", "طراحی بازی، برنامه‌نویسی، صداگذاری و انتشار در کافه‌بازار.", "gamepad"),
            ("انیمیشن و شخصیت", "ساخت شخصیت، استوری‌بورد و انیمیشن دوبعدی با زبان بصری یکدست.", "sparkles"),
            ("موشن و رسانه", "تیزر، ریلز و ویدیوی کوتاه برای معرفی محصول و شبکه‌های اجتماعی.", "film"),
            ("محتوای آموزشی", "هماهنگ با کتاب درسی؛ تمرین، آزمون و کاربرگ قابل چاپ.", "book")]
    rows_html = "".join(
        f"""<li class="row reveal" style="--d:{i*70}ms">
  <span class="row-n" aria-hidden="true">{["۰۱","۰۲","۰۳","۰۴"][i]}</span>
  <span class="row-ic">{ic(t, 24)}</span>
  <h3>{h}</h3>
  <p>{d}</p>
</li>""" for i, (h, d, t) in enumerate(rows))

    posts_html = "".join(f"""<article class="news reveal" style="--d:{i*80}ms">
  <a href="blog/{po['slug']}/"><span class="sr-only">{po['title']}</span></a>
  <span class="bdg">{po['tag']}</span>
  <h3>{po['title']}</h3>
  <p>{po['desc']}</p>
  <span class="card-more">ادامه {ic('arrow', 18)}</span>
</article>""" for i, po in enumerate(POSTS[:3]))

    pledges = ["بدون تبلیغات مزاحم", "بخش والدین قفل‌دار", "رده‌ی سنی روشن", "بدون جمع‌آوری اطلاعات کودک"]
    pl_html = "".join(f'<li>{ic("check", 18)}{t}</li>' for t in pledges)

    ld = [org_ld(), {"@context": "https://schema.org", "@type": "WebSite", "name": SITE["name"], "inLanguage": "fa"},
          {"@context": "https://schema.org", "@type": "ItemList", "name": "اپ‌های میکرو استودیو",
           "itemListElement": [{"@type": "ListItem", "position": i + 1, "item": {
               "@type": "SoftwareApplication", "name": p["name"], "description": p["short"],
               "applicationCategory": "EducationalApplication", "operatingSystem": "Android", "inLanguage": "fa",
               "url": p["bazaar"]}} for i, p in enumerate(live)]}]

    fan = "".join(
        f'<img class="fan fan--{i+1}" src="assets/img/{s}-1.webp" width="540" height="1170" alt="" loading="lazy" decoding="async">'
        for i, s in enumerate(["pashmak", "hoho-farsi"]))

    body = f"""<main id="main" class="home">

<section class="stage" aria-labelledby="h1">
  <div class="stage-bg" aria-hidden="true">
    <span class="aur aur--1"></span><span class="aur aur--2"></span><span class="aur aur--3"></span>
    <span class="scan"></span>
  </div>
  <div class="wrap stage-grid">
    <div class="stage-copy">
      <p class="kicker up" style="--d:40ms"><span class="sq" aria-hidden="true"></span>استودیوی بازی و انیمیشن کودک</p>
      <h1 id="h1" class="up" style="--d:120ms">ما دنیای<br><span class="grad">بازی کودک</span><br>را می‌سازیم</h1>
      <p class="stage-lead up" style="--d:200ms">اپ، بازی و انیمیشن فارسی برای کودکان ۳ تا ۱۰ سال. شخصیت، صدا، بازی و رابط کاربری؛ همه در یک استودیو.</p>
      <div class="actions up" style="--d:280ms">
        <a class="btn btn--neon" href="products/">دیدن اپ‌ها</a>
        <a class="btn btn--out" href="#showreel">{ic('play', 18)}تماشای نمونه</a>
      </div>
      <ul class="mini up" style="--d:360ms">
        <li><strong>۶</strong><span>اپ منتشرشده و در راه</span></li>
        <li><strong>۳–۱۰</strong><span>سال، رده‌ی سنی</span></li>
        <li><strong>اندروید</strong><span>کافه‌بازار</span></li>
      </ul>
    </div>
    <div class="stage-art up" style="--d:240ms" data-par>
      {fan}
      <div class="hero-dev">
        <span class="dev-aura" aria-hidden="true"></span>
        <img src="assets/img/goshyar-1.webp" width="540" height="1170" alt="صفحه‌ی اصلی اپ گوش‌یار با خرس هدفون‌به‌سر" fetchpriority="high">
      </div>
      <span class="tagfloat tagfloat--1">{ic('shield', 16)}بدون تبلیغ مزاحم</span>
      <span class="tagfloat tagfloat--2"><i class="live" aria-hidden="true"></i>۵ اپ روی کافه‌بازار</span>
    </div>
  </div>
</section>

<div class="ticker" aria-hidden="true"><div class="ticker-in"><div class="ticker-set">{mq}</div><div class="ticker-set">{mq}</div></div></div>

<section class="blk blk--games" id="games" aria-labelledby="g-h">
  <div class="wrap">
    <div class="bhead reveal">
      <div>
        <p class="kicker"><span class="sq" aria-hidden="true"></span>محصولات استودیو</p>
        <h2 id="g-h">اپ‌ها و بازی‌ها</h2>
      </div>
      <p class="bhead-note">هر کدام یک شخصیت، یک مهارت و یک دنیای کوچک. همه فارسی و بدون تبلیغ مزاحم.</p>
    </div>
    <div class="cards">{cards}</div>
    <p class="mid"><a class="btn btn--out" href="products/">همه‌ی اپ‌ها و جزئیات</a></p>
  </div>
</section>

<section class="blk blk--show" id="showreel" aria-labelledby="s-h">
  <div class="show-bg" aria-hidden="true"><span class="aur aur--4"></span></div>
  <div class="wrap show">
    <div class="show-media reveal reveal--r">
      <div class="scr">
        <span class="dev-aura" style="--accent:{p0['accent']}" aria-hidden="true"></span>
        <video controls preload="none" playsinline poster="assets/video/goshyar-demo.webp" aria-label="ضبط صفحه‌ی اپ گوش‌یار">
          <source src="assets/video/goshyar-demo.mp4" type="video/mp4">
        </video>
      </div>
    </div>
    <div class="show-copy reveal reveal--l">
      <p class="kicker"><span class="sq" aria-hidden="true"></span>نمونه‌ی کار</p>
      <h2 id="s-h">گوش‌یار؛ وقتی گوش کردن <span class="grad">بازی می‌شود</span></h2>
      <p>صدایی پخش می‌شود و کودک باید بین چند تصویر صاحبش را پیدا کند. حیوان‌ها، سازها، وسایل خانه و طبیعت؛ مرحله‌به‌مرحله و با ماموریت روزانه.</p>
      <ul class="specs">
        <li><span>ژانر</span><strong>بازی شنیداری</strong></li>
        <li><span>سن</span><strong>۳ تا ۸ سال</strong></li>
        <li><span>پلتفرم</span><strong>اندروید</strong></li>
        <li><span>زبان</span><strong>فارسی</strong></li>
      </ul>
      <div class="actions">
        <a class="btn btn--neon" style="--neon:{p0['accent']}" href="{p0['bazaar']}" target="_blank" rel="noopener">دانلود گوش‌یار</a>
        <a class="btn btn--out" href="products/goshyar/">صفحه‌ی اپ</a>
      </div>
    </div>
  </div>
</section>

<section class="blk blk--stats" aria-label="استودیو در یک نگاه">
  <div class="wrap"><ul class="stats">{stats_html}</ul></div>
</section>

<section class="blk blk--serv" aria-labelledby="sv-h">
  <div class="wrap">
    <div class="bhead reveal">
      <div>
        <p class="kicker"><span class="sq" aria-hidden="true"></span>خدمات</p>
        <h2 id="sv-h">برای برندها و مراکز آموزشی</h2>
      </div>
      <p class="bhead-note">همان تیمی که اپ‌های خودش را می‌سازد، برای شما هم تولید می‌کند.</p>
    </div>
    <ul class="rows">{rows_html}</ul>
    <p class="mid"><a class="btn btn--out" href="services/">جزئیات خدمات و مسیر کار</a></p>
  </div>
</section>

<section class="blk blk--pledge" aria-labelledby="p-h">
  <div class="wrap pledge">
    <div class="reveal reveal--r">
      <p class="kicker"><span class="sq" aria-hidden="true"></span>تعهد به والدین</p>
      <h2 id="p-h">جایی که کودک تنها می‌ماند، باید امن باشد</h2>
      <p>اپ کودک فقط سرگرمی نیست. این چهار اصل در همه‌ی محصول‌های ما ثابت است.</p>
      <div class="actions"><a class="btn btn--out" href="about/">درباره‌ی استودیو</a></div>
    </div>
    <ul class="pl reveal reveal--l">{pl_html}</ul>
  </div>
</section>

<section class="blk blk--news" aria-labelledby="n-h">
  <div class="wrap">
    <div class="bhead reveal">
      <div>
        <p class="kicker"><span class="sq" aria-hidden="true"></span>بلاگ</p>
        <h2 id="n-h">راهنمای والدین و معلم‌ها</h2>
      </div>
      <a class="btn btn--out btn--sm" href="blog/">همه‌ی نوشته‌ها</a>
    </div>
    <div class="newsgrid">{posts_html}</div>
  </div>
</section>

<section class="blk blk--end" aria-labelledby="c-h">
  <div class="end-bg" aria-hidden="true"><span class="aur aur--5"></span><span class="aur aur--6"></span></div>
  <div class="wrap end">
    <h2 id="c-h" class="reveal">بازی را <span class="grad">شروع کنید</span></h2>
    <p class="reveal">همه‌ی اپ‌های ما در کافه‌بازار هستند. پشت‌صحنه‌ی ساخت و خبر اپ‌های تازه در اینستاگرام.</p>
    <div class="actions reveal">
      <a class="btn btn--neon" href="products/">دیدن اپ‌ها</a>
      <a class="btn btn--out" href="{SITE['instagram']}" target="_blank" rel="noopener">{ic('instagram', 18)}اینستاگرام</a>
    </div>
  </div>
</section>
</main>
"""
    out = (head("میکرو استودیو | اپ‌های آموزشی و بازی کودک فارسی",
                "میکرو استودیو اپ، بازی و انیمیشن فارسی برای کودکان می‌سازد: گوش‌یار، پشمک و هوهو (فارسی، ریاضی، علوم سوم دبستان). بدون تبلیغات مزاحم؛ دانلود از کافه‌بازار.",
                R, "", ld=ld, image="assets/img/goshyar-1.webp", preload_img="assets/img/goshyar-1.webp",
                extra='<link rel="stylesheet" href="home.css">')
           + header(R, "").replace('<body>', '<body class="has-home">')
              .replace('<meta name="theme-color" content="#FFF8EC">', '<meta name="theme-color" content="#0E0122">')
           + body + footer(R).replace('<script src="script.js" defer></script>',
                                      '<script src="script.js" defer></script>\n<script src="home.js" defer></script>'))
    out = out.replace('<meta name="theme-color" content="#FFF8EC">', '<meta name="theme-color" content="#0E0122">')
    write("index.html", out)
    PAGES.append(("", "1.0"))
