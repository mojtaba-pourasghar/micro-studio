{{-- FAQ section. Vars: $faqs, $title, $lead, $character, $button (bool) --}}
<section class="section faq" id="faq" aria-labelledby="faq-title">
  <div class="container faq-grid">
    <div class="faq-copy">
      <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> سؤالات متداول</p>
      <h2 id="faq-title" class="h2" data-split>{{ heading($title) }}</h2>
      <p class="lead reveal blur" style="--d:.3s">{{ $lead }}</p>
      @if (! empty($button))<a class="btn btn-primary reveal from-right" href="{{ route('contact') }}" data-magnetic>سؤال بپرسید <svg aria-hidden="true"><use href="#i-play"/></svg></a>@endif
      <img class="sec-char" src="{{ img($character) }}" alt="" loading="lazy" data-x="100" data-speed="-0.15" data-depth="1.3">
    </div>
    <div class="faq-list stagger">
      @foreach ($faqs as $faq)
        <details class="faq-item" @if ($loop->first) open @endif><summary>{{ $faq->question }}<svg aria-hidden="true"><use href="#i-plus"/></svg></summary><p>{{ $faq->answer }}</p></details>
      @endforeach
    </div>
  </div>
</section>
