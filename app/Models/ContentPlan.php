<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ContentPlan extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    protected $casts = ['due_date' => 'date'];

    public const TYPES = ['post' => 'مطلب وبلاگ', 'game_update' => 'به‌روزرسانی بازی', 'image' => 'تصویر / طراحی', 'other' => 'سایر'];

    public const STATUSES = ['planned' => 'برنامه‌ریزی‌شده', 'in_progress' => 'در حال انجام', 'done' => 'انجام شد', 'skipped' => 'لغو شد'];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function category()
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
