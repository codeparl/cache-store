<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Support;

use RuntimeException;

final class CacheSerializer
{
    /**
     * Serialize a value for cache storage.
     *
     * Uses PHP serialization to preserve:
     * - arrays
     * - objects
     * - collections
     * - complex data types
     *
     * @param mixed $value
     * @return string
     */
    public function serialize(
        mixed $value
    ): string {

        try {

            return serialize($value);
        } catch (\Throwable $e) {

            throw new RuntimeException(
                'Unable to serialize cache value.',
                0,
                $e
            );
        }
    }



    /**
     * Unserialize a cached value.
     *
     * @param string $value
     * @return mixed
     */
    public function unserialize(
        string $value
    ): mixed {

        try {

            return unserialize(
                $value,
                [
                    'allowed_classes' => true,
                ]
            );
        } catch (\Throwable $e) {

            throw new RuntimeException(
                'Unable to unserialize cache value.',
                0,
                $e
            );
        }
    }



    /**
     * Determine if a value is serialized.
     */
    public function isSerialized(
        mixed $value
    ): bool {

        if (!is_string($value)) {
            return false;
        }


        $result = @unserialize(
            $value,
            [
                'allowed_classes' => false,
            ]
        );


        return $result !== false
            || $value === 'b:0;';
    }
}
