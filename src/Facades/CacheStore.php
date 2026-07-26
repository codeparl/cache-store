<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * CacheStore Facade
 *
 * Provides access to the context-aware cache manager.
 *
 * @method static \SchoolPalm\CacheStore\Contracts\CacheStore driver(?string $driver = null)
 * @method static \SchoolPalm\CacheStore\Contracts\CacheStore store(?string $store = null)
 *
 * @method static \SchoolPalm\CacheStore\Contracts\CacheStore forContext(?string $tenantId, ?string $schoolId)
 * @method static \SchoolPalm\CacheStore\Contracts\CacheStore tags(array|string $tags)
 *
 * @method static mixed remember(string $key, \DateTimeInterface|\DateInterval|int|null $ttl, \Closure $callback)
 * @method static mixed rememberForever(string $key, \Closure $callback)
 *
 * @method static \Illuminate\Cache\CacheLock lock(string $key, int $seconds)
 *
 * @method static mixed get(string $key, mixed $default = null)
 *
 * @method static bool put(
 *     string $key,
 *     mixed $value,
 *     \DateTimeInterface|\DateInterval|int|null $ttl = null
 * )
 *
 * @method static bool forever(string $key, mixed $value)
 *
 * @method static bool add(
 *     string $key,
 *     mixed $value,
 *     \DateTimeInterface|\DateInterval|int|null $ttl = null
 * )
 *
 * @method static bool forget(string $key)
 *
 * @method static bool flush()
 *
 * @method static int increment(string $key, int $value = 1)
 *
 * @method static int decrement(string $key, int $value = 1)
 *
 * @method static array many(array $keys)
 *
 * @method static bool putMany(
 *     array $values,
 *     \DateTimeInterface|\DateInterval|int|null $ttl = null
 * )
 *
 * @method static mixed pull(string $key, mixed $default = null)
 *
 * @see \SchoolPalm\CacheStore\CacheStoreManager
 */
final class CacheStore extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'cache-store';
    }
}
