<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/** Point the home "who we are" image at the new Canva world map if it still uses the old default. */
return new class extends Migration
{
    public function up(): void
    {
        if (Setting::where('key', 'home.about_image')->value('value') === 'assets/img/scenes/razino-map.webp') {
            Setting::put('home.about_image', 'assets/img/canva/world-map.webp');
        }
        if (Setting::where('key', 'site.trailer_url')->value('value') === 'assets/video/trailer.mp4') {
            Setting::put('site.trailer_url', 'assets/video/studio-trailer.mp4');
        }
        if (Setting::where('key', 'home.banner_image')->value('value') === 'assets/img/scenes/puzzle-room.webp') {
            Setting::put('home.banner_image', 'assets/video/studio-trailer-poster.webp');
        }
        Setting::where('key', 'home.testi_image')->delete();
        \Illuminate\Support\Facades\Cache::forget('settings.all');
    }

    public function down(): void {}
};
