{{-- Inner-page hero. Vars: $group (settings group), $crumbs [[label, url|null]], $chips [[icon, text]] (optional), $compact (bool) --}}
@php $titleId = $group.'-title'; @endphp
<section class="hero page-hero {{ ($compact ?? true) ? 'compact' : '' }} notch-bottom" id="home" aria-labelledby="{{ $titleId }}">
  <div class="hero-bg" aria-hidden="true" data-depth="-0.6"><img src="{{ img($image ?? setting($group.'.hero_image')) }}" alt="" width="1456" height="818" data-parallax="0.12" fetchpriority="high"></div>
  <div class="hero-fx" aria-hidden="true" data-depth="-1.4"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
  <div class="container hero-grid">
    <div class="hero-copy">
      <nav class="breadcrumb reveal from-right" aria-label="مسیر صفحه">
        <ol>
          @foreach ($crumbs as [$label, $url])
            @if ($url)<li><a href="{{ $url }}">{{ $label }}</a></li>@else<li aria-current="page">{{ $label }}</li>@endif
          @endforeach
        </ol>
      </nav>
      {{ $before ?? '' }}
      <h1 id="{{ $titleId }}" class="display" data-split>{{ heading($heroTitle ?? setting($group.'.hero_title'), true) }}</h1>
      <p class="hero-lead reveal blur" style="--d:.7s">{{ $heroLead ?? setting($group.'.hero_lead') }}</p>
      @if (! empty($chips))
        <ul class="meta-chips reveal from-right" style="--d:.85s">
          @foreach ($chips as [$icon, $text])
            <li><svg aria-hidden="true"><use href="#i-{{ $icon }}"/></svg>{{ $text }}</li>
          @endforeach
        </ul>
      @endif
      {{ $after ?? '' }}
    </div>
    @php $char = $character ?? setting($group.'.hero_character'); @endphp
    @if ($char)
      <figure class="hero-char reveal-up" style="--d:.3s">
        <img class="float-slow {{ str_contains($char, 'kairo') || str_contains($char, 'ilara') ? 'pixel' : '' }}" src="{{ img($char) }}" alt="{{ $characterAlt ?? '' }}" data-depth="1.6" data-speed="-0.3">
        <figcaption class="char-tag" data-depth="2.6">{{ $characterName ?? setting($group.'.hero_character_name') }} <small>{{ $characterSub ?? setting($group.'.hero_character_sub') }}</small></figcaption>
      </figure>
    @endif
  </div>
</section>
