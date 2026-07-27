<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use SchoolPalm\CacheStore\Drivers\DatabaseCacheDriver;
use SchoolPalm\CacheStore\Tests\Support\CreatesCacheDrivers;
use SchoolPalm\CacheStore\Tests\Concerns\CacheDriverBehaviour;


uses(
    CreatesCacheDrivers::class,
    CacheDriverBehaviour::class
);


beforeEach(function () {

    // Configure Laravel to use database cache store backed by SQLite
    config()->set('cache.default', 'database');

    config()->set('cache.stores.database', [

        'driver' => 'database',

        'table' => 'cache',

        'connection' => 'testing',

        'lock_connection' => 'testing',

    ]);


    // Create the native Laravel cache table needed by the database store
    Schema::connection('testing')->create('cache', function (Blueprint $table) {

        $table->string('key')->primary();

        $table->text('value');

        $table->integer('expiration');
    });
});


beforeEach(function () {

    $this->driver = $this->makeDriver(
        DatabaseCacheDriver::class
    );
});



require __DIR__ . '/../../Support/cacheDriverTests.php';
