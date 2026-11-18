<?php

use Database\Seeders\GameContentSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** New Canva art goes in the "story" section of five apps; their headers go back to the app icon. */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('games')->exists()) {
            (new GameContentSeeder)->run();
        }
    }

    public function down(): void {}
};
