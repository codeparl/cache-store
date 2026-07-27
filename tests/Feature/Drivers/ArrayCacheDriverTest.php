<?php

declare(strict_types=1);

use SchoolPalm\CacheStore\Drivers\ArrayCacheDriver;
use SchoolPalm\CacheStore\Tests\Concerns\CacheDriverBehaviour;


uses(CacheDriverBehaviour::class);


beforeEach(function () {

    $this->driver = new ArrayCacheDriver(

        app(\Illuminate\Contracts\Cache\Repository::class),

        app(\SchoolPalm\CacheStore\Support\CacheSerializer::class)

    );
});


require __DIR__ . '/../../Support/cacheDriverTests.php';
