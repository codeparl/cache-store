<p align="center">
    <img src="https://schoolpalm.com/images/cache-store-logo.png" alt="CacheStore Logo" width="200">
</p>

<h1 align="center">CacheStore — Context-Aware Cache for Laravel</h1>

<p align="center">
    <a href="https://packagist.org/packages/schoolpalm/cache-store">
        <img src="https://img.shields.io/packagist/v/schoolpalm/cache-store" alt="Latest Version">
    </a>
    <a href="https://packagist.org/packages/schoolpalm/cache-store">
        <img src="https://img.shields.io/packagist/dt/schoolpalm/cache-store" alt="Total Downloads">
    </a>
    <a href="https://packagist.org/packages/schoolpalm/cache-store">
        <img src="https://img.shields.io/packagist/php-v/schoolpalm/cache-store" alt="PHP Version">
    </a>
    <a href="https://github.com/schoolpalm/cache-store/blob/main/LICENSE">
        <img src="https://img.shields.io/github/license/schoolpalm/cache-store" alt="License">
    </a>
    <a href="https://github.com/schoolpalm/cache-store/actions">
        <img src="https://img.shields.io/github/actions/workflow/status/schoolpalm/cache-store/tests.yml" alt="Tests">
    </a>
</p>

---

**CacheStore** is a powerful, context-aware cache abstraction layer for Laravel applications. It goes beyond simple key-value storage by introducing **multi-tenant**, **multi-school**, and **custom context** scoping — making it ideal for SaaS platforms, school management systems, and any application that needs isolated cache namespaces.

## Features

- 🏢 **Context-Aware Caching** — Automatically scope cache keys by tenant, school, or custom context
- 🚀 **Multiple Drivers** — Array, File, Database, Redis, and Laravel Cache drivers included
- 🔌 **Pluggable Architecture** — Implement your own drivers via the `CacheDriver` contract
- 🔄 **Serialization** — Automatic PHP serialization for complex types (arrays, objects)
- ⚡ **Atomic Operations** — Increment/decrement bypass serialization for raw numeric operations
- 🧪 **Test-Ready** — Array driver for testing, trait-based test helpers
- 📦 **Auto-Discovery** — Laravel package auto-discovery for service provider and facade

## Quick Start

```bash
composer require schoolpalm/cache-store
```

