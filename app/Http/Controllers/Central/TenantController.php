<?php

namespace App\Http\Controllers\Central;

use App\Actions\CreateTenant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreTenantRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(): Response
    {
        $tenants = Tenant::with('domains')->latest()->get()->map(fn (Tenant $tenant) => [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'domain' => $tenant->primaryDomain(),
            'logo_url' => $tenant->logoUrl(),
            'brand_color' => $tenant->brand_color,
            'created_at' => $tenant->created_at->toDateString(),
        ]);

        return Inertia::render('Central/Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Central/Tenants/Create', [
            'baseDomain' => config('tenancy.base_domain'),
        ]);
    }

    public function store(StoreTenantRequest $request, CreateTenant $createTenant): RedirectResponse
    {
        $tenant = $createTenant->handle(
            $request->safe()->except('logo'),
            $request->file('logo'),
        );

        return redirect()
            ->route('central.tenants.index')
            ->with('success', "{$tenant->name} is live at {$tenant->primaryDomain()}.");
    }
}
