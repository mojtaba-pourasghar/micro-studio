<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('card_image');   // square app icon / game logo
            $table->json('videos')->nullable()->after('trailer_url');  // [{src, poster, title, duration, w, h}]
            $table->json('gameplay')->nullable()->after('features');   // [{title, text}] how to play, step by step
            $table->json('benefits')->nullable()->after('gameplay');   // ["…"] what it is good for / skills it builds
            $table->string('gameplay_title')->nullable()->after('story_title');
        });
    }

    public function down(): void
    {
        Schema::table('games', fn (Blueprint $t) => $t->dropColumn(['logo', 'videos', 'gameplay', 'benefits', 'gameplay_title']));
    }
};
