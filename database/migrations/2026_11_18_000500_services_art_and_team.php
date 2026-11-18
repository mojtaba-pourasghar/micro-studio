<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Services page: new Canva art for the educational-app and voice-over services.
 * The "people behind the games" team section is hidden until real photos are added (admin → team members → publish).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('services')->where('slug', 'edu')->where('image', 'assets/img/works/hoho-farsi-shot.webp')
            ->update(['image' => 'assets/img/services/edu-app.webp', 'image_is_phone' => false]);
        DB::table('services')->where('slug', 'voice')->where('image', 'assets/img/scenes/puzzle-room.webp')
            ->update(['image' => 'assets/img/services/voice.webp']);
        DB::table('team_members')->update(['is_published' => false]);
    }

    public function down(): void {}
};
