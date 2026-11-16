<?php

namespace App\Support;

use App\Models\ContentPlan;
use App\Models\Game;
use App\Models\PostCategory;
use Illuminate\Support\Carbon;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

/** Imports rows of the content calendar from an .xlsx or .csv file. */
class ContentPlanImporter
{
    protected const HEADERS = [
        'title' => ['title', 'موضوع', 'عنوان'], 'brief' => ['brief', 'توضیح', 'شرح'], 'type' => ['type', 'نوع'],
        'due_date' => ['due_date', 'date', 'تاریخ'], 'game' => ['game', 'بازی'], 'category' => ['category', 'دسته'],
    ];

    /** @return array{0:int, 1:array<int,string>} */
    public static function import(string $path): array
    {
        $rows = static::rows($path);
        if (! $rows) {
            return [0, ['فایل خالی است.']];
        }
        $map = [];
        foreach (array_shift($rows) as $i => $h) {
            $h = trim(mb_strtolower((string) $h));
            foreach (self::HEADERS as $key => $aliases) {
                if (in_array($h, $aliases, true)) {
                    $map[$key] = $i;
                }
            }
        }
        if (! isset($map['title'])) {
            return [0, ['ستون title یا «موضوع» در ردیف اول پیدا نشد.']];
        }
        $created = 0;
        $errors = [];
        foreach ($rows as $n => $row) {
            $get = fn ($k) => isset($map[$k]) ? trim((string) ($row[$map[$k]] ?? '')) : '';
            if ($get('title') === '') {
                continue;
            }
            $type = $get('type');
            $type = array_key_exists($type, ContentPlan::TYPES) ? $type : (array_search($type, ContentPlan::TYPES, true) ?: 'post');
            try {
                ContentPlan::create([
                    'title' => $get('title'), 'brief' => $get('brief') ?: null, 'type' => $type,
                    'due_date' => static::date($row[$map['due_date'] ?? -1] ?? null),
                    'game_id' => $get('game') ? Game::where('slug', $get('game'))->orWhere('short_title', $get('game'))->value('id') : null,
                    'post_category_id' => $get('category') ? PostCategory::where('slug', $get('category'))->orWhere('name', $get('category'))->value('id') : null,
                ]);
                $created++;
            } catch (\Throwable $e) {
                $errors[] = 'ردیف '.fa_digits($n + 2).': '.$e->getMessage();
            }
        }

        return [$created, $errors];
    }

    protected static function rows(string $path): array
    {
        $isXlsx = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'xlsx' || str_starts_with((string) file_get_contents($path, false, null, 0, 2), 'PK');
        $reader = $isXlsx ? new XlsxReader : new CsvReader;
        $reader->open($path);
        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = array_map(fn ($c) => $c instanceof \DateTimeInterface ? $c : (string) $c, $row->toArray());
            }
            break;
        }
        $reader->close();
        if ($rows && isset($rows[0][0]) && is_string($rows[0][0])) {
            $rows[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', $rows[0][0]);
        }

        return $rows;
    }

    /** Accepts a DateTime, 2026-12-01, or a Solar Hijri date like 1405/09/10 (Persian or Latin digits). */
    public static function date(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        $v = strtr(trim((string) $value), ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']);
        if ($v === '') {
            return null;
        }
        if (preg_match('~^(1[34]\d\d)[/\-.](\d{1,2})[/\-.](\d{1,2})$~', $v, $m)) {
            return Jalali::toGregorian((int) $m[1], (int) $m[2], (int) $m[3]);
        }
        try {
            return Carbon::parse($v)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
