# -*- coding: utf-8 -*-
"""سازنده‌ی صفحه‌های ثابت سایت میکرو استودیو.

استفاده:
    python3 tools/build.py                          # بدون دامنه (canonical و sitemap ساخته نمی‌شود)
    python3 tools/build.py --domain https://example.ir   # با دامنه: canonical، og:url، sitemap و داده‌ی کامل‌تر

خروجی، فایل‌های HTML ثابت در ریشه‌ی مخزن است؛ بدون نیاز به سرور خاص.
"""
import argparse
import html
import json
import os
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from content import (SITE, PRODUCTS, PRODUCT_BY_SLUG, POSTS, POST_BY_SLUG, SERVICES, STANDARDS)  # noqa: E402

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DOMAIN = ""
PAGES = []  # (path, priority) برای sitemap

esc = html.escape

# ------------------------------------------------------------------ آیکن‌ها (خطی، ۲۴×۲۴)
ICONS = {
    "shield": "M12 3l8 3v6c0 5-3.5 8.5-8 9.5C7.5 20.5 4 17 4 12V6z",
    "check": "M5 12.5l4.5 4.5L19 7.5",
    "book": "M5 4h13a1 1 0 0 1 1 1v14H6a2 2 0 0 0-2 2V5a1 1 0 0 1 1-1z M4 19a2 2 0 0 1 2-2h13",
    "pencil": "M4 20l1-4L16 5l3 3L8 19z M14 7l3 3",
    "play": "M8 5v14l11-7z",
    "star": "M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z",
    "spark": "M12 3l2 6 6 2-6 2-2 6-2-6-6-2 6-2z",
    "sparkles": "M10 4l1.6 4.4L16 10l-4.4 1.6L10 16l-1.6-4.4L4 10l4.4-1.6z M18 14l.9 2.1L21 17l-2.1.9L18 20l-.9-2.1L15 17l2.1-.9z",
    "users": "M9 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6z M3.5 19a5.5 5.5 0 0 1 11 0 M16 6a3 3 0 0 1 0 5.6 M17 14.2A5.5 5.5 0 0 1 20.5 19",
    "film": "M4 5h16v14H4z M8 5v14 M16 5v14 M4 9h4 M4 15h4 M16 9h4 M16 15h4",
    "gamepad": "M7 8h10a4 4 0 0 1 4 4v2a3.5 3.5 0 0 1-6 2.4L14 15h-4l-1 1.4A3.5 3.5 0 0 1 3 14v-2a4 4 0 0 1 4-4z M8 11v3 M6.5 12.5h3 M16 12h.01 M18 13.5h.01",
    "headphones": "M4 15v-3a8 8 0 0 1 16 0v3 M4 15h3.5v5H6a2 2 0 0 1-2-2z M20 15h-3.5v5H18a2 2 0 0 0 2-2z",
    "camera": "M4 8h3l1.8-2.5h6.4L17 8h3v11H4z M12 17a3.8 3.8 0 1 0 0-7.6 3.8 3.8 0 0 0 0 7.6z",
    "brush": "M18.5 4.5a2 2 0 0 1 0 3L11 15l-3-3 7.5-7.5a2 2 0 0 1 3 0z M8 12c-2.5 0-4 1.5-4 4 0 2 1.5 4 4 4 2.5 0 3-2 3-4",
    "moon": "M20 14.5A8 8 0 0 1 9.5 4 8 8 0 1 0 20 14.5z",
    "download": "M12 4v10 M8 10.5l4 4 4-4 M5 19h14",
    "map": "M4 6l5-2 6 2 5-2v14l-5 2-6-2-5 2z M9 4v14 M15 6v14",
    "refresh": "M20 11a8 8 0 1 0-2 5.6 M20 5v6h-6",
    "calculator": "M6 3h12v18H6z M9 7h6 M9 11h.01 M12 11h.01 M15 11h.01 M9 15h.01 M12 15h.01 M15 15h.01",
    "search": "M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14z M20 20l-4-4",
    "flask": "M9 3h6 M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4A2 2 0 0 0 19 18l-5-9V3 M8 15h8",
    "tool": "M14.5 6.5a4 4 0 0 0 4.7 5.2l-8.2 8.2a2 2 0 0 1-2.8-2.8l8.2-8.2A4 4 0 0 0 14.5 6.5z",
    "heart": "M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z",
    "message": "M4 5h16v11H9l-5 4z",
    "instagram": "M7 3h10a4 4 0 0 1 4 4v10a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4z M12 16.5a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9z M17.3 6.7h.01",
    "plus": "M12 5v14 M5 12h14",
    "arrow": "M19 12H5 M11 6l-6 6 6 6",
}


def ic(name, size=24, cls="ic"):
    return (f'<svg class="{cls}" width="{size}" height="{size}" viewBox="0 0 24 24" fill="none" '
            f'stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" '
            f'aria-hidden="true"><path d="{ICONS[name]}"/></svg>')


MARK = ('<svg viewBox="0 0 64 64" aria-hidden="true" width="38" height="38">'
        '<path d="M8 54V32a11 11 0 0 1 22 0v22z" fill="#9A46BB"/>'
        '<path d="M21 54V24a11 11 0 0 1 22 0v30z" fill="#F25371"/>'
        '<path d="M34 54V32a11 11 0 0 1 22 0v22z" fill="#FEB230"/>'
        '<path d="M49 6l2 4.5 5 .5-3.7 3.2 1.2 4.8-4.5-2.6-4.5 2.6 1.2-4.8L42 11l5-.5z" fill="#1BC1BE"/>'
        '<path d="M15 38q4 3 8 0M28 30q4 3 8 0M41 38q4 3 8 0" fill="none" stroke="#330185" '
        'stroke-width="2.4" stroke-linecap="round"/></svg>')


def url(path):
    return f"{DOMAIN}/{path}" if DOMAIN else ""


def write(path, content):
    full = os.path.join(ROOT, path)
    os.makedirs(os.path.dirname(full) or ROOT, exist_ok=True)
    with open(full, "w", encoding="utf-8") as f:
        f.write(content)


def jsonld(data):
    return '<script type="application/ld+json">' + json.dumps(data, ensure_ascii=False) + "</script>"


# ------------------------------------------------------------------ اجزای مشترک
NAV = [
    ("products", "اپ‌ها", "products/"),
    ("services", "خدمات", "services/"),
    ("blog", "بلاگ", "blog/"),
    ("about", "درباره‌ی ما", "about/"),
    ("contact", "تماس", "contact/"),
]


