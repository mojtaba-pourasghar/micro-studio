@extends('layouts.site', ['bodyClass' => 'home', 'preload' => setting('home.hero_image')])

@section('content')
  <!-- ============ HERO ============ -->
  <section class="hero notch-bottom" id="home" aria-labelledby="hero-title">
    <div class="hero-bg" aria-hidden="true" data-depth="-0.6"><img src="{{ img(setting('home.hero_image')) }}" alt="" width="1456" height="818" data-parallax="0.12" fetchpriority="high"></div>
    <div class="hero-fx" aria-hidden="true" data-depth="-1.4"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
    <div class="container hero-grid">
      <div class="hero-copy">
        <h1 id="hero-title" class="display" data-split>{{ heading(setting('home.hero_title'), true) }}</h1>
        <p class="hero-lead reveal blur" style="--d:.7s">{{ setting('home.hero_lead') }}</p>
        <a class="round-cta reveal zoom" style="--d:.9s" href="#contact" data-magnetic>{{ heading(setting('home.hero_cta')) }}</a>
      </div>
      <figure class="hero-char reveal-up" style="--d:.3s">
        <img class="float-slow pixel" src="{{ img(setting('home.hero_character')) }}" alt="{{ setting('home.hero_character_name') }}" data-depth="1.6" data-speed="-0.3">
        <figcaption class="char-tag" data-depth="2.6">{{ setting('home.hero_character_name') }} <small>{{ setting('home.hero_character_sub') }}</small></figcaption>
      </figure>
    </div>
    <a class="scroll-hint" href="#about" aria-label="اسکرول به بخش بعد"><span></span></a>
  </section>

  <!-- ============ ABOUT ============ -->
  <section class="section about" id="about" aria-labelledby="about-title">
    <p class="watermark" aria-hidden="true" data-sx="-180">MICRO</p>
    <div class="container about-grid">
      <div class="about-media">
        <div class="notch-card wipe"><img src="{{ img(setting('home.about_image')) }}" alt="" width="1200" height="800" loading="lazy"></div>
        <img class="about-hero-char" src="{{ img(setting('home.about_character')) }}" alt="" loading="lazy" data-x="160" data-speed="-0.25" data-depth="1.3">
      </div>
      <div class="about-copy">
        <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> {{ setting('home.about_eyebrow') }}</p>
        <h2 id="about-title" class="h2" data-split>{{ heading(setting('home.about_title')) }}</h2>
        <p class="lead reveal blur" style="--d:.35s">{{ setting('home.about_lead') }}</p>
        <a class="btn btn-primary reveal from-right" style="--d:.5s" href="{{ route('about') }}" data-magnetic>بیشتر درباره ما <svg aria-hidden="true"><use href="#i-play"/></svg></a>
        <div class="about-thumb wipe" data-speed="-0.12"><img src="{{ img(setting('home.about_thumb')) }}" alt="" width="1200" height="800" loading="lazy"></div>
      </div>
    </div>
    <img class="about-dino float" src="{{ asset('assets/img/characters/razino-dino.webp') }}" alt="" width="200" height="264" loading="lazy" data-speed="-0.5" data-rot="-12" data-depth="2.2">
  </section>

  <!-- ============ SERVICES ============ -->
  <section class="section services notch-both" id="services" aria-labelledby="services-title">
    <div class="services-bg" aria-hidden="true"><img src="{{ img(setting('home.services_image')) }}" alt="" width="1456" height="818" loading="lazy" data-parallax="0.08"></div>
    <div class="container services-grid" data-slider>
      <div class="services-char reveal-up"><img class="pixel float-slow" src="{{ img(setting('home.services_character')) }}" alt="" loading="lazy" data-x="180" data-speed="-0.2" data-depth="1.5"></div>
      <div class="services-main">
        <p class="eyebrow light reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> {{ setting('home.services_eyebrow') }}</p>
        <h2 id="services-title" class="h2 light" data-split>{{ heading(setting('home.services_title')) }}</h2>
        <div class="slider-track" tabindex="0" aria-label="فهرست خدمات؛ با کلیدهای جهت‌نما جابه‌جا شوید">
          @foreach ($services as $svc)
            <article class="svc-card">
              <div><h3>{{ $svc->title }}</h3><p>{{ $svc->summary }}</p></div>
              <img src="{{ img($svc->character_image) }}" alt="" loading="lazy">
              <div class="svc-foot"><span class="svc-num">{{ sprintf('%02d', $loop->iteration) }}</span><a class="more" href="{{ route('services') }}#svc-{{ $svc->slug }}">بیشتر <svg aria-hidden="true"><use href="#i-play"/></svg></a></div>
            </article>
          @endforeach
        </div>
        <div class="slider-nav">
          <button class="icon-btn" type="button" data-dir="prev" aria-label="خدمت قبلی"><svg aria-hidden="true"><use href="#i-arrow"/></svg></button>
          <button class="icon-btn" type="button" data-dir="next" aria-label="خدمت بعدی"><svg class="flip" aria-hidden="true"><use href="#i-arrow"/></svg></button>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ WORKS ============ -->
  <section class="section works" id="works" aria-labelledby="works-title" data-rail>
    <div class="container works-head">
      <div>
        <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> {{ setting('home.works_eyebrow') }}</p>
        <h2 id="works-title" class="h2" data-split>{{ heading(setting('home.works_title')) }}</h2>
      </div>
      <a class="btn btn-primary reveal from-left" href="{{ route('games.index') }}" data-magnetic>همه‌ی کارها <svg aria-hidden="true"><use href="#i-play"/></svg></a>
    </div>
    <div class="rail-track stagger" tabindex="0" aria-label="نمونه‌کارها؛ با کلیدهای جهت‌نما یا کشیدن جابه‌جا شوید">
      @foreach ($games as $game)
        @include('partials.work-card', ['game' => $game])
      @endforeach
    </div>
    <div class="container rail-nav">
      <button class="icon-btn dark" type="button" data-dir="prev" aria-label="کار قبلی"><svg aria-hidden="true"><use href="#i-arrow"/></svg></button>
      <div class="rail-progress" aria-hidden="true"><span></span></div>
      <button class="icon-btn dark" type="button" data-dir="next" aria-label="کار بعدی"><svg class="flip" aria-hidden="true"><use href="#i-arrow"/></svg></button>
    </div>
  </section>

  @include('partials.create-cta', [
      'eyebrow' => setting('home.create_eyebrow'), 'title' => setting('home.create_title'), 'lead' => setting('home.create_lead'),
      'image' => setting('home.create_image'), 'character' => setting('home.create_character'), 'href' => '#contact',
  ])

  @include('partials.honors', ['honors' => $honors])

  <!-- ============ VIDEO BANNER ============ -->
  <section class="video-banner notch-top" aria-labelledby="vb-title">
    <img class="vb-bg" src="{{ img(setting('home.banner_image')) }}" alt="" width="1456" height="818" loading="lazy" data-parallax="0.1">
    <div class="vb-inner">
      <h2 id="vb-title" class="display-md" data-split>{{ heading(setting('home.banner_title')) }}</h2>
      <button class="play-btn reveal zoom" style="--d:.5s" data-magnetic type="button" data-video="{{ img(setting('site.trailer_url')) }}" aria-label="پخش تریلر"><svg aria-hidden="true"><use href="#i-play"/></svg></button>
    </div>
  </section>

  @include('partials.testimonials', ['testimonials' => $testimonials])

  <!-- ============ CONTACT ============ -->
  <section class="section contact" id="contact" aria-labelledby="contact-title">
    <div class="container contact-grid">
      <div class="contact-copy">
        <h2 id="contact-title" class="h2 big" data-split>{{ heading(setting('home.contact_title')) }}</h2>
        <p class="lead reveal blur" style="--d:.35s">{{ setting('home.contact_lead') }}</p>
        <img class="contact-char" src="{{ asset('assets/img/characters/razino-girl.webp') }}" alt="" width="190" height="378" loading="lazy" data-x="140" data-speed="-0.25" data-depth="1.5">
      </div>
      @include('partials.contact-form', ['subjects' => null])
    </div>
    @include('partials.gallery')
  </section>
@endsection
