@extends('layouts.site', ['bodyClass' => 'home inner-page about-page', 'title' => 'درباره ما', 'preload' => setting('about.hero_image'), 'description' => setting('about.hero_lead')])

@section('content')
  @include('partials.page-hero', [
      'group' => 'about',
      'crumbs' => [['صفحه اصلی', route('home')], ['درباره ما', null]],
      'chips' => $values->take(3)->map(fn ($v) => [$v->icon ?: 'gem', $v->title])->all(),
  ])

  <!-- ============ STORY ============ -->
  <section class="section about" id="story" aria-labelledby="story-title">
    <p class="watermark" aria-hidden="true" data-sx="-180">MICRO</p>
    <div class="container about-grid">
      <div class="about-media">
        <div class="notch-card wipe"><img src="{{ img(setting('about.story_image')) }}" alt="" width="1456" height="818" loading="lazy"></div>
        <img class="about-hero-char pixel" src="{{ asset('assets/img/characters/kairo.webp') }}" alt="" width="350" height="668" loading="lazy" data-x="160" data-speed="-0.25" data-depth="1.3">
      </div>
      <div class="about-copy">
        <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> {{ setting('about.story_eyebrow') }}</p>
        <h2 id="story-title" class="h2" data-split>{{ heading(setting('about.story_title')) }}</h2>
        <p class="lead reveal blur" style="--d:.3s">{{ setting('about.story_lead') }}</p>
        <ul class="story-points stagger">
          <li><b data-count="{{ $counts['all'] }}">{{ fa_digits($counts['all']) }}</b><span>بازی و اپ</span></li>
          <li><b data-count="{{ $counts['edu'] }}">{{ fa_digits($counts['edu']) }}</b><span>اپ آموزشی</span></li>
          <li><b data-count="{{ $counts['game'] }}">{{ fa_digits($counts['game']) }}</b><span>بازی داستانی و خلاقانه</span></li>
        </ul>
        <a class="btn btn-primary reveal from-right" style="--d:.5s" href="{{ route('games.index') }}" data-magnetic>بازی‌های ما <svg aria-hidden="true"><use href="#i-play"/></svg></a>
        <div class="about-thumb wipe" data-speed="-0.12"><img src="{{ img(setting('about.story_thumb')) }}" alt="" width="1200" height="800" loading="lazy"></div>
      </div>
    </div>
    <img class="about-dino float" src="{{ asset('assets/img/characters/razino-dino.webp') }}" alt="" width="200" height="264" loading="lazy" data-speed="-0.5" data-rot="-12" data-depth="2.2">
  </section>

  <!-- ============ VALUES ============ -->
  <section class="section night notch-both values" id="values" aria-labelledby="values-title">
    <img class="night-bg" src="{{ asset('assets/img/scenes/dark-castle.webp') }}" alt="" width="1456" height="818" loading="lazy" data-parallax="0.08">
    <img class="night-char values-char pixel" src="{{ asset('assets/img/characters/ilara.webp') }}" alt="" width="517" height="699" loading="lazy" data-x="-180" data-speed="-0.25" data-depth="1.5">
    <div class="container">
      <div class="section-head">
        <div>
          <p class="eyebrow light reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> {{ setting('about.values_eyebrow') }}</p>
          <h2 id="values-title" class="h2 light" data-split>{{ heading(setting('about.values_title')) }}</h2>
        </div>
        <p class="lead reveal blur" style="--d:.3s">{{ setting('about.values_lead') }}</p>
      </div>
      <div class="feature-grid stagger">
        @foreach ($values as $v)
          <article class="feature-card"><span class="feature-ico"><svg aria-hidden="true"><use href="#i-{{ $v->icon ?: 'gem' }}"/></svg></span><h3>{{ $v->title }}</h3><p>{{ $v->text }}</p></article>
        @endforeach
      </div>
    </div>
  </section>

  @if ($team->isNotEmpty())
    <!-- ============ TEAM ============ -->
    <section class="section team" id="team" aria-labelledby="team-title">
      <img class="sec-char team-char" src="{{ asset('assets/img/characters/razino-buddy.webp') }}" alt="" width="172" height="417" loading="lazy" data-x="-140" data-speed="-0.3" data-depth="1.6">
      <div class="container">
        <div class="section-head">
          <div>
            <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> {{ setting('about.team_eyebrow') }}</p>
            <h2 id="team-title" class="h2" data-split>{{ heading(setting('about.team_title')) }}</h2>
          </div>
          <p class="lead reveal blur" style="--d:.3s">{{ setting('about.team_lead') }}</p>
        </div>
        <div class="team-grid stagger">
          @foreach ($team as $m)
            <article class="team-card"><div class="team-face"><img src="{{ img($m->avatar, 'assets/img/characters/kairo-face-2.webp') }}" alt="" width="320" height="320" loading="lazy"></div>@if ($m->tag)<span class="tag {{ tag_color($m->tag_color) }}">{{ $m->tag }}</span>@endif<b>{{ $m->name }}</b><span>{{ $m->role }}</span></article>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  @include('partials.honors', ['honors' => $honors, 'class' => 'on-white'])

  @include('partials.testimonials', ['testimonials' => $testimonials, 'notch' => true])

  @include('partials.create-cta', [
      'eyebrow' => setting('about.cta_eyebrow'), 'title' => setting('about.cta_title'), 'lead' => setting('about.cta_lead'),
      'image' => 'assets/img/scenes/temple-gate.webp', 'character' => 'assets/img/characters/razino-wizard.webp', 'button' => 'تماس با ما',
  ])
@endsection
