# موشن «این‌طوری بازی می‌سازیم»

یک موشن ۵۵ ثانیه‌ای درباره‌ی روند کار استودیو، در هفت بخش:

1. لوگوموشن
2. ایده
3. داستان و طراحی
4. طراحی رابط
5. صدا و موسیقی
6. تست با بچه‌ها
7. انتشار و به‌روزرسانی

در پایان هم لوگوموشن کامل می‌آید.

## فایل‌ها

- `script.json`: متن گوینده
- `tts.py`: صدای گوینده‌ی فارسی با Gemini TTS (صدای Fenrir).
  - کلیدها از متغیر `GEMINI_API_KEYS` خوانده می‌شوند (با کاما جدا شده‌اند) و هرگز در گیت نیستند.
  - مدل هر جمله را دو بار می‌خواند: اول آرام، بعد هیجانی. برداشت دوم (بعد از مکث) استفاده شده است.
- `music.py`: آهنگ الکترونیک ۱۲۸ BPM که با کد ساخته شده و نمونه‌ی صوتی آماده ندارد. بخش‌هایش با برش‌های تصویر هماهنگ است.
- `mix.py`: صدا و آهنگ را میکس می‌کند (آهنگ زیر صدای گوینده پایین می‌آید) و بلندی صدا را برای هر فریم می‌نویسد (`amp.json`، برای اکولایزر صحنه‌ی صدا).
- `motion.html`: کل انیمیشن، که هر فریمش با `render(t)` ساخته می‌شود.
- `render.js`: فریم‌ها را با Playwright می‌گیرد و به ffmpeg می‌دهد.

## خروجی‌ها در سایت

- `public/assets/video/studio-motion.mp4`
- `public/assets/video/studio-motion-poster.webp`
- `public/assets/video/banner-loop.mp4`: نسخه‌ی محو و بی‌صدا برای پس‌زمینه‌ی بنر

## ساخت دوباره

مسیر فایل‌ها در `motion.html` نسبت به پوشه‌ی کاری تنظیم شده است. اگر جای پوشه عوض شد، مسیرها را اصلاح کنید.

```bash
GEMINI_API_KEYS=key1,key2 python3 tts.py gemini-3.8-flash-tts Fenrir
```
```bash
python3 music.py && python3 mix.py
```
```bash
node render.js video
```
```bash
ffmpeg -i motion-silent.mp4 -i mix.wav -map 0:v -map 1:a -c:v libx264 -crf 25 -c:a aac -b:a 160k -movflags +faststart -shortest studio-motion.mp4
```
