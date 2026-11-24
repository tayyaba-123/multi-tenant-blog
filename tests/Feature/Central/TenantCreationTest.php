<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->create();
});

function tenantPayload(array $overrides = []): array
{
    return [
        'name' => 'Acme Inc',
        'subdomain' => 'acme',
        'brand_color' => '#E11D48',
        'owner_name' => 'Wile E. Coyote',
        'owner_email' => 'wile@acme.test',
        'owner_password' => 'super-secret-password',
        ...$overrides,
    ];
}

test('the create tenant form can be rendered', function () {
    $this->actingAs($this->admin)
        ->get('http://blogs.test/admin/tenants/create')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Central/Tenants/Create')
            ->where('baseDomain', 'blogs.test'));
});

test('an admin can create a tenant with a domain, logo and brand color', function () {
    $response = $this->actingAs($this->admin)->post('http://blogs.test/admin/tenants', tenantPayload([
        'logo' => UploadedFile::fake()->image('logo.png', 120, 120),
    ]));

    $response->assertRedirect(route('central.tenants.index'))
        ->assertSessionHas('success');

    $tenant = Tenant::with('domains')->findOrFail('acme');

    expect($tenant->name)->toBe('Acme Inc')
        ->and($tenant->brand_color)->toBe('#e11d48')
        ->and($tenant->primaryDomain())->toBe('acme.blogs.test')
        ->and($tenant->logo_path)->toStartWith('logos/')
        ->and(Storage::disk('public')->exists($tenant->logo_path))->toBeTrue();

    Storage::disk('public')->delete($tenant->logo_path);
});

test('the first writer is created inside the tenant database', function () {
    $this->actingAs($this->admin)->post('http://blogs.test/admin/tenants', tenantPayload());

    expect(User::where('email', 'wile@acme.test')->exists())->toBeFalse();

    Tenant::findOrFail('acme')->run(function () {
        $writer = User::where('email', 'wile@acme.test')->firstOrFail();

        expect(Hash::check('super-secret-password', $writer->password))->toBeTrue()
            ->and($writer->email_verified_at)->not->toBeNull();
    });
});

test('the new writer can sign in on the tenant domain', function () {
    $this->actingAs($this->admin)->post('http://blogs.test/admin/tenants', tenantPayload());
    auth()->logout();

    $this->post('http://acme.blogs.test/login', [
        'email' => 'wile@acme.test',
        'password' => 'super-secret-password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticated();
});

test('subdomains must be unique', function () {
    $this->createTenant('acme');

    $this->actingAs($this->admin)
        ->post('http://blogs.test/admin/tenants', tenantPayload())
        ->assertSessionHasErrors('subdomain');
});

test('subdomains are validated', function (string $subdomain) {
    $this->actingAs($this->admin)
        ->post('http://blogs.test/admin/tenants', tenantPayload(['subdomain' => $subdomain]))
        ->assertSessionHasErrors('subdomain');

    expect(Tenant::count())->toBe(0);
})->with(['www', 'admin', '-acme', 'acme-', 'ac me', 'acme.evil', 'ac_me']);

test('brand colors must be hex colors', function () {
    $this->actingAs($this->admin)
        ->post('http://blogs.test/admin/tenants', tenantPayload(['brand_color' => 'red']))
        ->assertSessionHasErrors('brand_color');
});

test('logos must be images', function () {
    $this->actingAs($this->admin)
        ->post('http://blogs.test/admin/tenants', tenantPayload([
            'logo' => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
        ]))
        ->assertSessionHasErrors('logo');
});

test('guests can not create tenants', function () {
    $this->post('http://blogs.test/admin/tenants', tenantPayload())
        ->assertRedirect(route('central.login'));

    expect(Tenant::count())->toBe(0);
});

test('the tenants list shows every tenant with its domain', function () {
    $this->createTenant('acme');
    $this->createTenant('globex');

    $this->actingAs($this->admin)
        ->get('http://blogs.test/admin/tenants')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Central/Tenants/Index')
            ->has('tenants', 2)
            ->where('tenants.0.domain', fn ($domain) => in_array($domain, ['acme.blogs.test', 'globex.blogs.test'])));
});
