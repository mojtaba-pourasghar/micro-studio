<?php

namespace App\Mcp;

use App\Models\Block;
use App\Models\ContactMessage;
use App\Models\ContentPlan;
use App\Models\Faq;
use App\Models\Game;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/**
 * Registry of every content type the agent can read and write.
 * Field descriptions are shown to the agent (describe_content_types), so they double as documentation.
 */
class ContentTypes
{
    public const IMAGE = 'public image path: "assets/img/…" (template asset) or "uploads/…" returned by upload_image';

    public const HEADING = 'heading syntax: [[outline text]]  {{gradient text}}  | = line break';

    /** @return array<string, array{model: class-string<Model>, label: string, ability: string|null, write: bool, title: string, search: array<int,string>, fields: array<string,string>}> */
    public static function all(): array
    {
        $color = 'one of red|green|blue|pink|orange|purple';
        $icon = 'icon id: gem, text, noads, offline, shield, bulb, test, heart, pad, code, brush, mic, store, users, bug, book, branch, puzzle, refresh, age, chip, check, star, mail, phone, clock, pin';

        return [
            'games' => [
                'model' => Game::class, 'label' => 'Games & apps (/games, /games/{slug})', 'ability' => 'content.write', 'write' => true, 'title' => 'title', 'search' => ['title', 'short_title', 'slug', 'summary'],
                'fields' => [
                    'slug' => 'URL slug, latin a-z0-9- (required, unique)', 'title' => 'full title; "Name: subtitle" is split into two hero lines (required)',
                    'short_title' => 'short name for card badge (required)', 'category' => 'game | edu', 'status' => 'released | in_development | designing',
                    'summary' => 'card text, 1 sentence (required)', 'tagline' => 'hero lead on the game page', 'description' => 'story section HTML (<p>…</p>)',
                    'story_title' => 'story heading; '.self::HEADING, 'story_points' => 'array of short strings (check-list)',
                    'tags' => "array of {label, color} — color $color", 'age_range' => 'e.g. "۸ تا ۱۲ سال"', 'platforms' => 'array: android | web',
                    'engine' => 'e.g. "Godot 4.3"', 'language' => 'e.g. "فارسی (راست‌چین)"', 'genre' => 'genre text', 'internet' => 'internet requirement text',
                    'card_image' => self::IMAGE.' (required)', 'card_is_phone' => 'bool: card image is a tall phone screenshot',
                    'hero_image' => self::IMAGE, 'hero_character' => self::IMAGE.' (transparent PNG/WebP character)', 'story_image' => self::IMAGE, 'banner_image' => self::IMAGE,
                    'trailer_url' => 'mp4 path or https URL', 'logo' => self::IMAGE.' (square app icon)', 'videos' => 'array of {src, poster, title, duration(seconds), vertical(bool)}; upload mp4/posters with upload_image', 'gameplay_title' => 'gameplay heading; '.self::HEADING, 'gameplay' => 'array of {title, text}: how to play, step by step', 'benefits' => 'array of strings: what it is good for / skills it builds', 'store_links' => 'array of {store, url} (empty url = "coming soon")',
                    'stats' => 'array (max 4) of {value, label}; value in latin digits', 'features' => "array of {icon, title, text}; $icon",
                    'screenshots' => 'array of {image, caption}', 'characters' => 'array of {name, role, image, dark(bool)}', 'download_note' => 'text of the download section',
                    'seo_title' => 'max 70 chars', 'seo_description' => 'max 170 chars', 'is_featured' => 'bool (spotlight on /games)', 'is_published' => 'bool', 'sort_order' => 'int',
                ],
            ],
            'services' => [
                'model' => Service::class, 'label' => 'Services (/services and home slider)', 'ability' => 'content.write', 'write' => true, 'title' => 'title', 'search' => ['title', 'summary'],
                'fields' => [
                    'slug' => 'anchor slug, e.g. game → /services#svc-game', 'title' => 'required', 'summary' => 'short text for the home slider (required)',
                    'description' => 'long text for /services', 'deliverables' => 'array of strings', 'image' => self::IMAGE, 'image_is_phone' => 'bool',
                    'character_image' => self::IMAGE, 'cta_label' => 'button text', 'is_published' => 'bool', 'sort_order' => 'int',
                ],
            ],
            'team_members' => [
                'model' => TeamMember::class, 'label' => 'Team (/about#team)', 'ability' => 'content.write', 'write' => true, 'title' => 'name', 'search' => ['name', 'role'],
                'fields' => ['name' => 'required', 'role' => 'short description', 'tag' => 'badge text', 'tag_color' => $color, 'bio' => 'text', 'avatar' => self::IMAGE, 'is_published' => 'bool', 'sort_order' => 'int'],
            ],
            'testimonials' => [
                'model' => Testimonial::class, 'label' => 'Family testimonials (home, /about)', 'ability' => 'content.write', 'write' => true, 'title' => 'name', 'search' => ['name', 'quote'],
                'fields' => ['name' => 'required', 'role' => 'e.g. "مادر دانش‌آموز سوم"', 'quote' => 'required, without «» quotes', 'rating' => 'int 1-5', 'is_published' => 'bool', 'sort_order' => 'int'],
            ],
            'faqs' => [
                'model' => Faq::class, 'label' => 'FAQ (/services#faq, /contact#faq)', 'ability' => 'content.write', 'write' => true, 'title' => 'question', 'search' => ['question', 'answer'],
                'fields' => ['page' => 'services | contact', 'question' => 'required', 'answer' => 'required', 'is_published' => 'bool', 'sort_order' => 'int'],
            ],
            'blocks' => [
                'model' => Block::class, 'label' => 'Small cards: honors (stats), promises, values, steps, tools, support, contact_steps', 'ability' => 'content.write', 'write' => true, 'title' => 'title', 'search' => ['title', 'text'],
                'fields' => [
                    'group' => 'honors | promises | values | steps | tools | support | contact_steps (required)', 'title' => 'required', 'text' => 'text',
                    'value' => 'honors: number (latin digits); steps: output text', 'label' => 'honors: side label', 'icon' => $icon,
                    'link_label' => 'support: link text', 'link_url' => 'support: link URL', 'is_published' => 'bool', 'sort_order' => 'int',
                ],
            ],
            'post_categories' => [
                'model' => PostCategory::class, 'label' => 'Blog categories', 'ability' => 'content.write', 'write' => true, 'title' => 'name', 'search' => ['name', 'slug'],
                'fields' => ['name' => 'required', 'slug' => 'latin slug (required)', 'sort_order' => 'int'],
            ],
            'posts' => [
                'model' => Post::class, 'label' => 'Blog posts (/blog/{slug}) — the owner approves publication', 'ability' => 'posts.write', 'write' => true, 'title' => 'title', 'search' => ['title', 'excerpt', 'slug'],
                'fields' => [
                    'slug' => 'latin slug (required, unique)', 'title' => 'required', 'excerpt' => '1-2 sentence summary (required)',
                    'body' => 'HTML. Allowed: p, h2 (with id), h3, strong, em, a, ul, ol, li, blockquote, figure.prose-figure>img+figcaption, table. Classes: p.prose-lead (first paragraph), ul.prose-stats>li>b+span (number cards). Scripts/styles are stripped.',
                    'cover_image' => self::IMAGE, 'post_category_id' => 'id from post_categories', 'game_id' => 'id of related game',
                    'tags' => 'array of strings (use _ instead of spaces)', 'author' => 'default "تیم میکرو استودیو"',
                    'status' => 'draft | pending_review (agent) — published only with the posts.publish ability', 'published_at' => 'ISO datetime for scheduled publishing (optional)',
                    'is_featured' => 'bool', 'seo_title' => 'max 70 chars', 'seo_description' => 'max 170 chars',
                    'review_note' => 'read-only for the agent: the owner\'s feedback when a post is returned (status rejected)',
                ],
            ],
            'content_plans' => [
                'model' => ContentPlan::class, 'label' => 'Content calendar (what to produce and when)', 'ability' => 'content.write', 'write' => true, 'title' => 'title', 'search' => ['title', 'brief'],
                'fields' => [
                    'title' => 'required', 'brief' => 'instructions from the owner', 'type' => 'post | game_update | image | other', 'due_date' => 'YYYY-MM-DD',
                    'game_id' => 'related game id', 'post_category_id' => 'blog category id', 'status' => 'planned | in_progress | done | skipped',
                    'post_id' => 'id of the post created for this item', 'result_note' => 'short report of what the agent did',
                ],
            ],
            'media' => [
                'model' => Media::class, 'label' => 'Media library (create with upload_image)', 'ability' => 'media.upload', 'write' => true, 'title' => 'path', 'search' => ['path', 'alt', 'original_name'],
                'fields' => ['alt' => 'Persian alternative text', 'path' => 'read-only', 'source' => 'read-only: admin | agent | canva'],
            ],
            'contact_messages' => [
                'model' => ContactMessage::class, 'label' => 'Contact form messages (read-only)', 'ability' => 'messages.read', 'write' => false, 'title' => 'name', 'search' => ['name', 'email', 'message'],
                'fields' => ['name' => '', 'email' => '', 'phone' => '', 'subject' => '', 'message' => '', 'read_at' => ''],
            ],
        ];
    }

