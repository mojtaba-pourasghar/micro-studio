# Micro Studio website: standing instructions for Claude

Laravel 12 + Filament site for Micro Studio (میکرو استودیو). Persian, RTL. The owner runs it on cPanel in Iran.

## Owner's standing instructions
- **Claude manages every section of the site**: games, services, pages, settings, blog, SEO, media. Use the site's MCP tools (`site_overview`, `get_content_rules`, `save_item`, `upload_image`, …) or edit the repo.
- **Articles**: write them with care (real facts only, from the site and the game repos; no invented numbers, quotes or claims). Send new articles with `status=pending_review` and tell the owner what is waiting for approval; the owner decides whether a token may publish (the `posts.publish` ability in the admin). Never claim the apps are ad-free (some show ads).
- **All images, covers and posters are made in Canva**: 2D cartoon style, brand colours (purple `#7457ff`, orange, night navy), the games' own characters as references. No Persian text inside images (Canva garbles it). Generate at the aspect ratio the slot needs (square for cards/story sections, 16:9 for wide art) and never stretch or squash. The studio logo is stamped automatically on upload (`App\Support\Watermark`; `php artisan ms:watermark` for files in the repo).
- Only **final** video clips go on the site.
- Store links: **Cafe Bazaar only** (no Myket).
- Keep secrets (sms.ir key, Gemini keys, tokens) in `.env` only, never in git.

## Practical notes
- Canva downloads come from `export-download.canva.com`; uploads go to `www.canva.com` (both allowed in the `micro-studio` cloud environment). A design's custom size is often ignored, so put square images in an "Instagram Post (Square)" design and check the exported size before using it.
- Game content lives in `database/seeders/GameContentSeeder.php`; full article texts in `ArticleSeeder.php`. Data changes for the live site ship as migrations (`php artisan migrate --force` on the server).
- Run `php artisan test` before pushing; commit to `master`.
- The motion/trailer build scripts are in `documents/motion/`; the agent guide (Persian) is `documents/claude-agent.md`.
