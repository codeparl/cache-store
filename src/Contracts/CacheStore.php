<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Contracts;

use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Cache\CacheLock;
use Illuminate\Cache\Lock;

/**
 * Interface CacheStore
 *
 * Defines the contract for the high-level cache manager, providing
 * context-aware caching, tagging, and atomic locks.
 */
interface CacheStore
{
    /**
     * Get a cache driver instance.
     *
     * @param string|null $driver The name of the driver to resolve.
     * @return static
     */
    public function driver(?string $driver = null): static;

    /**
     * Get a cache store instance.
     *
     * @param string|null $store The name of the store to resolve.
     * @return static
     */
    public function store(?string $store = null): static;

    /**
     * Scope the cache operations to a specific tenant and/or school context.
     *
     * @param string|null $tenantId The ID of the tenant.
     * @param string|null $schoolId The ID of the school.
     * @return static
     */
    public function forContext(
        ?string $tenantId,
        ?string $schoolId
    ): static;

    /**
     * Begin executing a new tags operation.
     *
     * @param array<string>|string $tags The tags to assign to the cached items.
     * @return static
     */
    public function tags(array|string $tags): static;

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param string $key The cache key.
     * @param DateTimeInterface|DateInterval|int|null $ttl The time-to-live.
     * @param Closure $callback The callback to execute if the item does not exist.
     * @return mixed The cached or newly resolved value.
     */
    public function remember(
        string $key,
        DateTimeInterface|DateInterval|int|null $ttl,
        Closure $callback
    ): mixed;

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     *
     * @param string $key The cache key.
     * @param Closure $callback The callback to execute if the item does not exist.
     * @return mixed The cached or newly resolved value.
     */
    public function rememberForever(
        string $key,
        Closure $callback
    ): mixed;


    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key The cache key.
     * @param mixed $default The default value to return if the key doesn't exist.
     * @return mixed
     */
    public function get(
        string $key,
        mixed $default = null
    ): mixed;

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param string $key The cache key.
     * @param mixed $value The value to store.
     * @param DateTimeInterface|DateInterval|int|null $ttl The time-to-live.
     * @return bool
     */
    public function put(
        string $key,
        mixed $value,
        DateTimeInterface|DateInterval|int|null $ttl = null
    ): bool;

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key The cache key.
     * @param mixed $value The value to store.
     * @return bool
     */
    public function forever(
        string $key,
        mixed $value
    ): bool;

    /**
     * Store an item in the cache if the key does not exist.
     *
     * @param string $key The cache key.
     * @param mixed $value The value to store.
     * @param DateTimeInterface|DateInterval|int|null $ttl The time-to-live.
     * @return bool
     */
    public function add(
        string $key,
        mixed $value,
        DateTimeInterface|DateInterval|int|null $ttl = null
    ): bool;

    /**
     * Remove an item from the cache.
     *
     * @param string $key The cache key.
     * @return bool
     */
    public function forget(
        string $key
    ): bool;

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush(): bool;

    /**
     * Increment the value of an integer item in the cache.
     *
     * @param string $key The cache key.
     * @param int $value The amount to increment by.
     * @return int
     */
    public function increment(
        string $key,
        int $value = 1
    ): int;

    /**
     * Decrement the value of an integer item in the cache.
     *
     * @param string $key The cache key.
     * @param int $value The amount to decrement by.
     * @return int
     */
    public function decrement(
        string $key,
        int $value = 1
    ): int;

    /**
     * Retrieve multiple items from the cache by key.
     *
     * @param array<string> $keys The keys to retrieve.
     * @return array<string, mixed>
     */
    public function many(
        array $keys
    ): array;

    /**
     * Store multiple items in the cache for a given number of seconds.
     *
     * @param array<string, mixed> $values An associative array of keys and values.
     * @param DateTimeInterface|DateInterval|int|null $ttl The time-to-live.
     * @return bool
     */
    public function putMany(
        array $values,
        DateTimeInterface|DateInterval|int|null $ttl = null
    ): bool;

    /**
     * Retrieve an item from the cache and then delete it.
     *
     * @param string $key The cache key.
     * @param mixed $default The default value.
     * @return mixed
     */
    public function pull(
        string $key,
        mixed $default = null
    ): mixed;

    /**
     * Get a lock instance.
     *
     * @param string $key
     * @param int $seconds
     * @param string|null $owner
     * @return Lock
     */
    public function lock(string $key, int $seconds = 0, ?string $owner = null): CacheLock;
}
