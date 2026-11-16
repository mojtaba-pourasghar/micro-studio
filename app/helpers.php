<?php

use App\Models\Setting;

if (! function_exists('fa_digits')) {
    /** Convert Latin digits to Persian digits. */
    function fa_digits(string|int|float|null $value): string
    {
        return strtr((string) $value, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
    }
}

if (! function_exists('img')) {
    /** Public URL for an image column: template asset, media upload or absolute URL. */
    function img(?string $path, string $fallback = 'assets/img/scenes/hero-two-skies.webp'): string
    {
        $path = $path ?: $fallback;
        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        try {
            return Setting::get($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}

if (! function_exists('tag_color')) {
    /** Map a tag color name to the template's .t-* class. */
    function tag_color(?string $color): string
    {
        return in_array($color, ['red', 'green', 'blue', 'pink', 'orange', 'purple'], true) ? 't-'.$color : 't-purple';
    }
}

if (! function_exists('heading')) {
    /**
     * Render the heading mini-syntax safely:  [[outline]]  {{gradient}}  | = <br>
     * With $lines = true every "|" segment becomes a block <span> (Hero display titles).
     */
    function heading(?string $text, bool $lines = false): \Illuminate\Support\HtmlString
    {
        $html = e((string) $text);
        $html = preg_replace('/\[\[(.+?)\]\]/u', '<span class="outline">$1</span>', $html);
        $html = preg_replace('/\{\{(.+?)\}\}/u', '<span class="grad">$1</span>', $html);
        if ($lines) {
            $html = implode('', array_map(fn ($l) => '<span>'.trim($l).'</span>', explode('|', $html)));
        } else {
            $html = str_replace('|', '<br>', $html);
        }

        return new \Illuminate\Support\HtmlString($html);
    }
}

if (! function_exists('heading_plain')) {
    /** Heading text without markup (for <title>, alt, aria). */
    function heading_plain(?string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', str_replace(['[[', ']]', '{{', '}}', '|'], ['', '', '', '', ' '], (string) $text)));
    }
}
