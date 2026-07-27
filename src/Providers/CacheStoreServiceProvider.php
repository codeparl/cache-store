<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Providers;

use Illuminate\Support\ServiceProvider;
use SchoolPalm\CacheStore\CacheDriverFactory;
use SchoolPalm\CacheStore\Context\CacheContextResolver;
use SchoolPalm\CacheStore\Context\CacheKeyBuilder;
use SchoolPalm\CacheStore\Manager\CacheStoreManager;
use SchoolPalm\CacheStore\Support\CacheConfiguration;
use SchoolPalm\CacheStore\Support\CacheSerializer;

final class CacheStoreServiceProvider extends ServiceProvider
{
    /**
     * Register package services.
     *
     * Registers:
     * - CacheSerializer as a singleton
     * - CacheConfiguration from the published config
     * - CacheContextResolver
     * - CacheDriverFactory
     * - CacheStoreManager
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
                $app->make('config')
                    ->get('cache-store', [])
            )
        );


        $this->app->singleton(
            CacheKeyBuilder::class,
            fn($app) => new CacheKeyBuilder(
                $app->make(CacheConfiguration::class)
            )
        );


        $this->app->singleton(
            CacheContextResolver::class,
            fn($app) => new CacheContextResolver(
                $app->make(CacheKeyBuilder::class)
            )
        );


        $this->app->singleton(
            CacheDriverFactory::class,
            fn($app) => new CacheDriverFactory($app)
        );


        $this->app->singleton(
            'cache-store',
            fn($app) => new CacheStoreManager(
                $app->make(CacheDriverFactory::class),
                $app->make(CacheContextResolver::class),
                $app->make(CacheConfiguration::class)
            )
        );
    }



    /**
     * Bootstrap package services.
     *
     * Publishes:
     * - configuration
     * - database migrations
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
