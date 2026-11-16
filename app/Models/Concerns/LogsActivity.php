<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use App\Support\Actor;
use Illuminate\Database\Eloquent\Model;

/** Writes an activity_logs row (with a before/after snapshot) for every create, update and delete. */
trait LogsActivity
{
    /** Persian names used in activity summaries. */
    protected const LABELS = [
        'Game' => 'بازی', 'Service' => 'خدمت', 'TeamMember' => 'عضو تیم', 'Testimonial' => 'نظر', 'Faq' => 'سؤال', 'Block' => 'کارت',
        'Post' => 'مطلب', 'PostCategory' => 'دسته', 'Media' => 'تصویر', 'ContentRule' => 'قانون', 'ContentPlan' => 'برنامه', 'Setting' => 'تنظیم',
    ];

    public static function bootLogsActivity(): void
    {
        static::created(fn (Model $m) => static::writeLog($m, 'created', null, $m->attributesToArray()));
        static::updated(function (Model $m) {
            $changed = array_keys($m->getChanges());
            $changed = array_values(array_diff($changed, ['updated_at']));
            if (! $changed) {
                return;
            }
            $before = array_intersect_key($m->getOriginal(), array_flip($changed));
            $after = array_intersect_key($m->attributesToArray(), array_flip($changed));
            static::writeLog($m, 'updated', $before, $after);
        });
        static::deleted(fn (Model $m) => static::writeLog($m, 'deleted', $m->attributesToArray(), null));
        if (method_exists(static::class, 'restored')) {
            static::restored(fn (Model $m) => static::writeLog($m, 'restored', null, $m->attributesToArray()));
        }
    }

    protected static function writeLog(Model $m, string $action, ?array $before, ?array $after): void
    {
        ActivityLog::create([
            'actor' => Actor::label(),
            'action' => $action,
            'subject_type' => $m->getMorphClass(),
            'subject_id' => $m->getKey(),
            'summary' => (self::LABELS[class_basename($m)] ?? class_basename($m)).' «'.($m->title ?? $m->name ?? $m->question ?? $m->key ?? $m->path ?? '#'.$m->getKey()).'»',
            'before' => $before,
            'after' => $after,
        ]);
    }
}
