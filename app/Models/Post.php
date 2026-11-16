<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use App\Support\Html;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use LogsActivity, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = ['tags' => 'array', 'published_at' => 'datetime', 'is_featured' => 'boolean'];

    public const STATUSES = ['draft' => 'پیش‌نویس', 'pending_review' => 'در انتظار تأیید', 'published' => 'منتشرشده', 'rejected' => 'ردشده'];

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if ($post->isDirty('body') && $post->body) {
                $post->body = Html::sanitize($post->body);
                $post->read_minutes = max(1, (int) ceil(mb_strlen(strip_tags($post->body)) / 1100));
            }
            if ($post->status === 'published' && ! $post->published_at) {
                $post->published_at = now();
            }
        });
    }

    /** Body HTML with relative asset/upload paths turned into absolute URLs. */
    public function renderedBody(): string
    {
        return preg_replace_callback('~(src|href)="(assets/|uploads/)([^"]*)"~', fn ($m) => $m[1].'="'.asset($m[2].$m[3]).'"', (string) $this->body);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category()
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published')->where('published_at', '<=', now())->orderByDesc('published_at');
    }
}
