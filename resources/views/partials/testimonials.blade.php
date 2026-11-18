{{-- Testimonials marquee (text only). Vars: $testimonials, $notch (bool) --}}
<section class="section testi {{ ! empty($notch) ? 'notch-top' : '' }}" id="testimonials" aria-labelledby="testi-title">
  <div class="container">
    <p class="eyebrow light reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> {{ setting('home.testi_eyebrow') }}</p>
    <h2 id="testi-title" class="h2 light" data-split>{{ heading(setting('home.testi_title')) }}</h2>
  </div>
  <div class="testi-rail" data-marquee>
    <div class="testi-track">
      @foreach ($testimonials as $t)
        <figure class="t-card">
          <div class="stars" role="img" aria-label="{{ fa_digits($t->rating) }} ستاره">@for ($i = 0; $i < $t->rating; $i++)<svg><use href="#i-star"/></svg>@endfor</div>
          <blockquote>{{ $t->quote }}</blockquote>
          <figcaption><b>{{ $t->name }}</b><span>{{ $t->role }}</span></figcaption>
        </figure>
      @endforeach
    </div>
  </div>
</section>
