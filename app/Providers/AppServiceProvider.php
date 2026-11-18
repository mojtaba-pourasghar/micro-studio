<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->ensureAppKey();
    }

    public function boot(): void
    {
        //
    }

    /**
     * Hosts without SSH (cPanel) cannot run `php artisan key:generate`, and without APP_KEY not even the
     * web installer can open. On the very first request we create a random key and save it in .env.
     */
    protected function ensureAppKey(): void
    {
        if (config('app.key') || $this->app->runningUnitTests()) {
            return;
        }
        $env = base_path('.env');
        if (! is_file($env) || ! is_writable($env)) {
            return;
        }
        $key = 'base64:'.base64_encode(random_bytes(32));
        $contents = (string) file_get_contents($env);
        $contents = preg_match('/^APP_KEY=.*$/m', $contents)
            ? preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$key, $contents)
            : rtrim($contents)."\nAPP_KEY=".$key."\n";
        file_put_contents($env, $contents);
        config(['app.key' => $key]);
    }
}
