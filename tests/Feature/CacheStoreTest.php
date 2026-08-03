<?php

declare(strict_types=1);

use SchoolPalm\CacheStore\Facades\CacheStore;

it('applies contextual scope to file cache keys in workbench', function () {
    /** @var \SchoolPalm\CacheStore\Manager\CacheStoreManager $manager */
    $manager = app(\SchoolPalm\CacheStore\Manager\CacheStoreManager::class);

    // 1. Store value with specific context
    $manager->forContext('tenant_1', 'school_A')->put('name', 'SchoolPalm');

    // 2. Resolve the full context key through your manager
    // (If resolveKey is protected, we resolve via your ContextResolver directly)
    $resolver = app(\SchoolPalm\CacheStore\Contracts\CacheContextResolver::class);
    $resolvedKey = $resolver->forContext('tenant_1', 'school_A')->resolve('name');

    // 3. Get the exact path calculated by Laravel's file driver
    /** @var \Illuminate\Cache\FileStore $fileStore */
    $fileStore = app('cache')->driver('file')->getStore();
    $actualFilePath = $fileStore->path($resolvedKey);

    // 4. Assert the file exists at the workbench path
    expect(file_exists($actualFilePath))->toBeTrue(
        "Cache file not found at expected path: {$actualFilePath}"
    );

    // 5. Verify contents
    $content = file_get_contents($actualFilePath);
    expect($content)->toContain('SchoolPalm');
});
it('correctly inspects underlying store and resolves context-scoped keys', function () {
    /** @var \SchoolPalm\CacheStore\Manager\CacheStoreManager $manager */
    $manager = app(\SchoolPalm\CacheStore\Manager\CacheStoreManager::class)
        ->driver('file')
        ->forContext(tenantId: 'tenant_123', schoolId: 'school_456');

    $key = 'user_settings';
    $value = 'active';

    // Store value using context-scoped manager
    $manager->put($key, $value, 60);

    // Assert retrieval
    expect($manager->get($key))->toBe($value);

    // Reflection inspection for test verification
    $reflection = new ReflectionClass($manager);

    $resolveKeyMethod = $reflection->getMethod('resolveKey');
    $resolveKeyMethod->setAccessible(true);
    $resolvedKey = $resolveKeyMethod->invoke($manager, $key);

    $driverMethod = $reflection->getMethod('resolveDriver');
    $driverMethod->setAccessible(true);
    $driver = $driverMethod->invoke($manager);

    // Verification Assertions
    expect(get_class($driver))->toBe(\SchoolPalm\CacheStore\Drivers\FileCacheDriver::class)
        ->and($resolvedKey)->toBe('schoolpalm:tenant_123:school_456:user_settings');

    // Clean up
    $manager->forget($key);
    expect($manager->get($key))->toBeNull();
});



it('automatically applies bound dummy resolver defaults and supports container rebound context', function () {
    /** @var \Illuminate\Cache\FileStore $fileStore */
    $fileStore = app('cache')->driver('file')->getStore();

    // 1. Transparent store (uses dummy default: tenant_1 & school_A)
    CacheStore::put('setting', 'active');

    // Expected key: schoolpalm:tenant_1:school_A:setting
    $defaultKey = 'schoolpalm:tenant_1:school_A:setting';
    $defaultPath = $fileStore->path($defaultKey);

    expect(file_exists($defaultPath))->toBeTrue();
    expect(CacheStore::get('setting'))->toBe('active');

    // 2. Re-bind resolver to simulate switching tenant/school in the app lifecycle
    $resolver = app(\SchoolPalm\CacheStore\Contracts\CacheContextResolver::class);
    app()->instance(
        \SchoolPalm\CacheStore\Contracts\CacheContextResolver::class,
        $resolver->forContext('tenant_2', 'school_B')
    );

    // 3. Cache miss under new ambient context (different key hash)
    expect(CacheStore::get('setting'))->toBeNull();

    // 4. Store under new context
    CacheStore::put('setting', 'inactive');

    // Expected key: schoolpalm:tenant_2:school_B:setting
    $newKey = 'schoolpalm:tenant_2:school_B:setting';
    $newPath = $fileStore->path($newKey);

    expect(file_exists($newPath))->toBeTrue();
    expect($newPath)->not->toBe($defaultPath);
});
it('stores and retrieves cache values', function () {

    CacheStore::put(
        'name',
        'SchoolPalm'
    );

    expect(
        CacheStore::get('name')
    )->toBe(
        'SchoolPalm'
    );
});

