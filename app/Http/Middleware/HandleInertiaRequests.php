<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            // The tenant model is loaded from the central database when tenancy
            // is identified, so branding is read without touching the tenant DB.
            'tenant' => fn () => tenancy()->initialized ? [
                'id' => tenant()->id,
                'name' => tenant()->name,
                'logo_url' => tenant()->logoUrl(),
                'brand_color' => tenant()->brand_color,
            ] : null,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
            ],
        ];
    }
}
