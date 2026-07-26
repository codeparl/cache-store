<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Contracts;

use DateInterval;
use DateTimeInterface;

/**
 * Interface CacheDriver
 *
 * Defines the standard contract for cache driver implementations.
 */
interface CacheDriver
{
    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key The unique key of this item in the cache.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The cached value, or the default value if not found.
     */
    public function get(
        string $key,
        mixed $default = null
    ): mixed;

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param string $key The key under which to store the value.
     * @param mixed $value The value to store.
     * @param DateTimeInterface|DateInterval|int|null $ttl The time-to-live, in seconds, or as a DateTime/DateInterval instance.
     * @return bool True on success and false on failure.
     */
    public function put(
        string $key,
        mixed $value,
        DateTimeInterface|DateInterval|int|null $ttl = null
    ): bool;

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key The key under which to store the value.
     * @param mixed $value The value to store.
     * @return bool True on success and false on failure.
     */
    public function forever(
        string $key,
        mixed $value
    ): bool;

    /**
     * Store an item in the cache if the key does not exist.
     *
     * @param string $key The key under which to store the value.
     * @param mixed $value The value to store.
     * @param DateTimeInterface|DateInterval|int|null $ttl The time-to-live.
     * @return bool True if the item was added to the cache, false otherwise.
     */
    public function add(
        string $key,
        mixed $value,
        DateTimeInterface|DateInterval|int|null $ttl = null
    ): bool;

    /**
     * Determine if an item exists in the cache.
     *
     * @param string $key The key to check.
     * @return bool True if the item exists, false otherwise.
     */
    public function has(
        string $key
    ): bool;

    /**
     * Remove an item from the cache.
     *
     * @param string $key The key of the item to remove.
     * @return bool True if the item was successfully removed. False if there was an error.
     */
    public function forget(
        string $key
    ): bool;

    /**
     * Remove all items from the cache.
     *
     * @return bool True on success and false on failure.
     */
    public function flush(): bool;

    /**
     * Increment the value of an integer item in the cache.
     *
     * @param string $key The key of the item to increment.
     * @param int $value The amount by which to increment.
     * @return int The new value.
     */
    public function increment(
        string $key,
        int $value = 1
    ): int;

    /**
     * Decrement the value of an integer item in the cache.
     *
     * @param string $key The key of the item to decrement.
     * @param int $value The amount by which to decrement.
     * @return int The new value.
     */
    public function decrement(
        string $key,
        int $value = 1
    ): int;

    /**
     * Retrieve an item from the cache and then delete it.
     *
     * @param string $key The key of the item to pull.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The cached value, or the default value if not found.
     */
    public function pull(
        string $key,
        mixed $default = null
    ): mixed;

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value in the returned array.
     *
     * @param array<string> $keys An array of keys to retrieve.
     * @return array<string, mixed> An associative array of cached values keyed by the requested keys.
     */
    public function many(
        array $keys
    ): array;

    /**
     * Store multiple items in the cache for a given number of seconds.
     *
     * @param array<string, mixed> $values An associative array of keys and values to store.
     * @param DateTimeInterface|DateInterval|int|null $ttl The time-to-live.
     * @return bool True on success and false on failure.
     */
    public function putMany(
        array $values,
        DateTimeInterface|DateInterval|int|null $ttl = null
    ): bool;
}
