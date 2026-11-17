<?php

namespace App\Filament\Auth;

use App\Support\Captcha;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

/** Admin login with a self-hosted image captcha, and a notice when the session expired. */
class Login extends BaseLogin
{
    public int $captchaNonce = 0;

    public function mount(): void
    {
        parent::mount();
        if (request()->boolean('expired')) {
            \Filament\Notifications\Notification::make()->title('نشست شما به پایان رسید؛ دوباره وارد شوید.')->warning()->send();
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            Html::make(fn () => new HtmlString(
                '<div style="display:flex;align-items:center;gap:10px;direction:ltr">'
                .'<img src="'.route('captcha', 'login').'?n='.$this->captchaNonce.'" alt="کد امنیتی" width="170" height="56" style="border-radius:10px;border:1px solid rgba(116,87,255,.3)">'
                .'<button type="button" wire:click="refreshCaptcha" style="font-size:13px;color:#7457ff;font-weight:700">↻ کد تازه</button></div>'
            )),
            TextInput::make('captcha')
                ->label('کد امنیتی')
                ->helperText('عددهای تصویر بالا را وارد کنید.')
                ->required()
                ->maxLength(10)
                ->extraInputAttributes(['dir' => 'ltr', 'inputmode' => 'numeric', 'autocomplete' => 'off']),
            $this->getRememberFormComponent(),
        ]);
    }

    public function refreshCaptcha(): void
    {
        $this->captchaNonce++;
        $this->data['captcha'] = null;
    }

    public function authenticate(): ?LoginResponse
    {
        if (! Captcha::check('login', $this->data['captcha'] ?? null)) {
            $this->refreshCaptcha();
            throw ValidationException::withMessages(['data.captcha' => 'کد امنیتی درست نیست؛ کد تازه را وارد کنید.']);
        }

        try {
            return parent::authenticate();
        } catch (ValidationException $e) {
            $this->refreshCaptcha(); // a used code never works twice
            throw $e;
        }
    }
}
