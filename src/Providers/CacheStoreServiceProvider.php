<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Providers;

use Illuminate\Support\ServiceProvider;
use SchoolPalm\CacheStore\CacheDriverFactory;
use SchoolPalm\CacheStore\Contracts\CacheContextResolver as CacheContextResolverContract;
use SchoolPalm\CacheStore\Context\CacheContextResolver;
use SchoolPalm\CacheStore\Context\CacheKeyBuilder;
use SchoolPalm\CacheStore\Manager\CacheStoreManager;
use SchoolPalm\CacheStore\Support\CacheConfiguration;
use SchoolPalm\CacheStore\Support\CacheSerializer;

final class CacheStoreServiceProvider extends ServiceProvider
{
    /**
     * Register package services.
     */
    public function register(): void
    {
        $this->app->singleton(
            CacheSerializer::class,
            fn() => new CacheSerializer()
        );

        $this->app->singleton(
            CacheConfiguration::class,
            fn($app) => new CacheConfiguration(
                $app->make('config')->get('cache-store', [])
            )
        );

        $this->app->singleton(
            CacheKeyBuilder::class,
            fn($app) => new CacheKeyBuilder(
                $app->make(CacheConfiguration::class)
            )
        );

        // Bind the interface to concrete implementation so container rebinding works seamlessly
        $this->app->singleton(
            CacheContextResolverContract::class,
            fn($app) => new CacheContextResolver(
                $app->make(CacheKeyBuilder::class)
            )
        );

        // Alias class-string to interface for convenience
        $this->app->alias(
            CacheContextResolverContract::class,
            CacheContextResolver::class
        );

        $this->app->singleton(
            CacheDriverFactory::class,
            fn($app) => new CacheDriverFactory($app)
        );

        // Register CacheStoreManager dynamic instance
        $this->app->singleton(
            CacheStoreManager::class,
            fn($app) => new CacheStoreManager(
                $app->make(CacheDriverFactory::class),
                $app->make(CacheContextResolverContract::class),
                $app->make(CacheConfiguration::class)
            )
        );

        // Bind string key for Facade root resolution
        $this->app->singleton(
            'cache-store',
            fn($app) => $app->make(CacheStoreManager::class)
        );
    }

    /**
     * Bootstrap package services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Publish Configuration
        |--------------------------------------------------------------------------
        */

        $this->publishes(
            [
                __DIR__ . '/../../config/cache-store.php'
                =>
                config_path('cache-store.php'),
            ],
            'cache-store-config'
        );

        /*
        |--------------------------------------------------------------------------
        | Publish Database Migrations
        |--------------------------------------------------------------------------
        */

        $this->publishes(
            [
                __DIR__
                    . '/../../database/migrations/create_cache_store_table.php'
                =>
                database_path(
                    'migrations/'
                        . date('Y_m_d_His')
                        . '_create_cache_store_table.php'
                ),
            ],
            'cache-store-migrations'
        );
    }
}
