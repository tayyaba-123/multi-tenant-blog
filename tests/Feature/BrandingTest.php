<?php

test('each tenant blog is styled with its own logo and color', function () {
    $acme = $this->createTenant('acme', ['brand_color' => '#e11d48', 'logo_path' => 'logos/acme.png']);
    $globex = $this->createTenant('globex', ['brand_color' => '#0d9488']);

    $this->get($this->tenantUrl($acme))
        ->assertInertia(fn ($page) => $page
            ->where('tenant.name', 'Acme')
            ->where('tenant.brand_color', '#e11d48')
            ->where('tenant.logo_url', '/storage/logos/acme.png'));

    $this->get($this->tenantUrl($globex))
        ->assertInertia(fn ($page) => $page
            ->where('tenant.name', 'Globex')
            ->where('tenant.brand_color', '#0d9488')
            ->where('tenant.logo_url', null));
});

test('branding is read from the central database', function () {
    $acme = $this->createTenant('acme', ['brand_color' => '#e11d48']);

    $acme->update(['brand_color' => '#2563eb']);

    $this->get($this->tenantUrl($acme))
        ->assertInertia(fn ($page) => $page->where('tenant.brand_color', '#2563eb'));
});

test('the central app has no tenant branding', function () {
    $this->get('http://blogs.test/')
        ->assertInertia(fn ($page) => $page->where('tenant', null));
});
