<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Support;

use RuntimeException;

final class CacheConfiguration
{
    /**
     * Package configuration.
     */
    public function __construct(
        private readonly array $config = []
    ) {}


    /**
     * Get the default cache driver.
     */
    public function defaultDriver(): string
    {
        return $this->config['default']
            ?? 'array';
    }

    public function keySeparator(): string
    {
        return config(
            'cache-store.key_separator',
            ':'
        );
    }


    /**
     * Get configured stores.
     */
    public function stores(): array
    {
        return $this->config['stores']
            ?? [];
    }


    /**
     * Get a specific store configuration.
     */
    public function store(
        string $name
    ): array {

        return $this->stores()[$name]
            ?? throw new RuntimeException(
                "Cache store [$name] is not configured."
            );
    }


    /**
     * Get default TTL.
     */
    public function defaultTtl(): ?int
    {
        return $this->config['ttl']
            ?? null;
    }


    /**
     * Get cache prefix.
     */
    public function prefix(): ?string
    {
        return $this->config['prefix']
            ?? null;
    }


    /**
     * Determine whether values should be serialized.
     */
    public function serialize(): bool
    {
        return $this->config['serialize']
            ?? true;
    }


    /**
     * Get driver-specific options.
     */
    public function driverOptions(
        string $driver
    ): array {

        return $this->config['drivers'][$driver]
            ?? [];
    }


    /**
     * Get Redis configuration.
     */
    public function redis(): array
    {
        return $this->driverOptions(
            'redis'
        );
    }


    /**
     * Get File configuration.
     */
    public function file(): array
    {
        return $this->driverOptions(
            'file'
        );
    }


    /**
     * Get Database configuration.
     */
    public function database(): array
    {
        return $this->driverOptions(
            'database'
        );
    }


    /**
     * Check if configuration exists.
     */
    public function has(
        string $key
    ): bool {

        return array_key_exists(
            $key,
            $this->config
        );
    }


    /**
     * Return raw configuration.
     */
    public function all(): array
    {
        return $this->config;
    }
}
