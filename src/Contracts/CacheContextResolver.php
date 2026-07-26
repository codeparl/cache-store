<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Contracts;

/**
 * Interface CacheContextResolver
 *
 * Resolves cache context and generates
 * context-aware cache keys.
 */
interface CacheContextResolver
{
    /**
     * Configure current cache context.
     */
    public function forContext(
        ?string $tenantId,
        ?string $schoolId
    ): static;



    /**
     * Resolve a logical key into a context-aware key.
     */
    public function resolve(
        string $key
    ): string;



    /**
     * Get current tenant identifier.
     */
    public function tenantId(): ?string;



    /**
     * Get current school identifier.
     */
    public function schoolId(): ?string;



    /**
     * Determine whether context exists.
     */
    public function hasContext(): bool;
}
