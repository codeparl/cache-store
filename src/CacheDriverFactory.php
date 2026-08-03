<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore;

use Closure;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use SchoolPalm\CacheStore\Contracts\CacheDriver;
use SchoolPalm\CacheStore\Drivers\ArrayCacheDriver;
use SchoolPalm\CacheStore\Drivers\DatabaseCacheDriver;
use SchoolPalm\CacheStore\Drivers\FileCacheDriver;
use SchoolPalm\CacheStore\Drivers\LaravelCacheDriver;
use SchoolPalm\CacheStore\Drivers\MemoryCacheDriver;
use SchoolPalm\CacheStore\Drivers\RedisCacheDriver;

/**
 * Class CacheDriverFactory
 *
 * Responsible for creating cache driver instances.
 *
 * The factory separates driver creation from the cache manager,
 * allowing new drivers to be added without changing the manager logic.
 *
 * Supported built-in drivers:
 *
 * - array
 *      Laravel array cache driver, mainly used for testing.
 *
 * - memory
 *      Lightweight in-process cache implementation.
 *
 * - file
 *      File based persistent cache.
 *
 * - database
 *      Database backed cache storage.
 *
 * - redis
 *      Distributed production cache storage.
 *
 * - laravel
 *      Generic Laravel cache repository adapter.
 */
final class CacheDriverFactory
{
    /**
     * Custom driver creators.
     *
     * @var array<string, Closure(Container, ?string): CacheDriver>
     */
    private array $customCreators = [];

    /**
     * Built-in driver mappings.
     *
     * @var array<string, class-string<CacheDriver>>
     */
    private array $drivers = [
        'array' => ArrayCacheDriver::class,
        'memory' => MemoryCacheDriver::class,
        'file' => FileCacheDriver::class,
        'database' => DatabaseCacheDriver::class,
        'redis' => RedisCacheDriver::class,
        'laravel' => LaravelCacheDriver::class,
    ];

    /**
     * Create a new cache driver factory.
     */
    public function __construct(
        private readonly Container $container
    ) {}

    /**
     * Create a cache driver instance.
     *
     * @param string $driver The driver name (e.g., 'redis', 'memory').
     * @param string|null $store Optional named store configuration.
     * @return CacheDriver
     * @throws InvalidArgumentException
     */
    public function make(
        string $driver,
        ?string $store = null
    ): CacheDriver {
        $driverKey = strtolower($driver);

        /*
        |--------------------------------------------------------------------------
        | Custom Drivers
        |--------------------------------------------------------------------------
        */
        if (isset($this->customCreators[$driverKey])) {
            return ($this->customCreators[$driverKey])(
                $this->container,
                $store
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Built-in Drivers
        |--------------------------------------------------------------------------
        */
        if (isset($this->drivers[$driverKey])) {
            $driverClass = $this->drivers[$driverKey];

            return $this->container->make($driverClass, [
                'store' => $store,
            ]);
        }

        throw new InvalidArgumentException(
            "Unsupported cache driver [{$driver}]."
        );
    }

    /**
     * Register a custom cache driver.
     *
     * Example:
     *
     * CacheDriverFactory::extend(
     *     'mongodb',
     *     fn($app, $store) => new MongoCacheDriver($store)
     * );
     *
     * @param string $driver
     * @param Closure(Container, ?string): CacheDriver $creator
     */
    public function extend(
        string $driver,
        Closure $creator
    ): void {
        $this->customCreators[strtolower($driver)] = $creator;
    }

    /**
     * Determine whether a cache driver exists.
     */
    public function has(
        string $driver
    ): bool {
        $driver = strtolower($driver);

        return isset($this->drivers[$driver])
            || isset($this->customCreators[$driver]);
    }

    /**
     * Get all available cache driver names.
     *
     * @return array<int, string>
     */
    public function available(): array
    {
        return array_values(
            array_unique(
                array_merge(
                    array_keys($this->drivers),
                    array_keys($this->customCreators)
                )
            )
        );
    }

    /**
     * Register a built-in driver mapping.
     *
     * Useful for package extensions.
     */
    public function register(
        string $name,
        string $driverClass
    ): void {
        $this->drivers[strtolower($name)] = $driverClass;
    }
}
