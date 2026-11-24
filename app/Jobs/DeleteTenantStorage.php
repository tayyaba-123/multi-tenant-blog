<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Stancl\Tenancy\Contracts\Tenant;

class DeleteTenantStorage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Tenant $tenant) {}

    /**
     * Remove storage/tenant_{id}, where the filesystem bootstrapper keeps the
     * tenant's uploads. Runs in central context, so storage_path() is the root.
     */
    public function handle(): void
    {
        $directory = storage_path(config('tenancy.filesystem.suffix_base').$this->tenant->getTenantKey());

        File::deleteDirectory($directory);
    }
}
