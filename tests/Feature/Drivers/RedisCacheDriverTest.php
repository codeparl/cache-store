<?php

declare(strict_types=1);

use SchoolPalm\CacheStore\Drivers\RedisCacheDriver;
use SchoolPalm\CacheStore\Tests\Support\CreatesCacheDrivers;
use SchoolPalm\CacheStore\Tests\Concerns\CacheDriverBehaviour;


uses(
    CreatesCacheDrivers::class,
    CacheDriverBehaviour::class
);



beforeEach(function () {

    $this->driver = $this->makeDriver(
        RedisCacheDriver::class
    );


    $this->driver->flush();
});



require __DIR__ . '/../../Support/cacheDriverTests.php';
