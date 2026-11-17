<?php

namespace App\Support;

use App\Models\Faq;
use App\Models\Game;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/** schema.org JSON-LD for every page (one @graph per page, linked by @id). */
class Seo
{
    public static function orgId(): string
    {
        return url('/').'#organization';
    }

    public static function siteId(): string
    {
        return url('/').'#website';
    }

    public static function organization(): array
    {
        $sameAs = array_values(array_filter([setting('contact.instagram_url'), setting('contact.telegram_url'), setting('contact.whatsapp_url')]));

        return array_filter([
            '@type' => 'Organization',
            '@id' => static::orgId(),
            'name' => setting('site.name'),
            'alternateName' => 'Micro Studio',
            'url' => url('/'),
            'logo' => ['@type' => 'ImageObject', 'url' => asset('assets/brand/icon-512.png'), 'width' => 512, 'height' => 512],
            'image' => asset('assets/brand/logo-horizontal-dark.png'),
            'description' => setting('site.meta_description'),
            'email' => setting('contact.email'),
            'telephone' => setting('contact.phone'),
            'address' => ['@type' => 'PostalAddress', 'addressCountry' => 'IR', 'addressLocality' => setting('contact.address')],
            'sameAs' => $sameAs ?: null,
            'contactPoint' => [[
                '@type' => 'ContactPoint', 'contactType' => 'customer support', 'email' => setting('contact.email'),
                'telephone' => setting('contact.phone'), 'availableLanguage' => ['fa', 'Persian'], 'areaServed' => 'IR',
            ]],
            'knowsLanguage' => 'fa-IR',
        ]);
    }

    public static function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => static::siteId(),
            'url' => url('/'),
            'name' => setting('site.name'),
            'inLanguage' => 'fa-IR',
            'publisher' => ['@id' => static::orgId()],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => ['@type' => 'EntryPoint', 'urlTemplate' => route('blog.index').'?q={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /** @param array<int, array{0:string,1:string}> $items [name, url] */
    public static function breadcrumb(array $items): array
    {
        return [
            '@type' => 'BreadcrumbList',
            '@id' => url()->current().'#breadcrumb',
            'itemListElement' => collect($items)->values()->map(fn ($c, $i) => [
                '@type' => 'ListItem', 'position' => $i + 1, 'name' => $c[0], 'item' => $c[1],
            ])->all(),
        ];
    }

    /** Breadcrumb trail for the current route. */
    public static function crumbsForRequest(): array
    {
        $route = request()->route();
        $name = $route?->getName();
        $home = [['صفحه اصلی', route('home')]];

        return match (true) {
            $name === 'home' => $home,
            $name === 'games.index' => [...$home, ['بازی‌ها و اپ‌ها', route('games.index')]],
            $name === 'games.show' => [...$home, ['بازی‌ها و اپ‌ها', route('games.index')], [optional($route->parameter('game'))->title ?? '', url()->current()]],
            $name === 'services' => [...$home, ['خدمات', route('services')]],
            $name === 'about' => [...$home, ['درباره ما', route('about')]],
            $name === 'contact' => [...$home, ['تماس با ما', route('contact')]],
            $name === 'blog.index' => [...$home, ['وبلاگ', route('blog.index')]],
            $name === 'blog.show' => [...$home, ['وبلاگ', route('blog.index')], [optional($route->parameter('post'))->title ?? '', url()->current()]],
            default => $home,
        };
    }

    public static function webPage(string $type = 'WebPage', array $extra = []): array
    {
        return array_filter([
            '@type' => $type,
            '@id' => url()->current().'#webpage',
            'url' => url()->current(),
            'inLanguage' => 'fa-IR',
            'isPartOf' => ['@id' => static::siteId()],
            'about' => ['@id' => static::orgId()],
            'breadcrumb' => ['@id' => url()->current().'#breadcrumb'],
        ] + $extra);
    }

    public static function faqPage(Collection $faqs): ?array
    {
        if ($faqs->isEmpty()) {
            return null;
        }

        return [
            '@type' => 'FAQPage',
            '@id' => url()->current().'#faq',
            'mainEntity' => $faqs->map(fn (Faq $f) => [
                '@type' => 'Question', 'name' => $f->question,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer],
            ])->values()->all(),
        ];
    }

    public static function itemList(string $id, Collection $items, callable $map): array
    {
        return [
            '@type' => 'ItemList',
            '@id' => url()->current().'#'.$id,
            'numberOfItems' => $items->count(),
            'itemListElement' => $items->values()->map(fn ($item, $i) => ['@type' => 'ListItem', 'position' => $i + 1] + $map($item))->all(),
        ];
    }

