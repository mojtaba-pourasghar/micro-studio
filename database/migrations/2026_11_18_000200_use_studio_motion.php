<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

/** Switch the trailer banner to the new "how we make games" motion, unless the admin chose something else. */
return new class extends Migration
{
    public function up(): void
    {
        $map = [
            'site.trailer_url' => [['assets/video/trailer.mp4', 'assets/video/studio-trailer.mp4'], 'assets/video/studio-motion.mp4'],
            'home.banner_image' => [['assets/img/scenes/puzzle-room.webp', 'assets/video/studio-trailer-poster.webp'], 'assets/video/studio-motion-poster.webp'],
        ];
        foreach ($map as $key => [$old, $new]) {
            if (in_array(Setting::where('key', $key)->value('value'), $old, true)) {
                Setting::put($key, $new);
            }
        }
        Cache::forget('settings.all');
    }

    public function down(): void {}
};
