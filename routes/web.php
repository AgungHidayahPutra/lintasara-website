<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
|
| Halaman yang dapat diakses semua pengunjung.
|
*/

Route::inertia('/', 'Welcome')->name('home');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
|
| Dashboard dapat diakses admin dan penulis yang sudah login,
| terverifikasi, dan memiliki akun aktif.
|
*/

Route::middleware([
    'auth',
    'verified',
    'role:admin,penulis',
])->group(function () {
    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Halaman yang hanya dapat diakses oleh administrator.
|
*/

Route::middleware([
    'auth',
    'verified',
    'role:admin',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Route khusus admin akan ditambahkan di sini.
    });

/*
|--------------------------------------------------------------------------
| Settings Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';