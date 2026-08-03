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
 * Class ArrayCacheDriver
 *
 * In-memory array based cache driver.
 *
 * This driver stores cache values inside the current PHP process memory.
 *
 * Suitable for:
 *
 * - automated tests
 * - local development
 * - temporary runtime caching
 *
 * Limitations:
 *
 * - data is lost when the PHP process ends
 * - not suitable for distributed applications
 * - not suitable for production multi-server environments
 *
 * ---
 *
 * Serialization behavior:
 *
 * All values are serialized using CacheSerializer before being persisted
 * to the underlying storage engine. This ensures consistent data
 * representation across different cache driver implementations.
 *
 * When values are retrieved, they are unserialized back to their original
 * PHP types. If the key does not exist in the underlying store, the
 * provided default value is returned without attempting unserialization.
 *
 * Numeric atomic operations (increment/decrement) bypass serialization
 * entirely, as they require raw numeric values at the storage layer.
 */
final class ArrayCacheDriver implements CacheDriver
{
    /**
     * Create a new array cache driver.
     *
     * @param Repository      $cache      The underlying cache repository.
     * @param CacheSerializer $serializer The serializer for value serialization.
     */
    public function __construct(
        private readonly Repository $cache,
        protected CacheSerializer $serializer
    ) {}

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
     * Retrieve an item from cache.
     *
     * The stored value is unserialized before being returned.
     * If the key does not exist, the default value is returned
     * without attempting unserialization.
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
     * Determine whether an item exists.
     *
     * Only checks key existence without unserializing the value.
     */
    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

    /**
     * Store an item only if it does not already exist.
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
    public function forget(string $key): bool
    {
        return $this->cache->forget($key);
    }

    /**
     * Retrieve an item and remove it.
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
     * Increment a numeric cache value.
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
     * Decrement a numeric cache value.
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
     * Retrieve multiple cache values.
     *
     * Each stored value is unserialized before being returned.
     * Keys that do not exist will have a null value in the result.
     */
    public function many(array $keys): array
    {
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
     * Store multiple cache values.
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
     * Remove all cache items.
     *
     * Does not involve the serializer.
     */
    public function flush(): bool
    {
        return $this->cache->getStore()->flush();
    }

    /**
     * Create an in-memory atomic cache lock.
     *
     * @param string $key The key for the lock.
     * @param int $seconds The number of seconds the lock should be held.
     * @param string|null $owner The lock owner identifier.
     * @return Lock
     * @throws \RuntimeException If the underlying store does not support atomic locking.
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
     * Get path identifier (always returns null for in-memory array storage).
     */
    public function getPath(string $key): ?string
    {
        return null;
    }

    /**
     * Get the underlying ArrayStore instance.
     */
    public function getStore(): mixed
    {
        return $this->cache->getStore();
    }
}