    public static function get(string $type): ?array
    {
        return static::all()[$type] ?? null;
    }

    /** Validation rules for create/update (partial updates validate only the given keys). */
    public static function rules(string $type, ?Model $record): array
    {
        $id = $record?->getKey();
        $img = ['nullable', 'string', 'max:255'];
        $bool = ['boolean'];
        $int = ['integer', 'min:0', 'max:100000'];
        $create = $record === null;
        $req = fn (array $r) => $create ? ['required', ...$r] : ['sometimes', 'required', ...$r];

        return match ($type) {
            'games' => [
                'slug' => $req(['alpha_dash', 'max:80', Rule::unique('games', 'slug')->ignore($id)]), 'title' => $req(['string', 'max:190']),
                'short_title' => $req(['string', 'max:60']), 'summary' => $req(['string', 'max:400']), 'card_image' => $req(['string', 'max:255']),
                'category' => [Rule::in(array_keys(Game::CATEGORIES))], 'status' => [Rule::in(array_keys(Game::STATUSES))],
                'tagline' => ['nullable', 'string', 'max:500'], 'description' => ['nullable', 'string', 'max:20000'], 'story_title' => ['nullable', 'string', 'max:190'],
                'story_points' => ['nullable', 'array'], 'story_points.*' => ['string', 'max:200'],
                'tags' => ['nullable', 'array'], 'tags.*.label' => ['required', 'string', 'max:30'], 'tags.*.color' => ['nullable', 'string'],
                'age_range' => ['nullable', 'string', 'max:40'], 'platforms' => ['nullable', 'array'], 'platforms.*' => [Rule::in(['android', 'web'])],
                'engine' => ['nullable', 'string', 'max:60'], 'language' => ['nullable', 'string', 'max:60'], 'genre' => ['nullable', 'string', 'max:90'], 'internet' => ['nullable', 'string', 'max:90'],
                'card_is_phone' => $bool, 'hero_image' => $img, 'hero_character' => $img, 'story_image' => $img, 'banner_image' => $img,
                'trailer_url' => ['nullable', 'string', 'max:255'], 'logo' => $img, 'gameplay_title' => ['nullable', 'string', 'max:190'],
                'videos' => ['nullable', 'array'], 'videos.*.src' => ['required', 'string', 'max:255'], 'videos.*.poster' => ['nullable', 'string', 'max:255'], 'videos.*.title' => ['nullable', 'string', 'max:120'], 'videos.*.duration' => ['nullable', 'integer'], 'videos.*.vertical' => ['nullable', 'boolean'],
                'gameplay' => ['nullable', 'array'], 'gameplay.*.title' => ['required', 'string', 'max:80'], 'gameplay.*.text' => ['nullable', 'string', 'max:400'],
                'benefits' => ['nullable', 'array'], 'benefits.*' => ['string', 'max:200'],
                'store_links' => ['nullable', 'array'], 'store_links.*.store' => ['required', 'string', 'max:40'], 'store_links.*.url' => ['nullable', 'string', 'max:255'],
                'stats' => ['nullable', 'array', 'max:4'], 'stats.*.value' => ['required', 'string', 'max:12'], 'stats.*.label' => ['required', 'string', 'max:40'],
                'features' => ['nullable', 'array'], 'features.*.title' => ['required', 'string', 'max:60'], 'features.*.text' => ['nullable', 'string', 'max:240'], 'features.*.icon' => ['nullable', 'string', 'max:20'],
                'screenshots' => ['nullable', 'array'], 'screenshots.*.image' => ['required', 'string', 'max:255'], 'screenshots.*.caption' => ['nullable', 'string', 'max:80'],
                'characters' => ['nullable', 'array'], 'characters.*.name' => ['required', 'string', 'max:40'], 'characters.*.role' => ['nullable', 'string', 'max:90'],
                'characters.*.image' => ['nullable', 'string', 'max:255'], 'characters.*.dark' => ['nullable', 'boolean'],
                'download_note' => ['nullable', 'string', 'max:500'], 'seo_title' => ['nullable', 'string', 'max:70'], 'seo_description' => ['nullable', 'string', 'max:170'],
                'is_featured' => $bool, 'is_published' => $bool, 'sort_order' => $int,
            ],
            'services' => [
                'slug' => $req(['alpha_dash', 'max:40', Rule::unique('services', 'slug')->ignore($id)]), 'title' => $req(['string', 'max:90']), 'summary' => $req(['string', 'max:300']),
                'description' => ['nullable', 'string', 'max:800'], 'deliverables' => ['nullable', 'array'], 'deliverables.*' => ['string', 'max:150'],
                'image' => $img, 'image_is_phone' => $bool, 'character_image' => $img, 'cta_label' => ['nullable', 'string', 'max:60'], 'is_published' => $bool, 'sort_order' => $int,
            ],
            'team_members' => [
                'name' => $req(['string', 'max:90']), 'role' => ['nullable', 'string', 'max:120'], 'tag' => ['nullable', 'string', 'max:30'],
                'tag_color' => ['nullable', Rule::in(['red', 'green', 'blue', 'pink', 'orange', 'purple'])], 'bio' => ['nullable', 'string', 'max:1000'],
                'avatar' => $img, 'is_published' => $bool, 'sort_order' => $int,
            ],
            'testimonials' => [
                'name' => $req(['string', 'max:90']), 'quote' => $req(['string', 'max:400']), 'role' => ['nullable', 'string', 'max:90'],
                'rating' => ['integer', 'between:1,5'], 'is_published' => $bool, 'sort_order' => $int,
            ],
            'faqs' => ['page' => $req([Rule::in(['services', 'contact'])]), 'question' => $req(['string', 'max:190']), 'answer' => $req(['string', 'max:2000']), 'is_published' => $bool, 'sort_order' => $int],
            'blocks' => [
                'group' => $req([Rule::in(['honors', 'promises', 'values', 'steps', 'tools', 'support', 'contact_steps'])]), 'title' => $req(['string', 'max:190']),
                'text' => ['nullable', 'string', 'max:600'], 'value' => ['nullable', 'string', 'max:60'], 'label' => ['nullable', 'string', 'max:60'], 'icon' => ['nullable', 'string', 'max:20'],
                'link_label' => ['nullable', 'string', 'max:60'], 'link_url' => ['nullable', 'string', 'max:255'], 'is_published' => $bool, 'sort_order' => $int,
            ],
            'post_categories' => ['name' => $req(['string', 'max:60']), 'slug' => $req(['alpha_dash', 'max:40', Rule::unique('post_categories', 'slug')->ignore($id)]), 'sort_order' => $int],
            'posts' => [
                'slug' => $req(['string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('posts', 'slug')->ignore($id)]),
                'title' => $req(['string', 'max:190']), 'excerpt' => $req(['string', 'max:400']), 'body' => ['nullable', 'string', 'max:200000'],
                'cover_image' => $img, 'post_category_id' => ['nullable', 'integer', 'exists:post_categories,id'], 'game_id' => ['nullable', 'integer', 'exists:games,id'],
                'tags' => ['nullable', 'array'], 'tags.*' => ['string', 'max:40'], 'author' => ['nullable', 'string', 'max:90'],
                'status' => [Rule::in(array_keys(Post::STATUSES))], 'published_at' => ['nullable', 'date'], 'is_featured' => $bool,
                'seo_title' => ['nullable', 'string', 'max:70'], 'seo_description' => ['nullable', 'string', 'max:170'],
            ],
            'content_plans' => [
                'title' => $req(['string', 'max:190']), 'brief' => ['nullable', 'string', 'max:5000'], 'type' => [Rule::in(array_keys(ContentPlan::TYPES))],
                'due_date' => ['nullable', 'date'], 'game_id' => ['nullable', 'integer', 'exists:games,id'], 'post_category_id' => ['nullable', 'integer', 'exists:post_categories,id'],
                'status' => [Rule::in(array_keys(ContentPlan::STATUSES))], 'post_id' => ['nullable', 'integer', 'exists:posts,id'], 'result_note' => ['nullable', 'string', 'max:2000'],
            ],
            'media' => ['alt' => ['nullable', 'string', 'max:190']],
            default => [],
        };
    }

    /** Compact array for list results. */
    public static function summary(string $type, Model $m): array
    {
        $def = static::get($type);
        $row = ['id' => $m->getKey(), 'title' => $m->{$def['title']}];
        foreach (['slug', 'status', 'group', 'page', 'category', 'is_published', 'due_date', 'type', 'path'] as $k) {
            if (array_key_exists($k, $m->getAttributes())) {
                $row[$k] = $m->{$k} instanceof \DateTimeInterface ? $m->{$k}->format('Y-m-d') : $m->{$k};
            }
        }
        if (method_exists($m, 'trashed') && $m->trashed()) {
            $row['deleted'] = true;
        }
        if ($url = static::publicUrl($type, $m)) {
            $row['url'] = $url;
        }

        return $row;
    }

    public static function publicUrl(string $type, Model $m): ?string
    {
        return match ($type) {
            'games' => route('games.show', $m->slug),
            'posts' => route('blog.show', $m->slug),
            'services' => route('services').'#svc-'.$m->slug,
            'media' => asset($m->path),
            default => null,
        };
    }
}
