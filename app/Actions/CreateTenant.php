<?php

namespace App\Actions;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CreateTenant
{
    /**
     * Create a tenant with its domain, database, logo and first writer.
     *
     * Must run in central context: the logo is stored on the central public
     * disk and the tenant record in the central database.
     *
     * @param  array{name: string, subdomain: string, brand_color: string, owner_name: string, owner_email: string, owner_password: string}  $data
     */
    public function handle(array $data, ?UploadedFile $logo = null): Tenant
    {
        $logoPath = $logo?->store('logos', 'public');
        $tenant = null;

        try {
            // Creating the tenant synchronously creates and migrates its database.
            $tenant = Tenant::create([
                'id' => $data['subdomain'],
                'name' => $data['name'],
                'brand_color' => strtolower($data['brand_color']),
                'logo_path' => $logoPath,
            ]);

            $tenant->domains()->create([
                'domain' => $data['subdomain'].'.'.config('tenancy.base_domain'),
            ]);

            $tenant->run(fn () => User::forceCreate([
                'name' => $data['owner_name'],
                'email' => $data['owner_email'],
                'password' => $data['owner_password'],
                'email_verified_at' => now(),
            ]));
        } catch (Throwable $e) {
            $tenant?->delete();

            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }

            throw $e;
        }

        return $tenant->load('domains');
    }
}
