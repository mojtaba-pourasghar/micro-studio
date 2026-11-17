@extends('layouts.site', [
    'bodyClass' => 'home post-page',
    'title' => $post->seo_title ?: $post->title,
    'description' => $post->seo_description ?: $post->excerpt,
    'preload' => $post->cover_image,
    'ogImage' => $post->cover_image,
    'ogType' => 'article',
    'noindex' => $post->status !== 'published',
    'ogExtra' => $post->published_at ? '<meta property="article:published_time" content="'.$post->published_at->toIso8601String().'"><meta property="article:modified_time" content="'.$post->updated_at->toIso8601String().'">'.($post->category ? '<meta property="article:section" content="'.e($post->category->name).'">' : '') : '',
    'schema' => [
        \App\Support\Seo::webPage('WebPage', ['name' => $post->title, 'description' => $post->excerpt, 'primaryImageOfPage' => $post->cover_image ? img($post->cover_image) : null]),
        \App\Support\Seo::post($post),
    ],
])

@section('content')
  <div class="read-progress" aria-hidden="true"><span></span></div>

  <!-- ============ POST HERO ============ -->
  <section class="hero page-hero compact post-hero notch-bottom" id="home" aria-labelledby="post-title">
    <div class="hero-bg" aria-hidden="true" data-depth="-0.6"><img src="{{ img($post->cover_image) }}" alt="" width="1200" height="751" data-parallax="0.12" fetchpriority="high"></div>
    <div class="container post-hero-inner">
      <nav class="breadcrumb reveal from-right" aria-label="مسیر صفحه">
        <ol><li><a href="{{ route('home') }}">صفحه اصلی</a></li><li><a href="{{ route('blog.index') }}">وبلاگ</a></li><li aria-current="page">{{ $post->title }}</li></ol>
      </nav>
      @if ($post->category)<a class="post-cat reveal from-right" href="{{ route('blog.index', ['cat' => $post->category->slug]) }}">{{ $post->category->name }}</a>@endif
      <h1 id="post-title" class="display" data-split>{{ $post->title }}</h1>
      @include('partials.post-meta', ['post' => $post, 'author' => true, 'light' => true])
      @if ($post->status !== 'published')
        <p class="post-cat" style="margin-top:14px;background:#ffb547;color:#12131c">پیش‌نمایش — {{ \App\Models\Post::STATUSES[$post->status] ?? $post->status }}</p>
      @endif
    </div>
  </section>

  <!-- ============ ARTICLE ============ -->
  <section class="section post-section" aria-label="متن مطلب">
    <div class="container post-layout">
      <article class="prose">
        {!! $post->renderedBody() !!}
        <footer class="post-foot">
          @if ($post->tags)
            <div class="post-tags">@foreach ($post->tags as $tag)<a href="{{ route('blog.index', ['q' => str_replace('_', ' ', $tag)]) }}#posts">#{{ $tag }}</a>@endforeach</div>
          @endif
          <div class="share" aria-label="اشتراک‌گذاری">
            <span>اشتراک:</span>
            <a class="share-btn" data-share="telegram" href="https://t.me/share/url" target="_blank" rel="noopener" aria-label="اشتراک در تلگرام"><svg aria-hidden="true"><use href="#i-telegram"/></svg></a>
            <button class="share-btn" type="button" data-share="copy" aria-label="کپی پیوند"><svg aria-hidden="true"><use href="#i-link"/></svg></button>
            <span class="share-status" role="status" aria-live="polite"></span>
          </div>
        </footer>
        <div class="author-box reveal from-right">
          <img src="{{ img(setting('blog.author_avatar')) }}" alt="" width="320" height="320" loading="lazy">
          <div><b>{{ $post->author }}</b><p>{{ setting('blog.author_bio') }}</p></div>
        </div>
      </article>

      <aside class="post-aside" aria-label="فهرست و مطالب مرتبط">
        <nav class="toc" aria-labelledby="toc-title">
          <h2 id="toc-title" class="toc-title">در این مطلب</h2>
          <ol class="toc-list"></ol>
        </nav>
        @if ($post->game)
          <a class="aside-game" href="{{ route('games.show', $post->game) }}">
            <img src="{{ img($post->game->hero_character ?: 'assets/img/characters/razino-hero.webp') }}" alt="" loading="lazy">
            <span><small>بازی این مطلب</small><b>{{ $post->game->title }}</b></span>
          </a>
        @endif
      </aside>
    </div>
  </section>

  @if ($related->isNotEmpty())
    <!-- ============ RELATED POSTS ============ -->
    <section class="section related-posts" aria-labelledby="related-title">
      <div class="container">
        <div class="works-head">
          <div>
            <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> ادامه بدهید</p>
            <h2 id="related-title" class="h2" data-split>مطالب <span class="outline">مرتبط</span></h2>
          </div>
          <a class="btn btn-primary reveal from-left" href="{{ route('blog.index') }}" data-magnetic>همه‌ی مطالب <svg aria-hidden="true"><use href="#i-play"/></svg></a>
        </div>
        <div class="post-grid stagger">
          @foreach ($related as $r)
            <article class="post-card">
              <a class="post-thumb" href="{{ route('blog.show', $r) }}"><img src="{{ img($r->cover_image) }}" alt="" loading="lazy">@if ($r->category)<span class="post-cat">{{ $r->category->name }}</span>@endif</a>
              <div class="post-body">
                <h3><a href="{{ route('blog.show', $r) }}">{{ $r->title }}</a></h3>
                <p>{{ $r->excerpt }}</p>
                @include('partials.post-meta', ['post' => $r, 'plain' => true])
              </div>
            </article>
          @endforeach
        </div>
      </div>
    </section>
  @endif
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/blog.js') }}?v={{ filemtime(public_path('assets/js/blog.js')) }}"></script>
@endpush
