<?php

/*
|--------------------------------------------------------------------------
| Editable site texts (settings schema + defaults)
|--------------------------------------------------------------------------
| Every key here is editable from the admin "تنظیمات و متن صفحات" page and via
| the MCP tools (get_settings / update_settings). A value saved in the
| `settings` table overrides the default below. Key format: "<group>.<field>".
|
| Field types: text | textarea | heading | image | url
| Heading syntax: [[outline text]]  {{gradient text}}  | = line break
| For Hero titles ("hero_title"), every "|" starts a new line.
*/

$hero = fn (string $title, string $lead, string $image, string $char, string $charName, string $charSub) => [
    'hero_title' => ['label' => 'تیتر Hero', 'type' => 'heading', 'default' => $title],
    'hero_lead' => ['label' => 'متن زیر تیتر Hero', 'type' => 'textarea', 'default' => $lead],
    'hero_image' => ['label' => 'تصویر پس‌زمینه‌ی Hero', 'type' => 'image', 'default' => $image],
    'hero_character' => ['label' => 'کاراکتر Hero (PNG/WebP بدون پس‌زمینه)', 'type' => 'image', 'default' => $char],
    'hero_character_name' => ['label' => 'برچسب کاراکتر', 'type' => 'text', 'default' => $charName],
    'hero_character_sub' => ['label' => 'زیرنویس برچسب کاراکتر', 'type' => 'text', 'default' => $charSub],
];

