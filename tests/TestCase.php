<?php

namespace Tests;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        if ($this->app) {
            tenancy()->end();

            // Tenant databases live outside the central database, so truncation
            // can't remove them. Drop them directly: tests may fake events,
            // which would skip the DeleteDatabase listener.
            Tenant::all()->each(function (Tenant $tenant) {
                $manager = $tenant->database()->manager();

                if ($manager->databaseExists($tenant->database()->getName())) {
                    $manager->deleteDatabase($tenant);
                }
            });
        }

        parent::tearDown();
    }

    protected function createTenant(?string $id = null, array $attributes = []): Tenant
    {
        $id ??= 't'.Str::lower(Str::random(10));

        $tenant = Tenant::create(['id' => $id, ...$attributes]);
        $tenant->domains()->create(['domain' => $this->tenantDomain($id)]);

        return $tenant->load('domains');
    }

    /**
     * Enter tenant context and point relative test requests at the tenant's domain.
     */
    protected function initializeTenant(?Tenant $tenant = null): Tenant
    {
        $tenant ??= $this->createTenant();

        tenancy()->initialize($tenant);
        URL::forceRootUrl('http://'.$tenant->domains->first()->domain);

        return $tenant;
    }

    protected function tenantDomain(string $id): string
    {
        return $id.'.'.config('tenancy.base_domain');
    }

    protected function tenantUrl(Tenant $tenant, string $path = '/'): string
    {
        return 'http://'.$tenant->domains->first()->domain.'/'.ltrim($path, '/');
    }
}
