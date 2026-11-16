{{-- Testimonials marquee. Vars: $testimonials, $notch (bool) --}}
<section class="section testi {{ ! empty($notch) ? 'notch-top' : '' }}" id="testimonials" aria-labelledby="testi-title">
  <img class="testi-bg" src="{{ img(setting('home.testi_image')) }}" alt="" width="1456" height="818" loading="lazy">
  <img class="testi-char" src="{{ asset('assets/img/characters/razino-enemy.webp') }}" alt="" width="253" height="351" loading="lazy" data-x="-200" data-speed="-0.3" data-depth="1.6">
  <div class="container">
    <p class="eyebrow light reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> {{ setting('home.testi_eyebrow') }}</p>
    <h2 id="testi-title" class="h2 light" data-split>{{ heading(setting('home.testi_title')) }}</h2>
  </div>
  <div class="testi-rail" data-marquee>
    <div class="testi-track">
      @foreach ($testimonials as $t)
        <figure class="t-card"><img src="{{ img($t->avatar, 'assets/img/characters/kairo-face-1.webp') }}" alt="" width="320" height="320" loading="lazy"><div><div class="stars" role="img" aria-label="{{ fa_digits($t->rating) }} ستاره">@for ($i = 0; $i < $t->rating; $i++)<svg><use href="#i-star"/></svg>@endfor</div><blockquote>«{{ $t->quote }}»</blockquote><figcaption><b>{{ $t->name }}</b><span>{{ $t->role }}</span></figcaption></div></figure>
      @endforeach
    </div>
  </div>
</section>
