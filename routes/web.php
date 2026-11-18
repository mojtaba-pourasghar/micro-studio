<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/captcha/{key}.png', \App\Http\Controllers\CaptchaController::class)->middleware('throttle:60,1')->name('captcha');

// lets an open admin tab notice that its session expired (see resources/views/filament/session-guard.blade.php)
Route::get('/session/alive', fn () => response()->json(['auth' => auth()->check()])->header('Cache-Control', 'no-store'))->name('session.alive');

Route::controller(\App\Http\Controllers\SeoController::class)->group(function () {
    Route::get('/sitemap.xml', 'sitemap')->name('sitemap');
    Route::get('/feed.xml', 'feed')->name('feed');
    Route::get('/robots.txt', 'robots')->name('robots');
    Route::get('/manifest.webmanifest', 'manifest')->name('manifest');
    Route::get('/llms.txt', 'llms')->name('llms');
});

// renamed pages keep their old links working ("Pilo" is now "Time Guardians")
Route::permanentRedirect('/games/pilo', '/games/time-guardians');
Route::permanentRedirect('/blog/pilo-time-lens', '/blog/time-lens-puzzles');
Route::permanentRedirect('/blog/ad-free-games-parents', '/blog/safe-games-parents-checklist');

Route::controller(SiteController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/games', 'games')->name('games.index');
    Route::get('/games/{game}', 'game')->name('games.show');
    Route::get('/services', 'services')->name('services');
    Route::get('/about', 'about')->name('about');
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'storeContact')->middleware('throttle:6,1')->name('contact.store');
    Route::post('/newsletter', 'storeNewsletter')->middleware('throttle:6,1')->name('newsletter.store');
    Route::get('/blog', 'blog')->name('blog.index');
    Route::get('/blog/{post}', 'post')->name('blog.show');
});

// one-time installer for cPanel hosts without SSH (needs INSTALL_KEY in .env)
Route::get('/install', [\App\Http\Controllers\InstallController::class, 'show']);
Route::post('/install', [\App\Http\Controllers\InstallController::class, 'store'])->middleware('throttle:5,1');
