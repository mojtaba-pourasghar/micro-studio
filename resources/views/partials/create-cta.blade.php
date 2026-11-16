{{-- "Let's create" CTA. Vars: $eyebrow, $title, $lead, $image, $character, $button, $href --}}
<section class="section create" id="create" aria-labelledby="create-title">
  <div class="container create-grid">
    <div class="create-art">
      <div class="orb reveal zoom" data-rot="20"><img class="orb-bg" src="{{ img($image) }}" alt="" width="1456" height="818" loading="lazy"></div>
      <img class="create-char reveal-up {{ str_contains($character, 'kairo') || str_contains($character, 'ilara') ? 'pixel' : '' }}" src="{{ img($character) }}" alt="" loading="lazy" data-x="120" data-speed="-0.22" data-depth="1.4">
      <span class="gem g1" aria-hidden="true" data-speed="-0.6" data-rot="90" data-depth="3"></span><span class="gem g2" aria-hidden="true" data-speed="-0.35" data-rot="-70" data-depth="2.2"></span><span class="gem g3" aria-hidden="true" data-speed="-0.8" data-rot="120" data-depth="3.4"></span>
    </div>
    <div class="create-copy">
      <p class="vertical-label reveal from-top" aria-hidden="true"><svg><use href="#i-gem"/></svg> {{ $eyebrow }}</p>
      <h2 id="create-title" class="h2 big" data-split>{{ heading($title) }}</h2>
      <p class="lead reveal blur" style="--d:.35s">{{ $lead }}</p>
      <a class="btn btn-primary reveal from-right" style="--d:.5s" href="{{ $href ?? route('contact') }}" data-magnetic>{{ $button ?? 'بیایید صحبت کنیم' }} <svg aria-hidden="true"><use href="#i-play"/></svg></a>
    </div>
  </div>
</section>
