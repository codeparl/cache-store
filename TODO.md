# Fix all Driver Tests - TODO

## Plan

1. ✅ **Fix `tests/Concerns/CacheDriverBehaviour.php`**: Remove `defineCacheDriverBehaviour()` method, keep only `cacheDriver()` helper

2. ✅ **Create `tests/Support/cacheDriverTests.php`**: Define the 4 shared tests using `test()` at top level (Pest style)

3. ✅ **Fix `tests/Pest.php`**: Remove the stale `require_once` reference to cacheDriverTests.php

4. ✅ **Fix `tests/Feature/Drivers/ArrayCacheDriverTest.php`**: Use proper Pest pattern with `uses()` + `beforeEach()` + `require` shared file

5. ✅ **Fix `tests/Feature/Drivers/DatabaseCacheDriverTest.php`**: Same pattern

6. ✅ **Fix `tests/Feature/Drivers/FileCacheDriverTest.php`**: Same pattern

7. ✅ **Fix `tests/Feature/Drivers/LaravelCacheDriverTest.php`**: Same pattern

8. ✅ **Fix `tests/Feature/Drivers/RedisCacheDriverTest.php`**: Same pattern

9. ✅ **Run tests**: Execute `vendor/bin/pest` to verify all tests pass

## Result
**Tests: 102 passed (184 assertions)**
**Duration: 8.77s**

