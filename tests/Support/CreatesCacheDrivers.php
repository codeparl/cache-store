<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Tests\Support;

use Illuminate\Contracts\Cache\Repository;
use SchoolPalm\CacheStore\Support\CacheSerializer;

trait CreatesCacheDrivers
{
    protected function makeDriver(
        string $driver
    ): object {

        return new $driver(
            app(Repository::class),
            app(CacheSerializer::class)
        );
    }
}
