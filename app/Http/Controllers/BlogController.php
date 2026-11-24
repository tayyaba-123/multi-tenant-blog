<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(): Response
    {
        $posts = Post::published()
            ->with('author:id,name')
            ->latest('published_at')
            ->paginate(10)
            ->through(fn (Post $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => Str::limit($post->body, 220),
                'author' => $post->author?->name,
                'published_at' => $post->published_at->toIso8601String(),
            ]);

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
                'author' => $post->author?->name,
                'published_at' => $post->published_at->toIso8601String(),
            ],
        ]);
    }
}
