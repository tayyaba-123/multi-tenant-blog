<?php

namespace App\Jobs;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Publishes the current tenant's scheduled posts whose time has come.
 *
 * Dispatch it inside tenant context: QueueTenancyBootstrapper stores the
 * tenant id in the payload and re-initializes that tenant on the worker,
 * so the queries below hit the right tenant database.
 */
class PublishScheduledPosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle(): void
    {
        Post::query()
            ->where('status', PostStatus::Scheduled)
            ->where('published_at', '<=', now())
            ->each(fn (Post $post) => $post->update(['status' => PostStatus::Published]));
    }
}
