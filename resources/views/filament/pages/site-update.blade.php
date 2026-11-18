<x-filament-panels::page>
    @php($pending = $this->pending())
    <x-filament::section>
        <x-slot name="heading">وضعیت</x-slot>
        @if (count($pending))
            <p>{{ count($pending) }} تغییر دیتابیس منتظر اجراست. دکمه‌ی «اجرای به‌روزرسانی» را بزنید.</p>
            <ul style="margin-top:.75rem;list-style:disc;padding-inline-start:1.5rem;direction:ltr;text-align:left">
                @foreach ($pending as $m)<li><code>{{ $m }}</code></li>@endforeach
            </ul>
        @else
            <p>سایت به‌روز است؛ تغییری منتظر اجرا نیست.</p>
        @endif
    </x-filament::section>

    @if ($log)
        <x-filament::section>
            <x-slot name="heading">نتیجه</x-slot>
            <pre style="white-space:pre-wrap;direction:ltr;text-align:left;font-size:.85rem">{{ implode("\n\n", $log) }}</pre>
        </x-filament::section>
    @endif

    <x-filament::section>
        <x-slot name="heading">به‌روزرسانی بدون SSH</x-slot>
        <ol style="list-style:decimal;padding-inline-start:1.5rem;line-height:2">
            <li>از دیتابیس (phpMyAdmin ← Export) و پوشه‌ی <code>public/uploads</code> نسخه‌ی پشتیبان بگیرید.</li>
            <li>بسته‌ی تازه‌ی <code>micro-studio-cpanel.zip</code> را در File Manager روی پوشه‌ی سایت Extract کنید (فایل <code>.env</code> و <code>public/uploads</code> دست نمی‌خورند).</li>
            <li>به همین صفحه برگردید و «اجرای به‌روزرسانی» را بزنید.</li>
        </ol>
    </x-filament::section>
</x-filament-panels::page>
