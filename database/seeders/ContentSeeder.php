<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\ContentRule;
use App\Models\Faq;
use App\Models\Game;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/** Initial content = exactly what the static template (documents/Template) shows. */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->games();
        $this->services();
        $this->people();
        $this->faqs();
        $this->blocks();
        $this->posts();
        $this->rules();
    }

    protected function games(): void
    {
        $android = ['android'];
        $games = [
            [
                'slug' => 'two-skies', 'title' => 'دو آسمان: کایرو و ایلارا', 'short_title' => 'دو آسمان', 'category' => 'game',
                'summary' => 'پلتفرمر معمایی پیکسل‌آرت دو شخصیتی با حس نوستالژیک و جهانی زنده.',
                'tagline' => 'پلتفرمر معمایی پیکسل‌آرت دو شخصیتی؛ کایرو و ایلارا را زیر دو آسمان همراهی کن.',
                'tags' => [['label' => 'ماجراجویی', 'color' => 'red'], ['label' => 'نقش‌آفرینی', 'color' => 'green']],
                'card_image' => 'assets/img/scenes/sunset-cliff.webp', 'hero_image' => 'assets/img/scenes/hero-two-skies.webp',
                'hero_character' => 'assets/img/characters/kairo.webp', 'repo_url' => 'https://github.com/mojtaba-pourasghar/two-skies',
                'genre' => 'پلتفرمر معمایی پیکسل‌آرت', 'status' => 'in_development',
                'characters' => [
                    ['name' => 'کایرو', 'role' => 'قهرمان دو آسمان', 'image' => 'assets/img/characters/kairo.webp'],
                    ['name' => 'ایلارا', 'role' => 'همسفر کایرو', 'image' => 'assets/img/characters/ilara.webp'],
                ],
                'screenshots' => [
                    ['image' => 'assets/img/scenes/hero-two-skies.webp', 'caption' => 'زیر دو آسمان'],
                    ['image' => 'assets/img/scenes/sunset-cliff.webp', 'caption' => 'صخره‌ی غروب'],
                    ['image' => 'assets/img/scenes/night-portal.webp', 'caption' => 'دروازه‌ی شب'],
                ],
            ],
            [
                'slug' => 'razino', 'title' => 'رازینو: ماجرای آغاز', 'short_title' => 'رازینو', 'category' => 'game', 'is_featured' => true,
                'summary' => 'ماجراجویی معمایی برای ۸ تا ۱۲ سال؛ ده فصل، ۱۶۶ صحنه، ۲۹ معما و ۲۱ پایان.',
                'tagline' => 'ماجراجویی معمایی دوبعدی فارسی برای کودکان ۸ تا ۱۲ سال؛ سرنخ‌ها را پیدا کن، معماها را حل کن و داستان را به یکی از ۲۱ پایان برسان.',
                'description' => '<p>رازینو داستانی ده‌فصلی است که در آن کودک با جست‌وجوی صحنه‌ها، گفت‌وگو با شخصیت‌ها و جمع‌کردن سرنخ‌ها، رازهای جهان بازی را یکی‌یکی باز می‌کند.</p><p>انتخاب‌هایی که در طول داستان انجام می‌دهی مسیر را عوض می‌کند؛ برای همین هر بار بازی‌کردن می‌تواند به پایان تازه‌ای برسد.</p>',
                'story_title' => 'رازی که فقط [[تو]]|[[می‌توانی حل کنی]]', 'story_image' => 'assets/img/scenes/razino-map.webp',
                'story_points' => ['۵۴ انتخاب داستانی که مسیر را تغییر می‌دهد', '۹۶ سرنخ برای کشف', '۴۰ قسمت و ۲۲۳ خط دیالوگ فارسی'],
                'tags' => [['label' => 'معمایی', 'color' => 'blue'], ['label' => 'داستانی', 'color' => 'pink'], ['label' => 'ماجراجویی', 'color' => 'green']],
                'age_range' => '۸ تا ۱۲ سال', 'engine' => 'Godot 4.3 · GDScript', 'language' => 'فارسی (راست‌چین)', 'genre' => 'ماجراجویی معمایی دوبعدی',
                'internet' => 'فقط برای دریافت فصل‌ها', 'status' => 'in_development',
                'download_note' => 'نسخه‌ی اندروید به‌زودی منتشر می‌شود. برای باخبر شدن از انتشار، با ما در تماس باشید.',
                'card_image' => 'assets/img/scenes/razino-screens.webp', 'hero_image' => 'assets/img/scenes/razino-menu.webp',
                'hero_character' => 'assets/img/characters/razino-hero.webp', 'banner_image' => 'assets/img/scenes/puzzle-room.webp',
                'trailer_url' => 'assets/video/trailer.mp4', 'repo_url' => 'https://github.com/mojtaba-pourasghar/razino',
                'store_links' => [['store' => 'کافه‌بازار', 'url' => ''], ['store' => 'مایکت', 'url' => '']],
                'stats' => [['value' => '10', 'label' => 'فصل داستانی'], ['value' => '166', 'label' => 'صحنه'], ['value' => '29', 'label' => 'معما'], ['value' => '21', 'label' => 'پایان متفاوت']],
                'features' => [
                    ['icon' => 'branch', 'title' => 'داستان چندپایانی', 'text' => '۵۴ انتخاب در طول بازی و ۲۱ پایان متفاوت؛ هر بار یک ماجرای تازه.'],
                    ['icon' => 'puzzle', 'title' => 'معما و سرنخ', 'text' => '۲۹ معما و ۹۶ سرنخ که فکر کردن، دقت و استدلال را تمرین می‌دهند.'],
                    ['icon' => 'text', 'title' => 'کاملاً فارسی', 'text' => 'راست‌چین از روز اول، با فونت وزیرمتن و ۲۲۳ خط دیالوگ فارسی.'],
                    ['icon' => 'refresh', 'title' => 'فصل‌های تازه', 'text' => 'فصل‌ها داده هستند، نه کد؛ فصل جدید بدون به‌روزرسانی از فروشگاه اضافه می‌شود.'],
                    ['icon' => 'shield', 'title' => 'بسته‌های امضاشده', 'text' => 'محتوای دانلودی امضا و بررسی می‌شود و هرگز کد اجرا نمی‌کند.'],
                    ['icon' => 'offline', 'title' => 'بازی آفلاین', 'text' => 'بعد از دریافت فصل‌ها، بازی بدون اینترنت اجرا می‌شود.'],
                ],
                'screenshots' => [
                    ['image' => 'assets/img/scenes/razino-menu.webp', 'caption' => 'منوی اصلی'],
                    ['image' => 'assets/img/scenes/razino-map.webp', 'caption' => 'نقشه‌ی جهان'],
                    ['image' => 'assets/img/scenes/razino-board.webp', 'caption' => 'صفحه‌ی معما'],
                    ['image' => 'assets/img/scenes/razino-ui.webp', 'caption' => 'رابط بازی'],
                    ['image' => 'assets/img/scenes/razino-screens.webp', 'caption' => 'صحنه‌های داستان'],
                ],
                'characters' => [
                    ['name' => 'قهرمان', 'role' => 'کاوشگر کنجکاوی که راز را دنبال می‌کند', 'image' => 'assets/img/characters/razino-hero.webp'],
                    ['name' => 'همراه نقشه‌خوان', 'role' => 'راه‌ها و نشانه‌ها را می‌شناسد', 'image' => 'assets/img/characters/razino-companion.webp'],
                    ['name' => 'جادوگر پیر', 'role' => 'معماها را می‌سازد و راهنمایی می‌کند', 'image' => 'assets/img/characters/razino-wizard.webp'],
                    ['name' => 'بازرگان', 'role' => 'سرنخ‌ها را با وسایل عوض می‌کند', 'image' => 'assets/img/characters/razino-merchant.webp'],
                    ['name' => 'نگهبان سایه', 'role' => 'راز را از همه پنهان می‌کند', 'image' => 'assets/img/characters/razino-enemy.webp', 'dark' => true],
                ],
            ],
            [
                'slug' => 'pilo', 'title' => 'سفر پیلو در رنگارستان', 'short_title' => 'پیلو', 'category' => 'game',
                'summary' => 'سفری میان گذشته و حال با لنز زمان؛ ۹ جهان و ۷۲ مرحله، برای ۵ تا ۹ سال.',
                'tags' => [['label' => 'پلتفرمر', 'color' => 'green'], ['label' => 'معمایی', 'color' => 'blue']],
                'age_range' => '۵ تا ۹ سال', 'card_image' => 'assets/img/scenes/pilo-1.webp', 'hero_image' => 'assets/img/scenes/pilo-world.webp',
                'repo_url' => 'https://github.com/mojtaba-pourasghar/pilo', 'genre' => 'پلتفرمر معمایی',
                'stats' => [['value' => '9', 'label' => 'جهان'], ['value' => '72', 'label' => 'مرحله']],
                'screenshots' => [['image' => 'assets/img/scenes/pilo-1.webp', 'caption' => 'رنگارستان'], ['image' => 'assets/img/scenes/pilo-3.webp', 'caption' => 'انتخاب مرحله'], ['image' => 'assets/img/scenes/pilo-world.webp', 'caption' => 'پیلو و دوستش']],
            ],
            [
                'slug' => 'pashmak', 'title' => 'دنیای بازی پشمک', 'short_title' => 'پشمک', 'category' => 'game', 'card_is_phone' => true,
                'summary' => 'کودک روی کاغذ نقاشی می‌کشد، با دوربین اسکن می‌کند و نقاشی‌اش کنار هیولای نارنجی پشمک جان می‌گیرد؛ ۳ تا ۸ سال.',
                'tags' => [['label' => 'خلاقیت', 'color' => 'orange'], ['label' => 'کودکانه', 'color' => 'purple']],
                'age_range' => '۳ تا ۸ سال', 'card_image' => 'assets/img/works/pashmak-shot.webp', 'repo_url' => 'https://github.com/mojtaba-pourasghar/pashmak_game',
            ],
            [
                'slug' => 'popo', 'title' => 'پوپو: ببین، لمس کن، کشف کن!', 'short_title' => 'پوپو', 'category' => 'game', 'card_is_phone' => true,
                'summary' => 'پوپوی روباه با کمک کودک جشن جنگل را از نو می‌سازد؛ ۸ فصل و ۳۲ بخش، ۲ تا ۸ سال.',
                'tags' => [['label' => 'ساختنی', 'color' => 'pink'], ['label' => 'کودکانه', 'color' => 'blue']],
                'age_range' => '۲ تا ۸ سال', 'card_image' => 'assets/img/works/popo-shot.webp', 'repo_url' => 'https://github.com/mojtaba-pourasghar/pokpok',
                'stats' => [['value' => '8', 'label' => 'فصل'], ['value' => '32', 'label' => 'بخش']],
            ],
            [
                'slug' => 'goshyar', 'title' => 'گوش‌یار', 'short_title' => 'گوش‌یار', 'category' => 'edu', 'card_is_phone' => true,
                'summary' => 'تقویت شنیداری، تمرکز و حافظه با صدای حیوان‌ها، خانه، طبیعت و موسیقی؛ ۳ تا ۸ سال.',
                'tags' => [['label' => 'آموزشی', 'color' => 'green'], ['label' => 'شنیداری', 'color' => 'orange']],
                'age_range' => '۳ تا ۸ سال', 'card_image' => 'assets/img/works/goshyar-shot.webp', 'repo_url' => 'https://github.com/mojtaba-pourasghar/goshyar_game',
            ],
            [
                'slug' => 'hoho-farsi-3', 'title' => 'هوهو فارسی کلاس سوم', 'short_title' => 'هوهو', 'category' => 'edu', 'card_is_phone' => true,
                'summary' => 'جغد معلم هوهو ۱۷ درس و ۱۱۹ صفحه‌ی کتاب فارسی سوم را با صدا درس می‌دهد.',
                'tags' => [['label' => 'آموزشی', 'color' => 'purple'], ['label' => 'فارسی', 'color' => 'red']],
                'card_image' => 'assets/img/works/hoho-farsi-shot.webp', 'repo_url' => 'https://github.com/mojtaba-pourasghar/hohoapp_farsi_p3e',
                'stats' => [['value' => '17', 'label' => 'درس'], ['value' => '119', 'label' => 'صفحه']],
            ],
            [
                'slug' => 'hoho-math-3', 'title' => 'هوهو ریاضی کلاس سوم', 'short_title' => 'هوهو', 'category' => 'edu', 'card_is_phone' => true, 'status' => 'designing',
                'summary' => 'اپ ریاضی سوم دبستان با جغد معلم هوهو؛ تمرین، بازی و بازخورد صوتی.',
                'tags' => [['label' => 'آموزشی', 'color' => 'purple'], ['label' => 'ریاضی', 'color' => 'blue']],
                'card_image' => 'assets/img/works/hoho-math-shot.webp', 'repo_url' => 'https://github.com/mojtaba-pourasghar/hohoapp_math_p3e',
            ],
            [
                'slug' => 'hoho-olom-3', 'title' => 'هوهو علوم کلاس سوم', 'short_title' => 'هوهو', 'category' => 'edu', 'card_is_phone' => true,
                'summary' => '۱۴ درس و ۱۲۲ صفحه‌ی علوم سوم با صدا، زوم روی تصویر و آزمون.',
                'tags' => [['label' => 'آموزشی', 'color' => 'purple'], ['label' => 'علوم', 'color' => 'green']],
                'card_image' => 'assets/img/works/hoho-olom-shot.webp', 'repo_url' => 'https://github.com/mojtaba-pourasghar/hohoapp_olom_p3e',
                'stats' => [['value' => '14', 'label' => 'درس'], ['value' => '122', 'label' => 'صفحه']],
            ],
        ];
        foreach ($games as $i => $g) {
            Game::updateOrCreate(['slug' => $g['slug']], $g + [
                'platforms' => $android, 'language' => 'فارسی (راست‌چین)', 'sort_order' => $i + 1,
                'tagline' => $g['summary'],
            ]);
        }
    }

    protected function services(): void
    {
        $rows = [
            ['game', 'ساخت بازی', 'از ایده و سند طراحی تا انتشار در کافه‌بازار؛ پلتفرمر، ماجراجویی معمایی و نقش‌آفرینی با Godot و Flutter.',
                'از ایده و سند طراحی تا انتشار در کافه‌بازار؛ پلتفرمر، ماجراجویی معمایی، بازی داستانی و نقش‌آفرینی با Godot و Flutter.',
                ['سند طراحی بازی و نقشه‌ی مراحل', 'نسخه‌ی قابل بازی (پروتوتایپ) در همان هفته‌های اول', 'تست با کودکان گروه سنی هدف', 'انتشار در فروشگاه و پشتیبانی پس از انتشار'],
                'assets/img/scenes/razino-screens.webp', false, 'assets/img/characters/razino-wizard.webp', 'سفارش ساخت بازی'],
            ['art', 'طراحی شخصیت و هنر', 'شخصیت‌پردازی، برگه‌ی حالت‌ها، پیکسل‌آرت و صحنه‌های نقاشی‌شده؛ هر قهرمان با هویت و داستان خودش.',
                'شخصیت‌پردازی، برگه‌ی حالت‌ها، پیکسل‌آرت و صحنه‌های نقاشی‌شده؛ هر قهرمان با هویت، داستان و حالت‌های چهره‌ی خودش.',
                ['طراحی شخصیت و برگه‌ی حالت‌های چهره', 'پیکسل‌آرت و انیمیشن حرکت', 'پس‌زمینه‌ها و صحنه‌های بازی', 'آیکن، رابط کاربری و تصاویر فروشگاه'],
                'assets/img/scenes/sunset-cliff.webp', false, 'assets/img/characters/razino-companion.webp', 'سفارش طراحی'],
            ['edu', 'اپ آموزشی', 'اپ‌های درسی تعاملی هم‌گام با کتاب‌های دبستان؛ تمرین، بازی و بازخورد صوتی برای یادگیری شاد.',
                'اپ‌های درسی تعاملی هم‌گام با کتاب‌های دبستان؛ مثل هوهو فارسی و علوم کلاس سوم با ۲۴۱ صفحه‌ی درسی صوتی، تمرین و آزمون.',
                ['تبدیل کتاب درسی به درس‌های تعاملی', 'روایت صوتی برای هر صفحه', 'کاربرگ، تمرین و آزمون سطح‌بندی‌شده', 'کار آفلاین، بدون تبلیغ و ردیاب'],
                'assets/img/works/hoho-farsi-shot.webp', true, 'assets/img/characters/razino-girl.webp', 'سفارش اپ آموزشی'],
            ['voice', 'صداگذاری فارسی', 'دوبله، موسیقی و افکت‌های صوتی؛ صدای فارسی طبیعی برای شخصیت‌ها و درس‌ها.',
                'دوبله، موسیقی و افکت‌های صوتی؛ صدای فارسی طبیعی و گرم برای شخصیت‌ها، راوی داستان و معلم درس‌ها.',
                ['دوبله‌ی شخصیت‌ها و روایت فارسی', 'موسیقی متن اختصاصی', 'افکت‌های صوتی رابط و محیط', 'میکس و آماده‌سازی برای موبایل'],
                'assets/img/scenes/puzzle-room.webp', false, 'assets/img/characters/razino-merchant.webp', 'سفارش صداگذاری'],
        ];
        foreach ($rows as $i => [$slug, $title, $summary, $desc, $deliv, $image, $phone, $char, $cta]) {
            Service::updateOrCreate(['slug' => $slug], [
                'title' => $title, 'summary' => $summary, 'description' => $desc, 'deliverables' => $deliv, 'image' => $image,
                'image_is_phone' => $phone, 'character_image' => $char, 'cta_label' => $cta, 'sort_order' => $i + 1,
            ]);
        }
    }

    protected function people(): void
    {
        $team = [
            ['طراحی بازی', 'ایده، داستان، سند طراحی و معماها', 'کارگردانی', 'purple', 'assets/img/characters/kairo-face-2.webp'],
            ['هنر و شخصیت‌پردازی', 'پیکسل‌آرت، صحنه‌ها و انیمیشن', 'هنر', 'pink', 'assets/img/characters/ilara-face-2.webp'],
            ['برنامه‌نویسی', 'Godot، Flutter و تست خودکار', 'فنی', 'blue', 'assets/img/characters/kairo-face-3.webp'],
            ['صدا و محتوای آموزشی', 'دوبله‌ی فارسی، موسیقی و درس‌ها', 'صدا', 'orange', 'assets/img/characters/ilara-face-4.webp'],
        ];
        foreach ($team as $i => [$name, $role, $tag, $color, $avatar]) {
            TeamMember::updateOrCreate(['name' => $name], ['role' => $role, 'tag' => $tag, 'tag_color' => $color, 'avatar' => $avatar, 'sort_order' => $i + 1]);
        }

        $testimonials = [
            ['علی رضایی', 'پدر و گیمر', 'معماهای رازینو واقعاً فکر کردن را یاد می‌دهند؛ بدون تبلیغ و بدون اینترنت، خیالم راحت است.', 'assets/img/characters/kairo-face-5.webp'],
            ['سارا محمدی', 'مربی پیش‌دبستانی', 'دخترم نقاشی‌هایش را با پشمک زنده می‌کند؛ شوقی که در چشمانش است قیمت ندارد.', 'assets/img/characters/ilara-face-5.webp'],
            ['حمید کریمی', 'مدیر مجموعه‌ی آموزشی', 'تیم میکرو استودیو اپ آموزشی ما را از صفر تا انتشار ساخت؛ دقیق، خوش‌قول و خلاق.', 'assets/img/characters/kairo-face-1.webp'],
            ['مریم احمدی', 'مادر دانش‌آموز سوم', 'پسرم هر شب درس فارسی فردا را با هوهو گوش می‌دهد؛ صدای گرم معلم را از حفظ شده!', 'assets/img/characters/ilara-face-1.webp'],
        ];
        foreach ($testimonials as $i => [$name, $role, $quote, $avatar]) {
            Testimonial::updateOrCreate(['name' => $name], ['role' => $role, 'quote' => $quote, 'avatar' => $avatar, 'sort_order' => $i + 1]);
        }
    }

    protected function faqs(): void
    {
        $services = [
            ['ساخت یک بازی چقدر زمان می‌برد؟', 'به اندازه‌ی بازی بستگی دارد. یک بازی کوچک آموزشی معمولاً چند هفته و یک ماجراجویی داستانی چندفصلی چند ماه زمان می‌برد. بعد از جلسه‌ی اول، زمان‌بندی دقیق مرحله‌به‌مرحله را دریافت می‌کنید.'],
            ['هزینه‌ی پروژه چطور محاسبه می‌شود؟', 'بر اساس دامنه‌ی کار: تعداد مراحل یا درس‌ها، حجم هنر و انیمیشن و میزان صداگذاری. پس از جلسه‌ی مشاوره‌ی رایگان، پیشنهاد مکتوب با هزینه‌ی هر مرحله ارسال می‌کنیم.'],
            ['آیا فقط یک بخش، مثلاً طراحی شخصیت را سفارش دهم؟', 'بله. هر چهار خدمت را می‌توانید جداگانه سفارش دهید؛ مثلاً فقط شخصیت‌پردازی و برگه‌ی حالت‌ها یا فقط دوبله‌ی فارسی.'],
            ['برای چه پلتفرم‌هایی می‌سازید؟', 'تمرکز فعلی ما اندروید (موبایل و تبلت) است. با Godot و Flutter امکان خروجی وب و پلتفرم‌های دیگر هم وجود دارد.'],
            ['مالکیت کد و هنر پروژه با کیست؟', 'در پروژه‌های سفارشی، مالکیت خروجی نهایی طبق قرارداد به سفارش‌دهنده منتقل می‌شود. جزئیات پیش از شروع کار مکتوب می‌شود.'],
            ['بعد از انتشار هم پشتیبانی می‌کنید؟', 'بله؛ رفع اشکال، به‌روزرسانی برای نسخه‌های جدید اندروید و افزودن محتوای تازه (مثل فصل جدید) را پس از انتشار هم انجام می‌دهیم.'],
        ];
        $contact = [
            ['چقدر طول می‌کشد تا پاسخ بگیرم؟', 'به همه‌ی پیام‌ها ظرف ۲۴ ساعت کاری پاسخ می‌دهیم؛ گزارش‌های مشکل بازی‌ها در اولویت بررسی می‌شوند.'],
            ['بازی‌ها را از کجا دانلود کنم؟', 'لینک دریافت هر بازی در صفحه‌ی همان بازی قرار دارد. بازی‌های منتشرشده از فروشگاه‌های اندرویدی ایرانی قابل دریافت‌اند.'],
            ['آیا بازی‌ها به اینترنت نیاز دارند؟', 'خیر؛ بازی‌ها و اپ‌های ما بعد از نصب بدون اینترنت کار می‌کنند. فقط دریافت محتوای تازه (مثل فصل جدید رازینو) به اینترنت نیاز دارد.'],
            ['آیا اطلاعاتی از کودک جمع‌آوری می‌کنید؟', 'خیر. بازی‌ها حساب کاربری و ردیاب ندارند و پیشرفت بازی فقط روی همان دستگاه ذخیره می‌شود.'],
            ['برای سفارش پروژه چه اطلاعاتی بفرستم؟', 'ایده‌ی کلی، گروه سنی مخاطب، هدف (سرگرمی یا آموزشی)، پلتفرم و زمان‌بندی مدنظر. اگر نمونه‌ی مشابهی در ذهن دارید، آن را هم بنویسید.'],
            ['آیا امکان همکاری یا عضویت در تیم هست؟', 'بله؛ در فرم بالا موضوع «همکاری و استخدام» را انتخاب کنید و نمونه‌کار یا رزومه‌تان را برایمان بفرستید.'],
        ];
        foreach (['services' => $services, 'contact' => $contact] as $page => $rows) {
            foreach ($rows as $i => [$q, $a]) {
                Faq::updateOrCreate(['page' => $page, 'question' => $q], ['answer' => $a, 'sort_order' => $i + 1]);
            }
        }
    }

    protected function blocks(): void
    {
        $groups = [
            'honors' => [
                ['title' => 'بازی و اپ منتشرشده و در حال ساخت', 'value' => '9', 'label' => 'نمونه‌کار'],
                ['title' => 'مرحله‌ی طراحی‌شده در سفر پیلو', 'value' => '72', 'label' => 'پلتفرمر'],
                ['title' => 'صحنه‌ی داستانی در ده فصل رازینو', 'value' => '166', 'label' => 'ماجراجویی'],
                ['title' => 'صفحه‌ی درسی صوتی در هوهو فارسی و علوم', 'value' => '241', 'label' => 'آموزشی'],
            ],
            'promises' => [
                ['title' => 'بدون تبلیغ', 'text' => 'هیچ تبلیغ ناگهانی و پاپ‌آپی وسط بازی کودک ظاهر نمی‌شود.', 'icon' => 'noads'],
                ['title' => 'آفلاین', 'text' => 'بعد از نصب، بدون اینترنت کار می‌کند؛ در سفر، مدرسه یا خانه.', 'icon' => 'offline'],
                ['title' => 'فارسی و راست‌چین', 'text' => 'متن، رابط و صداگذاری از روز اول فارسی طراحی شده است.', 'icon' => 'text'],
                ['title' => 'حریم خصوصی کودک', 'text' => 'بدون ردیاب و حساب کاربری؛ داده‌ها فقط روی دستگاه می‌مانند.', 'icon' => 'shield'],
            ],
            'values' => [
                ['title' => 'فارسی از روز اول', 'text' => 'رابط راست‌چین، متن روان و صداگذاری فارسی؛ نه ترجمه‌ی بعد از ساخت.', 'icon' => 'text'],
                ['title' => 'بدون تبلیغ مزاحم', 'text' => 'کودک وسط بازی با تبلیغ و پیشنهاد خرید ناگهانی روبه‌رو نمی‌شود.', 'icon' => 'noads'],
                ['title' => 'حریم خصوصی کودک', 'text' => 'بدون ردیاب و حساب کاربری؛ داده‌ها روی دستگاه می‌ماند و بخش والدین قفل است.', 'icon' => 'shield'],
                ['title' => 'یادگیری با بازی', 'text' => 'معما، داستان و تمرین طوری طراحی می‌شوند که یادگرفتن حس بازی بدهد.', 'icon' => 'bulb'],
                ['title' => 'تست با کودکان', 'text' => 'هر نسخه پیش از انتشار با کودکان گروه سنی هدف آزموده می‌شود.', 'icon' => 'test'],
                ['title' => 'شخصیت و قصه', 'text' => 'هر بازی قهرمان، جهان و داستان خودش را دارد؛ از کایرو و ایلارا تا جغد معلم هوهو.', 'icon' => 'heart'],
            ],
            'steps' => [
                ['title' => 'ایده', 'text' => 'گفت‌وگو درباره‌ی مخاطب، هدف آموزشی و حس بازی؛ و نوشتن سند طراحی.', 'value' => 'خروجی: سند طراحی'],
                ['title' => 'طراحی', 'text' => 'شخصیت‌ها، صحنه‌ها و نقشه‌ی مراحل؛ همراه با یک پروتوتایپ ساده برای تست.', 'value' => 'خروجی: پروتوتایپ'],
                ['title' => 'ساخت', 'text' => 'برنامه‌نویسی، هنر نهایی، صداگذاری فارسی و تست مداوم با کودکان.', 'value' => 'خروجی: نسخه‌ی آزمایشی'],
                ['title' => 'انتشار', 'text' => 'آماده‌سازی فروشگاه، انتشار در کافه‌بازار و پشتیبانی و به‌روزرسانی.', 'value' => 'خروجی: بازی منتشرشده'],
            ],
            'tools' => [
                ['title' => 'Godot Engine', 'text' => 'موتور بازی‌های داستانی و پلتفرمر مثل رازینو و سفر پیلو.', 'icon' => 'pad'],
                ['title' => 'Flutter', 'text' => 'اپ‌های آموزشی سریع و روان برای اندروید، مثل هوهو.', 'icon' => 'code'],
                ['title' => 'پیکسل‌آرت و نقاشی دیجیتال', 'text' => 'شخصیت‌ها و صحنه‌های دو آسمان و رازینو.', 'icon' => 'brush'],
                ['title' => 'استودیوی صدا', 'text' => 'ضبط، دوبله و میکس صدای فارسی شخصیت‌ها و درس‌ها.', 'icon' => 'mic'],
                ['title' => 'تست خودکار و تست با کودکان', 'text' => 'کیفیت فنی و تجربه‌ی واقعی بازی، هر دو سنجیده می‌شوند.', 'icon' => 'test'],
                ['title' => 'انتشار در کافه‌بازار', 'text' => 'آماده‌سازی صفحه‌ی فروشگاه، تصاویر و به‌روزرسانی‌ها.', 'icon' => 'store'],
            ],
            'support' => [
                ['title' => 'حریم خصوصی کودکان', 'text' => 'بازی‌های ما ردیاب، تبلیغ و حساب کاربری ندارند و داده‌ها روی دستگاه می‌مانند. برای هر پرسشی درباره‌ی داده‌ها به ما بنویسید.', 'icon' => 'shield', 'link_label' => 'پرسش درباره‌ی حریم خصوصی', 'link_url' => 'mailto:mojtaba.pourasghar@gmail.com?subject=Privacy'],
                ['title' => 'راهنمای والدین', 'text' => 'گروه سنی هر بازی، زمان پیشنهادی بازی و بخش قفل‌شده‌ی والدین را در صفحه‌ی هر بازی ببینید.', 'icon' => 'users', 'link_label' => 'بازی‌ها و گروه سنی', 'link_url' => '/games'],
                ['title' => 'گزارش مشکل', 'text' => 'نام بازی، مدل گوشی و توضیح کوتاهی از مشکل را بفرستید؛ اگر بتوانید، یک عکس از صفحه هم ضمیمه کنید.', 'icon' => 'bug', 'link_label' => 'ارسال گزارش', 'link_url' => 'mailto:mojtaba.pourasghar@gmail.com?subject=Bug%20report'],
            ],
            'contact_steps' => [
                ['title' => '۱. پیام شما را می‌خوانیم', 'text' => 'و ظرف ۲۴ ساعت کاری پاسخ می‌دهیم.'],
                ['title' => '۲. جلسه‌ی مشاوره‌ی رایگان', 'text' => 'ایده، مخاطب و دامنه‌ی کار را با هم مرور می‌کنیم.'],
                ['title' => '۳. پیشنهاد مکتوب', 'text' => 'زمان‌بندی و هزینه‌ی هر مرحله را دریافت می‌کنید.'],
            ],
        ];
        foreach ($groups as $group => $rows) {
            foreach ($rows as $i => $row) {
                Block::updateOrCreate(['group' => $group, 'title' => $row['title']], $row + ['sort_order' => $i + 1]);
            }
        }
    }

    protected function posts(): void
    {
        $cats = [];
        foreach ([['devlog', 'یادداشت ساخت'], ['art', 'هنر و طراحی'], ['edu', 'آموزش'], ['parents', 'والدین']] as $i => [$slug, $name]) {
            $cats[$slug] = PostCategory::updateOrCreate(['slug' => $slug], ['name' => $name, 'sort_order' => $i + 1])->id;
        }
        $game = fn (string $slug) => Game::where('slug', $slug)->value('id');

        $razinoBody = <<<'HTML'
<p class="prose-lead">رازینو بازی ماجراجویی معمایی دوبعدی فارسی برای کودکان ۸ تا ۱۲ سال است که با Godot 4.3 برای اندروید ساخته می‌شود. در این یادداشت توضیح می‌دهیم چرا فصل‌ها را «داده» ساختیم و نه «کد»، و این تصمیم چه چیزی را ممکن کرد.</p>
<h2 id="data-not-code">فصل‌ها داده هستند، نه کد</h2>
<p>موتور رازینو فقط واژگان بسته‌ای از شرط‌ها و اثرها را می‌فهمد و چیز دیگری را اجرا نمی‌کند. هر فصل یک فایل داده است که صحنه‌ها، گفت‌وگوها، انتخاب‌ها، معماها و سرنخ‌هایش را توصیف می‌کند.</p>
<p>نتیجه‌ی مهم این تصمیم: نویسنده می‌تواند <strong>فصل یازدهم را بدون انتشار نسخه‌ی تازه در فروشگاه</strong> اضافه کند؛ بازیکن فقط بسته‌ی فصل را دریافت می‌کند.</p>
<figure class="prose-figure"><img src="assets/img/scenes/razino-map.webp" alt="نقشه‌ی جهان بازی رازینو" width="1200" height="800" loading="lazy"><figcaption>نقشه‌ی جهان رازینو.</figcaption></figure>
<h2 id="by-numbers">رازینو در عدد</h2>
<p>هر ده فصل به‌صورت داده‌ی اعتبارسنجی‌شده آماده است:</p>
<ul class="prose-stats"><li><b>۱۶۶</b><span>صحنه</span></li><li><b>۴۰</b><span>قسمت</span></li><li><b>۲۲۳</b><span>خط دیالوگ</span></li><li><b>۵۴</b><span>انتخاب</span></li><li><b>۲۹</b><span>معما</span></li><li><b>۹۶</b><span>سرنخ</span></li><li><b>۲۱</b><span>پایان</span></li></ul>
<h2 id="signed-packages">بسته‌های امضاشده و امن</h2>
<p>چون موتور هیچ کدی از بسته‌ها اجرا نمی‌کند، یک بسته‌ی دانلودشده <strong>هرگز نمی‌تواند کد اجرا کند</strong>. علاوه بر این، هر بسته امضا می‌شود و دریافت آن این مراحل را طی می‌کند:</p>
<ol><li>دریافت بسته</li><li>بررسی hash</li><li>بررسی امضا</li><li>فعال‌سازی اتمیک (یا همه‌چیز، یا هیچ‌چیز)</li><li>اجرای آفلاین</li></ol>
<blockquote><p>کلید عمومی امضای محتوا عمداً در مخزن هست؛ نیمه‌ی خصوصی آن هرگز.</p></blockquote>
<h2 id="testing">تست، قبل از هر چیز</h2>
<p>برای اینکه محتوای کودکان خطا نداشته باشد، بازی ۷۸ تست با ۸۴۷ assertion و ۱۹ بررسی یکپارچگی دانلود دارد که همه سبز هستند. هر ۱۶ صفحه‌ی سند طراحی هم راست‌چین و با فونت وزیرمتن پیاده شده است.</p>
<h2 id="whats-next">قدم‌های بعدی</h2>
<p>با شفافیت بگوییم: گرافیک نهایی هنوز آماده نیست و پس‌زمینه‌ها موقت هستند، صداگذاری ۲۵۰ خط هنوز ضبط نشده و خروجی اندروید هم هنوز ساخته نشده است. این‌ها کارهای فاز بعدی هستند و به‌محض آماده‌شدن، همین‌جا خبر می‌دهیم.</p>
<p><a href="/games/razino">صفحه‌ی بازی رازینو را ببینید</a> یا برای اطلاع از انتشار <a href="/contact">با ما در تماس باشید</a>.</p>
HTML;

        $posts = [
            ['razino-new-chapters', 'رازینو چطور فصل تازه می‌گیرد؟', 'فصل‌های رازینو داده هستند، نه کد. این یعنی فصل یازدهم بدون انتشار نسخه‌ی تازه در فروشگاه اضافه می‌شود؛ و یک بسته‌ی دانلودی هرگز نمی‌تواند کدی اجرا کند.', 'devlog', 'razino', 'assets/img/scenes/razino-board.webp', '2026-11-10', true, $razinoBody, ['رازینو', 'Godot', 'یادداشت_ساخت']],
            ['pilo-time-lens', 'لنز زمان: طراحی معماهای گذشته و حال در سفر پیلو', 'پلی که در گذشته سالم بوده، جوانه‌ای که در حال درخت شده؛ چطور ۷۲ مرحله را با یک ایده‌ی ساده ساختیم.', 'devlog', 'pilo', 'assets/img/scenes/pilo-world.webp', '2026-11-03', false, null, ['پیلو']],
            ['two-skies-pixel-art', 'پیکسل‌آرت دو آسمان: از برگه‌ی حالت تا صحنه', 'شش حالت چهره برای کایرو و ایلارا؛ چرا احساسات شخصیت‌ها را در چند پیکسل جا دادیم.', 'art', 'two-skies', 'assets/img/scenes/sunset-cliff.webp', '2026-10-27', false, null, ['دو_آسمان', 'پیکسل_آرت']],
            ['hoho-farsi-119-pages', '۱۱۹ صفحه با صدای هوهو: کتاب فارسی سوم چطور اپ شد', 'جغد معلم هر صفحه‌ی کتاب را درس می‌دهد؛ از بریدن تصویرهای کتاب تا کاربرگ سه‌سطحی.', 'edu', 'hoho-farsi-3', 'assets/img/works/hoho-farsi-shot.webp', '2026-10-20', false, null, ['هوهو']],
            ['ad-free-games-parents', 'بازی بدون تبلیغ و ردیاب: والدین به چه چیزی نگاه کنند؟', 'دروازه‌ی والدین، داده‌ی فقط‌روی‌دستگاه و بازی آفلاین؛ چک‌لیست ساده برای انتخاب بازی کودک.', 'parents', null, 'assets/img/works/goshyar-shot.webp', '2026-10-13', false, null, ['والدین']],
            ['popo-173-cartoons', '۱۷۳ کارتون زنده با کد: انیمیشن‌های پوپو', 'هر شیء نفس می‌کشد، پلک می‌زند و چهار حال دارد؛ همه با کد کشیده شده‌اند.', 'art', 'popo', 'assets/img/works/popo-shot.webp', '2026-10-06', false, null, ['پوپو']],
            ['making-pashmak', 'نقاشی روی کاغذ، جان‌گرفتن روی تبلت: ساخت پشمک', 'کودک نقاشی می‌کشد، دوربین آن را اسکن می‌کند و نقاشی کنار پشمک زنده می‌شود.', 'devlog', 'pashmak', 'assets/img/works/pashmak-shot.webp', '2026-09-29', false, null, ['پشمک']],
        ];
        foreach ($posts as [$slug, $title, $excerpt, $cat, $gameSlug, $cover, $date, $featured, $body, $tags]) {
            Post::updateOrCreate(['slug' => $slug], [
                'title' => $title, 'excerpt' => $excerpt, 'body' => $body ?? '<p class="prose-lead">'.e($excerpt).'</p>',
                'post_category_id' => $cats[$cat], 'game_id' => $gameSlug ? $game($gameSlug) : null, 'cover_image' => $cover,
                'status' => 'published', 'published_at' => $date.' 10:00:00', 'is_featured' => $featured, 'tags' => $tags,
            ]);
        }
    }

    protected function rules(): void
    {
        $rules = [
            ['زبان و لحن', 'همه‌ی محتوا فارسی روان، گرم و مناسب خانواده باشد؛ اعداد با رقم فارسی، نام‌های فنی (Godot، Flutter) لاتین بمانند. از اغراق تبلیغاتی و صفت‌های عالی بی‌پشتوانه پرهیز شود.'],
            ['فقط واقعیت', 'هر عدد یا ادعا درباره‌ی بازی‌ها باید از اطلاعات ثبت‌شده در سایت یا مخزن گیت‌هاب همان بازی باشد؛ عدد، جایزه، نظر کاربر یا تاریخ ساختگی ممنوع است.'],
            ['کودک و حریم خصوصی', 'محتوا برای کودکان و والدین امن باشد؛ هیچ داده‌ی شخصی کودک منتشر نشود و تصویر کودک واقعی بدون رضایت کتبی والدین استفاده نشود.'],
            ['تصاویر', 'تصاویر با سبک بصری سایت هماهنگ باشند (رنگ اصلی بنفش #7457ff، فضای شب سرمه‌ای، کاراکترهای بازی‌ها)؛ برای هر تصویر متن جایگزین (alt) فارسی نوشته شود.'],
            ['انتشار', 'هر مطلب وبلاگ پیش از انتشار باید توسط مدیر سایت تأیید شود؛ ایجنت فقط پیش‌نویس می‌سازد و برای تأیید می‌فرستد.'],
        ];
        foreach ($rules as $i => [$title, $body]) {
            ContentRule::updateOrCreate(['title' => $title], ['body' => $body, 'status' => 'proposed', 'proposed_by' => 'system', 'sort_order' => $i + 1]);
        }
    }
}
