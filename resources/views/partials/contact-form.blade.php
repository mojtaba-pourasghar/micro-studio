{{-- Contact form (posts to contact.store; validated client-side by main.js and server-side). Vars: $subjects (array|null) --}}
<form class="contact-form reveal from-left" id="contact-form" action="{{ route('contact.store') }}" method="post" novalidate>
  @csrf
  <div class="sr-only" aria-hidden="true"><label>وب‌سایت<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
  <div class="field-row">
    <div class="field">
      <label for="cf-name">نام شما <span aria-hidden="true">*</span></label>
      <input id="cf-name" name="name" type="text" autocomplete="name" required aria-describedby="cf-name-err">
      <p class="error" id="cf-name-err" aria-live="polite"></p>
    </div>
    <div class="field">
      <label for="cf-phone">شماره تماس</label>
      <input id="cf-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" dir="ltr" placeholder="0912 000 0000" aria-describedby="cf-phone-err">
      <p class="error" id="cf-phone-err" aria-live="polite"></p>
    </div>
  </div>
  @if (! empty($subjects))
    <div class="field-row">
      <div class="field">
        <label for="cf-email">ایمیل <span aria-hidden="true">*</span></label>
        <input id="cf-email" name="email" type="email" autocomplete="email" dir="ltr" required aria-describedby="cf-email-err">
        <p class="error" id="cf-email-err" aria-live="polite"></p>
      </div>
      <div class="field">
        <label for="cf-subject">موضوع</label>
        <select id="cf-subject" name="subject">
          @foreach ($subjects as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
        </select>
      </div>
    </div>
  @else
    <div class="field">
      <label for="cf-email">ایمیل <span aria-hidden="true">*</span></label>
      <input id="cf-email" name="email" type="email" autocomplete="email" dir="ltr" required aria-describedby="cf-email-err">
      <p class="error" id="cf-email-err" aria-live="polite"></p>
    </div>
  @endif
  <div class="field">
    <label for="cf-msg">پیام شما <span aria-hidden="true">*</span></label>
    <textarea id="cf-msg" name="message" rows="{{ ! empty($subjects) ? 5 : 4 }}" required aria-describedby="cf-msg-err" @if (! empty($subjects)) placeholder="درباره‌ی ایده، مخاطب و زمان‌بندی پروژه بنویسید…" @endif></textarea>
    <p class="error" id="cf-msg-err" aria-live="polite"></p>
  </div>
  <label class="check"><input type="checkbox" name="newsletter" value="1"> مرا از بازی‌ها و رویدادهای جدید باخبر کنید</label>
  <button class="btn btn-primary" type="submit">ارسال درخواست <svg aria-hidden="true"><use href="#i-play"/></svg></button>
  <p class="form-status" role="status" aria-live="polite">@if (session('contact_ok')) درخواست شما ثبت شد؛ به‌زودی با شما تماس می‌گیریم. @endif</p>
</form>
