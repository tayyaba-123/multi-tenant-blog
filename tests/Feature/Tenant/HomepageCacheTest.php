<?php

use App\Enums\PostStatus;
use App\Jobs\PublishScheduledPosts;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

function homepageTitles($test): array
{
    $titles = [];

    $test->get('/')->assertInertia(function ($page) use (&$titles) {
        $titles = collect($page->toArray()['props']['posts']['data'])->pluck('title')->all();
    });

    return $titles;
}

test('the homepage post list is served from the cache', function () {
    Post::factory()->published()->create(['title' => 'Cached']);

    expect(homepageTitles($this))->toBe(['Cached']);

    // Bypass Eloquent so no observer can invalidate the cache.
    DB::table('posts')->update(['title' => 'Changed behind the cache']);

    expect(homepageTitles($this))->toBe(['Cached']);
});

test('saving a post refreshes the cached list', function () {
    $post = Post::factory()->published()->create(['title' => 'Before']);
    expect(homepageTitles($this))->toBe(['Before']);

    $post->update(['title' => 'After']);

    expect(homepageTitles($this))->toBe(['After']);
});

test('deleting a post refreshes the cached list', function () {
    $post = Post::factory()->published()->create();
    expect(homepageTitles($this))->toHaveCount(1);

    $post->delete();

    expect(homepageTitles($this))->toBe([]);
});

test('scheduled publishing refreshes the cached list', function () {
    Post::factory()->create([
        'title' => 'Due now',
        'status' => PostStatus::Scheduled,
        'published_at' => now()->subMinute(),
    ]);
    expect(homepageTitles($this))->toBe([]);

    (new PublishScheduledPosts)->handle();

    expect(homepageTitles($this))->toBe(['Due now']);
});
