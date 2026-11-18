<?php

use Database\Seeders\ArticleSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** The parents' guide is no longer about "ad-free" games: new slug (the old one redirects), then fill its full text. */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('posts')->where('slug', 'ad-free-games-parents')->update(['slug' => 'safe-games-parents-checklist']);
        // article covers: the new Canva art instead of zoomed phone screenshots
        DB::table('posts')->where('cover_image', 'assets/img/works/popo-shot.webp')->update(['cover_image' => 'assets/games/popo/story.webp']);
        DB::table('posts')->where('cover_image', 'assets/img/works/hoho-farsi-shot.webp')->update(['cover_image' => 'assets/games/hoho-farsi-3/story.webp']);
        if (DB::table('post_categories')->exists()) {
            (new ArticleSeeder)->run();
        }
    }

    public function down(): void {}
};
