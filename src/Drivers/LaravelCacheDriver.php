<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Drivers;

use DateInterval;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository;
use SchoolPalm\CacheStore\Contracts\CacheDriver;
use SchoolPalm\CacheStore\Support\CacheSerializer;

/**
 * Class LaravelCacheDriver
 *
 * Generic adapter for Laravel cache repositories.
 *
 * This driver allows CacheStore to wrap any cache backend
 * supported by Laravel without requiring a dedicated driver.
 *
 * Supported examples:
 *
 * - redis
 * - file
 * - database
 * - array
 * - dynamodb
 * - custom Laravel cache stores
 *
 * The actual storage mechanism is delegated to Laravel's
 * Cache Repository implementation.
 *
 * ---
 *
 * Serialization behavior:
 *
 * The underlying Laravel cache repository is treated as raw storage.
 * All values are serialized using CacheSerializer before being persisted,
 * and unserialized after retrieval. This ensures consistent data
 * representation regardless of the cache backend used.
 *
 * When values are retrieved, they are unserialized back to their original
 * PHP types. If the key does not exist in the underlying store, the
 * provided default value is returned without attempting unserialization.
 *
 * Numeric atomic operations (increment/decrement) bypass serialization
 * entirely, as they require raw numeric values at the storage layer.
 */
final class LaravelCacheDriver implements CacheDriver
{
    /**
     * Create a new Laravel cache driver instance.
     *
     * @param Repository      $cache      The underlying Laravel cache repository.
     * @param CacheSerializer $serializer The serializer for value serialization.
     */
    public function __construct(
        private readonly Repository $cache,
        protected CacheSerializer $serializer
    ) {}

    /**
     * Retrieve an item from cache.
     *
     * The stored value is unserialized before being returned.
     * If the key does not exist, the default value is returned
     * without attempting unserialization.
     *
     * @param string $key Cache key.
     * @param mixed $default Default value when missing.
     */
    public function get(
        string $key,
        mixed $default = null
    ): mixed {
        $value = $this->cache->get($key);

        if ($value === null) {
            return $default;
        }

        return $this->serializer->unserialize($value);
    }

    /**
     * Store an item in cache.
     *
     * The value is serialized before storage to ensure type consistency.
     *
     * @param string $key Cache key.
     * @param mixed $value Value to store.
     * @param DateTimeInterface|DateInterval|int|null $ttl Lifetime.
     */
    public function put(
        string $key,
        mixed $value,
        DateTimeInterface|DateInterval|int|null $ttl = null
    ): bool {
        return $this->cache->put(
            $key,
            $this->serializer->serialize($value),
            $ttl
        );
    }

    /**
     * Store an item permanently.
     *
     * The value is serialized before storage to ensure type consistency.
     */
    public function forever(
        string $key,
        mixed $value
    ): bool {
        return $this->cache->forever(
            $key,
            $this->serializer->serialize($value)
        );
    }

    /**
     * Determine if an item exists.
     *
     * Only checks key existence without unserializing the value.
     */
    public function has(
        string $key
    ): bool {
        return $this->cache->has($key);
    }

    /**
     * Add an item only if it does not exist.
     *
     * The value is serialized before storage.
     */
    public function add(
        string $key,
        mixed $value,
        DateTimeInterface|DateInterval|int|null $ttl = null
    ): bool {
        return $this->cache->add(
            $key,
            $this->serializer->serialize($value),
            $ttl
        );
    }

    /**
     * Remove an item from cache.
     */
    public function forget(
        string $key
    ): bool {
        return $this->cache->forget($key);
    }

    /**
     * Retrieve and remove an item.
     *
     * The stored value is unserialized before being returned.
     * If the key does not exist, the default value is returned.
     */
    public function pull(
        string $key,
        mixed $default = null
    ): mixed {
        $value = $this->cache->pull($key);

        if ($value === null) {
            return $default;
        }

        return $this->serializer->unserialize($value);
    }

    /**
     * Increment a numeric value.
     *
     * Bypasses serialization as this requires raw numeric values
     * at the storage layer.
     */
    public function increment(
        string $key,
        int $value = 1
    ): int {
        return $this->cache->increment($key, $value);
    }

    /**
     * Decrement a numeric value.
     *
     * Bypasses serialization as this requires raw numeric values
     * at the storage layer.
     */
    public function decrement(
        string $key,
        int $value = 1
    ): int {
        return $this->cache->decrement($key, $value);
    }

    /**
     * Retrieve multiple values.
     *
     * Each stored value is unserialized before being returned.
     * Keys that do not exist will have a null value in the result.
     */
    public function many(
        array $keys
    ): array {
        $values = [];

        foreach ($keys as $key) {
            $stored = $this->cache->get($key);

            $values[$key] = $stored !== null
                ? $this->serializer->unserialize($stored)
                : null;
        }

        return $values;
    }

    /**
     * Store multiple values.
     *
     * Each value is serialized before storage.
     */
    public function putMany(
        array $values,
        DateTimeInterface|DateInterval|int|null $ttl = null
    ): bool {
        foreach ($values as $key => $value) {
            $this->cache->put(
                $key,
                $this->serializer->serialize($value),
                $ttl
            );
        }

        return true;
    }

    /**
     * Remove all cached items.
     *
     * Does not involve the serializer.
     *
     * Uses the underlying store when supported.
     */
    public function flush(): bool
    {
        $store = $this->cache->getStore();

        if (method_exists($store, 'flush')) {
            return $store->flush();
        }

        return false;
    }

    /**
     * Create an atomic cache lock if supported by the underlying store.
     *
     * @param string $key The key for the lock.
     * @param int $seconds The number of seconds the lock should be held.
     * @param string|null $owner The lock owner identifier.
     * @return Lock
     * @throws \RuntimeException If the underlying store does not support atomic locks.
     */
    public function lock(string $key, int $seconds = 0, ?string $owner = null): Lock
    {
        $store = $this->cache->getStore();

        if ($store instanceof LockProvider) {
            return $store->lock($key, $seconds, $owner);
        }

        throw new \RuntimeException(
            sprintf('Cache store [%s] does not support atomic locks.', get_class($store))
        );
    }

    /**
     * Get path identifier (delegates to the underlying store if available).
     */
    public function getPath(string $key): ?string
    {
        $store = $this->cache->getStore();

        if (method_exists($store, 'path')) {
            return $store->path($key);
        }

        return null;
    }

    /**
     * Return the underlying Laravel cache store instance.
     */
    public function getStore(): mixed
    {
        return $this->cache->getStore();
    }
}
