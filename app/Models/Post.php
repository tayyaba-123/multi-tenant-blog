<?php

namespace App\Models;

use App\Enums\PostStatus;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Lives in each tenant's database.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $title
 * @property string $slug
 * @property string $body
 * @property string|null $cover_image_path
 * @property PostStatus $status
 * @property Carbon|null $published_at
 */
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'status' => PostStatus::class,
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            $post->slug ??= static::uniqueSlug($post->title);
        });

        static::deleted(function (Post $post) {
            $post->deleteCoverImage();
        });
    }

    /**
     * Covers are stored on the public disk inside tenant context, which the
     * filesystem bootstrapper points at storage/tenant_{id}/app/public.
     */
    public function replaceCoverImage(?UploadedFile $file): void
    {
        $this->deleteCoverImage();

        $this->cover_image_path = $file?->store('covers', 'public');
    }

    public function deleteCoverImage(): void
    {
        if ($this->cover_image_path) {
            Storage::disk('public')->delete($this->cover_image_path);
            $this->cover_image_path = null;
        }
    }

    public function coverImageUrl(): ?string
    {
        return $this->cover_image_path ? tenant_asset($this->cover_image_path) : null;
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;

        for ($i = 2; static::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @param  Builder<Post>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', PostStatus::Published)
            ->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->status === PostStatus::Published
            && $this->published_at?->isPast();
    }
}