it('remembers cached values', function () {

    $counter = 0;

    $value = CacheStore::remember(
        'answer',
        60,
        function () use (&$counter) {

            $counter++;

            return 42;
        }
    );

    expect($value)->toBe(42);

    expect($counter)->toBe(1);

    $value = CacheStore::remember(
        'answer',
        60,
        function () use (&$counter) {

            $counter++;

            return 100;
        }
    );

    expect($value)->toBe(42);

    expect($counter)->toBe(1);
});

it('remembers values forever', function () {

    $counter = 0;

    CacheStore::rememberForever(
        'forever',
        function () use (&$counter) {

            $counter++;

            return 'cached';
        }
    );

    CacheStore::rememberForever(
        'forever',
        function () use (&$counter) {

            $counter++;

            return 'new';
        }
    );

    expect($counter)->toBe(1);

    expect(
        CacheStore::get('forever')
    )->toBe('cached');
});

it('forgets cached values', function () {

    CacheStore::put(
        'name',
        'SchoolPalm'
    );

    expect(
        CacheStore::forget('name')
    )->toBeTrue();

    expect(
        CacheStore::get('name')
    )->toBeNull();
});
it('pulls values from cache', function () {

    CacheStore::put(
        'language',
        'PHP'
    );

    expect(
        CacheStore::pull('language')
    )->toBe('PHP');

    expect(
        CacheStore::get('language')
    )->toBeNull();
});
it('stores arrays', function () {

    $data = [

        'name' => 'Emma',

        'students' => 1200,

    ];

    CacheStore::put(
        'school',
        $data
    );

    expect(
        CacheStore::get('school')
    )->toBe($data);
});
it('stores objects', function () {

    $object = (object) [

        'name' => 'Hassan',

        'role' => 'CEO',

    ];

    CacheStore::put(
        'user',
        $object
    );

    expect(
        CacheStore::get('user')
    )->toEqual($object);
});

it('isolates cache by context', function () {

    CacheStore::forContext(
        'emma',
        'main'
    )->put(
        'students',
        500
    );

    CacheStore::forContext(
        'greenhill',
        'main'
    )->put(
        'students',
        900
    );

    expect(
        CacheStore::forContext(
            'emma',
            'main'
        )->get('students')
    )->toBe(500);

    expect(
        CacheStore::forContext(
            'greenhill',
            'main'
        )->get('students')
    )->toBe(900);
});
it('stores and retrieves multiple values', function () {

    CacheStore::putMany([
        'students' => 500,
        'teachers' => 40,
        'classes' => 20,
    ]);


    expect(
        CacheStore::many([
            'students',
            'teachers',
            'classes',
        ])
    )->toBe([
        'students' => 500,
        'teachers' => 40,
        'classes' => 20,
    ]);
});
it('increments and decrements values', function () {

    CacheStore::put(
        'visits',
        10
    );


    expect(
        CacheStore::increment('visits')
    )->toBe(11);


    expect(
        CacheStore::increment('visits', 5)
    )->toBe(16);


    expect(
        CacheStore::decrement('visits', 6)
    )->toBe(10);
});
it('flushes cache values', function () {

    CacheStore::put(
        'one',
        1
    );

    CacheStore::put(
        'two',
        2
    );


    expect(
        CacheStore::flush()
    )->toBeTrue();


    expect(
        CacheStore::get('one')
    )->toBeNull();


    expect(
        CacheStore::get('two')
    )->toBeNull();
});
it('supports custom drivers', function () {

    $factory = app(
        \SchoolPalm\CacheStore\CacheDriverFactory::class
    );


    expect(
        $factory->available()
    )->toContain('array');


    expect(
        $factory->has('array')
    )->toBeTrue();
});
