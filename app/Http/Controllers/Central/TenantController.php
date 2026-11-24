<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
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
}
