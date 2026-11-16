<?php

namespace App\Support;

use App\Models\User;
use Database\Seeders\ContentSeeder;
use Illuminate\Support\Facades\Artisan;

/** First-time setup, shared by `php artisan ms:install` and the one-time web installer. */
class Installer
{
    public static function lockPath(): string
    {
        return storage_path('app/installed.lock');
    }

    public static function installed(): bool
    {
        return is_file(static::lockPath());
    }

    /** @return array<int, string> log lines */
    public static function run(string $name, string $email, string $password, bool $seed = true): array
    {
        $log = [];
        Artisan::call('migrate', ['--force' => true]);
        $log[] = trim(Artisan::output()) ?: 'migrations done';
        if ($seed && ! \App\Models\Game::exists()) {
            Artisan::call('db:seed', ['--class' => ContentSeeder::class, '--force' => true]);
            $log[] = 'content seeded from the template';
        }
        $user = User::updateOrCreate(['email' => $email], ['name' => $name, 'password' => $password]);
        $log[] = "admin ready: {$user->email}";
        foreach (['storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'public/uploads'] as $dir) {
            if (! is_dir(base_path($dir))) {
                @mkdir(base_path($dir), 0755, true);
            }
        }
        file_put_contents(static::lockPath(), now()->toIso8601String());

        return $log;
    }
}