def head(title, desc, R, path, *, og_type="website", extra="", ld=None, image=None, preload_img=None):
    full_title = title if title.startswith(SITE["name"]) else f"{title} | {SITE['name']}"
    canon = f'<link rel="canonical" href="{url(path)}">' if DOMAIN else ""
    ogurl = f'<meta property="og:url" content="{url(path)}">' if DOMAIN else ""
    ogimg = f'<meta property="og:image" content="{DOMAIN}/{image}">' if (DOMAIN and image) else ""
    pre = f'<link rel="preload" href="{R}{preload_img}" as="image" type="image/webp">' if preload_img else ""
    ldtxt = "\n".join(jsonld(x) for x in (ld or []))
    return f"""<!doctype html>
<html lang="fa" dir="rtl" class="no-js">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>document.documentElement.className="js";</script>
<title>{esc(full_title)}</title>
<meta name="description" content="{esc(desc)}">
<meta name="robots" content="index, follow, max-image-preview:large">
<meta name="theme-color" content="#FFF8EC">
{canon}
<meta property="og:type" content="{og_type}">
<meta property="og:locale" content="fa_IR">
<meta property="og:site_name" content="{SITE['name']}">
<meta property="og:title" content="{esc(full_title)}">
<meta property="og:description" content="{esc(desc)}">
{ogurl}
{ogimg}
<link rel="icon" href="{R}favicon.svg" type="image/svg+xml">
<link rel="preload" href="{R}assets/fonts/Vazirmatn-var.woff2" as="font" type="font/woff2" crossorigin>
{pre}
<link rel="stylesheet" href="{R}style.css">
{extra}
{ldtxt}
</head>
"""


def header(R, active=""):
    links = "".join(
        f'<a href="{R}{href}"{" aria-current=\"page\"" if key == active else ""}>{label}</a>'
        for key, label, href in NAV)
    return f"""<body>
<a class="skip-link" href="#main">پرش به محتوای اصلی</a>
<header class="site-header">
  <div class="wrap bar">
    <a class="brand" href="{R}" aria-label="{SITE['name']}، صفحه‌ی اصلی">{MARK}<span>{SITE['name']}</span></a>
    <button class="menu-btn" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="باز و بسته کردن منو"><span></span></button>
    <nav class="nav" id="site-nav" aria-label="منوی اصلی">
      {links}
      <a class="btn btn--ink btn--sm nav-cta" href="{R}products/">دانلود اپ‌ها</a>
    </nav>
  </div>
</header>
"""


def footer(R):
    apps = "".join(f'<li><a href="{R}products/{p["slug"]}/">{p["name"]}</a></li>' for p in PRODUCTS)
    return f"""<footer class="site-footer">
  <div class="wrap foot-grid">
    <div class="foot-brand">
      <a class="brand" href="{R}">{MARK}<span>{SITE['name']}</span></a>
      <p class="en-tag" lang="en" dir="ltr">{SITE['tagline_en']}</p>
      <p>اپ‌های آموزشی و بازی فارسی برای کودکان؛ و محتوا و بازی سفارشی برای برندها و مراکز آموزشی.</p>
    </div>
    <div>
      <h2 class="foot-h">اپ‌ها</h2>
      <ul class="foot-list">{apps}</ul>
    </div>
    <div>
      <h2 class="foot-h">استودیو</h2>
      <ul class="foot-list">
        <li><a href="{R}services/">خدمات</a></li>
        <li><a href="{R}blog/">بلاگ</a></li>
        <li><a href="{R}about/">درباره‌ی ما</a></li>
        <li><a href="{R}contact/">تماس با ما</a></li>
      </ul>
    </div>
    <div>
      <h2 class="foot-h">ما را دنبال کنید</h2>
      <ul class="foot-list">
        <li><a class="with-ic" href="{SITE['instagram']}" target="_blank" rel="noopener">{ic('instagram', 20)}<span lang="en" dir="ltr">{SITE['instagram_handle']}</span></a></li>
      </ul>
    </div>
  </div>
  <div class="wrap foot-bottom"><p>© ۱۴۰۵ {SITE['name']}. همه‌ی حقوق محفوظ است.</p></div>
</footer>
<script src="{R}script.js" defer></script>
</body>
</html>
"""


def crumbs(R, items):
    """items: [(label, href|None)]"""
    out = [f'<li><a href="{R}">خانه</a></li>']
    for label, href in items:
        out.append(f'<li><a href="{href}">{label}</a></li>' if href else f'<li aria-current="page">{label}</li>')
    return f'<nav class="crumbs" aria-label="مسیر صفحه"><ol>{"".join(out)}</ol></nav>'


def crumb_ld(trail):
    if not DOMAIN:
        return []
    items = [{"@type": "ListItem", "position": i + 1, "name": n, "item": u} for i, (n, u) in enumerate(trail)]
    return [{"@context": "https://schema.org", "@type": "BreadcrumbList", "itemListElement": items}]


def chip(text, cls=""):
    return f'<span class="chip {cls}">{text}</span>'


def acc_style(p):
    return f'style="--accent:{p["accent"]};--tint:{p["tint"]};--on:{p["on"]}"'


def app_card(p, R):
    href = f'{R}products/{p["slug"]}/'
    chips = chip(p["age"]) + (chip("به‌زودی", "chip--soon") if p["status"] == "soon" else chip(p["kind"]))
    if p["status"] == "live":
        buy = (f'<a class="btn btn--accent btn--sm" href="{p["bazaar"]}" target="_blank" rel="noopener">'
               f'دانلود از کافه‌بازار<span class="sr-only"> {p["name"]}</span></a>')
    else:
        buy = '<span class="btn btn--off btn--sm">به‌زودی</span>'
    return f"""<article class="app" {acc_style(p)}>
  <div class="app-head">
    <img class="tile" src="{R}{p['icon']}" width="64" height="64" alt="" loading="lazy" decoding="async">
    <div><h3><a href="{href}">{p['name']}</a></h3><div class="chips">{chips}</div></div>
  </div>
  <p class="app-tag">{p['tagline']}</p>
  <p class="app-short">{p['short']}</p>
  <div class="actions">
    <a class="btn btn--ghost btn--sm" href="{href}">دیدن جزئیات<span class="sr-only"> {p['name']}</span></a>
    {buy}
  </div>
</article>"""


def org_ld():
    d = {"@context": "https://schema.org", "@type": "Organization", "name": SITE["name"],
         "alternateName": SITE["name_en"],
         "description": "استودیوی ایرانی اپ‌ها و بازی‌های آموزشی کودک به زبان فارسی",
         "sameAs": [SITE["instagram"]]}
    if DOMAIN:
        d["url"] = DOMAIN + "/"
    return d


def cta_band(R, title, text, primary, secondary=None):
    sec = f'<a class="btn btn--paper btn--ghost-light" href="{secondary[1]}"{secondary[2] if len(secondary) > 2 else ""}>{secondary[0]}</a>' if secondary else ""
    return f"""<section class="section cta-band" aria-label="{esc(title)}">
  <div class="wrap">
    <div class="cta-inner">
      <div><h2>{title}</h2><p>{text}</p></div>
      <div class="actions">{primary}{sec}</div>
    </div>
  </div>
</section>"""


def faq_block(faqs, heading="سؤال‌های رایج"):
    items = "".join(f'<details><summary>{q}</summary><div class="answer"><p>{a}</p></div></details>' for q, a in faqs)
    return f"""<section class="section" aria-labelledby="faq-h">
  <div class="wrap narrow">
    <div class="sec-head"><h2 id="faq-h">{heading}</h2></div>
    <div class="faq-list">{items}</div>
  </div>
</section>"""


