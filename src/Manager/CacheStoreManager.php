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
 * High level cache manager.
 *
 * Provides:
 *
 * - context aware cache keys
 * - multiple cache drivers
 * - Laravel compatible cache API
 * - remember helpers
 * - atomic locks
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
     * Active store.
     */
    protected ?string $store = null;


    /**
     * Active tags.
     *
     * @var array<string>
     */
    protected array $tags = [];


    /**
     * Current tenant.
     */
    protected ?string $tenantId = null;


    /**
     * Current school.
     */
    protected ?string $schoolId = null;



    public function __construct(
        protected readonly CacheDriverFactory $factory,
        protected  CacheContextResolver $resolver,
        protected readonly CacheConfiguration $config,
    ) {}



    /**
     * Set active cache driver.
     */
    public function driver(
        ?string $driver = null
    ): static {

        $this->driver = $driver;

        return $this;
    }



    /**
     * Set cache store.
     */
    public function store(
        ?string $store = null
    ): static {

        $this->store = $store;

        return $this;
    }



    /**
     * Set cache context.
     */
    public function forContext(
        ?string $tenantId,
        ?string $schoolId
    ): static {

        $this->tenantId = $tenantId;
        $this->schoolId = $schoolId;


        $this->resolver =
            $this->resolver->forContext(
                $tenantId,
                $schoolId
            );


        return $this;
    }



    /**
     * Set cache tags.
     */
    public function tags(
        array|string $tags
    ): static {

        $this->tags =
            is_array($tags)
            ? $tags
            : [$tags];


        return $this;
    }



    /**
     * Remember cached value.
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


        $value = $callback();


        $this->put(
            $key,
            $value,
            $ttl
        );


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


        $this->forever(
            $key,
            $value
        );


        return $value;
    }



    /**
     * Create cache lock.
     *
     * Note:
     * Drivers must support locking.
     */
    public function lock(
        string $key,
        int $seconds
    ): CacheLock {

        $driver = $this->resolveDriver();


        if (!method_exists($driver, 'lock')) {

            throw new \RuntimeException(
                'Cache driver does not support locks.'
            );
        }


        return $driver->lock(
            $this->resolveKey($key),
            $seconds
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
     * Store forever.
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
        return $this->resolveDriver()
            ->flush();
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
        $driver =
            $this->driver
            ??
            $this->config->defaultDriver();


        if (!isset($this->drivers[$driver])) {

            $this->drivers[$driver] =
                $this->factory->make(
                    $driver
                );
        }


        return $this->drivers[$driver];
    }



    /**
     * Resolve context-aware key.
     */
    protected function resolveKey(
        string $key
    ): string {

        return $this->resolver->resolve(
            $key
        );
    }
}
