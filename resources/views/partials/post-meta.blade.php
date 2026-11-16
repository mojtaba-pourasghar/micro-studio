{{-- Vars: $post, $author (bool), $plain (bool: no reveal animation), $light (bool) --}}
<ul class="post-meta {{ ! empty($light) ? 'light' : '' }} {{ empty($plain) ? 'reveal from-right' : '' }}" @if (empty($plain)) style="--d:.4s" @endif>
  @if (! empty($author))<li><svg aria-hidden="true"><use href="#i-user"/></svg>{{ $post->author }}</li>@endif
  @if ($post->published_at)<li><svg aria-hidden="true"><use href="#i-cal"/></svg><time datetime="{{ $post->published_at->toDateString() }}">{{ \App\Support\Jalali::format($post->published_at) }}</time></li>@endif
  @if ($post->read_minutes)<li><svg aria-hidden="true"><use href="#i-clock"/></svg>{{ fa_digits($post->read_minutes) }} دقیقه{{ ! empty($author) ? ' مطالعه' : '' }}</li>@endif
</ul>
