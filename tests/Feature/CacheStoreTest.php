<?php

declare(strict_types=1);

use SchoolPalm\CacheStore\Facades\CacheStore;

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
