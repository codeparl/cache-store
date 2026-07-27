<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Tests\Concerns;

trait CacheDriverBehaviour
{
    protected function cacheDriver()
    {
        return $this->driver;
    }
}
