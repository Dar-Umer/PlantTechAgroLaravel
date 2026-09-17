<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Versioned cache for public front-end content. Every cached front-end payload
 * is keyed by the current version; bumping the version invalidates them all at
 * once whenever any content model changes.
 */
class ContentCache
{
    protected const VERSION_KEY = 'frontend.content.version';

    public static function version(): int
    {
        try {
            return (int) Cache::get(self::VERSION_KEY, 1);
        } catch (\Throwable) {
            return 1;
        }
    }

    public static function bump(): void
    {
        try {
            Cache::forever(self::VERSION_KEY, self::version() + 1);
        } catch (\Throwable) {
            // Ignore cache failures (e.g. before migrations have run).
        }
    }

    public static function remember(string $name, Closure $callback): mixed
    {
        try {
            return Cache::remember('frontend.'.$name.'.'.self::version(), now()->addDay(), $callback);
        } catch (\Throwable) {
            return $callback();
        }
    }
}
