@php
    $siteName = setting('site.name');
    $pageTitle = isset($title) && $title ? $title.' | '.$siteName : setting('site.meta_title');
    $pageDescription = $description ?? setting('site.meta_description');
    $nav = [
        ['home', 'صفحه اصلی', route('home')],
        ['games.*', 'بازی‌ها و اپ‌ها', route('games.index')],
        ['services', 'خدمات', route('services')],
        ['about', 'درباره ما', route('about')],
        ['contact', 'تماس با ما', route('contact')],
    ];
    $ctaHref = request()->routeIs('contact') ? '#contact-form' : route('contact');
@endphp
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>{{ $pageTitle }}</title>
  @php
      $metaDescription = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($pageDescription))), 160);
      $canonical = $canonical ?? url()->current();
      $shareImage = img($ogImage ?? 'assets/brand/logo-horizontal-dark.png');
      $robots = ! empty($noindex) ? 'noindex, follow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
  @endphp
  <meta name="description" content="{{ $metaDescription }}">
  <meta name="robots" content="{{ $robots }}">
  <meta name="theme-color" content="#15162a">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="author" content="{{ $siteName }}">
  <link rel="canonical" href="{{ $canonical }}">
  <link rel="alternate" hreflang="fa-IR" href="{{ $canonical }}">
  <link rel="alternate" hreflang="x-default" href="{{ $canonical }}">
  <meta property="og:type" content="{{ $ogType ?? 'website' }}">
  <meta property="og:locale" content="fa_IR">
  <meta property="og:site_name" content="{{ $siteName }}">
  <meta property="og:url" content="{{ $canonical }}">
  <meta property="og:title" content="{{ $pageTitle }}">
  <meta property="og:description" content="{{ $metaDescription }}">
  <meta property="og:image" content="{{ $shareImage }}">
  <meta property="og:image:alt" content="{{ $title ?? $siteName }}">
  @isset($ogExtra){!! $ogExtra !!}@endisset
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="{{ $pageTitle }}">
  <meta name="twitter:description" content="{{ $metaDescription }}">
  <meta name="twitter:image" content="{{ $shareImage }}">
  <link rel="alternate" type="application/rss+xml" title="{{ $siteName }} — وبلاگ" href="{{ route('feed') }}">
  <link rel="manifest" href="{{ route('manifest') }}">
  {{ \App\Support\Seo::script(array_merge(
      [\App\Support\Seo::organization(), \App\Support\Seo::website(), \App\Support\Seo::breadcrumb(\App\Support\Seo::crumbsForRequest())],
      $schema ?? [\App\Support\Seo::webPage()]
  )) }}
  <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
  <link rel="icon" href="{{ asset('assets/brand/favicon-32.png') }}" sizes="32x32" type="image/png">
  <link rel="apple-touch-icon" href="{{ asset('assets/brand/apple-touch-icon.png') }}">
  <link rel="preload" href="{{ asset('assets/fonts/Vazirmatn-var.woff2') }}" as="font" type="font/woff2" crossorigin>
  @isset($preload)<link rel="preload" href="{{ img($preload) }}" as="image">@endisset
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
  @stack('head')
