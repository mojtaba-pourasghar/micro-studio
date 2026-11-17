<?php

namespace App\Http\Controllers;

use App\Support\Captcha;

class CaptchaController extends Controller
{
    public function __invoke(string $key)
    {
        abort_unless(in_array($key, ['login', 'contact'], true), 404);

        return response(Captcha::image($key), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
