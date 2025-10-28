<?php

/*
|--------------------------------------------------------------------------
| Test Case Configuration
|--------------------------------------------------------------------------
|
| Configure test cases for different test types with appropriate traits
| and base classes for optimal testing experience.
|
*/

// Increase timeout for long-running tests and coverage generation
ini_set('max_execution_time', 3600);
ini_set('memory_limit', '2048M');

// Feature tests use RefreshDatabase for full database interactions
uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

// Unit tests don't need database refresh for better performance
uses(Tests\TestCase::class)->in('Unit');

// Architecture tests for code structure validation
uses(Tests\TestCase::class)->in('Architecture');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Architecture Tests
|--------------------------------------------------------------------------
|
| Architecture tests are available through the pestphp/pest-plugin-arch
| package. Custom architecture rules can be defined in the Architecture/
| directory to maintain code quality and enforce consistent patterns.
|
*/
