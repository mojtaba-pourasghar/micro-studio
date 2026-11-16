<?php

namespace App\Support;

use DateTimeInterface;

/** Gregorian -> Solar Hijri (Jalali) conversion without dependencies. */
class Jalali
{
    public const MONTHS = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];

    /** @return array{0:int,1:int,2:int} */
    public static function fromGregorian(int $gy, int $gm, int $gd): array
    {
        $gdm = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $gy2 = $gm > 2 ? $gy + 1 : $gy;
        $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100) + intdiv($gy2 + 399, 400) + $gd + $gdm[$gm - 1];
        $jy = -1595 + (33 * intdiv($days, 12053));
        $days %= 12053;
        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;
        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }
        $jm = $days < 186 ? 1 + intdiv($days, 31) : 7 + intdiv($days - 186, 30);
        $jd = 1 + ($days < 186 ? $days % 31 : ($days - 186) % 30);

        return [$jy, $jm, $jd];
    }

    public static function format(?DateTimeInterface $date, string $style = 'long'): string
    {
        if (! $date) {
            return '';
        }
        [$y, $m, $d] = static::fromGregorian((int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j'));
        $s = $style === 'short' ? sprintf('%04d/%02d/%02d', $y, $m, $d) : $d.' '.self::MONTHS[$m - 1].' '.$y;

        return fa_digits($s);
    }

    public static function year(?DateTimeInterface $date = null): int
    {
        $date ??= now();

        return static::fromGregorian((int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j'))[0];
    }
}
