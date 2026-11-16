<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\DeleteItem;
use App\Mcp\Tools\DescribeContentTypes;
use App\Mcp\Tools\GetActivity;
use App\Mcp\Tools\GetContentRules;
use App\Mcp\Tools\GetItem;
use App\Mcp\Tools\GetSettings;
use App\Mcp\Tools\ListItems;
use App\Mcp\Tools\ProposeContentRule;
use App\Mcp\Tools\RollbackChange;
use App\Mcp\Tools\SaveItem;
use App\Mcp\Tools\SiteOverview;
use App\Mcp\Tools\UpdateSettings;
use App\Mcp\Tools\UploadImage;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Micro Studio Website')]
#[Version('1.0.0')]
#[Instructions(<<<'TXT'
You manage the website of Micro Studio (میکرو استودیو), a Persian (RTL) studio that makes story games and educational apps for children.
Workflow:
1. Call site_overview, then get_content_rules. Follow every approved rule; never invent facts, numbers, reviews or dates.
2. Use describe_content_types / get_settings to learn field formats before writing.
3. Content-calendar items (content_plans) due today are your tasks: set status "in_progress", do the work, then set "done" with a short result_note (and post_id for posts).
4. Blog posts: create with status "draft", then "pending_review" when ready. The owner publishes. If the owner returns a post (status "rejected"), read review_note, fix it and resubmit.
5. Images: create them (e.g. with Canva), then upload_image and use the returned path. Always write Persian alt text.
6. Everything you change is logged; use get_activity / rollback_change to undo your own mistakes.
All visible text must be Persian with Persian digits (except technical names and numeric fields).
TXT)]
class MicroStudioServer extends Server
{
    protected array $tools = [
        SiteOverview::class,
        GetContentRules::class,
        ProposeContentRule::class,
        DescribeContentTypes::class,
        ListItems::class,
        GetItem::class,
        SaveItem::class,
        DeleteItem::class,
        GetSettings::class,
        UpdateSettings::class,
        UploadImage::class,
        GetActivity::class,
        RollbackChange::class,
    ];
}
