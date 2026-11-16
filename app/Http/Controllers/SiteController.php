<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Game;
use App\Models\NewsletterSubscriber;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SiteController extends Controller
{
    protected function blocks(string $group)
    {
        return Block::where('group', $group)->visible()->get();
    }

    /** @return array{all:int, game:int, edu:int} */
    protected function gameCounts(): array
    {
        $counts = Game::where('is_published', true)->selectRaw('category, count(*) as c')->groupBy('category')->pluck('c', 'category');

        return ['all' => (int) $counts->sum(), 'game' => (int) ($counts['game'] ?? 0), 'edu' => (int) ($counts['edu'] ?? 0)];
    }

    public function home()
    {
        return view('pages.home', [
            'services' => Service::visible()->get(),
            'games' => Game::visible()->get(),
            'honors' => $this->blocks('honors'),
            'testimonials' => Testimonial::visible()->get(),
        ]);
    }

    public function games()
    {
        $games = Game::visible()->get();

        return view('pages.games', [
            'games' => $games,
            'counts' => $this->gameCounts(),
            'spotlight' => $games->firstWhere('is_featured', true) ?? $games->first(),
            'promises' => $this->blocks('promises'),
        ]);
    }

    public function game(Game $game)
    {
        abort_unless($game->is_published || auth()->check(), 404);

        return view('pages.game', [
            'game' => $game,
            'related' => Game::visible()->where('id', '!=', $game->id)
                ->orderByRaw('category = ? desc', [$game->category])->limit(3)->get(),
        ]);
    }

    public function services()
    {
        return view('pages.services', [
            'services' => Service::visible()->get(),
            'steps' => $this->blocks('steps'),
            'tools' => $this->blocks('tools'),
            'faqs' => Faq::where('page', 'services')->visible()->get(),
        ]);
    }

    public function about()
    {
        return view('pages.about', [
            'values' => $this->blocks('values'),
            'team' => TeamMember::visible()->get(),
            'honors' => $this->blocks('honors'),
            'testimonials' => Testimonial::visible()->get(),
            'counts' => $this->gameCounts(),
        ]);
    }

    public function contact()
    {
        return view('pages.contact', [
            'steps' => $this->blocks('contact_steps'),
            'support' => $this->blocks('support'),
            'faqs' => Faq::where('page', 'contact')->visible()->get(),
        ]);
    }

    public function storeContact(Request $request)
    {
        if ($request->filled('website')) { // honeypot
            return $this->contactResponse($request);
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9۰-۹+\s()\-]{8,}$/u'],
            'email' => ['required', 'email', 'max:190'],
            'subject' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'newsletter' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'لطفاً نام خود را وارد کنید.',
            'email.required' => 'ایمیل را وارد کنید.',
            'email.email' => 'ایمیل معتبر نیست.',
            'message.required' => 'پیام را بنویسید.',
            'message.min' => 'پیام باید دست‌کم ۱۰ حرف باشد.',
            'phone.regex' => 'شماره تماس معتبر نیست.',
        ]);
        $msg = ContactMessage::create($data + ['newsletter' => $request->boolean('newsletter'), 'ip' => $request->ip()]);
        if ($msg->newsletter) {
            NewsletterSubscriber::firstOrCreate(['email' => mb_strtolower($msg->email)]);
        }
        if ($to = setting('contact.notify_email')) {
            try {
                Mail::raw("پیام تازه از {$msg->name} <{$msg->email}>\nموضوع: {$msg->subject}\n\n{$msg->message}", fn ($m) => $m->to($to)->subject('پیام تازه از فرم تماس سایت'));
            } catch (\Throwable $e) {
                Log::warning('Contact notification failed: '.$e->getMessage());
            }
        }

        return $this->contactResponse($request);
    }

    protected function contactResponse(Request $request)
    {
        $text = 'درخواست شما ثبت شد؛ به‌زودی با شما تماس می‌گیریم.';

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'message' => $text])
            : back()->with('contact_ok', true)->withFragment('contact-form');
    }

    public function storeNewsletter(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:190']], ['email.*' => 'لطفاً یک ایمیل معتبر وارد کنید.']);
        NewsletterSubscriber::firstOrCreate(['email' => mb_strtolower($data['email'])]);
        $text = 'عضویت شما ثبت شد؛ یادداشت بعدی را برایتان می‌فرستیم.';

        return $request->expectsJson() ? response()->json(['ok' => true, 'message' => $text]) : back()->with('newsletter_ok', $text);
    }

    public function blog(Request $request)
    {
        $query = Post::published()->with('category');
        if ($cat = $request->string('cat')->toString()) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $cat));
        }
        if ($q = trim($request->string('q')->toString())) {
            $query->where(fn ($w) => $w->where('title', 'like', "%$q%")->orWhere('excerpt', 'like', "%$q%")->orWhere('tags', 'like', "%$q%"));
        }
        $filtered = $cat || $q;
        $featured = $filtered ? null : Post::published()->with('category')->where('is_featured', true)->first();
        if ($featured) {
            $query->where('id', '!=', $featured->id);
        }

        return view('pages.blog', [
            'featured' => $featured,
            'posts' => $query->paginate(9)->withQueryString(),
            'categories' => PostCategory::orderBy('sort_order')->get(),
        ]);
    }

    public function post(Post $post)
    {
        $visible = $post->status === 'published' && $post->published_at && $post->published_at->isPast();
        abort_unless($visible || auth()->check(), 404); // admins can preview drafts

        return view('pages.post', [
            'post' => $post->load('category', 'game'),
            'related' => Post::published()->with('category')->where('id', '!=', $post->id)
                ->orderByRaw('post_category_id = ? desc', [$post->post_category_id])->limit(3)->get(),
        ]);
    }

    public function sitemap()
    {
        $urls = collect([route('home'), route('games.index'), route('services'), route('about'), route('contact'), route('blog.index')])
            ->map(fn ($u) => ['loc' => $u, 'lastmod' => null]);
        Game::visible()->get(['slug', 'updated_at'])->each(fn ($g) => $urls->push(['loc' => route('games.show', $g), 'lastmod' => $g->updated_at]));
        Post::published()->get(['slug', 'updated_at'])->each(fn ($p) => $urls->push(['loc' => route('blog.show', $p), 'lastmod' => $p->updated_at]));
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $u) {
            $xml .= '<url><loc>'.e($u['loc']).'</loc>'.($u['lastmod'] ? '<lastmod>'.$u['lastmod']->toAtomString().'</lastmod>' : '').'</url>';
        }

        return response($xml.'</urlset>', 200, ['Content-Type' => 'application/xml']);
    }
}
