<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->acme = $this->createTenant('acme');
    $this->globex = $this->createTenant('globex');
});

describe('database', function () {
    test("tenant B's homepage never lists tenant A's posts", function () {
        $this->acme->run(fn () => Post::factory()->published()->create(['title' => 'Acme only']));
        $this->globex->run(fn () => Post::factory()->published()->create(['title' => 'Globex only']));

        $this->get($this->tenantUrl($this->globex))
            ->assertInertia(fn ($page) => $page
                ->has('posts.data', 1)
                ->where('posts.data.0.title', 'Globex only'));

        $this->get($this->tenantUrl($this->acme))
            ->assertInertia(fn ($page) => $page
                ->has('posts.data', 1)
                ->where('posts.data.0.title', 'Acme only'));
    });

    test("tenant B can't open tenant A's post", function () {
        $post = $this->acme->run(fn () => Post::factory()->published()->create());

        $this->get($this->tenantUrl($this->acme, "posts/{$post->slug}"))->assertOk();
        $this->get($this->tenantUrl($this->globex, "posts/{$post->slug}"))->assertNotFound();
    });

    test('the same slug can exist in both tenants with different content', function () {
        $this->acme->run(fn () => Post::factory()->published()->create(['title' => 'Hello', 'body' => 'From Acme']));
        $this->globex->run(fn () => Post::factory()->published()->create(['title' => 'Hello', 'body' => 'From Globex']));

        $this->get($this->tenantUrl($this->acme, 'posts/hello'))
            ->assertInertia(fn ($page) => $page->where('post.body', 'From Acme'));
        $this->get($this->tenantUrl($this->globex, 'posts/hello'))
            ->assertInertia(fn ($page) => $page->where('post.body', 'From Globex'));
    });

    test("a writer can't sign in to another tenant's dashboard", function () {
        $this->acme->run(fn () => User::factory()->create(['email' => 'writer@acme.test']));

        $this->post($this->tenantUrl($this->globex, 'login'), [
            'email' => 'writer@acme.test',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    });

    test("a writer can't edit another tenant's post by id", function () {
        $acmePost = $this->acme->run(fn () => Post::factory()->create());
        $globexWriter = $this->globex->run(fn () => User::factory()->create());

        $this->actingAs($globexWriter)
            ->get($this->tenantUrl($this->globex, "dashboard/posts/{$acmePost->id}/edit"))
            ->assertNotFound();
    });
});

describe('storage', function () {
    test("tenant B's disk can't see tenant A's files", function () {
        $this->acme->run(fn () => Storage::disk('public')->put('covers/secret.jpg', 'acme'));

        $this->globex->run(function () {
            expect(Storage::disk('public')->exists('covers/secret.jpg'))->toBeFalse();
        });

        $this->acme->run(function () {
            expect(Storage::disk('public')->get('covers/secret.jpg'))->toBe('acme');
        });
    });

    test('each tenant writes to its own storage directory', function () {
        $acmeRoot = $this->acme->run(fn () => Storage::disk('public')->path(''));
        $globexRoot = $this->globex->run(fn () => Storage::disk('public')->path(''));

        expect($acmeRoot)->toContain('tenant_acme')
            ->and($globexRoot)->toContain('tenant_globex')
            ->and($acmeRoot)->not->toBe($globexRoot);
    });
});

describe('cache', function () {
    test("tenant B can't read tenant A's cache", function () {
        $this->acme->run(fn () => Cache::put('greeting', 'hello from acme'));

        $this->globex->run(function () {
            expect(Cache::get('greeting'))->toBeNull();

            Cache::put('greeting', 'hello from globex');
        });

        $this->acme->run(fn () => expect(Cache::get('greeting'))->toBe('hello from acme'));
    });

    test('a cached homepage is never served to another tenant', function () {
        $this->acme->run(fn () => Post::factory()->published()->create(['title' => 'Acme cached']));

        // Warm Acme's homepage cache first; Globex has no posts.
        $this->get($this->tenantUrl($this->acme))->assertInertia(fn ($page) => $page->has('posts.data', 1));

        $this->get($this->tenantUrl($this->globex))->assertInertia(fn ($page) => $page->has('posts.data', 0));
    });

    test("invalidating one tenant's homepage leaves the other's cache alone", function () {
        $this->acme->run(fn () => Post::factory()->published()->create());
        $this->globex->run(fn () => Post::factory()->published()->create(['title' => 'Globex cached']));

        $this->get($this->tenantUrl($this->globex));
        $versionBefore = $this->globex->run(fn () => Cache::get('blog.home.version', 0));

        $this->acme->run(fn () => Post::factory()->published()->create());

        expect($this->globex->run(fn () => Cache::get('blog.home.version', 0)))->toBe($versionBefore);
    });
});
