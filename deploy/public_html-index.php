<?php

/*
 * Use this file ONLY if the site must live in public_html and you cannot point the
 * domain's document root to micro-studio/public (see README):
 *   1. upload the project to /home/USER/micro-studio
 *   2. copy everything from micro-studio/public/ into public_html/
 *   3. replace public_html/index.php with this file
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$root = __DIR__.'/../micro-studio';

if (file_exists($maintenance = $root.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $root.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $root.'/bootstrap/app.php';
$app->usePublicPath(__DIR__); // uploads and assets live in public_html

$app->handleRequest(Request::capture());
