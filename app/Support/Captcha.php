<?php

namespace App\Support;

/**
 * Self-hosted image captcha (no Google/third-party service — those are slow or blocked in Iran).
 * The answer lives in the session per "key" (e.g. "login", "contact") and is valid for one check only.
 */
class Captcha
{
    public const LENGTH = 5;

    public const TTL = 600; // seconds

    public static function generate(string $key): string
    {
        $code = '';
        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= (string) random_int(1, 9);
        }
        session()->put("captcha.$key", ['code' => $code, 'at' => time()]);

        return $code;
    }

    /** Compares (Persian/Arabic digits accepted) and always invalidates the stored answer. */
    public static function check(string $key, ?string $input): bool
    {
        $stored = session()->pull("captcha.$key");
        if (! $stored || time() - ($stored['at'] ?? 0) > self::TTL) {
            return false;
        }
        $input = strtr(trim((string) $input), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        return $input !== '' && hash_equals($stored['code'], preg_replace('/\D/', '', $input));
    }

    /** PNG bytes for a fresh code. */
    public static function image(string $key): string
    {
        $code = static::generate($key);
        $w = 170;
        $h = 56;
        $im = imagecreatetruecolor($w, $h);
        imagefilledrectangle($im, 0, 0, $w, $h, imagecolorallocate($im, 242, 242, 245));
        // noise: arcs and dots in brand tints
        for ($i = 0; $i < 6; $i++) {
            $c = imagecolorallocate($im, random_int(150, 210), random_int(140, 200), 255);
            imagesetthickness($im, random_int(1, 2));
            imagearc($im, random_int(0, $w), random_int(0, $h), random_int(60, 200), random_int(30, 90), 0, 360, $c);
        }
        for ($i = 0; $i < 260; $i++) {
            imagesetpixel($im, random_int(0, $w - 1), random_int(0, $h - 1), imagecolorallocate($im, random_int(120, 200), random_int(120, 200), random_int(160, 230)));
        }
        $font = resource_path('fonts/captcha.ttf');
        $x = 14;
        foreach (str_split($code) as $ch) {
            $color = imagecolorallocate($im, random_int(40, 100), random_int(20, 60), random_int(150, 230));
            imagettftext($im, random_int(22, 27), random_int(-22, 22), $x, random_int(36, 44), $color, $font, $ch);
            $x += random_int(26, 31);
        }
        imagesetthickness($im, 2);
        imageline($im, 0, random_int(18, 38), $w, random_int(18, 38), imagecolorallocate($im, 116, 87, 255));
        ob_start();
        imagepng($im);
        imagedestroy($im);

        return (string) ob_get_clean();
    }
}
