<?php

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->writer = User::factory()->create();
});

test('guests are sent to the tenant login', function () {
    $this->get('/dashboard/posts')->assertRedirect(route('login'));
});

test('the dashboard lists every post in the tenant', function () {
    Post::factory()->for($this->writer, 'author')->count(2)->create();
    Post::factory()->published()->create();

    $this->actingAs($this->writer)
        ->get('/dashboard/posts')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Posts/Index')
            ->has('posts.data', 3));
});

test('a writer can create a draft', function () {
    $this->actingAs($this->writer)
        ->post('/dashboard/posts', [
            'title' => 'Hello World',
            'body' => 'First post body.',
            'status' => 'draft',
        ])
        ->assertRedirect(route('dashboard.posts.index'));

    $post = Post::sole();

    expect($post->title)->toBe('Hello World')
        ->and($post->slug)->toBe('hello-world')
        ->and($post->status)->toBe(PostStatus::Draft)
        ->and($post->published_at)->toBeNull()
        ->and($post->user_id)->toBe($this->writer->id);
});

test('publishing stamps the publish time', function () {
    $this->freezeSecond();

    $this->actingAs($this->writer)->post('/dashboard/posts', [
        'title' => 'Live now',
        'body' => 'Body',
        'status' => 'published',
    ]);

    expect(Post::sole()->published_at->equalTo(now()))->toBeTrue();
});

test('a writer can schedule a post for later', function () {
    $publishAt = now()->addDay()->startOfMinute();

    $this->actingAs($this->writer)->post('/dashboard/posts', [
        'title' => 'Tomorrow',
        'body' => 'Body',
        'status' => 'scheduled',
        'published_at' => $publishAt->toIso8601String(),
    ])->assertSessionHasNoErrors();

    $post = Post::sole();

    expect($post->status)->toBe(PostStatus::Scheduled)
        ->and($post->published_at->equalTo($publishAt))->toBeTrue();
});

test('scheduled posts need a future publish time', function (?string $publishedAt) {
    $this->actingAs($this->writer)->post('/dashboard/posts', [
        'title' => 'Tomorrow',
        'body' => 'Body',
        'status' => 'scheduled',
        'published_at' => $publishedAt,
    ])->assertSessionHasErrors('published_at');
})->with([
    'missing' => null,
    'in the past' => fn () => now()->subHour()->toIso8601String(),
]);

test('posts are validated', function () {
    $this->actingAs($this->writer)
        ->post('/dashboard/posts', ['title' => '', 'body' => '', 'status' => 'archived'])
        ->assertSessionHasErrors(['title', 'body', 'status']);
});

test('duplicate titles get unique slugs', function () {
    Post::factory()->create(['title' => 'Same title']);
    $second = Post::factory()->create(['title' => 'Same title']);

    expect($second->slug)->toBe('same-title-2');
});

test('a writer can update their post', function () {
    $post = Post::factory()->for($this->writer, 'author')->create();

    $this->actingAs($this->writer)
        ->put("/dashboard/posts/{$post->id}", [
            'title' => 'Renamed',
            'body' => 'New body',
            'status' => 'draft',
        ])
        ->assertRedirect(route('dashboard.posts.index'));

    expect($post->fresh()->title)->toBe('Renamed');
});

test('republishing keeps the original publish time', function () {
    $post = Post::factory()->for($this->writer, 'author')->published()->create();
    $original = $post->published_at;

    $this->actingAs($this->writer)->put("/dashboard/posts/{$post->id}", [
        'title' => 'Edited',
        'body' => 'Body',
        'status' => 'published',
    ]);

    expect($post->fresh()->published_at->equalTo($original))->toBeTrue();
});

test('a writer can delete their post', function () {
    $post = Post::factory()->for($this->writer, 'author')->create();

    $this->actingAs($this->writer)
        ->delete("/dashboard/posts/{$post->id}")
        ->assertRedirect(route('dashboard.posts.index'));

    expect(Post::count())->toBe(0);
});

test("writers can not edit or delete another writer's post", function () {
    $post = Post::factory()->create();

    $this->actingAs($this->writer)->get("/dashboard/posts/{$post->id}/edit")->assertForbidden();
    $this->actingAs($this->writer)->put("/dashboard/posts/{$post->id}", [
        'title' => 'Hijacked',
        'body' => 'Body',
        'status' => 'draft',
    ])->assertForbidden();
    $this->actingAs($this->writer)->delete("/dashboard/posts/{$post->id}")->assertForbidden();

    expect($post->fresh()->title)->not->toBe('Hijacked');
});
