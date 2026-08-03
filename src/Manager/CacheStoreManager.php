<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Manager;

use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Cache\CacheLock;
use SchoolPalm\CacheStore\CacheDriverFactory;
use SchoolPalm\CacheStore\Contracts\CacheContextResolver;
use SchoolPalm\CacheStore\Contracts\CacheDriver;
use SchoolPalm\CacheStore\Contracts\CacheStore as CacheStoreContract;
use SchoolPalm\CacheStore\Support\CacheConfiguration;

/**
 * Class CacheStoreManager
 *
 * High-level cache manager.
 *
 * Provides:
 * - Context-aware cache keys
 * - Multiple cache drivers & dynamic store targeting
 * - Remember helpers with stampede protection via atomic locks
 * - Immutable fluency for safe context/driver chaining
 */
final class CacheStoreManager implements CacheStoreContract
{
    /**
     * Cached driver instances.
     *
     * @var array<string, CacheDriver>
     */
    protected array $drivers = [];

    /**
     * Active driver.
     */
    protected ?string $driver = null;

    /**
     * Active store configuration name.
     */
    protected ?string $store = null;

    /**
     * Active tags.
     *
     * @var array<string>
     */
    protected array $tags = [];

    /**
     * Current tenant context identifier.
     */
    protected ?string $tenantId = null;

    /**
     * Current school context identifier.
     */
    protected ?string $schoolId = null;

    public function __construct(
        protected readonly CacheDriverFactory $factory,
        protected CacheContextResolver $resolver,
        protected readonly CacheConfiguration $config,
    ) {}

    /**
     * Set active cache driver (Immutable).
     */
    public function driver(?string $driver = null): static
    {
        $clone = clone $this;
        $clone->driver = $driver;

        return $clone;
    }

    /**
     * Set cache store configuration name (Immutable).
     */
    public function store(?string $store = null): static
    {
        $clone = clone $this;
        $clone->store = $store;

        return $clone;
    }

    /**
     * Set cache context (Immutable).
     */
    public function forContext(?string $tenantId, ?string $schoolId): static
    {
        $clone = clone $this;
        $clone->tenantId = $tenantId;
        $clone->schoolId = $schoolId;

        $clone->resolver = $this->resolver->forContext(
            $tenantId,
            $schoolId
        );

        return $clone;
    }

    /**
     * Set cache tags (Immutable).
     */
    public function tags(array|string $tags): static
    {
        $clone = clone $this;
        $clone->tags = is_array($tags) ? $tags : [$tags];

        return $clone;
    }

    /**
     * Remember cached value with optional atomic lock protection against cache stampedes.
     */
    public function remember(
        string $key,
        DateTimeInterface|DateInterval|int|null $ttl,
        Closure $callback
    ): mixed {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $driver = $this->resolveDriver();

        // Attempt atomic locking to avoid race conditions if supported
        if ($driver instanceof CacheDriver && method_exists($driver, 'lock')) {
            $lockKey = 'lock:' . $this->resolveKey($key);

            return $driver->lock($lockKey, 10)->block(10, function () use ($key, $ttl, $callback) {
                // Re-check cache inside lock window
                $value = $this->get($key);

                if ($value !== null) {
                    return $value;
                }

                $value = $callback();
                $this->put($key, $value, $ttl);

                return $value;
            });
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    /**
     * Remember value forever.
     */
    public function rememberForever(
        string $key,
        Closure $callback
    ): mixed {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->forever($key, $value);

        return $value;
    }

    /**
     * Create cache lock.
     */
    public function lock(
        string $key,
        int $seconds = 0,
        ?string $owner = null
    ): CacheLock {
        $driver = $this->resolveDriver();

        if (!method_exists($driver, 'lock')) {
            throw new \RuntimeException(
                sprintf('Cache driver [%s] does not support locks.', get_class($driver))
            );
        }

        return $driver->lock(
            $this->resolveKey($key),
            $seconds,
            $owner
        );
    }

    /**
     * Get cached item.
     */
    public function get(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->resolveDriver()
            ->get(
                $this->resolveKey($key),
                $default
            );
    }

    /**
     * Store cached item.
     */
    public function put(
        string $key,
        mixed $value,
        DateTimeInterface|DateInterval|int|null $ttl = null
    ): bool {
        return $this->resolveDriver()
            ->put(
                $this->resolveKey($key),
                $value,
                $ttl
            );
    }

    /**
     * Store item indefinitely.
     */
    public function forever(
        string $key,
        mixed $value
    ): bool {
        return $this->resolveDriver()
            ->forever(
                $this->resolveKey($key),
                $value
            );
    }

    public function add(
        string $key,
        mixed $value,
        DateTimeInterface|DateInterval|int|null $ttl = null
    ): bool {
        return $this->resolveDriver()
            ->add(
                $this->resolveKey($key),
                $value,
                $ttl
            );
    }

    public function forget(
        string $key
    ): bool {
        return $this->resolveDriver()
            ->forget(
                $this->resolveKey($key)
            );
    }

    public function flush(): bool
    {
        return $this->resolveDriver()->flush();
    }

    public function increment(
        string $key,
        int $value = 1
    ): int {
        return $this->resolveDriver()
            ->increment(
                $this->resolveKey($key),
                $value
            );
    }

    public function decrement(
        string $key,
        int $value = 1
    ): int {
        return $this->resolveDriver()
            ->decrement(
                $this->resolveKey($key),
                $value
            );
    }

    public function many(array $keys): array
    {
        $mapping = [];

        foreach ($keys as $key) {
            $mapping[$this->resolveKey($key)] = $key;
        }

        $results = $this->resolveDriver()->many(
            array_keys($mapping)
        );

        $resolved = [];

        foreach ($results as $resolvedKey => $value) {
            $resolved[$mapping[$resolvedKey]] = $value;
        }

        return $resolved;
    }

    public function putMany(
        array $values,
        DateTimeInterface|DateInterval|int|null $ttl = null
    ): bool {
        $resolved = [];

        foreach ($values as $key => $value) {
            $resolved[$this->resolveKey($key)] = $value;
        }

        return $this->resolveDriver()
            ->putMany(
                $resolved,
                $ttl
            );
    }

    public function pull(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->resolveDriver()
            ->pull(
                $this->resolveKey($key),
                $default
            );
    }

    /**
     * Resolve active cache driver.
     */
    protected function resolveDriver(): CacheDriver
    {
        $driverName = $this->driver ?? $this->config->defaultDriver();
        $cacheKey = $driverName . ':' . ($this->store ?? 'default');

        if (!isset($this->drivers[$cacheKey])) {
            $this->drivers[$cacheKey] = $this->factory->make(
                $driverName,
                $this->store
            );
        }

        return $this->drivers[$cacheKey];
    }

    /**
     * Resolve context-aware key.
     */
    protected function resolveKey(string $key): string
    {
        // If an explicit context was set on this cloned instance via forContext(), use it;
        // otherwise fetch the current ambient instance from the container.
        $resolver = $this->tenantId !== null || $this->schoolId !== null
            ? $this->resolver
            : app(\SchoolPalm\CacheStore\Contracts\CacheContextResolver::class);

        return $resolver->resolve($key);
    }
}
