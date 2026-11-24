<?php

namespace App\Console\Commands;

use App\Jobs\PublishScheduledPosts;
use App\Models\Tenant;
use Illuminate\Console\Command;

class PublishDuePosts extends Command
{
    protected $signature = 'posts:publish-due {--tenant=* : Only these tenant ids}';

    protected $description = "Queue a job per tenant that publishes that tenant's due scheduled posts";

    public function handle(): int
    {
        // An empty list means every tenant.
        tenancy()->runForMultiple($this->option('tenant'), function (Tenant $tenant) {
            PublishScheduledPosts::dispatch();

            $this->line("Queued publishing for <info>{$tenant->id}</info>");
        });

        return self::SUCCESS;
    }
}
