{{-- Achievements list. Vars: $honors, $class --}}
<section class="section honors {{ $class ?? '' }}" id="honors" aria-labelledby="honors-title">
  <div class="container">
    <div class="honors-head">
      <div>
        <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> {{ setting('home.honors_eyebrow') }}</p>
        <h2 id="honors-title" class="h2" data-split>{{ heading(setting('home.honors_title')) }}</h2>
      </div>
      <div class="trophy reveal zoom" aria-hidden="true" data-depth="2"><svg viewBox="0 0 120 120"><use href="#i-trophy"/></svg></div>
      <p class="honors-note reveal from-left">{{ setting('home.honors_note') }}</p>
    </div>
    <div class="honors-body">
      <div class="notch-card small wipe" data-speed="-0.15"><img src="{{ img(setting('home.honors_image')) }}" alt="" width="1456" height="818" loading="lazy"></div>
      <ul class="honors-list stagger">
        @foreach ($honors as $h)
          <li class="reveal"><span class="year" @if (is_numeric($h->value)) data-count="{{ $h->value }}" @endif>{{ fa_digits($h->value) }}</span><span class="title">{{ $h->title }}</span><span class="note">{{ $h->label }}</span></li>
        @endforeach
      </ul>
    </div>
  </div>
</section>
