{{-- Strip of our games above the footer (replaces the old image strip) --}}
@php($stripGames = \App\Models\Game::visible()->get(['slug', 'title', 'short_title', 'logo', 'card_image', 'age_range']))
@if ($stripGames->isNotEmpty())
<nav class="app-strip" aria-label="بازی‌ها و اپ‌های ما">
  <div class="app-strip-track">
    @foreach ([$stripGames, $stripGames] as $round)
      @foreach ($round as $g)
        <a class="app-chip" href="{{ route('games.show', $g) }}" @if (! $loop->parent->first) tabindex="-1" aria-hidden="true" @endif>
          <img src="{{ img($g->logo ?: $g->card_image) }}" alt="" width="56" height="56" loading="lazy">
          <span><b>{{ $g->short_title ?: $g->title }}</b>@if ($g->age_range)<small>{{ $g->age_range }}</small>@endif</span>
        </a>
      @endforeach
    @endforeach
  </div>
</nav>
@endif
