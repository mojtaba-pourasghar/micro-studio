@extends('layouts.site', [
    'bodyClass' => 'home inner-page game-page',
    'title' => $game->seo_title ?: $game->title,
    'description' => $game->seo_description ?: ($game->tagline ?: $game->summary),
    'preload' => $game->hero_image ?: $game->card_image,
    'ogImage' => $game->logo ?: ($game->hero_image ?: $game->card_image),
    'ogExtra' => collect($game->videos ?? [])->take(1)->map(fn ($v) => '<meta property="og:video" content="'.e(img($v['src'])).'"><meta property="og:video:type" content="video/mp4">')->implode(''),
    'schema' => array_merge([
        \App\Support\Seo::webPage('ItemPage', ['name' => $game->seo_title ?: $game->title, 'description' => $game->seo_description ?: $game->summary, 'mainEntity' => ['@id' => route('games.show', $game).'#app'], 'primaryImageOfPage' => img($game->logo ?: $game->card_image)]),
        \App\Support\Seo::game($game),
    ], \App\Support\Seo::videos($game)),
])

@php
    $titleParts = array_map('trim', explode(':', $game->title, 2));
    $statusLabel = \App\Models\Game::STATUSES[$game->status] ?? $game->status;
    $platformLabel = collect($game->platforms ?? ['android'])->map(fn ($p) => $p === 'web' ? 'وب' : 'اندروید')->implode('، ');
@endphp

