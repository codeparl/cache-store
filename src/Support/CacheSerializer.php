<?php

declare(strict_types=1);

namespace SchoolPalm\CacheStore\Support;

use RuntimeException;

final class CacheSerializer
{
    /**
     * Serialize a value for cache storage.
     *
     * Primitive values are stored directly so that
     * atomic operations like increment/decrement can work.
     *
     * Complex values are PHP serialized.
     *
     * @param mixed $value
     * @return string
     */
    public function serialize(
        mixed $value
    ): string {

        try {

            if ($this->isPrimitive($value)) {

                return (string) $value;
            }


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
     * Restore cached value.
     *
     * @param mixed $value The raw value from the cache.
     * @return mixed
     */
    public function unserialize(
        mixed $value
    ): mixed {

        try {

            /*
            |--------------------------------------------------------------------------
            | Non-string values (e.g. raw integers from increment/decrement)
            |--------------------------------------------------------------------------
            */

            if (!is_string($value)) {
                return $value;
            }

            /*
            |--------------------------------------------------------------------------
            | Primitive values
            |--------------------------------------------------------------------------
            */

            if (!$this->isSerialized($value)) {

                return $this->castPrimitive(
                    $value
                );
            }


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
     * Determine primitive values.
     *
     * Booleans are excluded from primitive handling so they
     * go through PHP serialize() and unserialize() correctly,
     * preserving their type as boolean.
     */
    protected function isPrimitive(
        mixed $value
    ): bool {

        return is_string($value)
            || is_int($value)
            || is_float($value);
    }



    /**
     * Convert primitive values back.
     */
    protected function castPrimitive(
        string $value
    ): mixed {


        if ($value === 'true') {
            return true;
        }


        if ($value === 'false') {
            return false;
        }


        if (is_numeric($value)) {

            return str_contains($value, '.')
                ? (float) $value
                : (int) $value;
        }


        return $value;
    }



    /**
     * Determine if value is PHP serialized.
     */
    public function isSerialized(
        mixed $value
    ): bool {

        if (!is_string($value)) {
            return false;
        }


        if (
            $value === ''
            || $value === '0'
        ) {
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
