<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use LogsActivity, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = ['is_published' => 'boolean', 'deliverables' => 'array', 'image_is_phone' => 'boolean'];

    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('is_published', true)->orderBy('sort_order')->orderBy('id');
    }
}
