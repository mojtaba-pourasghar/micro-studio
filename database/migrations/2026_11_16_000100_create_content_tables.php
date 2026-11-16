<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * All site content. Every section of the template maps to one of these tables
 * (see documents/design-language.md). Image columns store a public path such as
 * "assets/img/scenes/x.webp" (template asset) or "uploads/2026/11/x.webp" (media library).
 */
return new class extends Migration
{
    public function up(): void
    {
        // key/value settings: contact info, socials, hero texts, section headings…
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('short_title');
            $table->string('category')->default('game'); // game | edu
            $table->string('tagline')->nullable();        // hero lead
            $table->text('summary')->nullable();          // card text
            $table->longText('description')->nullable();  // story section (HTML)
            $table->json('tags')->nullable();             // [{label, color}]
            $table->string('age_range')->nullable();
            $table->json('platforms')->nullable();        // ["android","web"]
            $table->string('engine')->nullable();
            $table->string('language')->nullable();
            $table->string('status')->default('released'); // released | in_development | designing
            $table->string('card_image')->nullable();
            $table->boolean('card_is_phone')->default(false);
            $table->string('hero_image')->nullable();
            $table->string('hero_character')->nullable();
            $table->string('trailer_url')->nullable();
            $table->string('banner_image')->nullable();   // trailer banner background
            $table->string('genre')->nullable();
            $table->string('internet')->nullable();       // e.g. "only for downloading chapters"
            $table->string('story_title')->nullable();    // heading syntax: [[outline]] {{gradient}} | = line break
            $table->string('story_image')->nullable();
            $table->json('story_points')->nullable();     // ["..."] check-list under the story
            $table->text('download_note')->nullable();
            $table->string('repo_url')->nullable();
            $table->json('store_links')->nullable();      // [{store, url}]
            $table->json('stats')->nullable();            // [{value, label}]
            $table->json('features')->nullable();         // [{title, text}]
            $table->json('screenshots')->nullable();      // [{image, caption}]
            $table->json('characters')->nullable();       // [{name, role, image}]
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('summary')->nullable();          // short (home slider)
            $table->text('description')->nullable();      // long (services page)
            $table->json('deliverables')->nullable();     // ["..."]
            $table->string('image')->nullable();
            $table->boolean('image_is_phone')->default(false);
            $table->string('character_image')->nullable();
            $table->string('cta_label')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('tag')->nullable();
            $table->string('tag_color')->default('purple');
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role')->nullable();
            $table->text('quote');
            $table->string('avatar')->nullable();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('page')->default('contact'); // services | contact
            $table->string('question');
            $table->text('answer');
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // small repeatable cards: honors, promises, values, process steps, tools, parent support
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->string('group'); // honors | promises | values | steps | tools | support
            $table->string('title');
            $table->text('text')->nullable();
            $table->string('value')->nullable();  // number for honors, output for steps
            $table->string('label')->nullable();  // honors note
            $table->string('icon')->nullable();   // sprite id without "i-"
            $table->string('link_label')->nullable();
            $table->string('link_url')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable(); // sanitized HTML
            $table->string('cover_image')->nullable();
            $table->foreignId('post_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('game_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author')->default('تیم میکرو استودیو');
            $table->unsignedSmallInteger('read_minutes')->nullable();
            $table->json('tags')->nullable();
            // draft -> pending_review -> published (only a human publishes) | rejected
            $table->string('status')->default('draft');
            $table->text('review_note')->nullable();
            $table->string('created_by')->default('admin'); // admin | agent
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'published_at']);
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique(); // public path, e.g. uploads/2026/11/x.webp
            $table->string('original_name')->nullable();
            $table->string('alt')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('source')->default('admin'); // admin | agent | canva
            $table->timestamps();
        });

        // rules every piece of content must follow; the owner approves them
        Schema::create('content_rules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('status')->default('proposed'); // proposed | approved | archived
            $table->string('proposed_by')->default('admin');
            $table->timestamp('approved_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // content calendar: what the agent should produce and when
        Schema::create('content_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('brief')->nullable();
            $table->string('type')->default('post'); // post | game_update | image | other
            $table->foreignId('game_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('post_category_id')->nullable()->constrained()->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->string('status')->default('planned'); // planned | in_progress | done | skipped
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->text('result_note')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->boolean('newsletter')->default(false);
            $table->string('ip')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamps();
        });

        // tokens for the AI agent (MCP + REST); only the SHA-256 hash is stored
        Schema::create('agent_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('token_hash', 64)->unique();
            $table->json('abilities')->nullable(); // null = all non-restricted abilities
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        // every change (agent or admin) with a "before" snapshot for rollback
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor');       // agent:<token name> | admin:<email>
            $table->string('action');      // created | updated | deleted | restored | uploaded | …
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('summary')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        foreach (['activity_logs', 'agent_tokens', 'newsletter_subscribers', 'contact_messages', 'content_plans', 'content_rules',
            'media', 'posts', 'post_categories', 'blocks', 'faqs', 'testimonials', 'team_members', 'services', 'games', 'settings'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