return [
    'groups' => [
        'site' => [
            'label' => 'عمومی سایت',
            'fields' => [
                'name' => ['label' => 'نام سایت', 'type' => 'text', 'default' => 'میکرو استودیو'],
                'brand_first' => ['label' => 'لوگو - بخش اول', 'type' => 'text', 'default' => 'میکرو'],
                'brand_second' => ['label' => 'لوگو - بخش دوم (بنفش)', 'type' => 'text', 'default' => 'استودیو'],
                'meta_title' => ['label' => 'عنوان صفحه‌ی اصلی (SEO)', 'type' => 'text', 'default' => 'میکرو استودیو | سازنده‌ی بازی‌های داستانی و اپ‌های آموزشی فارسی'],
                'meta_description' => ['label' => 'توضیح سایت (SEO)', 'type' => 'textarea', 'default' => 'میکرو استودیو؛ تیم کوچک و پرانرژی سازنده‌ی بازی‌های داستانی، پلتفرمر و اپ‌های آموزشی فارسی مثل دو آسمان، رازینو، سفر پیلو و هوهو.'],
                'header_cta' => ['label' => 'دکمه‌ی هدر', 'type' => 'text', 'default' => 'در تماس باشید'],
                'trailer_url' => ['label' => 'فایل یا لینک تریلر', 'type' => 'url', 'default' => 'assets/video/trailer.mp4'],
                'copyright' => ['label' => 'متن کپی‌رایت فوتر', 'type' => 'text', 'default' => 'همه‌ی حقوق محفوظ است.'],
                'footer_image' => ['label' => 'پس‌زمینه‌ی فوتر', 'type' => 'image', 'default' => 'assets/img/scenes/hero-two-skies.webp'],
            ],
        ],

        'contact' => [
            'label' => 'اطلاعات تماس',
            'fields' => [
                'email' => ['label' => 'ایمیل', 'type' => 'text', 'default' => 'mojtaba.pourasghar@gmail.com'],
                'phone' => ['label' => 'تلفن (برای لینک، مثل +982100000000)', 'type' => 'text', 'default' => '+982100000000'],
                'phone_display' => ['label' => 'تلفن (نمایشی)', 'type' => 'text', 'default' => '+98 (21) 0000 0000'],
                'address' => ['label' => 'آدرس', 'type' => 'text', 'default' => 'ایران، تهران'],
                'response_time' => ['label' => 'زمان پاسخ‌گویی', 'type' => 'text', 'default' => 'پاسخ در ۲۴ ساعت کاری'],
                'instagram_url' => ['label' => 'لینک اینستاگرام', 'type' => 'url', 'default' => 'https://instagram.com/microstudio.ir'],
                'instagram_handle' => ['label' => 'آیدی اینستاگرام', 'type' => 'text', 'default' => '@microstudio.ir'],
                'telegram_url' => ['label' => 'لینک تلگرام', 'type' => 'url', 'default' => 'https://t.me/microstudio_ir'],
                'telegram_handle' => ['label' => 'آیدی تلگرام', 'type' => 'text', 'default' => '@microstudio_ir'],
                'github_url' => ['label' => 'لینک گیت‌هاب', 'type' => 'url', 'default' => 'https://github.com/mojtaba-pourasghar'],
                'notify_email' => ['label' => 'ایمیل دریافت پیام‌های فرم (اختیاری)', 'type' => 'text', 'default' => ''],
            ],
        ],

        'home' => [
            'label' => 'صفحه‌ی اصلی',
            'fields' => $hero('ما بازی‌های|فراموش‌نشدنی|می‌سازیم', 'از ماجراجویی‌های داستانی مثل «دو آسمان» و «رازینو» تا اپ‌های آموزشی «هوهو»؛ هر پروژه را با شخصیت، داستان و زبان فارسی می‌سازیم.', 'assets/img/scenes/hero-two-skies.webp', 'assets/img/characters/kairo.webp', 'کایرو', 'دو آسمان') + [
                'hero_cta' => ['label' => 'دکمه‌ی گرد Hero', 'type' => 'heading', 'default' => 'شروع|پروژه'],
                'about_eyebrow' => ['label' => 'ما که هستیم - برچسب', 'type' => 'text', 'default' => 'ما که هستیم'],
                'about_title' => ['label' => 'ما که هستیم - تیتر', 'type' => 'heading', 'default' => 'تمرکز ما روی [[ساخت]]|[[بازی‌های زیبا]]'],
                'about_lead' => ['label' => 'ما که هستیم - متن', 'type' => 'textarea', 'default' => 'میکرو استودیو یک تیم کوچک و پرانرژی است که بازی‌های داستانی، پلتفرمر و اپ‌های آموزشی فارسی می‌سازد؛ راست‌چین از روز اول، با صداگذاری فارسی، بدون تبلیغ مزاحم و با احترام به حریم خصوصی کودکان.'],
                'about_image' => ['label' => 'ما که هستیم - تصویر اصلی', 'type' => 'image', 'default' => 'assets/img/scenes/razino-map.webp'],
                'about_thumb' => ['label' => 'ما که هستیم - تصویر کوچک', 'type' => 'image', 'default' => 'assets/img/scenes/pilo-world.webp'],
                'about_character' => ['label' => 'ما که هستیم - کاراکتر', 'type' => 'image', 'default' => 'assets/img/characters/razino-hero.webp'],
                'services_eyebrow' => ['label' => 'خدمات - برچسب', 'type' => 'text', 'default' => 'خدمات ما'],
                'services_title' => ['label' => 'خدمات - تیتر', 'type' => 'heading', 'default' => 'طراحی هنری و|توسعه‌ی بازی'],
                'services_image' => ['label' => 'خدمات - پس‌زمینه', 'type' => 'image', 'default' => 'assets/img/scenes/dark-castle.webp'],
                'services_character' => ['label' => 'خدمات - کاراکتر', 'type' => 'image', 'default' => 'assets/img/characters/ilara.webp'],
                'works_eyebrow' => ['label' => 'نمونه‌کارها - برچسب', 'type' => 'text', 'default' => 'بازی‌ها و اپ‌ها'],
                'works_title' => ['label' => 'نمونه‌کارها - تیتر', 'type' => 'heading', 'default' => 'نگاهی به [[کارهای شگفت‌انگیز]] ما'],
                'create_eyebrow' => ['label' => 'بیایید بسازیم - برچسب', 'type' => 'text', 'default' => 'بیایید بسازیم'],
                'create_title' => ['label' => 'بیایید بسازیم - تیتر', 'type' => 'heading', 'default' => 'بیایید با هم|[[بازی بسازیم]]'],
                'create_lead' => ['label' => 'بیایید بسازیم - متن', 'type' => 'textarea', 'default' => 'آماده‌ایم ایده‌ی شما را روی موبایل، تبلت یا وب به یک تجربه‌ی جذاب تبدیل کنیم. بازی زبان نسل امروز است و ما کمک می‌کنیم با همین زبان با کودکان و خانواده‌ها حرف بزنید.'],
                'create_image' => ['label' => 'بیایید بسازیم - تصویر دایره', 'type' => 'image', 'default' => 'assets/img/scenes/temple-gate.webp'],
                'create_character' => ['label' => 'بیایید بسازیم - کاراکتر', 'type' => 'image', 'default' => 'assets/img/characters/razino-wizard.webp'],
                'honors_eyebrow' => ['label' => 'دستاوردها - برچسب', 'type' => 'text', 'default' => 'دستاوردها'],
                'honors_title' => ['label' => 'دستاوردها - تیتر', 'type' => 'heading', 'default' => 'کارنامه‌ای که به آن|افتخار می‌کنیم'],
                'honors_note' => ['label' => 'دستاوردها - متن', 'type' => 'textarea', 'default' => 'هر پروژه را با تست کودکان، بازبینی محتوا و آزمون با گروه سنی هدف جلو می‌بریم؛ نتیجه بازی‌هایی است که خانواده‌ها با خیال راحت نصب می‌کنند.'],
                'honors_image' => ['label' => 'دستاوردها - تصویر', 'type' => 'image', 'default' => 'assets/img/scenes/night-portal.webp'],
                'banner_title' => ['label' => 'بنر تریلر - تیتر', 'type' => 'heading', 'default' => 'آماده‌ای مهارتت را|نشان بدهی؟'],
                'banner_image' => ['label' => 'بنر تریلر - تصویر', 'type' => 'image', 'default' => 'assets/img/scenes/puzzle-room.webp'],
                'testi_eyebrow' => ['label' => 'نظرات - برچسب', 'type' => 'text', 'default' => 'نظر خانواده‌ها'],
                'testi_title' => ['label' => 'نظرات - تیتر', 'type' => 'heading', 'default' => 'این‌ها [[حرف‌های گرم]]|[[همراهان ماست]]'],
                'testi_image' => ['label' => 'نظرات - پس‌زمینه', 'type' => 'image', 'default' => 'assets/img/scenes/dark-castle.webp'],
                'contact_title' => ['label' => 'فرم تماس - تیتر', 'type' => 'heading', 'default' => 'همین امروز|{{پروژه‌ات}} را شروع کن!'],
                'contact_lead' => ['label' => 'فرم تماس - متن', 'type' => 'textarea', 'default' => 'تیم ما آماده‌ی شنیدن ایده‌ی شماست. فرم را پر کنید؛ ظرف ۲۴ ساعت کاری با شما تماس می‌گیریم.'],
            ],
        ],

        'games' => [
            'label' => 'صفحه‌ی بازی‌ها و اپ‌ها',
            'fields' => $hero('دنیای بازی‌ها|و اپ‌های ما', 'از ماجراجویی پیکسل‌آرت «دو آسمان» و معماهای «رازینو» تا اپ‌های درسی «هوهو»؛ همه فارسی، راست‌چین، بدون تبلیغ مزاحم و ساخته‌شده برای خانواده‌ها.', 'assets/img/scenes/sunset-cliff.webp', 'assets/img/characters/ilara.webp', 'ایلارا', 'دو آسمان') + [
                'list_eyebrow' => ['label' => 'فهرست - برچسب', 'type' => 'text', 'default' => 'همه‌ی محصولات'],
                'list_title' => ['label' => 'فهرست - تیتر', 'type' => 'heading', 'default' => 'بازی کن، [[یاد بگیر،]]|[[کشف کن]]'],
                'spotlight_eyebrow' => ['label' => 'بازی ویژه - برچسب', 'type' => 'text', 'default' => 'بازی ویژه'],
                'spotlight_image' => ['label' => 'بازی ویژه - پس‌زمینه', 'type' => 'image', 'default' => 'assets/img/scenes/dark-castle.webp'],
                'promises_eyebrow' => ['label' => 'قول ما - برچسب', 'type' => 'text', 'default' => 'قول ما به خانواده‌ها'],
                'promises_title' => ['label' => 'قول ما - تیتر', 'type' => 'heading', 'default' => 'نصب با [[خیال راحت]]'],
                'promises_lead' => ['label' => 'قول ما - متن', 'type' => 'textarea', 'default' => 'هر بازی و اپ میکرو استودیو با همین چهار قاعده ساخته می‌شود؛ پیش از انتشار با کودکان گروه سنی هدف آزموده و محتوایش بازبینی می‌شود.'],
                'banner_title' => ['label' => 'بنر تریلر - تیتر', 'type' => 'heading', 'default' => 'تریلر بازی‌ها را|ببین'],
                'cta_eyebrow' => ['label' => 'دعوت - برچسب عمودی', 'type' => 'text', 'default' => 'بازی بعدی'],
                'cta_title' => ['label' => 'دعوت - تیتر', 'type' => 'heading', 'default' => 'بازی بعدی|[[مال شما باشد]]'],
                'cta_lead' => ['label' => 'دعوت - متن', 'type' => 'textarea', 'default' => 'ایده‌ی یک بازی یا اپ آموزشی دارید؟ از سند طراحی و شخصیت‌پردازی تا انتشار در کافه‌بازار کنارتان هستیم.'],
                'cta_image' => ['label' => 'دعوت - تصویر دایره', 'type' => 'image', 'default' => 'assets/img/scenes/pilo-world.webp'],
                'cta_character' => ['label' => 'دعوت - کاراکتر', 'type' => 'image', 'default' => 'assets/img/characters/kairo.webp'],
            ],
        ],

        'services' => [
            'label' => 'صفحه‌ی خدمات',
            'fields' => $hero('از ایده|تا انتشار', 'ساخت بازی، طراحی شخصیت، اپ آموزشی و صداگذاری فارسی؛ هر چیزی که یک بازی یا اپ کودکانه‌ی خوب لازم دارد، زیر یک سقف.', 'assets/img/scenes/dark-castle.webp', 'assets/img/characters/razino-wizard.webp', 'خدمات', 'میکرو استودیو') + [
                'detail_eyebrow' => ['label' => 'خدمات - برچسب', 'type' => 'text', 'default' => 'چهار خدمت اصلی'],
                'detail_title' => ['label' => 'خدمات - تیتر', 'type' => 'heading', 'default' => 'کاری که [[بهتر از همه]]|[[بلدیم]]'],
                'detail_lead' => ['label' => 'خدمات - متن', 'type' => 'textarea', 'default' => 'هر خدمت را می‌توانید جداگانه سفارش دهید یا یک پروژه را از صفر تا انتشار به ما بسپارید.'],
                'process_eyebrow' => ['label' => 'فرایند - برچسب', 'type' => 'text', 'default' => 'فرایند کار'],
                'process_title' => ['label' => 'فرایند - تیتر', 'type' => 'heading', 'default' => 'چهار قدم تا [[بازی شما]]'],
                'process_lead' => ['label' => 'فرایند - متن', 'type' => 'textarea', 'default' => 'در هر مرحله نسخه‌ای قابل دیدن تحویل می‌گیرید؛ هیچ‌وقت تا آخر پروژه منتظر نمی‌مانید.'],
                'process_image' => ['label' => 'فرایند - پس‌زمینه', 'type' => 'image', 'default' => 'assets/img/scenes/night-portal.webp'],
                'tools_eyebrow' => ['label' => 'ابزارها - برچسب', 'type' => 'text', 'default' => 'ابزارها و توانمندی‌ها'],
                'tools_title' => ['label' => 'ابزارها - تیتر', 'type' => 'heading', 'default' => 'با چه [[می‌سازیم؟]]'],
                'cta_title' => ['label' => 'بنر دعوت - تیتر', 'type' => 'heading', 'default' => 'ایده‌ات را|بازی کنیم؟'],
                'cta_lead' => ['label' => 'بنر دعوت - متن', 'type' => 'textarea', 'default' => 'برای یک جلسه‌ی مشاوره‌ی رایگان فرم تماس را پر کنید؛ ظرف ۲۴ ساعت کاری پاسخ می‌دهیم.'],
                'cta_image' => ['label' => 'بنر دعوت - تصویر', 'type' => 'image', 'default' => 'assets/img/scenes/temple-gate.webp'],
                'faq_title' => ['label' => 'سؤالات - تیتر', 'type' => 'heading', 'default' => 'پیش از [[شروع]]'],
                'faq_lead' => ['label' => 'سؤالات - متن', 'type' => 'textarea', 'default' => 'پاسخ سؤال‌هایی که بیشتر از ما می‌پرسند. سؤال دیگری دارید؟ همین حالا بپرسید.'],
            ],
        ],

        'about' => [
            'label' => 'صفحه‌ی درباره ما',
            'fields' => $hero('قصه‌ی|میکرو استودیو', 'یک تیم کوچک با یک هدف بزرگ: بازی‌هایی بسازیم که کودکان ایرانی با زبان خودشان بازی کنند و پدر و مادرها با خیال راحت نصبشان کنند.', 'assets/img/scenes/pilo-world.webp', 'assets/img/characters/razino-hero.webp', 'رازینو', 'میکرو استودیو') + [
                'story_eyebrow' => ['label' => 'داستان - برچسب', 'type' => 'text', 'default' => 'داستان ما'],
                'story_title' => ['label' => 'داستان - تیتر', 'type' => 'heading', 'default' => 'بازی، [[زبان نسل]]|[[امروز است]]'],
                'story_lead' => ['label' => 'داستان - متن', 'type' => 'textarea', 'default' => 'میکرو استودیو با یک سؤال ساده شروع شد: چرا کودکان ما باید بهترین بازی‌هایشان را به زبانی غیر از فارسی بازی کنند؟ از آن روز، بازی‌های داستانی، پلتفرمر و اپ‌های آموزشی می‌سازیم که راست‌چین، صداگذاری‌شده به فارسی و بدون تبلیغ مزاحم‌اند.'],
                'story_image' => ['label' => 'داستان - تصویر اصلی', 'type' => 'image', 'default' => 'assets/img/scenes/hero-two-skies.webp'],
                'story_thumb' => ['label' => 'داستان - تصویر کوچک', 'type' => 'image', 'default' => 'assets/img/scenes/razino-map.webp'],
                'values_eyebrow' => ['label' => 'ارزش‌ها - برچسب', 'type' => 'text', 'default' => 'ارزش‌های ما'],
                'values_title' => ['label' => 'ارزش‌ها - تیتر', 'type' => 'heading', 'default' => 'آنچه [[رویش]]|[[کوتاه نمی‌آییم]]'],
                'values_lead' => ['label' => 'ارزش‌ها - متن', 'type' => 'textarea', 'default' => 'این اصل‌ها در هر بازی و اپی که می‌سازیم رعایت می‌شود؛ چه محصول خودمان باشد، چه سفارش شما.'],
                'team_eyebrow' => ['label' => 'تیم - برچسب', 'type' => 'text', 'default' => 'تیم ما'],
                'team_title' => ['label' => 'تیم - تیتر', 'type' => 'heading', 'default' => 'آدم‌های [[پشت بازی‌ها]]'],
                'team_lead' => ['label' => 'تیم - متن', 'type' => 'textarea', 'default' => 'تیمی کوچک و چندمهارتی؛ هر کدام از ما در چند پروژه نقش داریم و هر بازی حاصل کار گروهی است.'],
                'cta_eyebrow' => ['label' => 'دعوت - برچسب عمودی', 'type' => 'text', 'default' => 'همکاری با ما'],
                'cta_title' => ['label' => 'دعوت - تیتر', 'type' => 'heading', 'default' => 'به قصه‌ی ما|[[بپیوندید]]'],
                'cta_lead' => ['label' => 'دعوت - متن', 'type' => 'textarea', 'default' => 'سفارش پروژه، همکاری آموزشی یا عضویت در تیم؛ هر کدام که باشد، خوشحال می‌شویم از شما بشنویم.'],
            ],
        ],

        'contact_page' => [
            'label' => 'صفحه‌ی تماس با ما',
            'fields' => $hero('بیا با هم|حرف بزنیم', 'سفارش بازی یا اپ آموزشی، همکاری، پرسش والدین یا گزارش مشکل؛ هر پیامی را می‌خوانیم و ظرف ۲۴ ساعت کاری پاسخ می‌دهیم.', 'assets/img/scenes/temple-gate.webp', 'assets/img/characters/razino-girl.webp', 'سلام!', 'منتظر پیامت هستیم') + [
                'form_lead' => ['label' => 'فرم - متن', 'type' => 'textarea', 'default' => 'فرم را پر کنید؛ هرچه درباره‌ی ایده، مخاطب و زمان‌بندی‌تان بیشتر بگویید، پیشنهاد دقیق‌تری می‌گیرید.'],
                'subjects' => ['label' => 'موضوع‌های فرم (هر خط یک مورد)', 'type' => 'textarea', 'default' => "ساخت بازی\nاپ آموزشی\nطراحی شخصیت و هنر\nصداگذاری فارسی\nپشتیبانی بازی‌ها / والدین\nهمکاری و استخدام\nسایر"],
                'support_eyebrow' => ['label' => 'پشتیبانی - برچسب', 'type' => 'text', 'default' => 'پشتیبانی و والدین'],
                'support_title' => ['label' => 'پشتیبانی - تیتر', 'type' => 'heading', 'default' => 'کنار [[خانواده‌ها]]'],
                'support_lead' => ['label' => 'پشتیبانی - متن', 'type' => 'textarea', 'default' => 'بازی‌های ما را نصب کرده‌اید و سؤالی دارید؟ از همین‌جا شروع کنید.'],
                'faq_title' => ['label' => 'سؤالات - تیتر', 'type' => 'heading', 'default' => 'شاید [[جوابتان]]|[[اینجا باشد]]'],
                'faq_lead' => ['label' => 'سؤالات - متن', 'type' => 'textarea', 'default' => 'پاسخ پرسش‌های رایج والدین و سفارش‌دهندگان.'],
            ],
        ],

        'blog' => [
            'label' => 'وبلاگ',
            'fields' => $hero('یادداشت‌های|ساخت بازی', 'پشت صحنه‌ی رازینو، سفر پیلو، دو آسمان و هوهو؛ از معماری بازی و پیکسل‌آرت تا راهنمای والدین برای انتخاب بازی مناسب.', 'assets/img/scenes/night-portal.webp', 'assets/img/characters/razino-wizard.webp', 'وبلاگ', 'میکرو استودیو') + [
                'newsletter_title' => ['label' => 'خبرنامه - تیتر', 'type' => 'heading', 'default' => 'یادداشت تازه را|از دست نده'],
                'newsletter_image' => ['label' => 'خبرنامه - تصویر', 'type' => 'image', 'default' => 'assets/img/scenes/temple-gate.webp'],
                'author_avatar' => ['label' => 'تصویر نویسنده', 'type' => 'image', 'default' => 'assets/img/characters/kairo-face-5.webp'],
                'author_bio' => ['label' => 'معرفی نویسنده', 'type' => 'textarea', 'default' => 'سازنده‌ی بازی‌های داستانی و اپ‌های آموزشی فارسی برای کودکان؛ بدون تبلیغ و با احترام به حریم خصوصی.'],
            ],
        ],
    ],
];
