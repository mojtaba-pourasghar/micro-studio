<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/sitemap.xml', 'sitemap')->name('sitemap');
});
