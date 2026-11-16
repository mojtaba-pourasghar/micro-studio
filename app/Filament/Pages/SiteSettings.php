<?php

namespace App\Filament\Pages;

use App\Filament\Forms\Fields;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Every editable text/image of the pages, generated from config/site.php. */
class SiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'محتوای سایت';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'متن صفحات و تنظیمات';

    protected static ?string $title = 'متن صفحات و تنظیمات';

    protected static ?string $slug = 'settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $state = [];
        foreach (array_keys(Setting::schema()) as $key) {
            data_set($state, $key, Setting::get($key));
        }
        $this->form->fill($state);
    }

    public function form(Schema $schema): Schema
    {
        $tabs = [];
        foreach (config('site.groups') as $group => $def) {
            $fields = [];
            foreach ($def['fields'] as $name => $f) {
                $key = "$group.$name";
                $fields[] = match ($f['type']) {
                    'image' => Fields::image($key, $f['label']),
                    'textarea' => Textarea::make($key)->label($f['label'])->rows(3)->columnSpanFull(),
                    'heading' => TextInput::make($key)->label($f['label'])->helperText('[[توخالی]]  {{گرادیان}}  | = خط بعد'),
                    default => TextInput::make($key)->label($f['label']),
                };
            }
            $tabs[] = Tab::make($def['label'])->schema([Grid::make(2)->schema($fields)]);
        }

        return $schema->statePath('data')->components([
            Tabs::make('groups')->tabs($tabs)->persistTabInQueryString()->columnSpanFull(),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([Actions::make([Action::make('save')->label('ذخیره‌ی تغییرات')->submit('save')->keyBindings(['mod+s'])])]),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $changed = 0;
        foreach (Setting::schema() as $key => $f) {
            $value = data_get($state, $key);
            if (is_array($value)) {
                $value = array_values($value)[0] ?? null;
            }
            $current = Setting::get($key);
            if ((string) $value === (string) $current) {
                continue;
            }
            $value === null || $value === '' || $value === $f['default']
                ? Setting::where('key', $key)->delete()
                : Setting::put($key, $value);
            $changed++;
        }
        Notification::make()->title($changed ? fa_digits($changed).' مورد ذخیره شد' : 'تغییری نبود')->success()->send();
    }
}
