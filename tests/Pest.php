<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use SchoolPalm\CacheStore\Tests\TestCase;

// Bind our custom package test case base globally for all Pest files in the Integration folder
uses(
    TestCase::class,
    RefreshDatabase::class
)->in('Integration', 'Feature');

//<?php

// DO NOT add namespace declarations here!
