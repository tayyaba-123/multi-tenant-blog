<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\BlogCache;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(Request $request): Response
    {
        $page = max(1, $request->integer('page', 1));

        $posts = BlogCache::homepage($page, fn () => Post::published()
            ->with('author:id,name')
            ->latest('published_at')
            ->paginate(10, page: $page)
            ->through(fn (Post $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => Str::limit($post->body, 220),
                'cover_image_url' => $post->coverImageUrl(),
                'author' => $post->author?->name,
                'published_at' => $post->published_at->toIso8601String(),
            ])
            ->toArray());

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
        ]);
    }

    public function show(Post $post): Response
    {
        abort_unless($post->isPublished(), 404);

        return Inertia::render('Blog/Show', [
            'post' => [
                'title' => $post->title,
                'body' => $post->body,
                'cover_image_url' => $post->coverImageUrl(),
                'author' => $post->author?->name,
                'published_at' => $post->published_at->toIso8601String(),
            ],
        ]);
    }
}