    /** ISO-8601 duration from seconds (PT1M47S). */
    public static function duration(?int $seconds): ?string
    {
        if (! $seconds) {
            return null;
        }

        return 'PT'.(intdiv($seconds, 60) ? intdiv($seconds, 60).'M' : '').($seconds % 60).'S';
    }

    public static function videos(Game $game): array
    {
        return collect($game->videos ?? [])->map(fn ($v, $i) => array_filter([
            '@type' => 'VideoObject',
            '@id' => route('games.show', $game).'#video-'.($i + 1),
            'name' => ($v['title'] ?? '') ?: $game->title,
            'description' => $game->tagline ?: $game->summary,
            'thumbnailUrl' => isset($v['poster']) ? img($v['poster']) : img($game->logo ?: $game->card_image),
            'contentUrl' => img($v['src']),
            'uploadDate' => $game->updated_at?->toIso8601String(),
            'duration' => static::duration($v['duration'] ?? null),
            'inLanguage' => 'fa-IR',
            'publisher' => ['@id' => static::orgId()],
        ]))->values()->all();
    }

    public static function game(Game $game): array
    {
        $images = collect([$game->logo, $game->card_image, $game->hero_image])->filter()
            ->merge(collect($game->screenshots ?? [])->pluck('image'))->filter()->unique()->map(fn ($p) => img($p))->values();
        $isGame = $game->category === 'game';

        return array_filter([
            '@type' => $isGame ? ['MobileApplication', 'VideoGame'] : 'MobileApplication',
            '@id' => route('games.show', $game).'#app',
            'name' => $game->title,
            'alternateName' => $game->short_title !== $game->title ? $game->short_title : null,
            'url' => route('games.show', $game),
            'description' => strip_tags($game->tagline ?: $game->summary),
            'applicationCategory' => $isGame ? 'GameApplication' : 'EducationalApplication',
            'genre' => $game->genre,
            'operatingSystem' => collect($game->platforms ?? ['android'])->map(fn ($p) => $p === 'web' ? 'Web' : 'Android')->implode(', '),
            'inLanguage' => 'fa-IR',
            'image' => $images->first(),
            'screenshot' => $images->slice(1)->values()->all() ?: null,
            'video' => collect(static::videos($game))->map(fn ($v) => ['@id' => $v['@id']])->all() ?: null,
            'author' => ['@id' => static::orgId()],
            'publisher' => ['@id' => static::orgId()],
            'audience' => $game->age_range ? ['@type' => 'PeopleAudience', 'audienceType' => $game->age_range] : null,
            'keywords' => collect($game->tags ?? [])->pluck('label')->implode('، ') ?: null,
            'featureList' => collect($game->features ?? [])->pluck('title')->all() ?: null,
            'gamePlatform' => $isGame ? 'Android' : null,
            'playMode' => $isGame ? 'SinglePlayer' : null,
            'creativeWorkStatus' => Game::STATUSES[$game->status] ?? null,
            'dateModified' => $game->updated_at?->toIso8601String(),
        ]);
    }

    public static function post(Post $post): array
    {
        return array_filter([
            '@type' => 'BlogPosting',
            '@id' => route('blog.show', $post).'#article',
            'headline' => Str::limit($post->title, 110, ''),
            'description' => $post->seo_description ?: $post->excerpt,
            'image' => $post->cover_image ? [img($post->cover_image)] : null,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'author' => ['@type' => 'Organization', 'name' => $post->author, 'url' => route('about')],
            'publisher' => ['@id' => static::orgId()],
            'mainEntityOfPage' => ['@id' => url()->current().'#webpage'],
            'articleSection' => $post->category?->name,
            'keywords' => collect($post->tags ?? [])->map(fn ($t) => str_replace('_', ' ', $t))->implode('، ') ?: null,
            'wordCount' => str_word_count(strip_tags((string) $post->body)) ?: null,
            'inLanguage' => 'fa-IR',
            'about' => $post->game ? ['@id' => route('games.show', $post->game).'#app'] : null,
        ]);
    }

    /** @param array<int, array|null> $nodes */
    public static function script(array $nodes): HtmlString
    {
        $graph = ['@context' => 'https://schema.org', '@graph' => array_values(array_filter($nodes))];

        return new HtmlString('<script type="application/ld+json">'.json_encode($graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG).'</script>');
    }
}
