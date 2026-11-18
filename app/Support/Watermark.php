<?php

namespace App\Support;

/**
 * Stamps the Micro Studio logo on an image (GD, no external services).
 * Used automatically for admin/agent uploads and by `php artisan ms:watermark` for bundled images.
 */
class Watermark
{
    public const MIN_SIDE = 240;

    public static function enabled(): bool
    {
        return filter_var(setting('site.watermark', true), FILTER_VALIDATE_BOOLEAN);
    }

    /** Watermark image bytes; returns the original bytes when the format is unsupported or the image is too small. */
    public static function bytes(string $bytes, ?string $mime = null, ?string $corner = null): string
    {
        $mime ??= (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes) ?: '';
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true) || ! function_exists('imagecreatefromstring')) {
            return $bytes;
        }
        $img = @imagecreatefromstring($bytes);
        if (! $img) {
            return $bytes;
        }
        if (! static::stamp($img, $corner)) {
            imagedestroy($img);

            return $bytes;
        }
        ob_start();
        match ($mime) {
            'image/jpeg' => imagejpeg($img, null, 88),
            'image/png' => imagepng($img, null, 7),
            'image/webp' => imagewebp($img, null, 84),
        };
        imagedestroy($img);

        return ob_get_clean() ?: $bytes;
    }

    /** Watermark a file in place. */
    /** @param string|null $corner force a corner ("bottom-left", "bottom-right") instead of picking the calmest spot */
    public static function file(string $path, ?string $corner = null): bool
    {
        if (! is_file($path)) {
            return false;
        }
        $orig = file_get_contents($path);
        $out = static::bytes($orig, null, $corner);
        if ($out === $orig) {
            return false;
        }
        file_put_contents($path, $out);

        return true;
    }

    /** Draw the logo onto a GD image (~15% of the width, in whichever corner has the least detail). */
    public static function stamp(\GdImage $img, ?string $corner = null): bool
    {
        $w = imagesx($img);
        $h = imagesy($img);
        if (min($w, $h) < self::MIN_SIDE) {
            return false;
        }
        $info = @getimagesize(static::logoPath());
        if (! $info) {
            return false;
        }
        imagealphablending($img, true);
        imagesavealpha($img, true);
        [$lw, $lh] = $info;
        // Wide images get a smaller mark; tall (phone) screenshots a bigger one so it stays readable.
        $tw = (int) round(max(110, min(360, $w * ($w > $h ? 0.15 : 0.32))));
        $th = (int) round($lh * $tw / $lw);
        $margin = (int) round(max(10, min($w, $h) * 0.025));
        [$x, $y, $bright] = static::calmestSpot($img, $tw, $th, $margin);
        if ($corner) { // fixed corner (e.g. wide hero backgrounds, where a centred mark would sit behind the page text)
            $x = str_ends_with($corner, 'right') ? $w - $tw - $margin : $margin;
            $y = str_starts_with($corner, 'top') ? $margin : $h - $th - $margin;
            $bright = static::meanLum($img, $x, $y, $tw, $th);
        }
        // White-text logo on dark/mid areas, dark-text logo on bright areas.
        $logo = @imagecreatefrompng(static::logoPath($bright > 175));
        if (! $logo) {
            return false;
        }
        $scaled = imagecreatetruecolor($tw, $th);
        imagealphablending($scaled, false);
        imagesavealpha($scaled, true);
        imagefill($scaled, 0, 0, imagecolorallocatealpha($scaled, 0, 0, 0, 127));
        imagecopyresampled($scaled, $logo, 0, 0, 0, 0, $tw, $th, $lw, $lh);
        static::fade($scaled, 0.85);
        imagecopy($img, $scaled, $x, $y, 0, 0, $tw, $th);
        imagedestroy($logo);
        imagedestroy($scaled);

        return true;
    }

    /**
     * Pick the corner (or top/bottom centre) whose pixels vary least, so the mark doesn't cover buttons or faces.
     * Ties prefer the corners, bottom-left first.
     */
    protected static function calmestSpot(\GdImage $img, int $tw, int $th, int $m): array
    {
        $w = imagesx($img);
        $h = imagesy($img);
        // Corners first (preferred), then edge midpoints and the inner rows.
        $xs = [$m, $w - $tw - $m, intdiv($w - $tw, 2)];
        $ys = [$h - $th - $m, $m, intdiv($h - $th, 2), intdiv($h - $th, 4), intdiv(3 * ($h - $th), 4)];
        $spots = [];
        foreach ($ys as $yi => $y) {
            foreach ($xs as $xi => $x) {
                $spots[] = [$x, $y, ($yi < 2 && $xi < 2) ? 0 : (($yi < 2 || $xi < 2) ? 1.5 : 3)];
            }
        }
        $best = [$spots[0][0], $spots[0][1], 0];
        $bestScore = INF;
        $lum = static function (int $x, int $y) use ($img): float {
            $c = imagecolorat($img, $x, $y);

            return 0.299 * (($c >> 16) & 255) + 0.587 * (($c >> 8) & 255) + 0.114 * ($c & 255);
        };
        $step = max(2, intdiv($tw, 70));
        foreach ($spots as $i => [$x, $y, $penalty]) {
            // Mean edge strength (neighbour differences): text, buttons and faces score high, flat areas low.
            $edges = $n = $sum = 0;
            for ($px = $x; $px < $x + $tw - $step && $px < $w - $step; $px += $step) {
                for ($py = $y; $py < $y + $th - $step && $py < $h - $step; $py += $step) {
                    $l = $lum($px, $py);
                    $sum += $l;
                    $edges += abs($lum($px + $step, $py) - $l) + abs($lum($px, $py + $step) - $l);
                    $n++;
                }
            }
            $score = ($n ? $edges / $n : INF) + $penalty;
            if ($score < $bestScore) {
                $bestScore = $score;
                $best = [$x, $y, $n ? $sum / $n : 0];
            }
        }

        return $best;
    }

    protected static function meanLum(\GdImage $img, int $x, int $y, int $tw, int $th): float
    {
        $sum = $n = 0;
        for ($px = $x; $px < $x + $tw; $px += 6) {
            for ($py = $y; $py < $y + $th; $py += 6) {
                $c = imagecolorat($img, min(imagesx($img) - 1, $px), min(imagesy($img) - 1, $py));
                $sum += 0.299 * (($c >> 16) & 255) + 0.587 * (($c >> 8) & 255) + 0.114 * ($c & 255);
                $n++;
            }
        }

        return $n ? $sum / $n : 0;
    }

    public static function logoPath(bool $forBrightBackground = false): string
    {
        return public_path($forBrightBackground ? 'assets/brand/watermark-dark.png' : 'assets/brand/watermark.png');
    }

    /** Multiply the alpha channel of a true-colour image by $opacity. */
    protected static function fade(\GdImage $im, float $opacity): void
    {
        $w = imagesx($im);
        $h = imagesy($im);
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $c = imagecolorat($im, $x, $y);
                $a = ($c >> 24) & 0x7F;
                if ($a === 127) {
                    continue;
                }
                $na = (int) round(127 - (127 - $a) * $opacity);
                imagesetpixel($im, $x, $y, ($na << 24) | ($c & 0xFFFFFF));
            }
        }
    }
}