def faq_ld(faqs):
    return {"@context": "https://schema.org", "@type": "FAQPage",
            "mainEntity": [{"@type": "Question", "name": q,
                            "acceptedAnswer": {"@type": "Answer", "text": a}} for q, a in faqs]}


# ------------------------------------------------------------------ صفحه‌ی اصلی
def page_home():
    R = ""
    p0 = PRODUCT_BY_SLUG["goshyar"]
    icons_row = "".join(
        f'<li><a href="products/{p["slug"]}/" title="{p["name"]}"><img src="{p["icon"]}" width="56" height="56" alt="{p["name"]}" loading="lazy"></a></li>'
        for p in PRODUCTS)
    live = [p for p in PRODUCTS if p["status"] == "live"]
    apps = "".join(app_card(p, R) for p in PRODUCTS)
    posts = "".join(post_card(po, R) for po in POSTS[:3])

    facts = [
        ("shield", "بدون تبلیغات مزاحم", "وسط بازی بنر و پنجره‌ی ناخواسته باز نمی‌شود."),
        ("users", "بخش والدین قفل‌دار", "تنظیمات و گزارش‌ها پشت پرسش یا رمز جدا هستند."),
        ("book", "فارسی و مطابق کتاب", "هوهو صفحه‌به‌صفحه با کتاب‌های سوم دبستان می‌رود."),
        ("heart", "ساخت ایران", "استودیویی کوچک که برای کودکان ایرانی می‌سازد."),
    ]
    facts_html = "".join(f'<li>{ic(i, 26)}<div><h3>{t}</h3><p>{d}</p></div></li>' for i, t, d in facts)

    ld = [org_ld(), {"@context": "https://schema.org", "@type": "WebSite", "name": SITE["name"], "inLanguage": "fa"},
          {"@context": "https://schema.org", "@type": "ItemList", "name": "اپ‌های میکرو استودیو",
           "itemListElement": [{"@type": "ListItem", "position": i + 1, "item": {
               "@type": "SoftwareApplication", "name": p["name"], "description": p["short"],
               "applicationCategory": "EducationalApplication", "operatingSystem": "Android", "inLanguage": "fa",
               "url": p["bazaar"]}} for i, p in enumerate(live)]}]

    body = f"""<main id="main">
<section class="hero" style="--accent:#9A46BB">
  <div class="wrap hero-grid">
    <div class="hero-copy">
      <h1>بازی و یادگیری امن برای کودکان فارسی‌زبان</h1>
      <p class="lead">اپ‌های آموزشی و بازی فارسی برای کودکان ۳ تا ۸ ساله و دانش‌آموزان سوم دبستان؛ بدون تبلیغات مزاحم و با بخش والدین قفل‌دار.</p>
      <div class="actions">
        <a class="btn btn--ink" href="products/">دیدن اپ‌ها</a>
        <a class="btn btn--ghost" href="services/">خدمات استودیو</a>
      </div>
      <div class="app-row">
        <p>اپ‌های ما</p>
        <ul>{icons_row}</ul>
      </div>
    </div>
    <div class="hero-art" aria-hidden="false">
      <span class="shape" aria-hidden="true"></span>
      <span class="dot" aria-hidden="true"></span>
      <div class="phone"><img src="assets/img/goshyar-1.webp" width="540" height="1170" alt="صفحه‌ی اصلی اپ گوش‌یار با خرس هدفون‌به‌سر" fetchpriority="high"></div>
    </div>
  </div>
</section>

<section class="facts" aria-label="ویژگی‌های اپ‌ها">
  <div class="wrap"><ul>{facts_html}</ul></div>
</section>

<section class="section" id="apps" aria-labelledby="apps-h">
  <div class="wrap">
    <div class="sec-head sec-head--row">
      <div><h2 id="apps-h">اپ‌های میکرو استودیو</h2><p>برای هر سن و هر مهارت یک اپ داریم. همه در کافه‌بازار هستند.</p></div>
      <a class="more" href="products/">همه‌ی اپ‌ها</a>
    </div>
    <div class="grid grid--3">{apps}</div>
  </div>
</section>

<section class="section section--band" aria-labelledby="spot-h">
  <div class="wrap spot">
    <div class="spot-text">
      <h2 id="spot-h">ببینید گوش‌یار چطور کار می‌کند</h2>
      <p>یک صدا پخش می‌شود و کودک باید بین چند تصویر، صاحبش را پیدا کند. حیوان‌ها، سازها، وسایل خانه و طبیعت؛ مرحله به مرحله و با ماموریت.</p>
      <ul class="ticks">
        <li>{ic('check', 20)}تمرین شنیدن، تمرکز و حافظه‌ی شنیداری</li>
        <li>{ic('check', 20)}برای کودکان ۳ تا ۸ ساله</li>
        <li>{ic('check', 20)}گزارش پیشرفت در بخش والدین قفل‌دار</li>
      </ul>
      <div class="actions">
        <a class="btn btn--accent" style="--accent:{p0['accent']};--on:{p0['on']}" href="{p0['bazaar']}" target="_blank" rel="noopener">دانلود گوش‌یار از کافه‌بازار</a>
        <a class="btn btn--ghost" href="products/goshyar/">دیدن جزئیات</a>
      </div>
    </div>
    <div class="video video--portrait">
      <video controls preload="none" playsinline poster="assets/video/goshyar-demo.webp" aria-label="ضبط صفحه‌ی اپ گوش‌یار">
        <source src="assets/video/goshyar-demo.mp4" type="video/mp4">
      </video>
    </div>
  </div>
</section>

<section class="section" aria-labelledby="studio-h">
  <div class="wrap">
    <div class="sec-head"><h2 id="studio-h">پشت اپ‌ها یک استودیو است</h2><p>غیر از اپ‌های خودمان، برای والدین راهنما می‌نویسیم و برای برندها و مراکز آموزشی محتوا و بازی می‌سازیم.</p></div>
    <div class="grid grid--2">
      <a class="panel" href="blog/">
        <span class="panel-ic">{ic('book', 28)}</span>
        <h3>برای والدین و معلم‌ها</h3>
        <p>راهنمای انتخاب اپ امن، زمان صفحه‌نمایش، بازی‌های خانگی و آموزش کار با اپ‌ها.</p>
        <span class="more">رفتن به بلاگ</span>
      </a>
      <a class="panel" href="services/">
        <span class="panel-ic">{ic('film', 28)}</span>
        <h3>برای برندها و مراکز آموزشی</h3>
        <p>موشن و ریلز، طراحی بازی، انیمیشن و بازی آموزشی اختصاصی.</p>
        <span class="more">دیدن خدمات</span>
      </a>
    </div>
  </div>
</section>

<section class="section section--band" aria-labelledby="blog-h">
  <div class="wrap">
    <div class="sec-head sec-head--row">
      <div><h2 id="blog-h">تازه‌ترین نوشته‌ها</h2></div>
      <a class="more" href="blog/">همه‌ی نوشته‌ها</a>
    </div>
    <div class="grid grid--3">{posts}</div>
  </div>
</section>

{cta_band(R, "اپ‌ها را از کافه‌بازار بگیرید", "همه‌ی اپ‌ها در کافه‌بازار هستند. خبر اپ‌های تازه را هم در اینستاگرام می‌گذاریم.",
          '<a class="btn btn--paper" href="products/">دیدن اپ‌ها</a>',
          ("اینستاگرام ما", SITE["instagram"], ' target="_blank" rel="noopener"'))}
</main>
"""
    out = (head("میکرو استودیو | اپ‌های آموزشی و بازی کودک فارسی",
                "میکرو استودیو اپ‌های آموزشی و بازی فارسی برای کودکان می‌سازد: گوش‌یار، پشمک و هوهو (فارسی، ریاضی، علوم سوم دبستان). بدون تبلیغات مزاحم؛ دانلود از کافه‌بازار.",
                R, "", ld=ld, image="assets/img/goshyar-1.webp", preload_img="assets/img/goshyar-1.webp")
           + header(R, "") + body + footer(R))
    write("index.html", out)
    PAGES.append(("", "1.0"))


