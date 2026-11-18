<?php

namespace App\Filament\Forms;

use App\Support\MediaStore;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/** Shared form fields for the admin. */
class Fields
{
    /**
     * Image picker stored as a public path ("assets/…" or "uploads/…").
     * New uploads get the studio logo stamped on them (unless $watermark is false, e.g. logos and avatars).
     * New uploads go through MediaStore (so they appear in the media library); files are never deleted from disk.
     */
    public static function image(string $name, string $label, bool $watermark = true): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->disk('site')
            ->image()
            ->maxSize(10240)
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'])
            ->imagePreviewHeight('140')
            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file) => MediaStore::fromUpload($file, null, 'admin', $watermark)->path)
            ->deleteUploadedFileUsing(fn () => null);
    }

    public static function tagColor(string $name = 'color'): Select
    {
        return Select::make($name)->label('رنگ')->default('purple')->native(false)->options([
            'red' => 'قرمز', 'green' => 'سبز', 'blue' => 'آبی', 'pink' => 'صورتی', 'orange' => 'نارنجی', 'purple' => 'بنفش',
        ]);
    }

    public static function icon(string $name = 'icon'): Select
    {
        $icons = ['gem' => 'ستاره', 'text' => 'متن/فارسی', 'noads' => 'بدون تبلیغ', 'offline' => 'آفلاین', 'shield' => 'سپر/امنیت', 'bulb' => 'ایده',
            'test' => 'آزمایش', 'heart' => 'قلب', 'pad' => 'دسته‌ی بازی', 'code' => 'کد', 'brush' => 'قلم‌مو', 'mic' => 'میکروفون', 'store' => 'فروشگاه',
            'users' => 'کاربران', 'bug' => 'باگ', 'book' => 'کتاب', 'branch' => 'انشعاب', 'puzzle' => 'پازل', 'refresh' => 'به‌روزرسانی', 'age' => 'سن',
            'chip' => 'تراشه', 'check' => 'تیک', 'star' => 'ستاره‌ی امتیاز', 'mail' => 'ایمیل', 'phone' => 'تلفن', 'clock' => 'ساعت', 'pin' => 'مکان'];

        return Select::make($name)->label('آیکن')->options($icons)->native(false)->searchable();
    }

    public static function stringList(string $name, string $label, string $item = 'مورد'): Repeater
    {
        return Repeater::make($name)->label($label)->simple(TextInput::make('value')->label($item)->required())
            ->reorderable()->defaultItems(0)->addActionLabel('افزودن '.$item);
    }
}
