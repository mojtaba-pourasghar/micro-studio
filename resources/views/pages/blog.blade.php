@extends('layouts.site', ['bodyClass' => 'home blog-page', 'preload' => setting('blog.hero_image'), 'description' => setting('blog.hero_lead'),
    'canonical' => route('blog.index', array_filter(['cat' => request('cat'), 'page' => $posts->currentPage() > 1 ? $posts->currentPage() : null])),
    'noindex' => filled(request('q')) || $posts->isEmpty(),
    'title' => request('cat') ? (optional($categories->firstWhere('slug', request('cat')))->name.' | وبلاگ') : 'وبلاگ',
    'schema' => [
        \App\Support\Seo::webPage('CollectionPage', ['name' => 'وبلاگ میکرو استودیو', 'description' => setting('blog.hero_lead'), 'mainEntity' => ['@id' => url()->current().'#posts']]),
        \App\Support\Seo::itemList('posts', $posts->getCollection(), fn ($p) => ['url' => route('blog.show', $p), 'name' => $p->title]),
    ],
])

@section('content')
  @include('partials.page-hero', [
      'group' => 'blog',
      'crumbs' => [['صفحه اصلی', route('home')], ['وبلاگ', null]],
  ])

  @if ($featured)
    <!-- ============ FEATURED POST ============ -->
    <section class="section blog-featured" aria-label="مطلب ویژه">
      <div class="container">
        <article class="post-featured">
          <a class="post-featured-media wipe" href="{{ route('blog.show', $featured) }}" tabindex="-1" aria-hidden="true"><img src="{{ img($featured->cover_image) }}" alt="" loading="lazy"></a>
          <div class="post-featured-body">
            <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> مطلب ویژه</p>
            @if ($featured->category)<a class="post-cat reveal from-right" href="{{ route('blog.index', ['cat' => $featured->category->slug]) }}">{{ $featured->category->name }}</a>@endif
            <h2 class="h2" data-split><a href="{{ route('blog.show', $featured) }}">{{ $featured->title }}</a></h2>
            <p class="lead reveal blur" style="--d:.3s">{{ $featured->excerpt }}</p>
            @include('partials.post-meta', ['post' => $featured, 'author' => true])
            <a class="btn btn-primary reveal from-right" style="--d:.5s" href="{{ route('blog.show', $featured) }}" data-magnetic>خواندن مطلب <svg aria-hidden="true"><use href="#i-play"/></svg></a>
          </div>
        </article>
      </div>
    </section>
  @endif

  <!-- ============ FILTER + GRID ============ -->
  <section class="section blog-list" id="posts" aria-labelledby="posts-title">
    <div class="container">
      <div class="blog-toolbar">
        <div>
          <p class="eyebrow reveal from-right"><svg aria-hidden="true"><use href="#i-gem"/></svg> همه‌ی مطالب</p>
          <h2 id="posts-title" class="h2" data-split>تازه‌ترین <span class="outline">یادداشت‌ها</span></h2>
        </div>
        <form class="blog-search reveal from-left" role="search" action="{{ route('blog.index') }}#posts" method="get">
          <label class="sr-only" for="blog-q">جست‌وجو در وبلاگ</label>
          <input id="blog-q" name="q" type="search" value="{{ request('q') }}" placeholder="جست‌وجو در مطالب…" autocomplete="off">
          <button type="submit" aria-label="جست‌وجو"><svg aria-hidden="true"><use href="#i-search"/></svg></button>
        </form>
      </div>

      <nav class="cat-filter reveal from-right" aria-label="دسته‌بندی مطالب">
        <a class="cat-chip {{ ! request('cat') ? 'is-active' : '' }}" href="{{ route('blog.index') }}#posts" @if (! request('cat')) aria-current="page" @endif>همه</a>
        @foreach ($categories as $cat)
          <a class="cat-chip {{ request('cat') === $cat->slug ? 'is-active' : '' }}" href="{{ route('blog.index', ['cat' => $cat->slug]) }}#posts" @if (request('cat') === $cat->slug) aria-current="page" @endif>{{ $cat->name }}</a>
        @endforeach
      </nav>

      @if ($posts->isEmpty())
        <p class="filter-empty">مطلبی پیدا نشد.</p>
      @else
        <div class="post-grid stagger">
          @foreach ($posts as $post)
            <article class="post-card">
              <a class="post-thumb" href="{{ route('blog.show', $post) }}"><img src="{{ img($post->cover_image) }}" alt="" loading="lazy">@if ($post->category)<span class="post-cat">{{ $post->category->name }}</span>@endif</a>
              <div class="post-body">
                <h3><a href="{{ route('blog.show', $post) }}">{{ $post->title }}</a></h3>
                <p>{{ $post->excerpt }}</p>
                @include('partials.post-meta', ['post' => $post, 'plain' => true])
              </div>
            </article>
          @endforeach
        </div>
      @endif

      @if ($posts->hasPages())
        <nav class="pagination reveal" aria-label="صفحه‌بندی مطالب">
          @if ($posts->onFirstPage())<span class="page-btn is-disabled" aria-hidden="true"><svg><use href="#i-arrow"/></svg></span>
          @else<a class="page-btn" href="{{ $posts->previousPageUrl() }}#posts" aria-label="صفحه‌ی قبل"><svg aria-hidden="true"><use href="#i-arrow"/></svg></a>@endif
          @foreach ($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
            <a class="page-btn {{ $page === $posts->currentPage() ? 'is-current' : '' }}" href="{{ $url }}#posts" @if ($page === $posts->currentPage()) aria-current="page" @endif>{{ fa_digits($page) }}</a>
          @endforeach
          @if ($posts->hasMorePages())<a class="page-btn" href="{{ $posts->nextPageUrl() }}#posts" aria-label="صفحه‌ی بعد"><svg class="flip" aria-hidden="true"><use href="#i-arrow"/></svg></a>
          @else<span class="page-btn is-disabled" aria-hidden="true"><svg class="flip"><use href="#i-arrow"/></svg></span>@endif
        </nav>
      @endif
    </div>
  </section>

  <!-- ============ NEWSLETTER ============ -->
  <section class="video-banner notch-top newsletter" aria-labelledby="nl-title">
    <img class="vb-bg" src="{{ img(setting('blog.newsletter_image')) }}" alt="" width="1456" height="818" loading="lazy" data-parallax="0.1">
    <div class="vb-inner">
      <h2 id="nl-title" class="display-md" data-split>{{ heading(setting('blog.newsletter_title')) }}</h2>
      <form class="nl-form reveal zoom" style="--d:.4s" action="{{ route('newsletter.store') }}" method="post" novalidate>
        @csrf
        <div class="sr-only" aria-hidden="true"><label>وب‌سایت<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
        <label class="sr-only" for="nl-email">ایمیل شما</label>
        <input id="nl-email" name="email" type="email" dir="ltr" placeholder="you@example.com" autocomplete="email" required>
        <button class="btn btn-primary" type="submit">عضویت <svg aria-hidden="true"><use href="#i-play"/></svg></button>
        <p class="nl-status" role="status" aria-live="polite"></p>
      </form>
    </div>
  </section>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/blog.js') }}?v={{ filemtime(public_path('assets/js/blog.js')) }}"></script>
@endpush