def post_card(po, R):
    return f"""<article class="post-card">
  <div class="meta">{chip(po['tag'])}<span>{po['date_fa']}</span></div>
  <h3><a href="{R}blog/{po['slug']}/">{po['title']}</a></h3>
  <p>{po['desc']}</p>
  <span class="more" aria-hidden="true">ادامه‌ی مطلب</span>
</article>"""


# ------------------------------------------------------------------ لیست محصولات
def page_products():
    R = "../"
    young = [p for p in PRODUCTS if p["slug"] in ("goshyar", "pashmak", "popo")]
    school = [p for p in PRODUCTS if p["slug"].startswith("hoho")]
    live = [p for p in PRODUCTS if p["status"] == "live"]
    ld = [{"@context": "https://schema.org", "@type": "CollectionPage", "name": "اپ‌های میکرو استودیو", "inLanguage": "fa"}]
    ld += crumb_ld([("خانه", url("")), ("اپ‌ها", url("products/"))])
    body = f"""<main id="main">
<section class="page-head">
  <div class="wrap">
    {crumbs(R, [("اپ‌ها", None)])}
    <h1>اپ‌های میکرو استودیو</h1>
    <p class="lead">اپ‌های آموزشی و بازی فارسی؛ بدون تبلیغات مزاحم و با بخش والدین قفل‌دار. همه در کافه‌بازار.</p>
  </div>
</section>
<section class="section section--tight" aria-labelledby="young-h">
  <div class="wrap">
    <div class="sec-head"><h2 id="young-h">برای کودکان ۲ تا ۸ ساله</h2><p>بازی و کشف: شنیدن، نقاشی و ساختن.</p></div>
    <div class="grid grid--3">{''.join(app_card(p, R) for p in young)}</div>
  </div>
</section>
<section class="section section--band section--tight" aria-labelledby="school-h">
  <div class="wrap">
    <div class="sec-head"><h2 id="school-h">برای دانش‌آموز سوم دبستان</h2><p>هوهو، جغد معلم؛ درس‌ها صفحه‌به‌صفحه با کتاب، همراه با تمرین، آزمون و کاربرگ.</p></div>
    <div class="grid grid--3">{''.join(app_card(p, R) for p in school)}</div>
  </div>
</section>
<section class="section section--tight">
  <div class="wrap narrow">
    <div class="note">
      <p><strong>در حال ساخت:</strong> پیلو و دو آسمان. خبر انتشارشان را در <a href="{SITE['instagram']}" target="_blank" rel="noopener">اینستاگرام</a> می‌گذاریم.</p>
    </div>
  </div>
</section>
{cta_band(R, "برای بچه‌تان کدام اپ مناسب‌تر است؟", "در دایرکت اینستاگرام سن کودک را بنویسید تا راهنمایی‌تان کنیم.",
          f'<a class="btn btn--paper" href="{SITE["instagram"]}" target="_blank" rel="noopener">پیام در اینستاگرام</a>')}
</main>
"""
    out = (head("اپ‌های آموزشی و بازی کودک فارسی",
                "اپ‌های میکرو استودیو: گوش‌یار (شنیدن)، پشمک (نقاشی)، هوهو فارسی، ریاضی و علوم سوم دبستان و پوپو. بدون تبلیغات مزاحم؛ دانلود از کافه‌بازار.",
                R, "products/", ld=ld) + header(R, "products") + body + footer(R))
    write("products/index.html", out)
    PAGES.append(("products/", "0.9"))


# ------------------------------------------------------------------ صفحه‌ی هر محصول
def gallery(p, R):
    figs = []
    for i in range(1, p["n_img"] + 1):
        cap = p["captions"][i - 1]
        src = f'{R}assets/img/{p["slug"]}-{i}.webp'
        figs.append(f"""<figure class="shot-item">
  <button class="shot" type="button" data-full="{src}" data-cap="{esc(cap)}" aria-label="بزرگ‌نمایی: {esc(cap)}">
    <img src="{src}" width="540" height="1170" alt="{esc(p['name'])}؛ {esc(cap)}" loading="lazy" decoding="async">
  </button>
  <figcaption>{cap}</figcaption>
</figure>""")
    return "".join(figs)


def video_block(p, v, R):
    cls = "video--wide" if v["orient"] == "landscape" else "video--portrait"
    return f"""<div class="vid-item">
  <div class="video {cls}">
    <video controls preload="none" playsinline poster="{R}assets/video/{v['src']}.webp" aria-label="{esc(v['title'])}">
      <source src="{R}assets/video/{v['src']}.mp4" type="video/mp4">
    </video>
  </div>
  <h3>{v['title']}</h3>
  <p>{v['text']}</p>
</div>"""


