<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! User::exists()) {
            $password = env('ADMIN_PASSWORD') ?: Str::password(16, symbols: false);
            User::create([
                'name' => 'مدیر سایت',
                'email' => env('ADMIN_EMAIL', 'admin@example.com'),
                'password' => $password,
            ]);
            $this->command?->warn('Admin: '.env('ADMIN_EMAIL', 'admin@example.com').' / '.$password);
        }

        $this->call(ContentSeeder::class);
    }
}
