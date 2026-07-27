<?php

declare(strict_types=1);

use SchoolPalm\CacheStore\Drivers\FileCacheDriver;
use SchoolPalm\CacheStore\Tests\Support\CreatesCacheDrivers;
use SchoolPalm\CacheStore\Tests\Concerns\CacheDriverBehaviour;


uses(
    CreatesCacheDrivers::class,
    CacheDriverBehaviour::class
);



beforeEach(function () {

    $this->driver = $this->makeDriver(
        FileCacheDriver::class
    );
});



require __DIR__ . '/../../Support/cacheDriverTests.php';
