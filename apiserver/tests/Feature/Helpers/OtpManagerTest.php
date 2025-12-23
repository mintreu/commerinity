<?php

declare(strict_types=1);

use App\Helpers\OtpManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->manager = new OtpManager(
        cache()->store(),
        app('hash'),
        true
    );
});

test('generates fixed demo OTP in demo mode', function () {
    $otp = $this->manager->generate('9876543210');

    expect($otp)->toBe(123456);
});

test('stores OTP in cache with correct key', function () {
    $this->manager->generate('9876543210');

    expect(Cache::has('otp:'.hash('xxh3', '9876543210')))->toBeTrue();
});

test('verifies correct OTP successfully', function () {
    $otp = $this->manager->generate('9876543210');

    $result = $this->manager->verify('9876543210', (string) $otp);

    expect($result)->toBeTrue();
});

test('rejects incorrect OTP', function () {
    $this->manager->generate('9876543210');

    $result = $this->manager->verify('9876543210', '000000');

    expect($result)->toBeFalse();
});

test('rejects expired OTP after 10 minutes', function () {
    $this->manager->generate('9876543210');

    $this->travel(11)->minutes();

    $result = $this->manager->verify('9876543210', '123456');

    expect($result)->toBeFalse();
});

test('clears OTP from cache', function () {
    $otp = $this->manager->generate('9876543210');
    $this->manager->verify('9876543210', (string) $otp);
    $this->manager->clear('9876543210');

    $result = $this->manager->verify('9876543210', (string) $otp);

    expect($result)->toBeFalse();
});

test('enforces rate limit - 3 requests per 15 minutes', function () {
    $manager = new OtpManager(cache()->store(), app('hash'), false);

    $manager->generate('9876543210');
    $manager->generate('9876543210');
    $manager->generate('9876543210');

    $manager->generate('9876543210');
})->throws(RuntimeException::class, 'Too many OTP requests');

test('enforces max verification attempts - 5 attempts', function () {
    $this->manager->generate('9876543210');

    for ($i = 0; $i < 5; $i++) {
        $this->manager->verify('9876543210', '000000');
    }

    $this->manager->verify('9876543210', '000000');
})->throws(RuntimeException::class, 'Maximum OTP attempts exceeded');

test('validates credential is not empty', function () {
    $this->manager->generate('');
})->throws(RuntimeException::class, 'Credential cannot be empty');

test('validates credential is not whitespace', function () {
    $this->manager->generate('   ');
})->throws(RuntimeException::class, 'Credential cannot be empty');
