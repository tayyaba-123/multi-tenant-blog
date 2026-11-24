<?php

use App\Models\User;

test('admin login screen can be rendered', function () {
    $this->get('http://blogs.test/admin/login')->assertOk();
});

test('admins can authenticate on the central domain', function () {
    $admin = User::factory()->create();

    $response = $this->post('http://blogs.test/admin/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect(route('central.tenants.index', absolute: false));
});

test('admins can not authenticate with an invalid password', function () {
    $admin = User::factory()->create();

    $this->post('http://blogs.test/admin/login', [
        'email' => $admin->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('guests are redirected to the admin login', function () {
    $this->get('http://blogs.test/admin/tenants')
        ->assertRedirect(route('central.login'));
});

test('admins can log out', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->post('http://blogs.test/admin/logout')
        ->assertRedirect(route('central.login'));

    $this->assertGuest();
});

test('a tenant writer can not log in to the central admin', function () {
    $tenant = $this->createTenant();
    $writer = $tenant->run(fn () => User::factory()->create());

    $this->post('http://blogs.test/admin/login', [
        'email' => $writer->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
});
