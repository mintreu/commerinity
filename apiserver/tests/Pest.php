<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Support\Facades\Facade;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

if (! Facade::getFacadeApplication()) {
    Facade::setFacadeApplication(app());
}

if (! app()->bound('hash')) {
    app()->singleton('hash', function () {
        return new class {
            private readonly BcryptHasher $driver;

            public function __construct()
            {
                $this->driver = new BcryptHasher;
            }

            public function driver($name = null): BcryptHasher
            {
                return $this->driver;
            }

            public function make($value, array $options = []): string
            {
                return $this->driver->make($value, $options);
            }

            public function check($value, string $hashedValue, array $options = []): bool
            {
                return $this->driver->check($value, $hashedValue, $options);
            }

            public function needsRehash(string $hashedValue, array $options = []): bool
            {
                return $this->driver->needsRehash($hashedValue, $options);
            }

            public function info(string $hashedValue): array
            {
                return $this->driver->info($hashedValue);
            }
        };
    });
}

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
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