def page_product(p):
    R = "../../"
    soon = p["status"] == "soon"
    if soon:
        primary = f'<a class="btn btn--accent" href="{SITE["instagram"]}" target="_blank" rel="noopener">خبر انتشار را در اینستاگرام ببینید</a>'
        facts_dl = [("نام", p["name"]), ("رده‌ی سنی", p["age"]), ("وضعیت", "به‌زودی در کافه‌بازار"), ("زبان", "فارسی"), ("سازنده", SITE["name"])]
    else:
        primary = (f'<a class="btn btn--accent" href="{p["bazaar"]}" target="_blank" rel="noopener">'
                   f'دانلود از کافه‌بازار<span class="sr-only"> {p["name"]}</span></a>')
        facts_dl = [("نام", p["name"]), ("رده‌ی سنی", p["age"]), ("پلتفرم", "اندروید (کافه‌بازار)"), ("زبان", "فارسی"), ("سازنده", SITE["name"])]
    dl = "".join(f"<div><dt>{k}</dt><dd>{v}</dd></div>" for k, v in facts_dl)
    intro = "".join(f"<p>{t}</p>" for t in p["intro"])
    feats = "".join(f'<li class="feat"><span class="feat-ic">{ic(i, 24)}</span><div><h3>{t}</h3><p>{d}</p></div></li>' for i, t, d in p["features"])
    parents = "".join(f'<li>{ic("check", 20)}<span>{t}</span></li>' for t in p["parents"])
    videos = ""
    if p["videos"]:
        videos = f"""<section class="section section--band" aria-labelledby="vid-h">
  <div class="wrap">
    <div class="sec-head"><h2 id="vid-h">ویدیو</h2><p>{'تیزر و تصویری از آنچه در راه است.' if soon else 'ببینید اپ در عمل چطور کار می‌کند.'}</p></div>
    <div class="vids">{''.join(video_block(p, v, R) for v in p['videos'])}</div>
  </div>
</section>"""
    if soon:
        reviews = f"""<section class="section" aria-labelledby="rev-h">
  <div class="wrap narrow">
    <div class="sec-head"><h2 id="rev-h">نظر والدین</h2></div>
    <div class="empty">{ic('message', 28)}<div><p>پوپو هنوز منتشر نشده و نظری ندارد. خبرش را در اینستاگرام اعلام می‌کنیم.</p></div></div>
  </div>
</section>"""
    else:
        reviews = f"""<section class="section" aria-labelledby="rev-h">
  <div class="wrap narrow">
    <div class="sec-head"><h2 id="rev-h">نظر والدین</h2><p>نظرهای واقعی کاربران را در صفحه‌ی {p['name']} در کافه‌بازار بخوانید. ما نظری از خودمان اینجا نمی‌گذاریم.</p></div>
    <div class="empty">{ic('message', 28)}
      <div>
        <p>اگر از {p['name']} استفاده کرده‌اید، نظر شما به والدین دیگر کمک می‌کند.</p>
        <div class="actions"><a class="btn btn--ghost btn--sm" href="{p['bazaar']}" target="_blank" rel="noopener">دیدن و نوشتن نظر در کافه‌بازار</a></div>
      </div>
    </div>
  </div>
</section>"""
    related = "".join(app_card(PRODUCT_BY_SLUG[s], R) for s in p["related"])
    posts = [po for po in POSTS if p["slug"] in po["related"]][:2]
    posts_html = ""
    if posts:
        posts_html = f"""<section class="section section--band section--tight" aria-labelledby="rp-h">
  <div class="wrap">
    <div class="sec-head"><h2 id="rp-h">بخوانید</h2></div>
    <div class="grid grid--2">{''.join(post_card(po, R) for po in posts)}</div>
  </div>
</section>"""

    page_path = f"products/{p['slug']}/"
    ld = []
    if not soon:
        app = {"@context": "https://schema.org", "@type": "SoftwareApplication", "name": p["name"],
               "description": p["seo_desc"], "applicationCategory": "EducationalApplication",
               "operatingSystem": "Android", "inLanguage": "fa", "url": p["bazaar"],
               "audience": ({"@type": "PeopleAudience", "suggestedMinAge": p["age_min"], "suggestedMaxAge": p["age_max"]}
                            if not p["slug"].startswith("hoho") else
                            {"@type": "EducationalAudience", "educationalRole": "student", "audienceType": "دانش‌آموز پایه‌ی سوم دبستان"}),
               "publisher": {"@type": "Organization", "name": SITE["name"]}}
        if DOMAIN:
            app["image"] = f"{DOMAIN}/assets/img/{p['slug']}-1.webp"
            app["screenshot"] = [f"{DOMAIN}/assets/img/{p['slug']}-{i}.webp" for i in range(1, p["n_img"] + 1)]
        ld.append(app)
    if DOMAIN:
        for v in p["videos"]:
            ld.append({"@context": "https://schema.org", "@type": "VideoObject", "name": f"{p['name']}: {v['title']}",
                       "description": v["text"], "thumbnailUrl": f"{DOMAIN}/assets/video/{v['src']}.webp",
                       "contentUrl": f"{DOMAIN}/assets/video/{v['src']}.mp4", "uploadDate": "2026-11-11"})
    ld.append(faq_ld(p["faq"]))
    ld += crumb_ld([("خانه", url("")), ("اپ‌ها", url("products/")), (p["name"], url(page_path))])

    dl_bar = ""
    if not soon:
        dl_bar = f"""<div class="dl-bar" {acc_style(p)}>
  <div><strong>{p['name']}</strong><span>{p['age']}</span></div>
  <a class="btn btn--accent btn--sm" href="{p['bazaar']}" target="_blank" rel="noopener">دانلود از کافه‌بازار</a>
</div>"""

    cta_primary = (f'<a class="btn btn--paper" href="{p["bazaar"]}" target="_blank" rel="noopener">دانلود از کافه‌بازار</a>' if not soon
                   else f'<a class="btn btn--paper" href="{SITE["instagram"]}" target="_blank" rel="noopener">دنبال کردن در اینستاگرام</a>')

    body = f"""<main id="main" class="product{' has-dl-bar' if not soon else ''}" {acc_style(p)}>
<section class="hero hero--product">
  <div class="wrap hero-grid">
    <div class="hero-copy">
      {crumbs(R, [("اپ‌ها", R + "products/"), (p['name'], None)])}
      <div class="title-row">
        <img class="tile tile--lg" src="{R}{p['icon']}" width="88" height="88" alt="آیکن {p['name']}">
        <h1>{p['name']}</h1>
      </div>
      <p class="lead">{p['tagline']}</p>
      <div class="chips">{chip(p['age'])}{chip(p['kind'])}{chip('به‌زودی', 'chip--soon') if soon else chip('فارسی')}</div>
      <p class="hero-short">{p['short']}</p>
      <div class="actions">{primary}<a class="btn btn--ghost" href="#gallery">دیدن تصویرها</a></div>
    </div>
    <div class="hero-art">
      <span class="shape" aria-hidden="true"></span>
      <div class="phone"><img src="{R}assets/img/{p['slug']}-1.webp" width="540" height="1170" alt="{p['name']}؛ {p['captions'][0]}" fetchpriority="high"></div>
    </div>
  </div>
</section>

<section class="section" aria-labelledby="about-h">
  <div class="wrap two-col">
    <div class="prose">
      <h2 id="about-h">درباره‌ی {p['name']}</h2>
      {intro}
    </div>
    <aside class="facts-card" aria-label="مشخصات اپ">
      <h3>مشخصات</h3>
      <dl>{dl}</dl>
      <div class="actions">{primary}</div>
    </aside>
  </div>
</section>

<section class="section section--band" aria-labelledby="feat-h">
  <div class="wrap">
    <div class="sec-head"><h2 id="feat-h">چه چیزهایی دارد؟</h2></div>
    <ul class="feat-grid">{feats}</ul>
  </div>
</section>

{videos}

<section class="section" id="gallery" aria-labelledby="gal-h">
  <div class="wrap">
    <div class="sec-head"><h2 id="gal-h">گالری تصویر</h2><p>روی هر تصویر بزنید تا بزرگ ببینید.</p></div>
  </div>
  <div class="wrap wrap--bleed"><div class="rail" role="list">{gallery(p, R)}</div></div>
</section>

<section class="section section--tint" aria-labelledby="par-h">
  <div class="wrap narrow">
    <div class="sec-head"><h2 id="par-h">برای والدین</h2></div>
    <ul class="ticks ticks--lg">{parents}</ul>
  </div>
</section>

{reviews}

{faq_block(p['faq'], f'سؤال‌های رایج درباره‌ی {p["name"]}')}

{posts_html}

<section class="section" aria-labelledby="rel-h">
  <div class="wrap">
    <div class="sec-head sec-head--row"><div><h2 id="rel-h">اپ‌های دیگر</h2></div><a class="more" href="{R}products/">همه‌ی اپ‌ها</a></div>
    <div class="grid grid--3">{related}</div>
  </div>
</section>

{cta_band(R, f'{p["name"]} را امتحان کنید' if not soon else 'اول از همه خبردار شوید',
          'از کافه‌بازار بگیرید و ده دقیقه خودتان هم بازی کنید.' if not soon else 'پوپو در انتظار تأیید کافه‌بازار است. اعلام انتشار اول در اینستاگرام است.',
          cta_primary)}
</main>
{dl_bar}
"""
    out = (head(p["seo_title"], p["seo_desc"], R, page_path, ld=ld, image=f"assets/img/{p['slug']}-1.webp",
                preload_img=f"assets/img/{p['slug']}-1.webp")
           + header(R, "products") + body + footer(R))
    write(f"{page_path}index.html", out)
    PAGES.append((page_path, "0.9" if not soon else "0.6"))


