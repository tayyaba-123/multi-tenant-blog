<?php

namespace App\Observers;

use App\Models\Post;
use App\Support\BlogCache;

class PostObserver
{
    public function saved(Post $post): void
    {
        BlogCache::flush();
    }

    public function deleted(Post $post): void
    {
        BlogCache::flush();
    }
}
