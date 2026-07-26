<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Drivers;

use DateInterval;
use DateTimeInterface;
use SchoolPalm\CacheStore\Contracts\CacheDriver;

/**
 * Class MemoryCacheDriver
 *
 * A cache driver implementation that stores data in an array for the 
 * lifecycle of the current PHP process/request.
 */
final class MemoryCacheDriver implements CacheDriver
{
    /**
     * Stored values.
     *
     * @var array<string, array{value: mixed, expires: ?int}>
     */
    private array $items = [];

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param string $key The key under which to store the value.
     * @param mixed $value The value to store.
     * @param DateTimeInterface|DateInterval|int|null $ttl The time-to-live, in seconds, or as a DateTime/DateInterval instance.
     * @return bool True on success.
     */
    public function put(
        string $key,
        mixed $value,
        DateTimeInterface|DateInterval|int|null $ttl = null
    ): bool {
        $this->items[$key] = [
            'value' => $value,
            'expires' => $this->expiration($ttl),
        ];

        return true;
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key The key under which to store the value.
     * @param mixed $value The value to store.
     * @return bool True on success.
     */
    public function forever(
        string $key,
        mixed $value
    ): bool {
        $this->items[$key] = [
            'value' => $value,
            'expires' => null,
        ];

        return true;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key The unique key of this item in the cache.
     * @param mixed $default The default value to return if the key does not exist or has expired.
     * @return mixed The cached value, or the default value if not found.
     */
    public function get(
        string $key,
        mixed $default = null
    ): mixed {
        if (! $this->has($key)) {
            return $default;
        }

        return $this->items[$key]['value'];
    }

    /**
     * Determine if an item exists in the cache and is not expired.
     *
     * @param string $key The key to check.
     * @return bool True if the item exists and is valid, false otherwise.
     */
    public function has(
        string $key
    ): bool {
        if (!isset($this->items[$key])) {
            return false;
        }

        $expires = $this->items[$key]['expires'];

        if (
            $expires !== null
            && time() >= $expires
        ) {
            unset($this->items[$key]);

            return false;
        }

        return true;
    }

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
    ): bool {
        if ($this->has($key)) {
            return false;
        }

        return $this->put(
            $key,
            $value,
            $ttl
        );
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key The key of the item to remove.
     * @return bool True on success.
     */
    public function forget(
        string $key
    ): bool {
        unset(
            $this->items[$key]
        );

        return true;
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool True on success.
     */
    public function flush(): bool
    {
        $this->items = [];

        return true;
    }

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
    ): mixed {
        $value = $this->get(
            $key,
            $default
        );

        $this->forget($key);

        return $value;
    }

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
    ): int {
        $current = (int) $this->get(
            $key,
            0
        );

        $current += $value;

        $this->forever(
            $key,
            $current
        );

        return $current;
    }

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
    ): int {
        return $this->increment(
            $key,
            -$value
        );
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * @param array<string> $keys An array of keys to retrieve.
     * @return array<string, mixed> An associative array of cached values keyed by the requested keys.
     */
    public function many(
        array $keys
    ): array {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }

        return $result;
    }

    /**
     * Store multiple items in the cache for a given number of seconds.
     *
     * @param array<string, mixed> $values An associative array of keys and values to store.
     * @param DateTimeInterface|DateInterval|int|null $ttl The time-to-live.
     * @return bool True on success.
     */
    public function putMany(
        array $values,
        DateTimeInterface|DateInterval|int|null $ttl = null
    ): bool {
        foreach ($values as $key => $value) {
            $this->put(
                $key,
                $value,
                $ttl
            );
        }

        return true;
    }

    /**
     * Calculate the expiration timestamp based on the provided TTL.
     *
     * @param DateTimeInterface|DateInterval|int|null $ttl The time-to-live.
     * @return int|null The Unix timestamp when the item expires, or null if it shouldn't expire.
     */
    private function expiration(
        DateTimeInterface|DateInterval|int|null $ttl
    ): ?int {
        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof DateTimeInterface) {
            return $ttl->getTimestamp();
        }

        if ($ttl instanceof DateInterval) {
            $date = new \DateTimeImmutable();

            return $date
                ->add($ttl)
                ->getTimestamp();
        }

        return time() + $ttl;
    }
}
