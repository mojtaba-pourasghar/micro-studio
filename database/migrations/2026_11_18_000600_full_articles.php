<?php

use Database\Seeders\ArticleSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Full article texts for the blog (stub posts only) + new studio-news posts waiting for review; proposed Canva rule. */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('post_categories')->exists()) {
            (new ArticleSeeder)->run();
        }
        if (DB::getSchemaBuilder()->hasTable('content_rules') && ! DB::table('content_rules')->where('title', 'تصاویر و پوسترها با Canva')->exists()) {
            DB::table('content_rules')->insert([
                'title' => 'تصاویر و پوسترها با Canva',
                'body' => 'همه‌ی تصاویر، کاورها و پوسترهای سایت با Canva ساخته می‌شوند: سبک کارتونی دوبعدی، رنگ‌های برند (بنفش #7457ff، نارنجی، سرمه‌ای شب) و کاراکترهای خود بازی‌ها. داخل تصویر متن فارسی نوشته نشود. تصویر با نسبت درست ساخته شود و هرگز کشیده یا فشرده نشود. لوگوی استودیو هنگام آپلود خودکار اضافه می‌شود.',
                'status' => 'proposed', 'proposed_by' => 'agent', 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void {}
};
