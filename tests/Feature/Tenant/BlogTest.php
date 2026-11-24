<?php

use App\Models\Post;

test('the homepage lists published posts, newest first', function () {
    $older = Post::factory()->published()->create(['published_at' => now()->subDays(2)]);
    $newer = Post::factory()->published()->create(['published_at' => now()->subDay()]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Blog/Index')
            ->has('posts.data', 2)
            ->where('posts.data.0.slug', $newer->slug)
            ->where('posts.data.1.slug', $older->slug));
});

test('drafts and scheduled posts are hidden from the homepage', function () {
    Post::factory()->create();
    Post::factory()->scheduled()->create();

    $this->get('/')
        ->assertInertia(fn ($page) => $page->has('posts.data', 0));
});

test('a published post has its own page', function () {
    $post = Post::factory()->published()->create(['title' => 'Readable']);

    $this->get("/posts/{$post->slug}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Blog/Show')
            ->where('post.title', 'Readable'));
});

test('unpublished posts return 404', function () {
    $draft = Post::factory()->create();
    $scheduled = Post::factory()->scheduled()->create();

    $this->get("/posts/{$draft->slug}")->assertNotFound();
    $this->get("/posts/{$scheduled->slug}")->assertNotFound();
});

test('the public pages are branded with the tenant name', function () {
    $this->get('/')
        ->assertInertia(fn ($page) => $page->where('tenant.name', tenant('name')));
});