@section('content')
  <!-- ============ GAME HERO ============ -->
  <section class="hero page-hero notch-bottom" id="home" aria-labelledby="game-title">
    <div class="hero-bg {{ ! $game->hero_image && $game->logo ? 'hero-bg-app' : '' }}" aria-hidden="true" data-depth="-0.6"><img src="{{ img($game->hero_image ?: ($game->logo ?: $game->card_image)) }}" alt="" width="1200" height="800" data-parallax="0.12" fetchpriority="high"></div>
    <div class="hero-fx" aria-hidden="true" data-depth="-1.4"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
    <div class="container hero-grid">
      <div class="hero-copy">
        <nav class="breadcrumb reveal from-right" aria-label="مسیر صفحه">
          <ol><li><a href="{{ route('home') }}">صفحه اصلی</a></li><li><a href="{{ route('games.index') }}">بازی‌ها و اپ‌ها</a></li><li aria-current="page">{{ $game->short_title }}</li></ol>
        </nav>
        @if ($game->tags)
          <div class="tags reveal from-right" style="--d:.1s">@foreach ($game->tags as $t)<span class="tag {{ tag_color($t['color'] ?? null) }}">{{ $t['label'] ?? '' }}</span>@endforeach</div>
        @endif
        <h1 id="game-title" class="display" data-split>@foreach ($titleParts as $part)<span>{{ $part }}</span>@endforeach</h1>
        <p class="hero-lead reveal blur" style="--d:.7s">{{ $game->tagline ?: $game->summary }}</p>
        <ul class="meta-chips reveal from-right" style="--d:.85s">
          @if ($game->age_range)<li><svg aria-hidden="true"><use href="#i-age"/></svg>{{ $game->age_range }}</li>@endif
          <li><svg aria-hidden="true"><use href="#i-android"/></svg>{{ $platformLabel }}</li>
          @if ($game->engine)<li><svg aria-hidden="true"><use href="#i-chip"/></svg><span dir="ltr">{{ \Illuminate\Support\Str::before($game->engine, ' ·') }}</span></li>@endif
          @if ($game->language)<li><svg aria-hidden="true"><use href="#i-text"/></svg>{{ $game->language }}</li>@endif
        </ul>
        <div class="hero-actions reveal zoom" style="--d:1s">
          <a class="btn btn-primary" href="#download" data-magnetic>دریافت {{ $game->category === 'edu' ? 'اپ' : 'بازی' }} <svg aria-hidden="true"><use href="#i-play"/></svg></a>
          @if ($game->trailer_url)
            <button class="play-btn small" type="button" data-video="{{ img($game->trailer_url) }}" aria-label="پخش تریلر {{ $game->short_title }}"><svg aria-hidden="true"><use href="#i-play"/></svg></button>
            <span class="play-label" aria-hidden="true">تماشای تریلر</span>
          @endif
        </div>
      </div>
      @if ($game->hero_character)
        <figure class="hero-char reveal-up" style="--d:.3s">
          <img class="float-slow {{ str_contains($game->hero_character, 'kairo') ? 'pixel' : '' }}" src="{{ img($game->hero_character) }}" alt="{{ $game->short_title }}" data-depth="1.6" data-speed="-0.3">
          <figcaption class="char-tag" data-depth="2.6">{{ $game->short_title }} <small>{{ setting('site.name') }}</small></figcaption>
        </figure>
      @elseif ($game->logo)
        <figure class="hero-app-icon reveal-up" style="--d:.3s">
          <img class="float-slow" src="{{ img($game->logo) }}" alt="لوگوی {{ $game->title }}" width="512" height="512" data-depth="1.4" data-speed="-0.2">
          <figcaption class="char-tag" data-depth="2.6">{{ $game->short_title }} <small>{{ \App\Models\Game::CATEGORIES[$game->category] ?? '' }}</small></figcaption>
        </figure>
      @endif
    </div>
  </section>

  @if ($game->stats)
    <!-- ============ STATS ============ -->
    <section class="stat-strip" aria-label="{{ $game->short_title }} در یک نگاه">
      <div class="container stat-grid stagger">
        @foreach (array_slice($game->stats, 0, 4) as $s)
          <div class="stat"><span class="stat-num" @if (is_numeric($s['value'] ?? null)) data-count="{{ $s['value'] }}" @endif>{{ fa_digits($s['value'] ?? '') }}</span><span class="stat-label">{{ $s['label'] ?? '' }}</span></div>
        @endforeach
      </div>
    </section>
  @endif

  @if ($game->description)
    <!-- ============ STORY ============ -->
    <section class="section about game-story" id="story" aria-labelledby="story-title">
      <p class="watermark" aria-hidden="true" data-sx="-180">{{ strtoupper(\Illuminate\Support\Str::of($game->slug)->before('-')) }}</p>
      <div class="container about-grid">
        <div class="about-media">
          <div class="notch-card wipe"><img src="{{ img($game->story_image ?: $game->card_image) }}" alt="{{ $game->short_title }}" loading="lazy"></div>
          @if (($c = collect($game->characters)->skip(1)->first()) && ! empty($c['image']))
            <img class="about-hero-char" src="{{ img($c['image']) }}" alt="" loading="lazy" data-x="160" data-speed="-0.25" data-depth="1.3">
          @endif
        </div>
        <div class="about-copy">
          <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> داستان {{ $game->category === 'edu' ? 'اپ' : 'بازی' }}</p>
          <h2 id="story-title" class="h2" data-split>{{ heading($game->story_title ?: 'درباره‌ی [['.$game->short_title.']]') }}</h2>
          <div class="lead reveal blur" style="--d:.35s">{!! \App\Support\Html::sanitize($game->description) !!}</div>
          @if ($game->story_points)
            <ul class="check-list reveal from-right" style="--d:.5s">
              @foreach ($game->story_points as $point)<li><svg aria-hidden="true"><use href="#i-check"/></svg> {{ $point }}</li>@endforeach
            </ul>
          @endif
        </div>
      </div>
    </section>
  @endif

  @if ($game->videos)
    <!-- ============ VIDEOS ============ -->
    <section class="section game-videos" id="videos" aria-labelledby="videos-title">
      <div class="container">
        <div class="section-head">
          <div>
            <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> ویدیو</p>
            <h2 id="videos-title" class="h2" data-split>{{ $game->short_title }} را <span class="outline">در حرکت ببین</span></h2>
          </div>
          <p class="lead reveal blur" style="--d:.3s">پیش از دانلود، چند دقیقه با {{ $game->short_title }} همراه شو و ببین چطور کار می‌کند.</p>
        </div>
        <div class="video-grid stagger">
          @foreach ($game->videos as $v)
            <figure class="video-card {{ ($v['vertical'] ?? true) ? 'is-vertical' : '' }}">
              <div class="video-frame">
                <video controls playsinline preload="none" poster="{{ img($v['poster'] ?? null) }}" aria-label="{{ $v['title'] ?? $game->short_title }}">
                  <source src="{{ img($v['src']) }}" type="video/mp4">
                </video>
              </div>
              <figcaption><b>{{ $v['title'] ?? '' }}</b>@if (! empty($v['duration']))<span>{{ fa_digits(intdiv($v['duration'], 60)) }}:{{ fa_digits(str_pad($v['duration'] % 60, 2, '0', STR_PAD_LEFT)) }}</span>@endif</figcaption>
            </figure>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  @if ($game->gameplay)
    <!-- ============ GAMEPLAY ============ -->
    <section class="section night notch-both game-play" id="gameplay" aria-labelledby="gameplay-title">
      <img class="night-bg" src="{{ img($game->banner_image ?: 'assets/img/scenes/night-portal.webp') }}" alt="" loading="lazy" data-parallax="0.08">
      <div class="container">
        <div class="section-head">
          <div>
            <p class="eyebrow light reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> گیم‌پلی</p>
            <h2 id="gameplay-title" class="h2 light" data-split>{{ heading($game->gameplay_title ?: 'چطور [[بازی می‌شود؟]]') }}</h2>
          </div>
          <p class="lead reveal blur" style="--d:.3s">{{ $game->summary }}</p>
        </div>
        <ol class="step-grid stagger {{ count($game->gameplay) > 4 ? 'five' : '' }}">
          @foreach ($game->gameplay as $step)
            <li class="step-card"><span class="step-num">{{ fa_digits($loop->iteration) }}</span><h3>{{ $step['title'] ?? '' }}</h3><p>{{ $step['text'] ?? '' }}</p></li>
          @endforeach
        </ol>
      </div>
    </section>
  @endif

  @if ($game->features)
    <!-- ============ FEATURES ============ -->
    <section class="section services notch-both game-features" id="features" aria-labelledby="features-title">
      <div class="services-bg" aria-hidden="true"><img src="{{ img('assets/img/scenes/dark-castle.webp') }}" alt="" width="1456" height="818" loading="lazy" data-parallax="0.08"></div>
      <div class="container">
        <p class="eyebrow light reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> ویژگی‌ها</p>
        <h2 id="features-title" class="h2 light" data-split>چرا {{ $game->short_title }}<br>متفاوت است؟</h2>
        <div class="feature-grid stagger">
          @foreach ($game->features as $f)
            <article class="feature-card"><span class="feature-ico"><svg aria-hidden="true"><use href="#i-{{ $f['icon'] ?? 'gem' }}"/></svg></span><h3>{{ $f['title'] ?? '' }}</h3><p>{{ $f['text'] ?? '' }}</p></article>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  @if ($game->benefits)
    <!-- ============ BENEFITS ============ -->
    <section class="section game-benefits" id="benefits" aria-labelledby="benefits-title">
      <div class="container benefits-grid">
        <div>
          <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> کاربرد</p>
          <h2 id="benefits-title" class="h2" data-split>{{ $game->short_title }} <span class="outline">به چه کار می‌آید؟</span></h2>
          <p class="lead reveal blur" style="--d:.3s">{{ $game->category === 'edu' ? 'همراه درس و یادگیری کودک، در خانه و مدرسه.' : 'سرگرمی‌ای که هم‌زمان مهارت‌های مهمی را تقویت می‌کند.' }}@if ($game->age_range) مناسب {{ $game->age_range }}.@endif</p>
        </div>
        <ul class="benefit-list stagger">
          @foreach ($game->benefits as $b)
            <li><svg aria-hidden="true"><use href="#i-check"/></svg><span>{{ $b }}</span></li>
          @endforeach
        </ul>
      </div>
    </section>
  @endif

  @if ($game->screenshots)
    <!-- ============ GALLERY ============ -->
    <section class="section works game-gallery" id="gallery" aria-labelledby="gallery-title" data-rail>
      <div class="container works-head">
        <div>
          <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> گالری تصاویر · {{ fa_digits(count($game->screenshots)) }} تصویر</p>
          <h2 id="gallery-title" class="h2" data-split>نگاهی به <span class="outline">دنیای {{ $game->short_title }}</span></h2>
        </div>
      </div>
      <div class="rail-track stagger" tabindex="0" aria-label="تصاویر؛ با کلیدهای جهت‌نما یا کشیدن جابه‌جا شوید">
        @foreach ($game->screenshots as $shot)
          @php $path = $shot['image'] ?? null; $dim = $path && is_file(public_path($path)) ? @getimagesize(public_path($path)) : null; $tall = $dim && $dim[1] > $dim[0]; @endphp
          <figure class="shot-card {{ $tall ? 'is-phone' : '' }}">
            <a href="{{ img($path) }}" data-lightbox="game-{{ $game->id }}" data-caption="{{ $shot['caption'] ?? '' }}" aria-label="بزرگ‌نمایی تصویر {{ fa_digits($loop->iteration) }}{{ ! empty($shot['caption']) ? '، '.$shot['caption'] : '' }}">
              <img src="{{ img($path) }}" alt="{{ ($shot['caption'] ?? '') ?: $game->short_title.' — تصویر '.fa_digits($loop->iteration) }}" loading="lazy" @if ($dim) width="{{ $dim[0] }}" height="{{ $dim[1] }}" @endif>
            </a>
            @if (! empty($shot['caption']))<figcaption>{{ $shot['caption'] }}</figcaption>@endif
          </figure>
        @endforeach
      </div>
      <div class="container rail-nav">
        <button class="icon-btn dark" type="button" data-dir="prev" aria-label="تصویر قبلی"><svg aria-hidden="true"><use href="#i-arrow"/></svg></button>
        <div class="rail-progress" aria-hidden="true"><span></span></div>
        <button class="icon-btn dark" type="button" data-dir="next" aria-label="تصویر بعدی"><svg class="flip" aria-hidden="true"><use href="#i-arrow"/></svg></button>
      </div>
    </section>
  @endif

  @if ($game->characters)
    <!-- ============ CHARACTERS ============ -->
    <section class="section game-chars" id="characters" aria-labelledby="chars-title">
      <div class="container">
        <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> شخصیت‌ها</p>
        <h2 id="chars-title" class="h2" data-split>با <span class="outline">همسفرهایت</span> آشنا شو</h2>
        <div class="char-grid stagger">
          @foreach ($game->characters as $c)
            <figure class="char-card {{ ! empty($c['dark']) ? 'dark' : '' }}"><img class="{{ str_contains($c['image'] ?? '', 'kairo') || str_contains($c['image'] ?? '', 'ilara') ? 'pixel' : '' }}" src="{{ img($c['image'] ?? null) }}" alt="" loading="lazy"><figcaption><b>{{ $c['name'] ?? '' }}</b><span>{{ $c['role'] ?? '' }}</span></figcaption></figure>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  @if ($game->trailer_url && ! $game->videos)
    <!-- ============ VIDEO BANNER ============ -->
    <section class="video-banner notch-top" aria-labelledby="vb-title">
      <img class="vb-bg" src="{{ img($game->banner_image ?: setting('home.banner_image')) }}" alt="" width="1456" height="818" loading="lazy" data-parallax="0.1">
      <div class="vb-inner">
        <h2 id="vb-title" class="display-md" data-split>{{ $game->short_title }} را<br>در حرکت ببین</h2>
        <button class="play-btn reveal zoom" style="--d:.5s" data-magnetic type="button" data-video="{{ img($game->trailer_url) }}" aria-label="پخش تریلر {{ $game->short_title }}"><svg aria-hidden="true"><use href="#i-play"/></svg></button>
      </div>
    </section>
  @endif

  <!-- ============ DETAILS + DOWNLOAD ============ -->
  <section class="section game-download" id="download" aria-labelledby="download-title">
    <div class="container download-grid">
      <div class="spec-card reveal from-left">
        <h3 class="spec-title">مشخصات {{ $game->category === 'edu' ? 'اپ' : 'بازی' }}</h3>
        <dl class="spec-list">
          @if ($game->genre)<div><dt>ژانر</dt><dd>{{ $game->genre }}</dd></div>@endif
          @if ($game->age_range)<div><dt>گروه سنی</dt><dd>{{ $game->age_range }}</dd></div>@endif
          <div><dt>پلتفرم</dt><dd>{{ $platformLabel }}</dd></div>
          @if ($game->engine)<div><dt>موتور</dt><dd dir="ltr">{{ $game->engine }}</dd></div>@endif
          @if ($game->language)<div><dt>زبان</dt><dd>{{ $game->language }}</dd></div>@endif
          @if ($game->internet)<div><dt>اینترنت</dt><dd>{{ $game->internet }}</dd></div>@endif
          <div><dt>وضعیت</dt><dd><span class="status-badge">{{ $statusLabel }}</span></dd></div>
        </dl>
      </div>
      <div class="download-copy">
        <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> دریافت</p>
        <h2 id="download-title" class="h2 big" data-split>همین حالا <span class="grad">{{ $game->short_title }}</span> را {{ $game->category === 'edu' ? 'نصب' : 'بازی' }} کن</h2>
        <p class="lead reveal blur" style="--d:.35s">{{ $game->download_note ?: ($game->status === 'released' ? 'از فروشگاه‌های زیر دریافت کنید.' : 'به‌زودی منتشر می‌شود. برای باخبر شدن از انتشار، با ما در تماس باشید.') }}</p>
        <div class="store-btns reveal from-right" style="--d:.5s">
          @foreach ($game->store_links ?? [] as $s)
            @if (! empty($s['url']))
              <a class="store-btn" href="{{ $s['url'] }}" target="_blank" rel="noopener"><svg aria-hidden="true"><use href="#i-android"/></svg><span><small>دریافت از</small>{{ $s['store'] ?? '' }}</span></a>
            @else
              <span class="store-btn is-soon" aria-disabled="true"><svg aria-hidden="true"><use href="#i-android"/></svg><span><small>به‌زودی در</small>{{ $s['store'] ?? '' }}</span></span>
            @endif
          @endforeach
        </div>
        <a class="btn btn-primary reveal from-right" style="--d:.65s" href="{{ route('contact') }}" data-magnetic>خبرم کنید <svg aria-hidden="true"><use href="#i-play"/></svg></a>
      </div>
    </div>
  </section>

  @if ($related->isNotEmpty())
    <!-- ============ RELATED ============ -->
    <section class="section works related" aria-labelledby="related-title">
      <div class="container works-head">
        <div>
          <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> بازی‌های دیگر</p>
          <h2 id="related-title" class="h2" data-split>شاید این‌ها را هم <span class="outline">دوست داشته باشی</span></h2>
        </div>
        <a class="btn btn-primary reveal from-left" href="{{ route('games.index') }}" data-magnetic>همه‌ی بازی‌ها <svg aria-hidden="true"><use href="#i-play"/></svg></a>
      </div>
      <div class="container related-grid stagger">
        @foreach ($related as $r)
          @include('partials.work-card', ['game' => $r, 'short' => true])
        @endforeach
      </div>
    </section>
  @endif
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/gallery.js') }}?v={{ filemtime(public_path('assets/js/gallery.js')) }}" defer></script>
@endpush
