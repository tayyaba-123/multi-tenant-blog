<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Caches the public homepage post list.
 *
 * Inside tenant context, CacheTenancyBootstrapper tags every key with the
 * tenant id, so "blog.home..." is a different entry for every tenant.
 * Invalidation bumps a version number instead of flushing tags: flushing
 * with an extra tag would also wipe other tenants' entries sharing that tag.
 */
class BlogCache
{
    private const VERSION_KEY = 'blog.home.version';

    private const TTL_SECONDS = 600;

    public static function homepage(int $page, Closure $callback): mixed
    {
        $version = Cache::get(self::VERSION_KEY, 0);

        return Cache::remember("blog.home.v{$version}.page.{$page}", self::TTL_SECONDS, $callback);
    }

    public static function flush(): void
    {
        Cache::increment(self::VERSION_KEY);
    }
}