# ------------------------------------------------------------------ خدمات
def page_services():
    R = "../"
    cards = "".join(f'<article class="svc"><span class="feat-ic">{ic(i, 26)}</span><h3>{t}</h3><p>{d}</p></article>' for i, t, d in SERVICES)
    steps = [
        ("گفت‌وگو", "هدف، مخاطب و محدوده‌ی کار را با هم روشن می‌کنیم."),
        ("پیشنهاد", "محدوده، زمان و قیمت را به‌صورت مشخص برایتان می‌نویسیم."),
        ("تولید", "کار را مرحله‌به‌مرحله می‌سازیم و پیش‌نویس را نشان می‌دهیم."),
        ("تحویل", "فایل‌های نهایی را با اصلاحات توافق‌شده تحویل می‌دهیم."),
    ]
    steps_html = "".join(f"<li><h3>{t}</h3><p>{d}</p></li>" for t, d in steps)
    stds = "".join(f'<li>{ic("check", 20)}<span>{s}</span></li>' for s in STANDARDS)
    samples = "".join(app_card(PRODUCT_BY_SLUG[s], R) for s in ("goshyar", "pashmak", "hoho-farsi"))
    ld = [{"@context": "https://schema.org", "@type": "Service", "name": "خدمات استودیو میکرو استودیو",
           "provider": {"@type": "Organization", "name": SITE["name"]}, "areaServed": "IR", "inLanguage": "fa",
           "serviceType": ["موشن گرافیک", "طراحی بازی", "انیمیشن", "بازی آموزشی"]}]
    ld += crumb_ld([("خانه", url("")), ("خدمات", url("services/"))])
    body = f"""<main id="main">
<section class="page-head">
  <div class="wrap">
    {crumbs(R, [("خدمات", None)])}
    <h1>خدمات استودیو</h1>
    <p class="lead">غیر از اپ‌های خودمان، برای برندها، ناشران، مهدکودک‌ها و مراکز آموزشی هم موشن، بازی و انیمیشن می‌سازیم.</p>
    <div class="actions"><a class="btn btn--ink" href="{R}contact/">درخواست پروژه</a><a class="btn btn--ghost" href="#samples">دیدن نمونه‌کارها</a></div>
  </div>
</section>

<section class="section section--tight" aria-labelledby="svc-h">
  <div class="wrap">
    <div class="sec-head"><h2 id="svc-h">چه چیزهایی می‌سازیم؟</h2><p>قیمت و زمان هر پروژه بعد از بررسی نیاز مشخص می‌شود.</p></div>
    <div class="grid grid--3">{cards}</div>
  </div>
</section>

<section class="section section--band" aria-labelledby="proc-h">
  <div class="wrap">
    <div class="sec-head"><h2 id="proc-h">مسیر کار</h2></div>
    <ol class="proc">{steps_html}</ol>
  </div>
</section>

<section class="section" id="samples" aria-labelledby="smp-h">
  <div class="wrap">
    <div class="sec-head"><h2 id="smp-h">نمونه‌کارها</h2><p>بهترین نمونه‌ی کار ما اپ‌هایی است که خودمان ساخته‌ایم: شخصیت، انیمیشن، رابط کاربری و بازی، همه از یک تیم.</p></div>
    <div class="grid grid--3">{samples}</div>
    <div class="vids vids--row">
      <div class="vid-item"><div class="video video--portrait"><video controls preload="none" playsinline poster="{R}assets/video/hoho-farsi-teaser.webp" aria-label="تیزر هوهو فارسی"><source src="{R}assets/video/hoho-farsi-teaser.mp4" type="video/mp4"></video></div><h3>تیزر هوهو فارسی</h3></div>
      <div class="vid-item"><div class="video video--portrait"><video controls preload="none" playsinline poster="{R}assets/video/popo-teaser.webp" aria-label="تیزر پوپو"><source src="{R}assets/video/popo-teaser.mp4" type="video/mp4"></video></div><h3>تیزر پوپو</h3></div>
    </div>
  </div>
</section>

<section class="section section--tint" aria-labelledby="std-h">
  <div class="wrap narrow">
    <div class="sec-head"><h2 id="std-h">استانداردهای ما در کار با محتوای کودک</h2><p>چون برای کودکان می‌سازیم، این اصول در هر پروژه ثابت است.</p></div>
    <ul class="ticks ticks--lg">{stds}</ul>
  </div>
</section>

{cta_band(R, "پروژه‌تان را برایمان بنویسید", "در دایرکت اینستاگرام موضوع، مخاطب و زمان تقریبی را بنویسید تا درباره‌اش حرف بزنیم.",
          f'<a class="btn btn--paper" href="{R}contact/">راه‌های تماس</a>')}
</main>
"""
    out = (head("خدمات استودیو: موشن، طراحی بازی و انیمیشن کودک",
                "خدمات میکرو استودیو: موشن و ریلز تبلیغاتی، طراحی و ساخت بازی، انیمیشن و شخصیت، بازی آموزشی اختصاصی برای مهدکودک و مراکز آموزشی.",
                R, "services/", ld=ld) + header(R, "services") + body + footer(R))
    write("services/index.html", out)
    PAGES.append(("services/", "0.8"))


