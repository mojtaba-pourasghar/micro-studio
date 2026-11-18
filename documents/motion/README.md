# تریلر و موشن بنر

- `script.json`: متن گوینده برای هر صحنه
- `tts.py`: ساخت صدای فارسی با Gemini TTS. کلیدها از متغیر `GEMINI_API_KEYS` خوانده می‌شوند (با کاما جدا شده‌اند) و هرگز در گیت نیستند.
- `cards.js`: تیتر هر صحنه را با فونت‌های سایت به‌صورت PNG شفاف می‌سازد (Playwright).
- `compose.py`: با ffmpeg تریلر ۱۶:۹ و حلقه‌ی بی‌صدای بنر را می‌سازد.

خروجی‌ها در سایت:
- `public/assets/video/studio-trailer.mp4`
- `public/assets/video/banner-loop.mp4`
- `public/assets/video/studio-trailer-poster.webp`

```bash
GEMINI_API_KEYS=key1,key2 python3 tts.py gemini-3.8-flash-tts Puck
```
```bash
node cards.js
```
```bash
python3 compose.py
```
