<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\RegionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::inertia('/', 'Welcome')->name('home');

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
        // Users
        // akan ditambahkan nanti.
    });

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/

require __DIR__ . '/settings.php';
