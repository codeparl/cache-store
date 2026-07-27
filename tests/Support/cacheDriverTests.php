<?php

declare(strict_types=1);

test('stores and retrieves values', function () {

    $this->cacheDriver()->put(
        'name',
        'SchoolPalm'
    );


    expect(
        $this->cacheDriver()->get('name')
    )->toBe('SchoolPalm');
});



test('stores and retrieves integer values', function () {

    $this->cacheDriver()->put(
        'count',
        42
    );


    expect(
        $this->cacheDriver()->get('count')
    )->toBe(42);
});



test('returns default value for missing keys', function () {

    expect(
        $this->cacheDriver()->get('nonexistent', 'fallback')
    )->toBe('fallback');


    expect(
        $this->cacheDriver()->get('nonexistent')
    )->toBeNull();
});



test('checks existing keys', function () {

    $this->cacheDriver()->put(
        'name',
        'SchoolPalm'
    );


    expect(
        $this->cacheDriver()->has('name')
    )->toBeTrue();


    expect(
        $this->cacheDriver()->has('nonexistent')
    )->toBeFalse();
});



test('overwrites existing values', function () {

    $this->cacheDriver()->put(
        'key',
        'original'
    );


    $this->cacheDriver()->put(
        'key',
        'updated'
    );


    expect(
        $this->cacheDriver()->get('key')
    )->toBe('updated');
});



test('forgets values', function () {

    $this->cacheDriver()->put(
        'name',
        'SchoolPalm'
    );


    expect(
        $this->cacheDriver()->forget('name')
    )->toBeTrue();


    expect(
        $this->cacheDriver()->get('name')
    )->toBeNull();
});



test('stores arrays', function () {

    $data = [

        'students' => 500,

        'teachers' => 30,

    ];


    $this->cacheDriver()->put(
        'school',
        $data
    );


    expect(
        $this->cacheDriver()->get('school')
    )->toBe($data);
});



test('stores objects', function () {

    $object = (object) [

        'name' => 'Test',

        'value' => 123,

    ];


    $this->cacheDriver()->put(
        'object',
        $object
    );


    expect(
        $this->cacheDriver()->get('object')
    )->toEqual($object);
});



test('stores boolean values', function () {

    $this->cacheDriver()->put('flag_true', true);
    $this->cacheDriver()->put('flag_false', false);


    expect(
        $this->cacheDriver()->get('flag_true')
    )->toBeTrue();


    expect(
        $this->cacheDriver()->get('flag_false')
    )->toBeFalse();
});



test('stores values forever', function () {

    $this->cacheDriver()->forever(
        'permanent',
        'eternal'
    );


    expect(
        $this->cacheDriver()->get('permanent')
    )->toBe('eternal');
});



test('adds values only if key does not exist', function () {

    $added = $this->cacheDriver()->add(
        'unique',
        'first'
    );


    expect($added)->toBeTrue();


    expect(
        $this->cacheDriver()->get('unique')
    )->toBe('first');


    $addedAgain = $this->cacheDriver()->add(
        'unique',
        'second'
    );


    expect($addedAgain)->toBeFalse();


    expect(
        $this->cacheDriver()->get('unique')
    )->toBe('first');
});



test('pulls values from cache', function () {

    $this->cacheDriver()->put(
        'temporary',
        'value'
    );


    expect(
        $this->cacheDriver()->pull('temporary')
    )->toBe('value');


    expect(
        $this->cacheDriver()->get('temporary')
    )->toBeNull();
});



test('pulls returns default for missing keys', function () {

    expect(
        $this->cacheDriver()->pull('missing', 'default_back')
    )->toBe('default_back');
});



test('increments numeric values', function () {

    $this->cacheDriver()->put(
        'counter',
        0
    );


    expect(
        $this->cacheDriver()->increment('counter')
    )->toBe(1);


    expect(
        $this->cacheDriver()->increment('counter', 5)
    )->toBe(6);


    expect(
        $this->cacheDriver()->get('counter')
    )->toBe(6);
});



test('decrements numeric values', function () {

    $this->cacheDriver()->put(
        'counter',
        10
    );


    expect(
        $this->cacheDriver()->decrement('counter')
    )->toBe(9);


    expect(
        $this->cacheDriver()->decrement('counter', 4)
    )->toBe(5);


    expect(
        $this->cacheDriver()->get('counter')
    )->toBe(5);
});



test('stores and retrieves multiple values', function () {

    $this->cacheDriver()->putMany([

        'a' => 1,

        'b' => 2,

        'c' => 3,

    ]);


    expect(
        $this->cacheDriver()->many([
            'a',
            'b',
            'c',
        ])
    )->toBe([

        'a' => 1,

        'b' => 2,

        'c' => 3,

    ]);
});



test('many returns null for missing keys', function () {

    $this->cacheDriver()->put('exists', 'value');


    expect(
        $this->cacheDriver()->many([
            'exists',
            'missing',
        ])
    )->toBe([

        'exists' => 'value',

        'missing' => null,

    ]);
});



test('flushes all cache values', function () {

    $this->cacheDriver()->put('key1', 'value1');
    $this->cacheDriver()->put('key2', 'value2');


    expect(
        $this->cacheDriver()->flush()
    )->toBeTrue();


    expect(
        $this->cacheDriver()->get('key1')
    )->toBeNull();


    expect(
        $this->cacheDriver()->get('key2')
    )->toBeNull();
});
