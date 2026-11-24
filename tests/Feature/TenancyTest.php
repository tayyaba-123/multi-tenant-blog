<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

function databaseExists(string $name): bool
{
    return DB::connection('mysql')
        ->table('information_schema.schemata')
        ->where('schema_name', $name)
        ->exists();
}

test('creating a tenant provisions and migrates its own database', function () {
    $tenant = $this->createTenant('acme');

    $database = $tenant->database()->getName();

    expect($database)->toBe(config('tenancy.database.prefix').'acme')
        ->and(databaseExists($database))->toBeTrue();

    $tenant->run(function () {
        expect(DB::getDefaultConnection())->toBe('tenant')
            ->and(DB::getSchemaBuilder()->hasTable('users'))->toBeTrue();
    });
});

test('deleting a tenant drops its database', function () {
    $tenant = $this->createTenant();
    $database = $tenant->database()->getName();

    $tenant->delete();

    expect(databaseExists($database))->toBeFalse();
});

test('tenancy is initialized from the request domain', function () {
    $this->createTenant('acme');
    $this->createTenant('globex');

    $this->get('http://acme.blogs.test/')->assertOk()->assertSee('acme');
    expect(tenant('id'))->toBe('acme');

    $this->get('http://globex.blogs.test/')->assertOk()->assertSee('globex');
    expect(tenant('id'))->toBe('globex');
});

test('unknown domains do not resolve to a tenant', function () {
    $this->withoutExceptionHandling();

    $this->get('http://unknown.blogs.test/');
})->throws(Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException::class);

test('the central domain serves the central app', function () {
    $this->get('http://blogs.test/')->assertOk();

    expect(tenancy()->initialized)->toBeFalse();
});

test('tenant routes are not reachable from the central domain', function () {
    $this->get('http://blogs.test/login')->assertNotFound();
});

test('tenant records live in the central database', function () {
    $this->createTenant('acme');

    expect(Tenant::find('acme'))->not->toBeNull()
        ->and((new Tenant)->getConnectionName())->toBe(config('tenancy.database.central_connection'));
});
