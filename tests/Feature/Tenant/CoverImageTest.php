<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->tenant = tenant();
    $this->writer = User::factory()->create();
});

function createPostWithCover($test): Post
{
    $test->actingAs($test->writer)->post('/dashboard/posts', [
        'title' => 'With a cover',
        'body' => 'Body',
        'status' => 'published',
        'cover_image' => UploadedFile::fake()->image('cover.jpg', 1200, 600),
    ])->assertSessionHasNoErrors();

    return Post::sole();
}

test('a cover image is stored in the tenant\'s own storage directory', function () {
    $post = createPostWithCover($this);

    $tenantRoot = base_path('storage/tenant_'.$this->tenant->id.'/app/public');

    expect($post->cover_image_path)->toStartWith('covers/')
        ->and(Storage::disk('public')->exists($post->cover_image_path))->toBeTrue()
        ->and(Storage::disk('public')->path($post->cover_image_path))->toStartWith($tenantRoot)
        ->and(file_exists($tenantRoot.'/'.$post->cover_image_path))->toBeTrue();
});

test('the cover is served from the tenant asset route', function () {
    $post = createPostWithCover($this);

    expect($post->coverImageUrl())
        ->toBe($this->tenantUrl($this->tenant, 'tenancy/assets/'.$post->cover_image_path));

    $this->get($post->coverImageUrl())->assertOk();
});

test('replacing a cover deletes the old file', function () {
    $post = createPostWithCover($this);
    $oldPath = $post->cover_image_path;

    $this->actingAs($this->writer)->put("/dashboard/posts/{$post->id}", [
        'title' => $post->title,
        'body' => $post->body,
        'status' => 'published',
        'cover_image' => UploadedFile::fake()->image('new.png', 1200, 600),
    ])->assertSessionHasNoErrors();

    $post->refresh();

    expect($post->cover_image_path)->not->toBe($oldPath)
        ->and(Storage::disk('public')->exists($oldPath))->toBeFalse()
        ->and(Storage::disk('public')->exists($post->cover_image_path))->toBeTrue();
});

test('a cover can be removed', function () {
    $post = createPostWithCover($this);
    $path = $post->cover_image_path;

    $this->actingAs($this->writer)->put("/dashboard/posts/{$post->id}", [
        'title' => $post->title,
        'body' => $post->body,
        'status' => 'published',
        'remove_cover_image' => true,
    ]);

    expect($post->fresh()->cover_image_path)->toBeNull()
        ->and(Storage::disk('public')->exists($path))->toBeFalse();
});

test('deleting a post deletes its cover', function () {
    $post = createPostWithCover($this);
    $path = $post->cover_image_path;

    $this->actingAs($this->writer)->delete("/dashboard/posts/{$post->id}");

    expect(Storage::disk('public')->exists($path))->toBeFalse();
});

test('covers must be images', function () {
    $this->actingAs($this->writer)->post('/dashboard/posts', [
        'title' => 'Bad file',
        'body' => 'Body',
        'status' => 'draft',
        'cover_image' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
    ])->assertSessionHasErrors('cover_image');
});

test("another tenant can't read this tenant's covers", function () {
    $post = createPostWithCover($this);
    $path = $post->cover_image_path;

    $other = $this->createTenant();
    $other->run(fn () => Storage::disk('public')->put('covers/own.jpg', 'other tenant'));

    expect(fn () => $this->withoutExceptionHandling()
        ->get($this->tenantUrl($other, 'tenancy/assets/'.$path)))
        ->toThrow(Exception::class, 'Accessing a nonexistent file');

    expect(tenant('id'))->toBe($other->id);
});

test('deleting a tenant removes its storage directory', function () {
    createPostWithCover($this);
    tenancy()->end();

    $directory = storage_path('tenant_'.$this->tenant->id);
    expect(is_dir($directory))->toBeTrue();

    $this->tenant->delete();

    expect(is_dir($directory))->toBeFalse();
});
