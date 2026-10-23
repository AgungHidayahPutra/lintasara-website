<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ArticleController as PublicArticleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

/*
|--------------------------------------------------------------------------
| Detail Berita Publik
|--------------------------------------------------------------------------
| URL: /berita/{slug}
|
| Contoh:
| /berita/generasi-muda-dan-perkembangan-ekosistem-olahraga-daerah
|
| Controller publik dipisahkan dari controller artikel Admin.
|--------------------------------------------------------------------------
*/

Route::get('/berita/{slug}', [PublicArticleController::class, 'show'])
    ->name('public.articles.show');

/*
|--------------------------------------------------------------------------
| Admin & Penulis
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'verified',
    'role:admin,penulis',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('articles', ArticleController::class);
    });

/*
|--------------------------------------------------------------------------
| Khusus Admin
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'verified',
    'role:admin',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('categories', CategoryController::class)
            ->except(['show']);

        Route::resource('tags', TagController::class)
            ->except(['show']);

        Route::resource('regions', RegionController::class)
            ->except(['show']);

        Route::resource('users', UserController::class)
            ->except(['show']);
    });

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/

require __DIR__ . '/settings.php';
