<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SchoolPalm\CacheStore\Contracts\CacheContextResolver;
use SchoolPalm\CacheStore\Providers\CacheStoreServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * Register package service providers.
     */
    protected function getPackageProviders($app): array
    {
        return [
            CacheStoreServiceProvider::class,
        ];
    }

    /**
     * Configure the testing environment.
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set(
            'app.key',
            'base64:' . base64_encode(random_bytes(32))
        );

        /*
        |--------------------------------------------------------------------------
        | Laravel Cache Configuration
        |--------------------------------------------------------------------------
        */
        $app['config']->set('cache.default', 'file');
        $app['config']->set('cache.stores.file', [
            'driver' => 'file',
            'path'   => __DIR__ . '/../workbench/storage/cache',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Cache Store Package Configuration
        |--------------------------------------------------------------------------
        */
        $app['config']->set('cache-store', [
            'driver'        => 'file',
            'key_separator' => ':',
            'prefix'        => 'schoolpalm',
            'context'       => [
                'tenant' => true,
                'school' => true,
            ],
            'drivers'       => [
                'file' => [
                    'path' => __DIR__ . '/../workbench/storage/cache',
                ],
                'database' => [
                    'table' => 'cache',
                ],
                'redis' => [
                    'connection' => 'default',
                ],
            ],
        ]);

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'                  => 'sqlite',
            'database'                => ':memory:',
            'prefix'                  => '',
            'foreign_key_constraints' => true,
        ]);
    }

    /**
     * Perform test setup.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Clean out cache files before running test
        $storagePath = __DIR__ . '/../workbench/storage/cache';
        if (file_exists($storagePath)) {
            $this->app->make('files')->cleanDirectory($storagePath);
        } else {
            $this->app->make('files')->makeDirectory($storagePath, 0755, true);
        }

        // Bind dummy resolver if not bound elsewhere
        $this->app->singleton(CacheContextResolver::class, function () {
            return new class implements CacheContextResolver {
                public function __construct(
                    private ?string $tenantId = 'tenant_1',
                    private ?string $schoolId = 'school_A'
                ) {}

                public function tenantId(): ?string
                {
                    return $this->tenantId;
                }
                public function schoolId(): ?string
                {
                    return $this->schoolId;
                }

                public function hasContext(): bool
                {
                    return $this->tenantId !== null || $this->schoolId !== null;
                }

                public function forContext(?string $tenantId, ?string $schoolId): static
                {
                    return new static($tenantId, $schoolId);
                }

                public function resolve(string $key): string
                {
                    $parts = array_filter(['schoolpalm', $this->tenantId, $this->schoolId, $key]);
                    return implode(':', $parts);
                }
            };
        });

        $this->defineDatabaseMigrations();
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/../workbench/database/migrations'
        );
    }
}
