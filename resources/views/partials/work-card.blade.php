{{-- Game/app card. Vars: $game, $link (bool, default true), $more (bool), $short (bool: hide text + platforms) --}}
@php $link = $link ?? true; $tag = $link ? 'a' : 'article'; @endphp
<{{ $tag }} class="work-card {{ $game->card_is_phone ? 'phone' : '' }}" @if ($link) href="{{ route('games.show', $game) }}" @endif data-cat="{{ $game->category }}">
  <img class="work-bg" src="{{ img($game->card_image) }}" alt="" loading="lazy">
  <span class="work-badge">{{ $game->short_title }}</span>
  @if (! empty($more))<span class="more">جزئیات <svg aria-hidden="true"><use href="#i-play"/></svg></span>@endif
  <div class="work-info">
    <div class="tags">
      @foreach (array_slice($game->tags ?? [], 0, empty($short) ? 3 : 1) as $t)<span class="tag {{ tag_color($t['color'] ?? null) }}">{{ $t['label'] ?? '' }}</span>@endforeach
      @if ($game->status === 'designing' && empty($short))<span class="status-chip">در حال طراحی</span>@endif
    </div>
    <h3>{{ $game->title }}</h3>
    @if (empty($short))
      <p>{{ $game->summary }}</p>
      <div class="platforms">
        @foreach ($game->platforms ?? ['android'] as $p)
          <svg role="img" aria-label="{{ $p === 'web' ? 'وب' : 'اندروید' }}"><use href="#i-{{ $p === 'web' ? 'web' : 'android' }}"/></svg>
        @endforeach
      </div>
    @endif
  </div>
</{{ $tag }}>
