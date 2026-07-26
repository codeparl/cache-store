<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Context;

use SchoolPalm\CacheStore\Context\CacheKeyBuilder;
use SchoolPalm\CacheStore\Contracts\CacheContextResolver as CacheContextResolverContract;

final class CacheContextResolver implements CacheContextResolverContract
{
    private ?string $tenantId = null;

    private ?string $schoolId = null;


    public function __construct(
        protected CacheKeyBuilder $builder
    ) {}



    public function forContext(
        ?string $tenantId,
        ?string $schoolId
    ): static {

        $clone = clone $this;

        $clone->tenantId = $tenantId;
        $clone->schoolId = $schoolId;

        return $clone;
    }



    public function resolve(
        string $key
    ): string {

        return $this->builder
            ->tenant($this->tenantId)
            ->school($this->schoolId)
            ->build($key);
    }



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
        return $this->tenantId !== null
            || $this->schoolId !== null;
    }
}
