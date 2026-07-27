<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SchoolPalm\CacheStore\Providers\CacheStoreServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * Register package service providers.
     */
    protected function getPackageProviders(
        $app
    ): array {

        return [

            CacheStoreServiceProvider::class,

        ];
    }



    /**
     * Configure the testing environment.
     */
    protected function defineEnvironment(
        $app
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Application
        |--------------------------------------------------------------------------
        */

        $app['config']->set(
            'app.key',
            'base64:' . base64_encode(random_bytes(32))
        );



        /*
        |--------------------------------------------------------------------------
        | Laravel Cache
        |--------------------------------------------------------------------------
        */

        $app['config']->set(
            'cache.default',
            'array'
        );



        /*
        |--------------------------------------------------------------------------
        | Cache Store Package
        |--------------------------------------------------------------------------
        */

        $app['config']->set(
            'cache-store',
            [

                'driver' => 'array',

                'key_separator' => ':',

                'prefix' => 'schoolpalm',

                'context' => [

                    'tenant' => true,

                    'school' => true,

                ],

                'drivers' => [

                    'file' => [

                        'path' => __DIR__ . '/storage/cache',

                    ],

                    'database' => [

                        'table' => 'cache',

                    ],

                    'redis' => [

                        'connection' => 'default',

                    ],

                ],

            ]
        );


        $app['config']->set(
            'database.default',
            'testing'
        );

        $app['config']->set(
            'database.connections.testing',
            [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]
        );
    }



    /**
     * Perform test setup.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->defineDatabaseMigrations();
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/../workbench/database/migrations'
        );
    }
}
