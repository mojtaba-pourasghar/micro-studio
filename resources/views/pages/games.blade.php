@extends('layouts.site', ['bodyClass' => 'home inner-page games-page', 'title' => 'بازی‌ها و اپ‌ها', 'preload' => setting('games.hero_image'), 'description' => setting('games.hero_lead')])

@section('content')
  @include('partials.page-hero', [
      'group' => 'games',
      'crumbs' => [['صفحه اصلی', route('home')], ['بازی‌ها و اپ‌ها', null]],
      'chips' => [
          ['pad', fa_digits($counts['game']).' بازی داستانی و خلاقانه'],
          ['book', fa_digits($counts['edu']).' اپ آموزشی'],
          ['android', 'اندروید'],
      ],
  ])

  <!-- ============ FILTER + GRID ============ -->
  <section class="section games-list" id="all-games" aria-labelledby="list-title">
    <p class="watermark" aria-hidden="true" data-sx="-180">GAMES</p>
    <div class="container" style="position:relative;z-index:1">
      <div class="games-toolbar">
        <div>
          <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> {{ setting('games.list_eyebrow') }}</p>
          <h2 id="list-title" class="h2" data-split>{{ heading(setting('games.list_title')) }}</h2>
        </div>
        <p class="games-count reveal from-left" aria-live="polite"><b data-shown>{{ fa_digits($games->count()) }}</b> بازی و اپ</p>
      </div>

      <div class="cat-filter reveal from-right" role="group" aria-label="دسته‌بندی محصولات">
        <button type="button" class="cat-chip is-active" data-filter="all" aria-pressed="true">همه</button>
        <button type="button" class="cat-chip" data-filter="game" aria-pressed="false">بازی‌ها</button>
        <button type="button" class="cat-chip" data-filter="edu" aria-pressed="false">اپ‌های آموزشی</button>
      </div>

      <div class="games-grid stagger">
        @foreach ($games as $game)
          @include('partials.work-card', ['game' => $game, 'more' => true])
        @endforeach
      </div>
      <p class="filter-empty" hidden>محصولی در این دسته پیدا نشد.</p>
    </div>
  </section>

  @if ($spotlight)
    <!-- ============ SPOTLIGHT ============ -->
    <section class="section night notch-both spotlight" id="spotlight" aria-labelledby="spot-title">
      <img class="night-bg" src="{{ img(setting('games.spotlight_image')) }}" alt="" width="1456" height="818" loading="lazy" data-parallax="0.08">
      <div class="container spotlight-grid">
        <div class="spotlight-media">
          <div class="notch-card wipe"><img src="{{ img($spotlight->story_image ?: $spotlight->card_image) }}" alt="{{ $spotlight->title }}" loading="lazy"></div>
          @if ($spotlight->hero_character)
            <img class="sec-char" src="{{ img($spotlight->hero_character) }}" alt="" loading="lazy" data-x="160" data-speed="-0.25" data-depth="1.4">
          @endif
        </div>
        <div class="spotlight-copy">
          <p class="eyebrow light reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> {{ setting('games.spotlight_eyebrow') }}</p>
          <h2 id="spot-title" class="h2 light" data-split>{{ $spotlight->short_title }}؛ <span class="outline">{{ trim(\Illuminate\Support\Str::after($spotlight->title, ':')) ?: $spotlight->title }}</span></h2>
          <p class="lead reveal blur" style="--d:.3s">{{ $spotlight->tagline }}</p>
          @if ($spotlight->stats)
            <ul class="spot-stats stagger">
              @foreach (array_slice($spotlight->stats, 0, 4) as $s)
                <li><b @if (is_numeric($s['value'] ?? null)) data-count="{{ $s['value'] }}" @endif>{{ fa_digits($s['value'] ?? '') }}</b><span>{{ $s['label'] ?? '' }}</span></li>
              @endforeach
            </ul>
          @endif
          <a class="btn btn-primary reveal from-right" style="--d:.4s" href="{{ route('games.show', $spotlight) }}" data-magnetic>صفحه‌ی بازی {{ $spotlight->short_title }} <svg aria-hidden="true"><use href="#i-play"/></svg></a>
        </div>
      </div>
    </section>
  @endif

  <!-- ============ PROMISES ============ -->
  <section class="section promises" id="promises" aria-labelledby="promise-title">
    <img class="sec-char promise-dino float" src="{{ asset('assets/img/characters/razino-dino.webp') }}" alt="" width="200" height="264" loading="lazy" data-x="-140" data-speed="-0.4" data-rot="-10" data-depth="2">
    <div class="container">
      <div class="section-head">
        <div>
          <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> {{ setting('games.promises_eyebrow') }}</p>
          <h2 id="promise-title" class="h2" data-split>{{ heading(setting('games.promises_title')) }}</h2>
        </div>
        <p class="lead reveal blur" style="--d:.3s">{{ setting('games.promises_lead') }}</p>
      </div>
      <div class="feature-grid four stagger">
        @foreach ($promises as $b)
          <article class="feature-card light"><span class="feature-ico"><svg aria-hidden="true"><use href="#i-{{ $b->icon ?: 'gem' }}"/></svg></span><h3>{{ $b->title }}</h3><p>{{ $b->text }}</p></article>
        @endforeach
      </div>
    </div>
  </section>

  <!-- ============ VIDEO BANNER ============ -->
  <section class="video-banner notch-top" aria-labelledby="vb-title">
    <img class="vb-bg" src="{{ img(setting('home.banner_image')) }}" alt="" width="1456" height="818" loading="lazy" data-parallax="0.1">
    <div class="vb-inner">
      <h2 id="vb-title" class="display-md" data-split>{{ heading(setting('games.banner_title')) }}</h2>
      <button class="play-btn reveal zoom" style="--d:.5s" data-magnetic type="button" data-video="{{ img(setting('site.trailer_url')) }}" aria-label="پخش تریلر"><svg aria-hidden="true"><use href="#i-play"/></svg></button>
    </div>
  </section>

  @include('partials.create-cta', [
      'eyebrow' => setting('games.cta_eyebrow'), 'title' => setting('games.cta_title'), 'lead' => setting('games.cta_lead'),
      'image' => setting('games.cta_image'), 'character' => setting('games.cta_character'), 'button' => 'شروع پروژه',
  ])
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/blog.js') }}?v={{ filemtime(public_path('assets/js/blog.js')) }}"></script>
@endpush
