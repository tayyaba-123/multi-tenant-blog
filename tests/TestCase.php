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

            // Deleting tenants also drops their databases, which the central
            // truncation below would otherwise leave behind.
            Tenant::all()->each->delete();
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