```php
use SchoolPalm\CacheStore\Facades\CacheStore;

// Basic usage
CacheStore::put('students_count', 500);
$count = CacheStore::get('students_count'); // 500

// Context-aware caching
CacheStore::forContext('tenant_abc', 'school_123')
    ->put('students', 500);

CacheStore::forContext('tenant_xyz', 'school_456')
    ->put('students', 900);

// Values are isolated per context!
CacheStore::forContext('tenant_abc', 'school_123')
    ->get('students'); // 500

CacheStore::forContext('tenant_xyz', 'school_456')
    ->get('students'); // 900
```

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Drivers](#drivers)
- [Context-Aware Caching](#context-aware-caching)
- [Testing](#testing)
- [Driver Contract](#driver-contract)
- [Requirements](#requirements)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security](#security)
- [License](#license)

## Installation

You can install the package via Composer:

```bash
composer require schoolpalm/cache-store
```

### Service Provider Discovery

Laravel will auto-discover the `CacheStoreServiceProvider`. If you have disabled package discovery, register it manually in `config/app.php`:

```php
'providers' => [
    SchoolPalm\CacheStore\Providers\CacheStoreServiceProvider::class,
],
```

### Facade

The `CacheStore` facade is also auto-discovered. If needed, register it manually:

```php
'aliases' => [
    'CacheStore' => SchoolPalm\CacheStore\Facades\CacheStore::class,
],
```

### Publishing Configuration

```bash
php artisan vendor:publish --provider="SchoolPalm\CacheStore\Providers\CacheStoreServiceProvider" --tag="config"
```

## Configuration

The package configuration file `config/cache-store.php` is automatically merged with your application's config.

```php
return [

    /*
    | Default cache driver used by CacheStore.
    */
    'driver' => env('CACHE_STORE_DRIVER', 'file'),

    /*
    | Separator used when building context-aware cache keys.
    | Example: tenant:school:key  or  tenant.school.key
    */
    'key_separator' => env('CACHE_STORE_KEY_SEPARATOR', ':'),

    /*
    | Prefix for all generated cache keys.
    | Example: schoolpalm:tenant:1:school:5:students
    */
    'prefix' => env('CACHE_STORE_PREFIX', 'schoolpalm'),

    /*
    | Context settings
    */
    'context' => [
        'tenant' => true,  // Include tenant identifier
        'school' => true,  // Include school identifier
    ],

    /*
    | Driver-specific configurations
    */
    'drivers' => [
        'file' => [
            'path' => storage_path('framework/cache'),
        ],
        'redis' => [
            'connection' => env('CACHE_STORE_REDIS_CONNECTION', 'default'),
        ],
        'database' => [
            'table' => 'cache_store',
        ],
    ],
];
```

### Configuration Options

| Option                     | Type     | Default                           | Description                        |
| -------------------------- | -------- | --------------------------------- | ---------------------------------- |
| `driver`                   | `string` | `'file'`                          | Default cache driver               |
| `key_separator`            | `string` | `':'`                             | Separator for context key segments |
| `prefix`                   | `string` | `'schoolpalm'`                    | Global prefix for all cache keys   |
| `context.tenant`           | `bool`   | `true`                            | Enable tenant context scoping      |
| `context.school`           | `bool`   | `true`                            | Enable school context scoping      |
| `drivers.file.path`        | `string` | `storage_path('framework/cache')` | File cache storage path            |
| `drivers.redis.connection` | `string` | `'default'`                       | Redis connection name              |
| `drivers.database.table`   | `string` | `'cache_store'`                   | Database cache table name          |

## Usage

### Basic Cache Operations

```php
use SchoolPalm\CacheStore\Facades\CacheStore;

// Store a value
CacheStore::put('key', 'value', $ttl = 3600);

// Store a value permanently
CacheStore::forever('key', 'value');

// Retrieve a value
$value = CacheStore::get('key', $default = null);

// Check if key exists
if (CacheStore::has('key')) {
    // ...
}

// Store if not exists
CacheStore::add('key', 'value', $ttl = 3600);

// Retrieve and forget
$value = CacheStore::pull('key');

// Remove a value
CacheStore::forget('key');

// Increment / Decrement
CacheStore::increment('counter', 1);
CacheStore::decrement('counter', 1);

// Multiple values
CacheStore::putMany(['key1' => 'value1', 'key2' => 'value2']);
$values = CacheStore::many(['key1', 'key2']);

// Flush all cache
CacheStore::flush();
```

### Complex Data Types

```php
// Arrays
CacheStore::put('school', [
    'name' => 'Greenhill Academy',
    'students' => 1200,
    'teachers' => 80,
]);
$school = CacheStore::get('school');

// Objects
$user = (object) ['name' => 'John', 'role' => 'Admin'];
CacheStore::put('user', $user);
$retrieved = CacheStore::get('user'); // stdClass
```

### Remember Pattern

```php
// Cache the result of a callback
$value = CacheStore::remember('expensive_operation', 3600, function () {
    return someExpensiveOperation();
});

// Cache forever
$value = CacheStore::rememberForever('config', function () {
    return loadConfiguration();
});
```

## Drivers

CacheStore ships with five built-in drivers, each optimized for different use cases:

| Driver       | Class                 | Backend           | Use Case                      |
| ------------ | --------------------- | ----------------- | ----------------------------- |
| **Array**    | `ArrayCacheDriver`    | PHP memory        | Testing, local development    |
| **File**     | `FileCacheDriver`     | File system       | Single-server, small apps     |
| **Database** | `DatabaseCacheDriver` | SQL database      | Persistent, multi-server      |
| **Redis**    | `RedisCacheDriver`    | Redis             | High-performance, distributed |
| **Laravel**  | `LaravelCacheDriver`  | Any Laravel store | Bridge to existing cache      |

### Using a Specific Driver

```php
// At runtime via factory
$factory = app(\SchoolPalm\CacheStore\CacheDriverFactory::class);
$driver = $factory->driver('redis');
$driver->put('key', 'value');

// List available drivers
$drivers = $factory->available(); // ['array', 'file', 'database', 'redis', 'laravel']

// Check if a driver is available
if ($factory->has('redis')) {
    // ...
}
```

### Writing a Custom Driver

Implement the `CacheDriver` contract to create your own driver:

```php
use SchoolPalm\CacheStore\Contracts\CacheDriver;

class MongoCacheDriver implements CacheDriver
{
    // Implement all CacheDriver methods...
}

// Register it in the factory
$factory->extend('mongo', function () {
    return new MongoCacheDriver();
});
```

## Context-Aware Caching

The standout feature of CacheStore is **context-aware caching** — automatically scoping cache keys by tenant, school, or any custom context.

### How It Works

When you use a context, CacheStore prefixes your cache keys with context identifiers:

```
schoolpalm:tenant_abc:school_123:students
```

This ensures that the same key name in different contexts never collides.

### Usage

```php
// Store in a specific context
CacheStore::forContext('tenant_abc', 'school_123')
    ->put('students_count', 500);

// Retrieve from the same context
CacheStore::forContext('tenant_abc', 'school_123')
    ->get('students_count'); // 500

// Different context — isolated value
CacheStore::forContext('tenant_xyz', 'school_456')
    ->get('students_count'); // null
```

### Configuring Context

You can enable/disable context segments in the config:

```php
'context' => [
    'tenant' => true,  // Include tenant in key
    'school' => true,  // Include school in key
],
```

When disabled, the corresponding context segment is omitted from the cache key.

### Key Building Logic

The cache key is built using the following pattern:

```
{prefix}:{tenant}:{school}:{key}
```

Where:
- `{prefix}` — Global prefix from config (`schoolpalm`)
- `{tenant}` — Tenant identifier (if `context.tenant` is enabled)
- `{school}` — School identifier (if `context.school` is enabled)
- `{key}` — Your cache key
- Segments are joined using the configured `key_separator`

## Testing

CacheStore makes testing a breeze with its **Array driver** and shared test traits.

### Running Tests

```bash
composer test
```

### Test Traits

Use the `CacheDriverBehaviour` trait to run a comprehensive test suite against any driver:

```php
use SchoolPalm\CacheStore\Drivers\ArrayCacheDriver;
use SchoolPalm\CacheStore\Tests\Concerns\CacheDriverBehaviour;

uses(CacheDriverBehaviour::class);

beforeEach(function () {
    $this->driver = new ArrayCacheDriver(
        app(\Illuminate\Contracts\Cache\Repository::class),
        app(\SchoolPalm\CacheStore\Support\CacheSerializer::class)
    );
});
```

This automatically tests:
- Store & retrieve values
- Store & retrieve integer values
- Return default value for missing keys
- Key existence checks
- Overwrite existing values
- Forget operations
- Array storage
- Object storage
- Boolean storage
- Forever storage
- Add (unique key) operations
- Pull operations
- Pull returns default for missing keys
- Increment / Decrement
- Many operations
- Many returns null for missing keys
- Flush operations

### Creating a Custom Driver Test

```php
use SchoolPalm\CacheStore\Drivers\YourDriver;
use SchoolPalm\CacheStore\Tests\Concerns\CacheDriverBehaviour;
use SchoolPalm\CacheStore\Tests\Support\CreatesCacheDrivers;

uses(
    CreatesCacheDrivers::class,
    CacheDriverBehaviour::class
);

beforeEach(function () {
    $this->driver = $this->makeDriver(
        YourDriver::class
    );
});
```

> **Note:** Include `tests/Support/cacheDriverTests.php` in your test file to execute the shared test suite.

## Driver Contract

All cache drivers must implement the `SchoolPalm\CacheStore\Contracts\CacheDriver` interface:

```php
namespace SchoolPalm\CacheStore\Contracts;

interface CacheDriver
{
    public function get(string $key, mixed $default = null): mixed;
    public function put(string $key, mixed $value, DateTimeInterface|DateInterval|int|null $ttl = null): bool;
    public function forever(string $key, mixed $value): bool;
    public function add(string $key, mixed $value, DateTimeInterface|DateInterval|int|null $ttl = null): bool;
    public function has(string $key): bool;
    public function forget(string $key): bool;
    public function flush(): bool;
    public function increment(string $key, int $value = 1): int;
    public function decrement(string $key, int $value = 1): int;
    public function pull(string $key, mixed $default = null): mixed;
    public function many(array $keys): array;
    public function putMany(array $values, DateTimeInterface|DateInterval|int|null $ttl = null): bool;
}
```

### Serialization Behavior

Drivers handle serialization automatically via `CacheSerializer`:

| Value Type | Storage Format                   | Retrieval                      |
| ---------- | -------------------------------- | ------------------------------ |
| `string`   | Stored as-is                     | Returned as `string`           |
| `int`      | Stored as string                 | Cast back to `int`             |
| `float`    | Stored as string                 | Cast back to `float`           |
| `bool`     | PHP serialized (`b:1;` / `b:0;`) | Unserialized to `bool`         |
| `array`    | PHP serialized                   | Unserialized to `array`        |
| `object`   | PHP serialized                   | Unserialized to original class |
| `null`     | Not stored                       | Default value returned         |

> **Note:** `increment()` and `decrement()` bypass serialization for raw numeric operations at the storage layer.

## Requirements

- **PHP**: ^8.2
- **Laravel**: ^12.0 (Illuminate\Support and Illuminate\Cache)
- **Database Driver**: Requires a database connection when using the `database` cache driver
- **Redis Driver**: Requires `predis/predis` or `phpredis` extension when using the `redis` cache driver

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Development Setup

1. Clone the repository
2. Run `composer install`
3. Run `composer build` to set up the testbench workbench
4. Run `composer test` to execute tests

### Code Style

This package follows the PSR-12 coding standard.

## Security

Please see [SECURITY](SECURITY.md) for our security policy.

## Credits

- [SchoolPalm](https://schoolpalm.com)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

---

<p align="center">
    <sub>Built with ❤️ by SchoolPalm for the Laravel community</sub>
</p>