# ------------------------------------------------------------------ بلاگ
def page_blog_index():
    R = "../"
    cards = "".join(post_card(po, R) for po in POSTS)
    ld = [{"@context": "https://schema.org", "@type": "Blog", "name": "بلاگ میکرو استودیو", "inLanguage": "fa"}]
    ld += crumb_ld([("خانه", url("")), ("بلاگ", url("blog/"))])
    body = f"""<main id="main">
<section class="page-head">
  <div class="wrap">
    {crumbs(R, [("بلاگ", None)])}
    <h1>بلاگ میکرو استودیو</h1>
    <p class="lead">راهنمای والدین و معلم‌ها: انتخاب اپ امن، زمان صفحه‌نمایش، بازی‌های خانگی و آموزش کار با اپ‌ها.</p>
  </div>
</section>
<section class="section section--tight">
  <div class="wrap"><div class="grid grid--2">{cards}</div></div>
</section>
{cta_band(R, "اپ‌های ما را ببینید", "همه‌ی اپ‌ها در کافه‌بازار هستند.", f'<a class="btn btn--paper" href="{R}products/">دیدن اپ‌ها</a>')}
</main>
"""
    out = (head("بلاگ؛ راهنمای والدین برای بازی و اپ کودک",
                "نوشته‌هایی برای والدین و معلم‌ها: چطور اپ آموزشی امن انتخاب کنیم، زمان صفحه‌نمایش کودک، بازی‌های شنیداری در خانه و آموزش کار با اپ‌ها.",
                R, "blog/", ld=ld) + header(R, "blog") + body + footer(R))
    write("blog/index.html", out)
    PAGES.append(("blog/", "0.8"))


def page_post(po):
    R = "../../"
    path = f"blog/{po['slug']}/"
    body_html = po["body"].replace("{R}", R)
    rel = "".join(app_card(PRODUCT_BY_SLUG[s], R) for s in po["related"])
    others = [x for x in POSTS if x["slug"] != po["slug"]][:2]
    ld = [{"@context": "https://schema.org", "@type": "Article", "headline": po["title"], "description": po["desc"],
           "inLanguage": "fa", "datePublished": po["date"], "dateModified": po["date"],
           "author": {"@type": "Organization", "name": SITE["name"]},
           "publisher": {"@type": "Organization", "name": SITE["name"]}}]
    if DOMAIN:
        ld[0]["mainEntityOfPage"] = url(path)
    ld += crumb_ld([("خانه", url("")), ("بلاگ", url("blog/")), (po["title"], url(path))])
    body = f"""<main id="main">
<article class="article">
  <header class="page-head page-head--article">
    <div class="wrap narrow">
      {crumbs(R, [("بلاگ", R + "blog/"), (po['tag'], None)])}
      <h1>{po['title']}</h1>
      <p class="meta"><time datetime="{po['date']}">{po['date_fa']}</time><span>مطالعه: {po['read']}</span><span>{SITE['name']}</span></p>
    </div>
  </header>
  <div class="wrap narrow"><div class="prose prose--article">{body_html}</div></div>
</article>
<section class="section section--band" aria-labelledby="ra-h">
  <div class="wrap">
    <div class="sec-head"><h2 id="ra-h">اپ‌های مرتبط</h2></div>
    <div class="grid grid--2">{rel}</div>
  </div>
</section>
<section class="section section--tight" aria-labelledby="mo-h">
  <div class="wrap">
    <div class="sec-head"><h2 id="mo-h">بیشتر بخوانید</h2></div>
    <div class="grid grid--2">{''.join(post_card(x, R) for x in others)}</div>
  </div>
</section>
</main>
"""
    out = (head(po["title"], po["desc"], R, path, og_type="article", ld=ld) + header(R, "blog") + body + footer(R))
    write(f"{path}index.html", out)
    PAGES.append((path, "0.7"))


# ------------------------------------------------------------------ درباره و تماس
def page_about():
    R = "../"
    lines = [
        ("gamepad", "اپ و بازی", "گوش‌یار، پشمک، پوپو و هوهو؛ اپ‌هایی برای شنیدن، نقاشی، کشف و درس."),
        ("sparkles", "انیمیشن و شخصیت", "شخصیت‌هایی مثل پشمک، جغد هوهو و روباه پوپو که در اپ‌ها و ویدیوها دیده می‌شوند."),
        ("film", "موشن و رسانه", "ویدیوهای کوتاه، معما و محتوای شبکه‌های اجتماعی."),
        ("book", "راهنمای والدین", "نوشته‌هایی برای انتخاب اپ امن، زمان صفحه‌نمایش و بازی در خانه."),
        ("users", "خدمات به دیگران", "موشن، بازی و انیمیشن سفارشی برای برندها و مراکز آموزشی."),
    ]
    lines_html = "".join(f'<li class="feat"><span class="feat-ic">{ic(i, 24)}</span><div><h3>{t}</h3><p>{d}</p></div></li>' for i, t, d in lines)
    stds = "".join(f'<li>{ic("check", 20)}<span>{s}</span></li>' for s in STANDARDS)
    ld = [org_ld()] + crumb_ld([("خانه", url("")), ("درباره‌ی ما", url("about/"))])
    body = f"""<main id="main">
<section class="page-head">
  <div class="wrap">
    {crumbs(R, [("درباره‌ی ما", None)])}
    <h1>درباره‌ی میکرو استودیو</h1>
    <p class="lead en-line" lang="en" dir="ltr">{SITE['tagline_en']}</p>
  </div>
</section>

<section class="section section--tight" aria-labelledby="who-h">
  <div class="wrap two-col">
    <div class="prose">
      <h2 id="who-h">ما که هستیم؟</h2>
      <p>میکرو استودیو یک استودیوی کوچک ایرانی است که اپ‌ها و بازی‌های آموزشی کودک می‌سازد؛ با این باور که یادگیری باید شاد، امن و فارسی باشد.</p>
      <p>هدف ما این است که مرجع معتبری برای بازی، سرگرمی دیجیتال و محتوای انیمیشنی کودک و نوجوان در ایران بشویم، با محصولات و شخصیت‌های اختصاصی خودمان.</p>
      <p>اپ‌هایمان را برای والدین هم می‌سازیم: بدون تبلیغات مزاحم، با رده‌ی سنی روشن و با بخشی قفل‌دار برای بزرگ‌سالان.</p>
    </div>
    <aside class="facts-card" aria-label="در یک نگاه">
      <h3>در یک نگاه</h3>
      <dl>
        <div><dt>نام</dt><dd>{SITE['name']} (<span lang="en" dir="ltr">Micro Studio</span>)</dd></div>
        <div><dt>حوزه</dt><dd>اپ، بازی، انیمیشن و محتوای کودک</dd></div>
        <div><dt>زبان</dt><dd>فارسی</dd></div>
        <div><dt>اینستاگرام</dt><dd><a href="{SITE['instagram']}" target="_blank" rel="noopener" lang="en" dir="ltr">{SITE['instagram_handle']}</a></dd></div>
      </dl>
    </aside>
  </div>
</section>

<section class="section section--band" aria-labelledby="do-h">
  <div class="wrap">
    <div class="sec-head"><h2 id="do-h">چه می‌سازیم؟</h2><p>همه‌ی این‌ها به هم وصل‌اند: شخصیتی که در انیمیشن می‌بینید، در اپ هم همراه کودک است.</p></div>
    <ul class="feat-grid">{lines_html}</ul>
  </div>
</section>

<section class="section" aria-labelledby="std-h">
  <div class="wrap narrow">
    <div class="sec-head"><h2 id="std-h">استانداردهای ما</h2><p>چون برای کودکان می‌سازیم، این اصول را از اول قبول کرده‌ایم.</p></div>
    <ul class="ticks ticks--lg">{stds}</ul>
  </div>
</section>

{cta_band(R, "با ما حرف بزنید", "پرسش، پیشنهاد یا پروژه؛ در اینستاگرام برایمان بنویسید.",
          f'<a class="btn btn--paper" href="{R}contact/">راه‌های تماس</a>',
          ("دیدن اپ‌ها", R + "products/"))}
</main>
"""
    out = (head("درباره‌ی میکرو استودیو",
                "میکرو استودیو استودیوی کوچک ایرانی اپ و بازی آموزشی کودک است. ما اپ‌های فارسی، بدون تبلیغات مزاحم و با بخش والدین قفل‌دار می‌سازیم.",
                R, "about/", ld=ld) + header(R, "about") + body + footer(R))
    write("about/index.html", out)
    PAGES.append(("about/", "0.7"))


