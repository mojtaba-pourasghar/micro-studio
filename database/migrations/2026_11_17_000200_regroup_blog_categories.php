<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Blog categories requested by the owner: our own work, studio news, games of the day (+ guides for parents).
 * Existing posts are moved: devlog/art/edu → our-work, parents → guides.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $ids = [];
        foreach ([['our-work', 'کارهای ما', 1], ['news', 'اخبار استودیو', 2], ['trending', 'بازی‌های روز', 3], ['guides', 'راهنمای والدین', 4]] as [$slug, $name, $sort]) {
            $id = DB::table('post_categories')->where('slug', $slug)->value('id');
            if (! $id) {
                $id = DB::table('post_categories')->insertGetId(['slug' => $slug, 'name' => $name, 'sort_order' => $sort, 'created_at' => $now, 'updated_at' => $now]);
            }
            $ids[$slug] = $id;
        }
        $move = ['devlog' => 'our-work', 'art' => 'our-work', 'edu' => 'our-work', 'parents' => 'guides'];
        foreach ($move as $old => $new) {
            $oldId = DB::table('post_categories')->where('slug', $old)->value('id');
            if ($oldId) {
                DB::table('posts')->where('post_category_id', $oldId)->update(['post_category_id' => $ids[$new]]);
                DB::table('content_plans')->where('post_category_id', $oldId)->update(['post_category_id' => $ids[$new]]);
                DB::table('post_categories')->where('id', $oldId)->delete();
            }
        }
    }

    public function down(): void {}
};
