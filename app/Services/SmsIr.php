<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * sms.ir (v1 REST API). Credentials come from .env — never from code:
 *   SMSIR_API_KEY, SMSIR_LINE_NUMBER, SMSIR_BASE_URL (https://api.sms.ir/v1), SMSIR_ADMIN_MOBILE
 */
class SmsIr
{
    public function enabled(): bool
    {
        return filled(config('services.smsir.api_key')) && filled(config('services.smsir.line_number'));
    }

    /** Free-text SMS from the dedicated line to one or more mobiles (09xxxxxxxxx). */
    public function send(array|string $mobiles, string $text): bool
    {
        if (! $this->enabled()) {
            Log::info('SMS skipped (sms.ir not configured): '.mb_substr($text, 0, 60));

            return false;
        }
        $mobiles = array_values(array_filter(array_map([static::class, 'normalize'], (array) $mobiles)));
        if (! $mobiles) {
            return false;
        }
        try {
            $res = Http::timeout(15)
                ->withHeaders(['x-api-key' => config('services.smsir.api_key'), 'Accept' => 'application/json'])
                ->post(rtrim(config('services.smsir.base_url'), '/').'/send/bulk', [
                    'lineNumber' => (int) config('services.smsir.line_number'),
                    'messageText' => $text,
                    'mobiles' => $mobiles,
                ]);
            if ($res->successful() && (int) $res->json('status') === 1) {
                return true;
            }
            Log::warning('sms.ir send failed', ['http' => $res->status(), 'body' => mb_substr($res->body(), 0, 300)]);
        } catch (\Throwable $e) {
            Log::warning('sms.ir error: '.$e->getMessage());
        }

        return false;
    }

    /** Sends to the site owner's mobile (admin setting, else SMSIR_ADMIN_MOBILE). */
    public function notifyAdmin(string $text): bool
    {
        return $this->send(setting('contact.notify_mobile') ?: config('services.smsir.admin_mobile'), $text);
    }

    public static function normalize(?string $mobile): ?string
    {
        $m = preg_replace('/\D/', '', strtr((string) $mobile, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']));
        if (str_starts_with($m, '98')) {
            $m = '0'.substr($m, 2);
        } elseif (str_starts_with($m, '9') && strlen($m) === 10) {
            $m = '0'.$m;
        }

        return preg_match('/^09\d{9}$/', $m) ? $m : null;
    }
}
