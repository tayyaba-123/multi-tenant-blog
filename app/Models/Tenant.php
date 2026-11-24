<?php

namespace App\Models;

use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * @property string $id
 * @property string $name
 * @property string|null $logo_path
 * @property string $brand_color
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Attributes stored as real columns instead of inside the "data" JSON column.
     */
    public static function getCustomColumns(): array
    {
        return ['id', 'name', 'logo_path', 'brand_color'];
    }

    /**
     * Logos live on the central public disk. The URL stays relative because the
     * public/storage symlink is served on every domain, while the public disk's
     * own url() points at the tenant's storage once tenancy is initialized.
     */
    public function logoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return '/storage/'.ltrim($this->logo_path, '/');
    }

    public function primaryDomain(): ?string
    {
        return $this->domains->first()?->domain;
    }
}
