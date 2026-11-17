<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Service;

/** sitemap.xml (with images and videos), RSS, robots.txt, web manifest and llms.txt. */
class SeoController extends Controller
{
    protected function xml(string $body, string $type = 'application/xml')
    {
        return response($body, 200, ['Content-Type' => $type.'; charset=UTF-8', 'Cache-Control' => 'public, max-age=3600']);
    }

    public function sitemap()
    {
        $e = fn ($v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $latestGame = Game::visible()->max('updated_at');
        $latestPost = Post::published()->max('updated_at');
        $pages = [
            [route('home'), max($latestGame, $latestPost), 'daily', '1.0', []],
            [route('games.index'), $latestGame, 'weekly', '0.9', []],
            [route('services'), Service::max('updated_at'), 'monthly', '0.8', []],
            [route('about'), null, 'monthly', '0.6', []],
            [route('contact'), null, 'yearly', '0.6', []],
            [route('blog.index'), $latestPost, 'daily', '0.8', []],
        ];
        foreach (PostCategory::orderBy('sort_order')->get() as $cat) {
            if ($cat->posts()->where('status', 'published')->exists()) {
                $pages[] = [route('blog.index', ['cat' => $cat->slug]), null, 'weekly', '0.5', []];
            }
        }
        foreach (Game::visible()->get() as $g) {
            $media = collect([$g->logo, $g->card_image])->merge(collect($g->screenshots ?? [])->pluck('image'))->filter()->unique()
                ->map(fn ($p) => ['image', img($p), $g->title])->values()->all();
            foreach ($g->videos ?? [] as $v) {
                $media[] = ['video', $v, $g];
            }
            $pages[] = [route('games.show', $g), $g->updated_at, 'weekly', '0.9', $media];
        }
        foreach (Post::published()->get() as $p) {
            $pages[] = [route('blog.show', $p), $p->updated_at, 'monthly', '0.7', $p->cover_image ? [['image', img($p->cover_image), $p->title]] : []];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">'."\n";
        foreach ($pages as [$loc, $lastmod, $freq, $prio, $media]) {
            $xml .= '<url><loc>'.$e($loc).'</loc>'
                .($lastmod ? '<lastmod>'.\Illuminate\Support\Carbon::parse($lastmod)->toAtomString().'</lastmod>' : '')
                ."<changefreq>$freq</changefreq><priority>$prio</priority>";
            foreach ($media as $m) {
                if ($m[0] === 'image') {
                    $xml .= '<image:image><image:loc>'.$e($m[1]).'</image:loc></image:image>';
                } else {
                    [$_, $v, $g] = $m;
                    $xml .= '<video:video><video:thumbnail_loc>'.$e(img($v['poster'] ?? $g->logo ?? $g->card_image)).'</video:thumbnail_loc>'
                        .'<video:title>'.$e(($v['title'] ?? '') ?: $g->title).'</video:title>'
                        .'<video:description>'.$e($g->summary).'</video:description>'
                        .'<video:content_loc>'.$e(img($v['src'])).'</video:content_loc>'
                        .(! empty($v['duration']) ? '<video:duration>'.(int) $v['duration'].'</video:duration>' : '')
                        .'<video:family_friendly>yes</video:family_friendly></video:video>';
                }
            }
            $xml .= "</url>\n";
        }

        return $this->xml($xml.'</urlset>');
    }

    public function feed()
    {
        $e = fn ($v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $posts = Post::published()->with('category')->limit(30)->get();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/"><channel>'
            .'<title>'.$e(setting('site.name').' — وبلاگ').'</title><link>'.$e(route('blog.index')).'</link>'
            .'<description>'.$e(setting('blog.hero_lead')).'</description><language>fa-IR</language>'
            .'<atom:link href="'.$e(route('feed')).'" rel="self" type="application/rss+xml"/>'
            .($posts->first() ? '<lastBuildDate>'.$posts->first()->published_at->toRssString().'</lastBuildDate>' : '');
        foreach ($posts as $p) {
            $xml .= '<item><title>'.$e($p->title).'</title><link>'.$e(route('blog.show', $p)).'</link><guid isPermaLink="true">'.$e(route('blog.show', $p)).'</guid>'
                .'<pubDate>'.$p->published_at->toRssString().'</pubDate><description>'.$e($p->excerpt).'</description>'
                .($p->category ? '<category>'.$e($p->category->name).'</category>' : '')
                .($p->cover_image ? '<media:content url="'.$e(img($p->cover_image)).'" medium="image"/>' : '').'</item>';
        }

        return $this->xml($xml.'</channel></rss>', 'application/rss+xml');
    }

    public function robots()
    {
        $lines = app()->environment('production') || config('app.env') !== 'local'
            ? ['User-agent: *', 'Allow: /', 'Disallow: /admin', 'Disallow: /mcp', 'Disallow: /api/', 'Disallow: /install', 'Disallow: /captcha/', 'Disallow: /session/', 'Disallow: /*?q=']
            : ['User-agent: *', 'Disallow: /'];
        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap');

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function manifest()
    {
        return response()->json([
            'name' => setting('site.name'), 'short_name' => setting('site.name'), 'lang' => 'fa', 'dir' => 'rtl',
            'description' => setting('site.meta_description'), 'start_url' => '/', 'scope' => '/', 'display' => 'standalone',
            'background_color' => '#15162a', 'theme_color' => '#15162a',
            'icons' => [
                ['src' => asset('assets/brand/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => asset('assets/brand/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png'],
                ['src' => asset('assets/brand/icon.svg'), 'sizes' => 'any', 'type' => 'image/svg+xml', 'purpose' => 'any'],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** Plain-text summary for AI assistants and LLM-based search (llmstxt.org). */
    public function llms()
    {
        $out = ['# '.setting('site.name').' (Micro Studio)', '', '> '.setting('site.meta_description'), ''];
        $out[] = '## بازی‌ها و اپ‌ها';
        foreach (Game::visible()->get() as $g) {
            $out[] = '- ['.$g->title.']('.route('games.show', $g).'): '.$g->summary.($g->age_range ? ' — '.$g->age_range : '');
        }
        $out[] = '';
        $out[] = '## صفحه‌ها';
        foreach ([['خدمات', 'services'], ['درباره ما', 'about'], ['تماس با ما', 'contact'], ['وبلاگ', 'blog.index']] as [$name, $r]) {
            $out[] = "- [$name](".route($r).')';
        }
        $out[] = '';
        $out[] = '## تازه‌ترین مطالب';
        foreach (Post::published()->limit(10)->get() as $p) {
            $out[] = '- ['.$p->title.']('.route('blog.show', $p).'): '.$p->excerpt;
        }
        $out[] = '';
        $out[] = '## تماس';
        $out[] = '- ایمیل: '.setting('contact.email');
        $out[] = '- اینستاگرام: '.setting('contact.instagram_url');

        return response(implode("\n", $out)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
