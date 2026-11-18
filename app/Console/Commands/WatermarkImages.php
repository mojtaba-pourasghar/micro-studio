<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Support\Watermark;
use Illuminate\Console\Command;

/**
 * Stamps the studio logo on bundled site images (and optionally on older uploads).
 * Each processed file's hash is kept in database/watermarked.json, so running it again never stamps a file twice.
 */
class WatermarkImages extends Command
{
    protected $signature = 'ms:watermark {paths?* : Globs under public/ (default: site scenes, game screenshots and video posters)} {--uploads : Also stamp images in the media library} {--dry-run}';

    protected $description = 'Put the Micro Studio logo on site images (safe to run more than once)';

    public const DEFAULT_GLOBS = [
        'assets/img/scenes/*.webp', 'assets/img/canva/*.webp', 'assets/img/works/*-shot.webp',
        'assets/games/*/shot-*.webp', 'assets/games/*/*-poster.webp', 'assets/games/*/mascot-*.webp',
    ];

    public function handle(): int
    {
        $manifestPath = database_path('watermarked.json');
        $done = is_file($manifestPath) ? (json_decode(file_get_contents($manifestPath), true) ?: []) : [];

        $files = [];
        foreach ($this->argument('paths') ?: self::DEFAULT_GLOBS as $glob) {
            foreach (glob(public_path($glob)) ?: [] as $f) {
                $files[] = $f;
            }
        }
        if ($this->option('uploads')) {
            Media::where('mime', 'like', 'image/%')->each(function (Media $m) use (&$files) {
                $files[] = public_path($m->path);
            });
        }

        $stamped = 0;
        foreach (array_unique($files) as $file) {
            $rel = ltrim(str_replace(public_path(), '', $file), '/');
            if (! is_file($file) || (($done[$rel] ?? null) === sha1_file($file))) {
                continue;
            }
            if ($this->option('dry-run')) {
                $this->line("would stamp: $rel");

                continue;
            }
            if (Watermark::file($file)) {
                $stamped++;
                $this->line("stamped: $rel");
            }
            $done[$rel] = sha1_file($file);
        }
        if (! $this->option('dry-run')) {
            ksort($done);
            file_put_contents($manifestPath, json_encode($done, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        }
        $this->info("$stamped image(s) stamped.");

        return self::SUCCESS;
    }
}
