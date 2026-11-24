<?php

use App\Enums\PostStatus;
use App\Jobs\PublishScheduledPosts;
use App\Models\Post;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

test('the job publishes only scheduled posts that are due', function () {
    $tenant = $this->createTenant();

    $tenant->run(function () {
        $due = Post::factory()->create([
            'status' => PostStatus::Scheduled,
            'published_at' => now()->subMinute(),
        ]);
        $future = Post::factory()->scheduled()->create();
        $draft = Post::factory()->create();

        (new PublishScheduledPosts)->handle();

        expect($due->fresh()->status)->toBe(PostStatus::Published)
            ->and($due->fresh()->isPublished())->toBeTrue()
            ->and($future->fresh()->status)->toBe(PostStatus::Scheduled)
            ->and($draft->fresh()->status)->toBe(PostStatus::Draft);
    });
});

test('the command queues one tenant-aware job per tenant', function () {
    config(['queue.default' => 'database']);

    $acme = $this->createTenant('acme');
    $globex = $this->createTenant('globex');

    $this->artisan('posts:publish-due')->assertSuccessful();

    expect(tenancy()->initialized)->toBeFalse();

    $tenantIds = DB::table('jobs')->pluck('payload')
        ->map(fn ($payload) => json_decode($payload, true))
        ->each(fn ($payload) => expect($payload['displayName'])->toBe(PublishScheduledPosts::class))
        ->pluck('tenant_id')
        ->sort()
        ->values()
        ->all();

    expect($tenantIds)->toBe([$acme->id, $globex->id]);
});

test('queued jobs publish posts in the right tenant database', function () {
    config(['queue.default' => 'database']);

    $acme = $this->createTenant('acme');
    $globex = $this->createTenant('globex');

    $acmePost = $acme->run(fn () => Post::factory()->create([
        'status' => PostStatus::Scheduled,
        'published_at' => now()->subMinute(),
    ]));
    $globexPost = $globex->run(fn () => Post::factory()->scheduled()->create());

    $this->artisan('posts:publish-due')->assertSuccessful();

    // Process the queue from central context, exactly like a real worker.
    Artisan::call('queue:work', ['--stop-when-empty' => true]);

    expect(DB::table('jobs')->count())->toBe(0)
        ->and(DB::table('failed_jobs')->count())->toBe(0);

    $acme->run(fn () => expect($acmePost->fresh()->status)->toBe(PostStatus::Published));
    $globex->run(fn () => expect($globexPost->fresh()->status)->toBe(PostStatus::Scheduled));
});

test('the command can target specific tenants', function () {
    config(['queue.default' => 'database']);

    $this->createTenant('acme');
    $this->createTenant('globex');

    $this->artisan('posts:publish-due', ['--tenant' => ['acme']])->assertSuccessful();

    $tenantIds = DB::table('jobs')->pluck('payload')
        ->map(fn ($payload) => json_decode($payload, true)['tenant_id']);

    expect($tenantIds->all())->toBe(['acme']);
});

test('publishing runs every minute on the scheduler', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event) => str_contains($event->command, 'posts:publish-due'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('* * * * *');
});
