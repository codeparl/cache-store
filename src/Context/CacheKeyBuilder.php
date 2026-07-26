<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Context;

use SchoolPalm\CacheStore\Support\CacheConfiguration;

/**
 * Class CacheKeyBuilder
 *
 * A utility class for constructing structured cache keys based on 
 * configuration prefixes, tenant context, and school context.
 */
final class CacheKeyBuilder
{
    /**
     * The tenant identifier context.
     *
     * @var string|null
     */
    private ?string $tenant = null;

    /**
     * The school identifier context.
     *
     * @var string|null
     */
    private ?string $school = null;

    /**
     * Create a new CacheKeyBuilder instance.
     *
     * @param CacheConfiguration $config The cache configuration instance.
     */
    public function __construct(
        protected CacheConfiguration $config
    ) {}

    /**
     * Set the tenant context for the cache key.
     *
     * @param string|null $tenant The tenant identifier.
     * @return $this
     */
    public function tenant(
        ?string $tenant
    ): self {
        $this->tenant = $tenant;

        return $this;
    }

    /**
     * Set the school context for the cache key.
     *
     * @param string|null $school The school identifier.
     * @return $this
     */
    public function school(
        ?string $school
    ): self {
        $this->school = $school;

        return $this;
    }

    /**
     * Build the fully qualified cache key.
     * 
     * Constructs the key by concatenating the configured prefix, 
     * the tenant ID (if set), the school ID (if set), and the provided 
     * base key using the configured key separator.
     *
     * @param string $key The base cache key.
     * @return string The fully constructed cache key.
     */
    public function build(
        string $key
    ): string {
        $separator = $this->config->keySeparator();

        $parts = [];

        if ($prefix = $this->config->prefix()) {
            $parts[] = $prefix;
        }

        if ($this->tenant !== null) {
            $parts[] = 'tenant';
            $parts[] = $this->tenant;
        }

        if ($this->school !== null) {
            $parts[] = 'school';
            $parts[] = $this->school;
        }

        $parts[] = ltrim(
            $key,
            '.:'
        );

        return implode(
            $separator,
            $parts
        );
    }
}