def page_contact():
    R = "../"
    topics = [
        ("film", "سفارش پروژه", "موشن، بازی یا انیمیشن برای برند یا مجموعه‌ی شما؛ موضوع، مخاطب و زمان تقریبی را بنویسید."),
        ("book", "همکاری آموزشی", "مهدکودک، مدرسه یا مرکز آموزشی هستید؟ بنویسید چه سنی و چه موضوعی مدنظر است."),
        ("star", "همکاری رسانه‌ای", "پیشنهاد همکاری تبلیغاتی یا معرفی متقابل را با توضیح کوتاه بفرستید."),
        ("tool", "مشکل یا پیشنهاد درباره‌ی اپ", "نام اپ و توضیح مشکل یا پیشنهادتان را بنویسید. برای مشکل فنی، مدل گوشی هم مفید است."),
    ]
    topics_html = "".join(f'<article class="svc"><span class="feat-ic">{ic(i, 26)}</span><h3>{t}</h3><p>{d}</p></article>' for i, t, d in topics)
    ld = [org_ld()] + crumb_ld([("خانه", url("")), ("تماس با ما", url("contact/"))])
    body = f"""<main id="main">
<section class="page-head">
  <div class="wrap">
    {crumbs(R, [("تماس با ما", None)])}
    <h1>تماس با ما</h1>
    <p class="lead">سریع‌ترین راه، پیام در دایرکت اینستاگرام است. هر موضوعی که دارید، همان‌جا بنویسید.</p>
  </div>
</section>

<section class="section section--tight">
  <div class="wrap narrow">
    <a class="channel" href="{SITE['instagram']}" target="_blank" rel="noopener">
      <span class="feat-ic">{ic('instagram', 28)}</span>
      <span><strong>اینستاگرام</strong><span class="en-handle" lang="en" dir="ltr">{SITE['instagram_handle']}</span><span>پیام در دایرکت</span></span>
    </a>
  </div>
</section>

<section class="section section--band" aria-labelledby="top-h">
  <div class="wrap">
    <div class="sec-head"><h2 id="top-h">درباره‌ی چه موضوعی می‌نویسید؟</h2><p>با نوشتن این جزئیات در پیام اول، سریع‌تر به نتیجه می‌رسیم.</p></div>
    <div class="grid grid--2">{topics_html}</div>
  </div>
</section>

<section class="section" aria-labelledby="dl-h">
  <div class="wrap narrow">
    <div class="sec-head"><h2 id="dl-h">دنبال دانلود اپ هستید؟</h2></div>
    <p>همه‌ی اپ‌ها در کافه‌بازار هستند. از <a href="{R}products/">صفحه‌ی اپ‌ها</a> یکی را انتخاب کنید و روی «دانلود از کافه‌بازار» بزنید.</p>
  </div>
</section>
</main>
"""
    out = (head("تماس با ما",
                "راه‌های تماس با میکرو استودیو: پیام در اینستاگرام برای سفارش پروژه، همکاری آموزشی و رسانه‌ای، یا گزارش مشکل و پیشنهاد درباره‌ی اپ‌ها.",
                R, "contact/", ld=ld) + header(R, "contact") + body + footer(R))
    write("contact/index.html", out)
    PAGES.append(("contact/", "0.7"))


def page_404():
    R = "/"  # صفحه‌ی ۴۰۴ ممکن است از هر مسیری باز شود؛ لینک‌ها نسبت به ریشه
    # برای سازگاری با میزبانی در زیرپوشه، از مسیر نسبی ساده‌تر استفاده می‌کنیم.
    R = ""
    body = f"""<main id="main">
<section class="page-head">
  <div class="wrap narrow">
    <h1>این صفحه پیدا نشد</h1>
    <p class="lead">آدرس را اشتباه نوشته‌اید یا صفحه جابه‌جا شده است. از اینجا ادامه دهید:</p>
    <div class="actions"><a class="btn btn--ink" href="{R}index.html">صفحه‌ی اصلی</a><a class="btn btn--ghost" href="{R}products/index.html">اپ‌ها</a></div>
  </div>
</section>
</main>
"""
    out = head("صفحه پیدا نشد", "این صفحه پیدا نشد.", R, "404.html").replace('content="index, follow, max-image-preview:large"', 'content="noindex"') \
        + header(R, "").replace('href=""', 'href="index.html"') + body + footer(R)
    write("404.html", out)


def write_sitemap_and_robots():
    if DOMAIN:
        urls = "".join(f"<url><loc>{DOMAIN}/{path}</loc><priority>{prio}</priority></url>" for path, prio in PAGES)
        write("sitemap.xml", '<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
              + urls + "</urlset>\n")
        write("robots.txt", f"User-agent: *\nAllow: /\n\nSitemap: {DOMAIN}/sitemap.xml\n")
    else:
        write("robots.txt", "User-agent: *\nAllow: /\n")
        p = os.path.join(ROOT, "sitemap.xml")
        if os.path.exists(p):
            os.remove(p)


def main():
    global DOMAIN
    ap = argparse.ArgumentParser()
    ap.add_argument("--domain", default="", help="مثل https://example.ir (بدون اسلش پایانی)")
    a = ap.parse_args()
    DOMAIN = a.domain.rstrip("/")
    page_home()
    page_products()
    for p in PRODUCTS:
        page_product(p)
    page_services()
    page_blog_index()
    for po in POSTS:
        page_post(po)
    page_about()
    page_contact()
    page_404()
    write_sitemap_and_robots()
    print(f"ساخته شد: {len(PAGES)} صفحه" + (f" با دامنه {DOMAIN}" if DOMAIN else " (بدون دامنه)"))


if __name__ == "__main__":
    main()
