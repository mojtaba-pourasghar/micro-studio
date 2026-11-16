<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    protected $casts = ['value' => 'array'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings.all'));
        static::deleted(fn () => Cache::forget('settings.all'));
    }

    /** @return array<string, mixed> */
    public static function allCached(): array
    {
        return Cache::rememberForever('settings.all', fn () => static::query()->pluck('value', 'key')->all());
    }

    /** Value from the DB, else the default declared in config/site.php ("group.field"). */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::allCached();
        if (array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '') {
            return $all[$key];
        }
        [$group, $field] = array_pad(explode('.', $key, 2), 2, null);

        return config("site.groups.$group.fields.$field.default", $default);
    }

    /** @return array<string, array{label:string,type:string,default:mixed}> flat "group.field" => definition */
    public static function schema(): array
    {
        $out = [];
        foreach (config('site.groups') as $group => $def) {
            foreach ($def['fields'] as $field => $f) {
                $out["$group.$field"] = $f + ['group_label' => $def['label']];
            }
        }

        return $out;
    }

    public static function put(string $key, mixed $value): self
    {
        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
