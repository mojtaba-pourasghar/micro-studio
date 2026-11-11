# -*- coding: utf-8 -*-
"""صفحه‌ی اصلی: نسخه‌ی شیشه‌ای و متحرک. فقط همین صفحه از home.css و home.js استفاده می‌کند."""
from content import SITE, PRODUCTS, PRODUCT_BY_SLUG, POSTS


def build(ctx):
    head, header, footer, ic, chip, post_card, org_ld, jsonld, url, write, DOMAIN, PAGES = (
        ctx["head"], ctx["header"], ctx["footer"], ctx["ic"], ctx["chip"], ctx["post_card"],
        ctx["org_ld"], ctx["jsonld"], ctx["url"], ctx["write"], ctx["DOMAIN"], ctx["PAGES"])
    R = ""
    live = [p for p in PRODUCTS if p["status"] == "live"]
    p0 = PRODUCT_BY_SLUG["goshyar"]

    # ردیف آیکن‌های شناور در هیرو
    float_icons = "".join(
        f'<a class="fi fi--{i+1}" href="products/{p["slug"]}/" style="--accent:{p["accent"]}" '
        f'aria-label="{p["name"]}"><img src="{p["icon"]}" width="52" height="52" alt="" loading="lazy">'
        f'<span>{p["name"]}</span></a>' for i, p in enumerate(PRODUCTS[:4]))

    # نوار متحرک آیکن‌ها
    strip_items = "".join(
        f'<li><img src="{p["icon"]}" width="44" height="44" alt="" loading="lazy"><span>{p["name"]}</span></li>'
        for p in PRODUCTS)
    strip = f'<ul class="mq-track">{strip_items}</ul><ul class="mq-track" aria-hidden="true">{strip_items}</ul>'

    stats = [("۶", "اپ و بازی", "منتشرشده و در راه"), ("۳–۱۰", "سال", "رده‌ی سنی مخاطب"),
             ("۱۰۰٪", "فارسی", "صدا، متن و شخصیت"), ("۰", "تبلیغ مزاحم", "بدون بنر وسط بازی")]
    stats_html = "".join(
        f'<li class="stat glass"><strong data-count="{v}">{v}</strong><span>{l}</span><em>{d}</em></li>'
        for v, l, d in stats)

    # کارت اپ‌ها، نسخه‌ی شیشه‌ای
    def gcard(p, i):
        if p["status"] == "live":
            cta = (f'<a class="btn btn--accent btn--sm" href="{p["bazaar"]}" target="_blank" rel="noopener">'
                   f'دانلود<span class="sr-only"> {p["name"]} از کافه‌بازار</span></a>')
        else:
            cta = '<span class="btn btn--off btn--sm">به‌زودی</span>'
        return f"""<article class="gapp glass reveal" style="--accent:{p['accent']};--tint:{p['tint']};--on:{p['on']};--d:{i*70}ms">
  <span class="gapp-glow" aria-hidden="true"></span>
  <div class="gapp-top">
    <img class="gapp-icon" src="{p['icon']}" width="60" height="60" alt="" loading="lazy" decoding="async">
    <span class="gapp-age">{p['age']}</span>
  </div>
  <h3><a href="products/{p['slug']}/">{p['name']}</a></h3>
  <p class="gapp-tag">{p['tagline']}</p>
  <p class="gapp-short">{p['short']}</p>
  <div class="actions">{cta}<a class="btn btn--line btn--sm" href="products/{p['slug']}/">جزئیات</a></div>
</article>"""

    apps = "".join(gcard(p, i) for i, p in enumerate(PRODUCTS))

    caps = [("gamepad", "اپ و بازی", "از ایده و شخصیت تا انتشار در کافه‌بازار؛ طراحی بازی، صداگذاری و تست با کودک."),
            ("sparkles", "انیمیشن و شخصیت", "شخصیت‌هایی که در اپ، ویدیو و شبکه‌های اجتماعی یک زبان دارند."),
            ("film", "موشن و رسانه", "تیزر، ریلز و ویدیوی کوتاه برای معرفی محصول یا آموزش."),
            ("book", "محتوای آموزشی", "هماهنگ با کتاب درسی، با تمرین، آزمون و کاربرگ قابل چاپ.")]
    caps_html = "".join(
        f'<li class="cap glass reveal" style="--d:{i*80}ms"><span class="cap-ic">{ic(t, 26)}</span>'
        f'<h3>{h}</h3><p>{d}</p></li>' for i, (t, h, d) in enumerate(caps))

    steps = [("کشف", "با شما می‌نشینیم: مخاطب، هدف و محدوده‌ی کار روشن می‌شود."),
             ("طراحی", "جریان کار، شخصیت و رابط کاربری را طراحی و با شما مرور می‌کنیم."),
             ("ساخت", "مرحله‌به‌مرحله می‌سازیم و هر نسخه را می‌بینید."),
             ("تحویل", "فایل نهایی، انتشار و پشتیبانی بعد از تحویل.")]
    steps_html = "".join(
        f'<li class="step reveal" style="--d:{i*90}ms"><span class="step-n">{i+1}</span>'
        f'<h3>{t}</h3><p>{d}</p></li>' for i, (t, d) in enumerate(steps))

    pledges = ["بدون تبلیغات مزاحم و بدون پنجره‌ی ناخواسته", "بخش والدین پشت قفل، جدا از فضای کودک",
               "رده‌ی سنی روشن روی هر اپ", "بدون جمع‌آوری اطلاعات شخصی کودک"]
    pledges_html = "".join(f'<li>{ic("check", 20)}<span>{t}</span></li>' for t in pledges)

    posts = "".join(post_card(po, R) for po in POSTS[:3])

    ld = [org_ld(), {"@context": "https://schema.org", "@type": "WebSite", "name": SITE["name"], "inLanguage": "fa"},
          {"@context": "https://schema.org", "@type": "ItemList", "name": "اپ‌های میکرو استودیو",
           "itemListElement": [{"@type": "ListItem", "position": i + 1, "item": {
               "@type": "SoftwareApplication", "name": p["name"], "description": p["short"],
               "applicationCategory": "EducationalApplication", "operatingSystem": "Android", "inLanguage": "fa",
               "url": p["bazaar"]}} for i, p in enumerate(live)]}]

    body = f"""<main id="main" class="home">

<section class="h2o" aria-labelledby="h1">
  <div class="h2o-bg" aria-hidden="true">
    <span class="orb orb--1"></span><span class="orb orb--2"></span><span class="orb orb--3"></span>
    <span class="grid-lines"></span>
  </div>
  <div class="wrap h2o-grid">
    <div class="h2o-copy">
      <p class="eyebrow stage" style="--d:0ms">استودیوی بازی و سرگرمی کودک</p>
      <h1 id="h1" class="stage" style="--d:90ms">دنیایی که کودک<br>با آن <span class="hl">بازی می‌کند</span><br>و یاد می‌گیرد</h1>
      <p class="h2o-lead stage" style="--d:180ms">اپ، بازی و انیمیشن فارسی برای کودکان ۳ تا ۱۰ سال. همه‌چیز را خودمان می‌سازیم: شخصیت، صدا، بازی و رابط کاربری.</p>
      <div class="actions stage" style="--d:260ms">
        <a class="btn btn--glow" href="products/">دیدن اپ‌ها</a>
        <a class="btn btn--glass" href="services/">همکاری با ما</a>
      </div>
      <div class="h2o-float stage" style="--d:340ms">{float_icons}</div>
    </div>
    <div class="h2o-art stage" style="--d:200ms">
      <div class="dev" data-tilt>
        <span class="dev-glow" aria-hidden="true"></span>
        <div class="dev-screen"><img src="assets/img/goshyar-1.webp" width="540" height="1170" alt="صفحه‌ی اصلی اپ گوش‌یار با خرس هدفون‌به‌سر" fetchpriority="high"></div>
      </div>
      <div class="pill glass pill--1"><span class="dotlive" aria-hidden="true"></span>۶ اپ در کافه‌بازار</div>
      <div class="pill glass pill--2">{ic('shield', 18)}بدون تبلیغ مزاحم</div>
    </div>
  </div>
  <div class="mq" aria-hidden="true"><div class="mq-in">{strip}</div></div>
</section>

<section class="sec sec--stats" aria-label="میکرو استودیو در یک نگاه">
  <div class="wrap"><ul class="stats">{stats_html}</ul></div>
</section>

<section class="sec sec--apps" id="apps" aria-labelledby="apps-h">
  <div class="sec-bg" aria-hidden="true"><span class="orb orb--4"></span><span class="orb orb--5"></span></div>
  <div class="wrap">
    <div class="shead reveal">
      <p class="eyebrow">محصولات ما</p>
      <h2 id="apps-h">شش اپ برای شش مهارت</h2>
      <p>هر اپ یک شخصیت و یک هدف دارد؛ از تمرین شنیدن تا درس‌های سوم دبستان.</p>
    </div>
    <div class="gapps">{apps}</div>
    <p class="center-cta reveal"><a class="more" href="products/">دیدن همه‌ی اپ‌ها و جزئیات</a></p>
  </div>
</section>

<section class="sec sec--show" aria-labelledby="show-h">
  <div class="wrap show">
    <div class="show-art reveal reveal--right">
      <div class="dev dev--sm">
        <span class="dev-glow" aria-hidden="true" style="--accent:{p0['accent']}"></span>
        <div class="dev-screen">
          <video controls preload="none" playsinline poster="assets/video/goshyar-demo.webp" aria-label="ضبط صفحه‌ی اپ گوش‌یار">
            <source src="assets/video/goshyar-demo.mp4" type="video/mp4">
          </video>
        </div>
      </div>
    </div>
    <div class="show-text reveal reveal--left">
      <p class="eyebrow">نمونه‌ی کار</p>
      <h2 id="show-h">گوش‌یار؛ وقتی گوش کردن تبدیل به بازی می‌شود</h2>
      <p>صدایی پخش می‌شود و کودک باید بین چند تصویر صاحبش را پیدا کند. حیوان‌ها، سازها، وسایل خانه و طبیعت؛ مرحله‌به‌مرحله و با ماموریت روزانه.</p>
      <ul class="ticks">
        <li>{ic('check', 20)}تمرین تمرکز و حافظه‌ی شنیداری</li>
        <li>{ic('check', 20)}صدای واقعی و ضبط‌شده، نه صدای ساختگی</li>
        <li>{ic('check', 20)}گزارش پیشرفت در بخش والدین قفل‌دار</li>
      </ul>
      <div class="actions">
        <a class="btn btn--accent" style="--accent:{p0['accent']};--on:{p0['on']}" href="{p0['bazaar']}" target="_blank" rel="noopener">دانلود گوش‌یار</a>
        <a class="btn btn--line" href="products/goshyar/">صفحه‌ی اپ</a>
      </div>
    </div>
  </div>
</section>

<section class="sec sec--caps" aria-labelledby="caps-h">
  <div class="sec-bg" aria-hidden="true"><span class="orb orb--6"></span></div>
  <div class="wrap">
    <div class="shead shead--center reveal">
      <p class="eyebrow">خدمات استودیو</p>
      <h2 id="caps-h">همان تیمی که اپ‌های خودش را می‌سازد</h2>
      <p>برای برندها، ناشران و مراکز آموزشی هم محتوا، بازی و انیمیشن تولید می‌کنیم.</p>
    </div>
    <ul class="caps">{caps_html}</ul>
    <ol class="steps">{steps_html}</ol>
    <p class="center-cta reveal"><a class="btn btn--line" href="services/">جزئیات خدمات و نمونه‌کارها</a></p>
  </div>
</section>

<section class="sec sec--pledge" aria-labelledby="pl-h">
  <div class="wrap pledge">
    <div class="reveal reveal--right">
      <p class="eyebrow">تعهد ما به والدین</p>
      <h2 id="pl-h">محیطی که با خیال راحت به کودک می‌دهید</h2>
      <p>اپ کودک فقط سرگرمی نیست؛ جایی است که کودک تنها می‌ماند. این چهار اصل در همه‌ی اپ‌های ما ثابت است.</p>
      <div class="actions"><a class="btn btn--line" href="about/">درباره‌ی ما</a></div>
    </div>
    <ul class="ticks ticks--cards reveal reveal--left">{pledges_html}</ul>
  </div>
</section>

<section class="sec sec--blog" aria-labelledby="blog-h">
  <div class="wrap">
    <div class="shead shead--row reveal">
      <div><p class="eyebrow">بلاگ</p><h2 id="blog-h">راهنمای والدین و معلم‌ها</h2></div>
      <a class="more" href="blog/">همه‌ی نوشته‌ها</a>
    </div>
    <div class="grid grid--3 reveal">{posts}</div>
  </div>
</section>

<section class="sec sec--final" aria-labelledby="cta-h">
  <div class="final-bg" aria-hidden="true"><span class="orb orb--7"></span><span class="orb orb--8"></span></div>
  <div class="wrap">
    <div class="final glass reveal">
      <h2 id="cta-h">بازی را از کافه‌بازار شروع کنید</h2>
      <p>همه‌ی اپ‌های ما در کافه‌بازار هستند. خبر اپ‌های تازه و پشت‌صحنه‌ی ساخت را در اینستاگرام می‌گذاریم.</p>
      <div class="actions">
        <a class="btn btn--glow" href="products/">دیدن اپ‌ها</a>
        <a class="btn btn--glass" href="{SITE['instagram']}" target="_blank" rel="noopener">{ic('instagram', 20)}اینستاگرام ما</a>
      </div>
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
           + body + footer(R).replace('<script src="script.js" defer></script>',
                                      '<script src="script.js" defer></script>\n<script src="home.js" defer></script>'))
    write("index.html", out)
    PAGES.append(("", "1.0"))
