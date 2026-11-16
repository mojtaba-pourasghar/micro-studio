<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Undo one activity_logs entry (used by the admin and by the agent's rollback tool). */
class Rollback
{
    public static function apply(ActivityLog $log): string
    {
        $class = Relation::getMorphedModel($log->subject_type) ?? $log->subject_type;
        if (! class_exists($class)) {
            return 'نوع مورد شناخته نشد.';
        }
        $soft = in_array(SoftDeletes::class, class_uses_recursive($class), true);
        $query = $soft ? $class::withTrashed() : $class::query();
        $model = $query->find($log->subject_id);

        switch ($log->action) {
            case 'updated':
                if (! $model) {
                    return 'مورد دیگر وجود ندارد.';
                }
                $model->fill(collect($log->before ?? [])->except(['id', 'created_at', 'updated_at'])->all())->save();
                break;
            case 'deleted':
                if ($model && $soft && $model->trashed()) {
                    $model->restore();
                } elseif (! $model) {
                    (new $class)->forceFill(collect($log->before ?? [])->except(['deleted_at'])->all())->save();
                } else {
                    return 'مورد حذف نشده است.';
                }
                break;
            case 'created':
                if (! $model) {
                    return 'مورد دیگر وجود ندارد.';
                }
                $model->delete();
                break;
            default:
                return 'این رویداد قابل بازگرداندن نیست.';
        }
        $log->forceFill(['summary' => $log->summary.' (بازگردانده شد)'])->saveQuietly();

        return '✓ بازگردانده شد';
    }
}
