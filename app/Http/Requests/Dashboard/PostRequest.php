<?php

namespace App\Http\Requests\Dashboard;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post
            ? $this->user()->can('update', $post)
            : $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:100000'],
            'status' => ['required', Rule::enum(PostStatus::class)],
            'published_at' => [
                'nullable',
                'date',
                Rule::requiredIf($this->input('status') === PostStatus::Scheduled->value),
                Rule::when($this->input('status') === PostStatus::Scheduled->value, ['after:now']),
            ],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_cover_image' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'published_at.required' => 'Pick when the post should go live.',
            'published_at.after' => 'Scheduled posts need a time in the future.',
        ];
    }

    /**
     * The attributes to persist, with published_at derived from the status.
     *
     * @return array<string, mixed>
     */
    public function postAttributes(?Post $post = null): array
    {
        $status = $this->enum('status', PostStatus::class);

        $publishedAt = match ($status) {
            PostStatus::Draft => null,
            PostStatus::Scheduled => Carbon::parse($this->input('published_at'))->utc(),
            PostStatus::Published => $post?->isPublished() ? $post->published_at : now(),
        };

        return [
            'title' => $this->input('title'),
            'body' => $this->input('body'),
            'status' => $status,
            'published_at' => $publishedAt,
        ];
    }
}
