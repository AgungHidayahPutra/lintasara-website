<?php

use Laravel\Fortify\Features;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Guard
    |--------------------------------------------------------------------------
    */

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Password Broker
    |--------------------------------------------------------------------------
    */

    'passwords' => 'users',

    /*
    |--------------------------------------------------------------------------
    | Username / Email
    |--------------------------------------------------------------------------
    */

    'username' => 'email',

    'email' => 'email',

    'lowercase_usernames' => true,

    /*
    |--------------------------------------------------------------------------
    | Redirect After Authentication
    |--------------------------------------------------------------------------
    |
    | Admin dan Penulis menggunakan /admin.
    | Isi dashboard dibedakan berdasarkan role pengguna.
    |
    */

    'home' => '/admin',

    /*
    |--------------------------------------------------------------------------
    | Fortify Routes
    |--------------------------------------------------------------------------
    */

    'prefix' => '',

    'domain' => null,

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Views
    |--------------------------------------------------------------------------
    */

    'views' => true,

    /*
    |--------------------------------------------------------------------------
    | Authentication Features
    |--------------------------------------------------------------------------
    |
    | Registrasi publik dan Passkeys tidak diaktifkan.
    | Reset password, verifikasi email, dan 2FA dipertahankan.
    |
    */

    'features' => [
        Features::resetPasswords(),

        Features::emailVerification(),

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]),
    ],

];
