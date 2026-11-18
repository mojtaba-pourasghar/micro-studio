<?php

use Database\Seeders\GameContentSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * November 2026 content update:
 * - "Pilo" is now "Time Guardians" (نگهبانان زمان) with Arsam as the hero (new slug, old links redirect)
 * - no "ad-free" claims anywhere (some apps show ads)
 * - Cafe Bazaar links, new hero images, final clips only (refreshed from GameContentSeeder)
 */
return new class extends Migration
{
    /** Exact phrase replacements applied to every text value in the content tables. */
    protected array $phrases = [
        'راست‌چین از روز اول، با صداگذاری فارسی، بدون تبلیغ مزاحم و با احترام به حریم خصوصی کودکان.' => 'راست‌چین از روز اول، با صداگذاری فارسی و با احترام به حریم خصوصی کودکان.',
        'همه فارسی، راست‌چین، بدون تبلیغ مزاحم و ساخته‌شده برای خانواده‌ها.' => 'همه فارسی، راست‌چین و ساخته‌شده برای خانواده‌ها.',
        'راست‌چین، صداگذاری‌شده به فارسی و بدون تبلیغ مزاحم‌اند.' => 'راست‌چین و صداگذاری‌شده به فارسی‌اند.',
        'سازنده‌ی بازی‌های داستانی و اپ‌های آموزشی فارسی برای کودکان؛ بدون تبلیغ و با احترام به حریم خصوصی.' => 'سازنده‌ی بازی‌های داستانی و اپ‌های آموزشی فارسی برای کودکان، با احترام به حریم خصوصی.',
        'کار آفلاین، بدون تبلیغ و ردیاب' => 'کار آفلاین و بدون نیاز به اینترنت',
        'معماهای رازینو واقعاً فکر کردن را یاد می‌دهند؛ بدون تبلیغ و بدون اینترنت، خیالم راحت است.' => 'معماهای رازینو واقعاً فکر کردن را یاد می‌دهند؛ بدون اینترنت هم کار می‌کند و خیالم راحت است.',
        'بازی‌های ما ردیاب، تبلیغ و حساب کاربری ندارند و داده‌ها روی دستگاه می‌مانند. برای هر پرسشی درباره‌ی داده‌ها به ما بنویسید.' => 'حریم خصوصی کودکان برای ما مهم است و تا جای ممکن داده‌ها روی خود دستگاه می‌مانند. برای هر پرسشی درباره‌ی داده‌ها به ما بنویسید.',
        'بازی بدون تبلیغ و ردیاب: والدین به چه چیزی نگاه کنند؟' => 'بازی امن برای کودک: والدین به چه چیزی نگاه کنند؟',
        'لنز زمان: طراحی معماهای گذشته و حال در سفر پیلو' => 'لنز زمان: طراحی معماهای گذشته و حال در نگهبانان زمان',
        'سفر پیلو در رنگارستان' => 'نگهبانان زمان',
        'سفر پیلو' => 'نگهبانان زمان',
        '«پیلو»' => '«نگهبانان زمان»',
        'assets/img/scenes/pilo-world.webp' => 'assets/img/scenes/tg-gate.webp',
        'assets/img/scenes/pilo-1.webp' => 'assets/img/scenes/tg-forest.webp',
        'assets/img/scenes/pilo-3.webp' => 'assets/games/time-guardians/shot-03.webp',
        'assets/games/pilo/' => 'assets/games/time-guardians/',
    ];

    /** Whole feature items that are replaced (matched by title). */
    protected array $items = [
        'بدون تبلیغ' => ['title' => 'دروازه‌ی والدین', 'text' => 'تنظیمات و خریدها پشت دروازه‌ی والدین است تا کودک ناخواسته به آن‌ها نرسد.', 'icon' => 'users'],
        'بدون تبلیغ مزاحم' => ['title' => 'مناسب سن کودک', 'text' => 'محتوا، رنگ‌ها و اندازه‌ی دکمه‌ها برای همان رده‌ی سنی طراحی و با بچه‌ها تست می‌شود.', 'icon' => 'age'],
    ];

    protected array $tables = ['settings', 'blocks', 'faqs', 'games', 'posts', 'services', 'team_members', 'testimonials', 'content_rules', 'content_plans', 'post_categories'];

    public function up(): void
    {
        DB::table('games')->where('slug', 'pilo')->update(['slug' => 'time-guardians']);
        DB::table('posts')->where('slug', 'pilo-time-lens')->update(['slug' => 'time-lens-puzzles']);
        // only final clips stay on the site: the old template trailer is gone
        DB::table('games')->where('trailer_url', 'assets/video/trailer.mp4')->update(['trailer_url' => null]);
        // the CTA on the games page shows Arsam over the Time Guardians gate
        foreach (['games.cta_character' => ['assets/img/characters/kairo.webp', 'assets/img/characters/arsam.webp'],
            'games.cta_image' => ['assets/img/scenes/pilo-world.webp', 'assets/img/scenes/tg-gate-circle.webp']] as $key => [$old, $new]) {
            DB::table('settings')->where('key', $key)->where('value', json_encode($old))->update(['value' => json_encode($new)]);
        }

        // feature rows stored as columns (blocks table)
        foreach ($this->items as $title => $item) {
            DB::table('blocks')->where('title', $title)->where('icon', 'noads')->update($item);
        }

        foreach ($this->tables as $table) {
            if (! DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }
            foreach (DB::table($table)->get() as $row) {
                $changes = [];
                foreach ((array) $row as $col => $val) {
                    if (! is_string($val) || $col === 'id') {
                        continue;
                    }
                    $new = $this->fix($val);
                    if ($new !== $val) {
                        $changes[$col] = $new;
                    }
                }
                if ($changes) {
                    DB::table($table)->where('id', $row->id)->update($changes);
                }
            }
        }

        if (DB::table('games')->exists()) {
            (new GameContentSeeder)->run();
        }
        Cache::forget('settings.all');
    }

    /** Fix a column value: JSON is decoded, walked and re-encoded; plain text gets phrase replacements. */
    protected function fix(string $val): string
    {
        $first = ltrim($val)[0] ?? '';
        if ($first === '[' || $first === '{' || $first === '"') {
            $data = json_decode($val, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $walked = $this->walk($data);

                return $walked === $data ? $val : json_encode($walked);
            }
        }

        return strtr($val, $this->phrases);
    }

    protected function walk(mixed $v): mixed
    {
        if (is_string($v)) {
            return $v === 'پیلو' ? 'نگهبانان زمان' : strtr($v, $this->phrases);
        }
        if (! is_array($v)) {
            return $v;
        }
        if (isset($v['title']) && is_string($v['title']) && isset($this->items[$v['title']]) && (($v['icon'] ?? null) === 'noads')) {
            return array_merge($v, $this->items[$v['title']]);
        }
        $out = [];
        foreach ($v as $k => $item) {
            $out[$k] = $this->walk($item);
        }

        return $out;
    }

    public function down(): void {}
};