</head>
<body class="{{ $bodyClass ?? 'home' }}">
  @include('partials.sprite')

  <a class="skip" href="#main">رفتن به محتوای اصلی</a>

  <div class="intro" aria-hidden="true">
    <svg class="intro-mark" viewBox="0 18 200 132"><use href="#i-logo"/></svg>
    <span class="intro-bar"><i></i></span>
  </div>
  <div class="scroll-progress" aria-hidden="true"><span></span></div>
  <div class="cursor" aria-hidden="true"><span class="cursor-dot"></span><span class="cursor-ring"></span></div>

  <!-- ============ HEADER ============ -->
  <header class="site-header" id="top">
    <div class="container header-row">
      <a class="brand" href="{{ route('home') }}" aria-label="{{ $siteName }} - صفحه اصلی">
        <svg class="brand-mark" viewBox="0 18 200 132" aria-hidden="true"><use href="#i-logo"/></svg>
        <span class="brand-text">{{ setting('site.brand_first') }} <b>{{ setting('site.brand_second') }}</b></span>
      </a>
      <nav class="main-nav" id="main-nav" aria-label="منوی اصلی">
        <ul>
          @foreach ($nav as [$pattern, $label, $href])
            <li><a href="{{ $href }}" @if (request()->routeIs($pattern)) aria-current="page" @endif>{{ $label }}</a></li>
          @endforeach
        </ul>
        <a class="btn btn-primary nav-cta-mobile" href="{{ $ctaHref }}">{{ setting('site.header_cta') }} <svg aria-hidden="true"><use href="#i-play"/></svg></a>
      </nav>
      <a class="btn btn-primary header-cta" href="{{ $ctaHref }}">{{ setting('site.header_cta') }} <svg aria-hidden="true"><use href="#i-play"/></svg></a>
      <button class="nav-toggle" type="button" aria-controls="main-nav" aria-expanded="false" aria-label="باز کردن منو"><svg aria-hidden="true"><use href="#i-menu"/></svg></button>
    </div>
  </header>

  <main id="main">
    @yield('content')
  </main>

  <!-- ============ FOOTER ============ -->
  <footer class="site-footer">
    <img class="footer-bg" src="{{ img(setting('site.footer_image')) }}" alt="" width="1456" height="818" loading="lazy">
    <div class="container footer-grid">
      <div class="f-col">
        <h3 class="sr-only">شبکه‌های اجتماعی</h3>
        <div class="f-social">
          @if (setting('contact.instagram_url'))<a href="{{ setting('contact.instagram_url') }}" target="_blank" rel="noopener" aria-label="اینستاگرام میکرو استودیو"><svg aria-hidden="true"><use href="#i-insta"/></svg></a>@endif
          @if (setting('contact.telegram_url'))<a href="{{ setting('contact.telegram_url') }}" target="_blank" rel="noopener" aria-label="تلگرام"><svg aria-hidden="true"><use href="#i-telegram"/></svg></a>@endif
          @if (setting('contact.whatsapp_url'))<a href="{{ setting('contact.whatsapp_url') }}" target="_blank" rel="noopener" aria-label="واتس‌اپ"><svg aria-hidden="true"><use href="#i-whatsapp"/></svg></a>@endif
          <a href="mailto:{{ setting('contact.email') }}" aria-label="ایمیل"><svg aria-hidden="true"><use href="#i-mail"/></svg></a>
        </div>
      </div>
      <nav class="f-col" aria-label="درباره ما">
        <h3>درباره ما</h3>
        <a href="{{ route('about') }}">معرفی استودیو</a>@if (\App\Models\TeamMember::visible()->exists())<a href="{{ route('about') }}#team">تیم ما</a>@endif<a href="{{ route('games.index') }}">بازی‌های ما</a><a href="{{ route('blog.index') }}">وبلاگ</a>
      </nav>
      <nav class="f-col" aria-label="خدمات">
        <h3>خدمات</h3>
        @foreach (\App\Models\Service::visible()->limit(4)->get(['slug', 'title']) as $svc)
          <a href="{{ route('services') }}#svc-{{ $svc->slug }}">{{ $svc->title }}</a>
        @endforeach
      </nav>
      <nav class="f-col" aria-label="پشتیبانی">
        <h3>پشتیبانی</h3>
        <a href="{{ route('contact') }}#faq">سؤالات متداول</a><a href="{{ route('contact') }}#support">حریم خصوصی کودکان</a><a href="{{ route('contact') }}#support">راهنمای والدین</a><a href="{{ route('contact') }}#contact">گزارش مشکل</a>
      </nav>
      <div class="f-col f-contact">
        <h3>راه‌های ارتباطی</h3>
        <p>{{ setting('contact.address') }}</p>
        <a class="f-phone" href="tel:{{ setting('contact.phone') }}" dir="ltr">{{ setting('contact.phone_display') }}</a>
        <a href="mailto:{{ setting('contact.email') }}" dir="ltr">{{ setting('contact.email') }}</a>
      </div>
    </div>
    <div class="footer-mega" aria-hidden="true">
      <svg class="fm-logo" viewBox="0 18 200 132" data-speed="-0.15" data-rot="-8"><use href="#i-logo-mono"/></svg>
      <span class="fm-word" data-sx="120">MICRO</span>
      <img class="fm-left" src="{{ asset('assets/img/characters/razino-buddy.webp') }}" alt="" width="172" height="417" loading="lazy" data-x="120" data-depth="1.6">
      <img class="fm-dino" src="{{ asset('assets/img/characters/razino-dino.webp') }}" alt="" width="200" height="264" loading="lazy" data-x="200" data-depth="2.2">
      <img class="fm-right" src="{{ asset('assets/img/characters/razino-merchant.webp') }}" alt="" width="185" height="284" loading="lazy" data-x="-160" data-depth="1.8">
    </div>
    <p class="footer-bottom">© <span id="year">{{ fa_digits(\App\Support\Jalali::year()) }}</span> <a href="{{ route('home') }}">{{ $siteName }}</a> - {{ setting('site.copyright') }}
      @if (setting('site.designer_url'))<span class="designer-credit">طراحی و توسعه: <a href="{{ setting('site.designer_url') }}" target="_blank" rel="noopener">{{ setting('site.designer_name') }}</a></span>@endif
    </p>
  </footer>

  <a class="to-top" href="#top" aria-label="بازگشت به بالا"><svg aria-hidden="true"><use href="#i-up"/></svg></a>

  <div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="پخش تریلر" hidden>
    <button class="lb-close" type="button" aria-label="بستن"><svg aria-hidden="true"><use href="#i-close"/></svg></button>
    <div class="lb-frame"><video controls playsinline preload="none"></video></div>
  </div>

  <script src="{{ asset('assets/js/main.js') }}?v={{ filemtime(public_path('assets/js/main.js')) }}"></script>
  <script src="{{ asset('assets/js/motion.js') }}?v={{ filemtime(public_path('assets/js/motion.js')) }}"></script>
  @stack('scripts')
</body>
</html>
