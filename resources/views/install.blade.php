<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
  <title>نصب میکرو استودیو</title>
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <style>body{background:var(--night);color:#fff;display:grid;place-items:center;min-height:100vh;padding:24px}.box{width:min(520px,100%);background:#fff;color:var(--ink);border-radius:22px;padding:32px}.box h1{font-family:var(--f-display);font-size:2rem;margin-bottom:8px}.box code{direction:ltr;display:block;word-break:break-all;background:#f1f2f6;padding:12px;border-radius:12px;font-size:.85rem}</style>
</head>
<body>
  <div class="box">
    @if (! empty($done))
      <h1>نصب کامل شد ✓</h1>
      <ul style="margin-bottom:16px">@foreach ($log as $l)<li>{{ $l }}</li>@endforeach</ul>
      <p><b>آدرس کانکتور کلاد</b> (فقط همین یک بار نمایش داده می‌شود؛ جایی امن ذخیره کنید):</p>
      <code>{{ $mcpUrl }}</code>
      <p style="margin-top:16px">حالا مقدار <b>INSTALL_KEY</b> را در فایل ‎.env خالی کنید. ورود به پنل: <a href="{{ url('/admin') }}" style="color:var(--brand-ink)">{{ url('/admin') }}</a></p>
    @else
      <h1>نصب میکرو استودیو</h1>
      <p style="margin-bottom:18px">جدول‌های پایگاه داده ساخته می‌شوند، محتوای قالب وارد می‌شود و حساب مدیر ساخته می‌شود.</p>
      <form method="post" action="{{ url('/install') }}" class="contact-form" style="box-shadow:none;padding:0">
        @csrf
        <input type="hidden" name="key" value="{{ $key }}">
        @if ($errors->any())<p class="error">{{ $errors->first() }}</p>@endif
        <div class="field"><label>نام مدیر</label><input name="name" value="{{ old('name', 'مدیر سایت') }}" required></div>
        <div class="field"><label>ایمیل</label><input name="email" type="email" dir="ltr" value="{{ old('email') }}" required></div>
        <div class="field"><label>رمز عبور (حداقل ۱۰ حرف)</label><input name="password" type="password" dir="ltr" required></div>
        <div class="field"><label>تکرار رمز عبور</label><input name="password_confirmation" type="password" dir="ltr" required></div>
        <button class="btn btn-primary" type="submit">نصب</button>
      </form>
    @endif
  </div>
</body>
</html>
