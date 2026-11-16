<?php

namespace Tests\Unit;

use App\Support\Jalali;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function test_persian_digits(): void
    {
        $this->assertSame('۱۴۰۵/۰۸/۱۹', fa_digits('1405/08/19'));
    }

    public function test_jalali_both_directions(): void
    {
        $this->assertSame([1405, 1, 1], Jalali::fromGregorian(2026, 3, 21));
        $this->assertSame('2026-03-21', Jalali::toGregorian(1405, 1, 1));
    }

    public function test_heading_plain_strips_syntax(): void
    {
        $this->assertSame('همین امروز پروژه‌ات را شروع کن!', heading_plain('همین امروز|{{پروژه‌ات}} را شروع کن!'));
    }
}
