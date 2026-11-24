<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\PostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $posts = Post::with('author:id,name')
            ->latest('updated_at')
            ->paginate(15)
            ->through(fn (Post $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'status' => $post->status,
                'published_at' => $post->published_at?->toIso8601String(),
                'updated_at' => $post->updated_at->toIso8601String(),
                'author' => $post->author?->name,
                'can' => [
                    'update' => $request->user()->can('update', $post),
                    'delete' => $request->user()->can('delete', $post),
                ],
            ]);

        return Inertia::render('Dashboard/Posts/Index', [
            'posts' => $posts,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Dashboard/Posts/Create', [
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $request->user()->posts()->create($request->postAttributes());

        return redirect()->route('dashboard.posts.index')->with('success', 'Post created.');
    }

    public function edit(Post $post): Response
    {
        Gate::authorize('update', $post);

        return Inertia::render('Dashboard/Posts/Edit', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'body' => $post->body,
                'status' => $post->status,
                'published_at' => $post->published_at?->toIso8601String(),
            ],
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->postAttributes($post));

        return redirect()->route('dashboard.posts.index')->with('success', 'Post updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return redirect()->route('dashboard.posts.index')->with('success', 'Post deleted.');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statuses(): array
    {
        return array_map(
            fn (PostStatus $status) => ['value' => $status->value, 'label' => $status->label()],
            PostStatus::cases(),
        );
    }
}
