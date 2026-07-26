<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Drivers;

use DateInterval;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Repository;
use SchoolPalm\CacheStore\Contracts\CacheDriver;
use SchoolPalm\CacheStore\Support\CacheSerializer;

/**
 * Class FileCacheDriver
 *
 * A cache driver implementation that delegates caching operations
 * to an underlying Laravel cache repository.
 *
 * ---
 *
 * Serialization behavior:
 *
 * All values are serialized using CacheSerializer before being persisted
 * to the underlying file-based storage engine. This ensures consistent
 * data representation across different cache driver implementations.
 *
 * When values are retrieved, they are unserialized back to their original
 * PHP types. If the key does not exist in the underlying store, the
 * provided default value is returned without attempting unserialization.
 *
 * Numeric atomic operations (increment/decrement) bypass serialization
 * entirely, as they require raw numeric values at the storage layer.
 */
final class FileCacheDriver implements CacheDriver
{
    /**
     * Create a new FileCacheDriver instance.
     *
     * @param Repository      $cache      The underlying cache repository.
     * @param CacheSerializer $serializer The serializer for value serialization.
     */
    public function __construct(
        private readonly Repository $cache,
        protected CacheSerializer $serializer
    ) {}

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * The value is serialized before storage to ensure type consistency.
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
    ): bool {
        return $this->cache->put(
            $key,
            $this->serializer->serialize($value),
            $ttl
        );
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * The value is serialized before storage to ensure type consistency.
     *
     * @param string $key The key under which to store the value.
     * @param mixed $value The value to store.
     * @return bool True on success and false on failure.
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
     * Retrieve an item from the cache by key.
     *
     * The stored value is unserialized before being returned.
     * If the key does not exist, the default value is returned
     * without attempting unserialization.
     *
     * @param string $key The unique key of this item in the cache.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The cached value, or the default value if not found.
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
     * Determine if an item exists in the cache.
     *
     * Only checks key existence without unserializing the value.
     *
     * @param string $key The key to check.
     * @return bool True if the item exists, false otherwise.
     */
    public function has(
        string $key
    ): bool {
        return $this->cache->has(
            $key
        );
    }

    /**
     * Store an item in the cache if the key does not exist.
     *
     * The value is serialized before storage.
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
    ): bool {
        return $this->cache->add(
            $key,
            $this->serializer->serialize($value),
            $ttl
        );
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key The key of the item to remove.
     * @return bool True if the item was successfully removed, false otherwise.
     */
    public function forget(
        string $key
    ): bool {
        return $this->cache->forget(
            $key
        );
    }

    /**
     * Remove all items from the cache.
     *
     * Does not involve the serializer.
     *
     * @return bool True on success and false on failure.
     */
    public function flush(): bool
    {
        return $this->cache->getStore()->flush();
    }

    /**
     * Retrieve an item from the cache and then delete it.
     *
     * The stored value is unserialized before being returned.
     * If the key does not exist, the default value is returned.
     *
     * @param string $key The key of the item to pull.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The cached value, or the default value if not found.
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
     * Increment the value of an integer item in the cache.
     *
     * Bypasses serialization as this requires raw numeric values
     * at the storage layer.
     *
     * @param string $key The key of the item to increment.
     * @param int $value The amount by which to increment.
     * @return int The new value.
     */
    public function increment(
        string $key,
        int $value = 1
    ): int {
        return $this->cache->increment(
            $key,
            $value
        );
    }

    /**
     * Decrement the value of an integer item in the cache.
     *
     * Bypasses serialization as this requires raw numeric values
     * at the storage layer.
     *
     * @param string $key The key of the item to decrement.
     * @param int $value The amount by which to decrement.
     * @return int The new value.
     */
    public function decrement(
        string $key,
        int $value = 1
    ): int {
        return $this->cache->decrement(
            $key,
            $value
        );
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Each stored value is unserialized before being returned.
     * Keys that do not exist will have a null value in the result.
     *
     * @param array<string> $keys An array of keys to retrieve.
     * @return array<string, mixed> An associative array of cached values keyed by the requested keys.
     */
    public function many(
        array $keys
    ): array {

        $results = [];

        foreach ($keys as $key) {

            $stored = $this->cache->get($key);

            $results[$key] = $stored !== null
                ? $this->serializer->unserialize($stored)
                : null;
        }

        return $results;
    }

    /**
     * Store multiple items in the cache for a given number of seconds.
     *
     * Each value is serialized before storage.
     *
     * @param array<string, mixed> $values An associative array of keys and values to store.
     * @param DateTimeInterface|DateInterval|int|null $ttl The time-to-live.
     * @return bool True on success and false on failure.
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
}

