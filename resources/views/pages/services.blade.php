@extends('layouts.site', ['bodyClass' => 'home inner-page services-page', 'title' => 'خدمات', 'preload' => setting('services.hero_image'), 'description' => setting('services.hero_lead')])

@section('content')
  @include('partials.page-hero', [
      'group' => 'services',
      'crumbs' => [['صفحه اصلی', route('home')], ['خدمات', null]],
      'chips' => $services->take(4)->values()->map(fn ($s, $i) => [['pad', 'brush', 'book', 'mic'][$i] ?? 'gem', $s->title])->all(),
  ])

  <!-- ============ SERVICES IN DETAIL ============ -->
  <section class="section svc-detail" id="services" aria-labelledby="detail-title">
    <p class="watermark" aria-hidden="true" data-sx="-180">STUDIO</p>
    <div class="container" style="position:relative;z-index:1">
      <div class="section-head">
        <div>
          <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> {{ setting('services.detail_eyebrow') }}</p>
          <h2 id="detail-title" class="h2" data-split>{{ heading(setting('services.detail_title')) }}</h2>
        </div>
        <p class="lead reveal blur" style="--d:.3s">{{ setting('services.detail_lead') }}</p>
      </div>

      <div class="svc-rows">
        @foreach ($services as $svc)
          <article class="svc-row" id="svc-{{ $svc->slug }}">
            <div class="svc-row-media">
              <div class="notch-card wipe {{ $svc->image_is_phone ? 'phone-shot' : '' }}"><img src="{{ img($svc->image) }}" alt="{{ $svc->title }}" loading="lazy"></div>
              @if ($svc->character_image)
                <img class="sec-char" src="{{ img($svc->character_image) }}" alt="" loading="lazy" data-x="{{ $loop->odd ? -120 : 120 }}" data-speed="-0.2" data-depth="1.4">
              @endif
            </div>
            <div class="svc-row-copy">
              <span class="svc-big-num reveal from-right" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
              <h3 class="h2" data-split>{{ $svc->title }}</h3>
              <p class="lead reveal blur" style="--d:.3s">{{ $svc->description ?: $svc->summary }}</p>
              @if ($svc->deliverables)
                <p class="deliver-label reveal">آنچه تحویل می‌گیرید:</p>
                <ul class="check-list stagger">
                  @foreach ($svc->deliverables as $d)<li><svg aria-hidden="true"><use href="#i-check"/></svg>{{ $d }}</li>@endforeach
                </ul>
              @endif
              <a class="btn btn-primary reveal from-right" href="{{ route('contact') }}" data-magnetic>{{ $svc->cta_label ?: 'سفارش '.$svc->title }} <svg aria-hidden="true"><use href="#i-play"/></svg></a>
            </div>
          </article>
        @endforeach
      </div>
    </div>
  </section>

  <!-- ============ PROCESS ============ -->
  <section class="section night notch-both process" id="process" aria-labelledby="process-title">
    <img class="night-bg" src="{{ img(setting('services.process_image')) }}" alt="" width="1456" height="818" loading="lazy" data-parallax="0.08">
    <img class="night-char process-char pixel" src="{{ asset('assets/img/characters/ilara.webp') }}" alt="" width="517" height="699" loading="lazy" data-x="-180" data-speed="-0.25" data-depth="1.5">
    <div class="container">
      <div class="section-head">
        <div>
          <p class="eyebrow light reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> {{ setting('services.process_eyebrow') }}</p>
          <h2 id="process-title" class="h2 light" data-split>{{ heading(setting('services.process_title')) }}</h2>
        </div>
        <p class="lead reveal blur" style="--d:.3s">{{ setting('services.process_lead') }}</p>
      </div>
      <ol class="step-grid stagger">
        @foreach ($steps as $step)
          <li class="step-card"><span class="step-num">{{ fa_digits($loop->iteration) }}</span><h3>{{ $step->title }}</h3><p>{{ $step->text }}</p>@if ($step->value)<small>{{ $step->value }}</small>@endif</li>
        @endforeach
      </ol>
    </div>
  </section>

  <!-- ============ TOOLS ============ -->
  <section class="section tools" id="tools" aria-labelledby="tools-title">
    <div class="container">
      <div class="section-head">
        <div>
          <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> {{ setting('services.tools_eyebrow') }}</p>
          <h2 id="tools-title" class="h2" data-split>{{ heading(setting('services.tools_title')) }}</h2>
        </div>
        <a class="btn btn-primary reveal from-left" href="{{ route('games.index') }}" data-magnetic>نمونه‌کارها <svg aria-hidden="true"><use href="#i-play"/></svg></a>
      </div>
      <div class="tool-grid stagger">
        @foreach ($tools as $t)
          <div class="tool-card"><span class="feature-ico"><svg aria-hidden="true"><use href="#i-{{ $t->icon ?: 'gem' }}"/></svg></span><div><b>{{ $t->title }}</b><span>{{ $t->text }}</span></div></div>
        @endforeach
      </div>
    </div>
  </section>

  <!-- ============ CTA BANNER ============ -->
  <section class="video-banner notch-top cta-banner" aria-labelledby="cta-title">
    <img class="vb-bg" src="{{ img(setting('services.cta_image')) }}" alt="" width="1456" height="818" loading="lazy" data-parallax="0.1">
    <div class="vb-inner">
      <h2 id="cta-title" class="display-md" data-split>{{ heading(setting('services.cta_title')) }}</h2>
      <p class="lead reveal blur" style="--d:.3s">{{ setting('services.cta_lead') }}</p>
      <div class="cta-actions reveal zoom" style="--d:.45s">
        <a class="btn btn-primary" href="{{ route('contact') }}" data-magnetic>شروع پروژه <svg aria-hidden="true"><use href="#i-play"/></svg></a>
        <a class="btn btn-ghost" href="{{ route('games.index') }}">دیدن نمونه‌کارها <svg aria-hidden="true"><use href="#i-play"/></svg></a>
      </div>
    </div>
  </section>

  @include('partials.faq', [
      'faqs' => $faqs, 'title' => setting('services.faq_title'), 'lead' => setting('services.faq_lead'),
      'character' => 'assets/img/characters/razino-companion.webp', 'button' => true,
  ])
@endsection
