<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
});

test('admin can visit the dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.dashboard'));

    $response->assertOk();
});

test('penulis can visit their dashboard', function () {
    $penulis = User::factory()->create([
        'role' => 'penulis',
    ]);

    $response = $this
        ->actingAs($penulis)
        ->get(route('admin.dashboard'));

    $response->assertOk();
});