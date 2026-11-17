<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use LogsActivity, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'tags' => 'array', 'platforms' => 'array', 'store_links' => 'array', 'stats' => 'array',
        'features' => 'array', 'screenshots' => 'array', 'characters' => 'array', 'story_points' => 'array', 'videos' => 'array', 'gameplay' => 'array', 'benefits' => 'array',
        'card_is_phone' => 'boolean', 'is_featured' => 'boolean', 'is_published' => 'boolean',
    ];

    public const CATEGORIES = ['game' => 'بازی', 'edu' => 'اپ آموزشی'];

    public const STATUSES = ['released' => 'منتشرشده', 'in_development' => 'در حال ساخت', 'designing' => 'در حال طراحی'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('is_published', true)->orderBy('sort_order')->orderBy('id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
