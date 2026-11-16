<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AgentToken extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['token_hash'];

    protected $casts = ['abilities' => 'array', 'last_used_at' => 'datetime', 'revoked_at' => 'datetime'];

    /** Abilities a token never has unless they are explicitly granted. */
    public const RESTRICTED = ['posts.publish', 'rules.approve', 'messages.read'];

    public const ABILITIES = [
        'content.write' => 'ویرایش محتوا (بازی‌ها، خدمات، تیم، نظرات، سؤالات، کارت‌ها، تنظیمات، تقویم)',
        'posts.write' => 'نوشتن مطلب و ارسال برای تأیید',
        'posts.publish' => 'انتشار مستقیم مطلب (بدون تأیید شما)',
        'media.upload' => 'بارگذاری تصویر',
        'rules.propose' => 'پیشنهاد قانون محتوا',
        'rules.approve' => 'تأیید قانون محتوا',
        'delete' => 'حذف (نرم و قابل بازگشت) موارد',
        'messages.read' => 'خواندن پیام‌های فرم تماس',
    ];

    /** @return array{0: AgentToken, 1: string} the model and the plain token (shown only once) */
    public static function issue(string $name, ?array $abilities = null): array
    {
        $plain = 'ms_'.Str::random(48);
        $token = static::create(['name' => $name, 'token_hash' => hash('sha256', $plain), 'abilities' => $abilities]);

        return [$token, $plain];
    }

    public static function findByPlain(?string $plain): ?self
    {
        if (! $plain) {
            return null;
        }

        return static::where('token_hash', hash('sha256', $plain))->whereNull('revoked_at')->first();
    }

    public function allows(string $ability): bool
    {
        if ($this->abilities === null) {
            return ! in_array($ability, self::RESTRICTED, true);
        }

        return in_array($ability, $this->abilities, true);
    }
}
