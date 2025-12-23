<?php

declare(strict_types=1);

use App\Exceptions\Auth\UserBannedException;
use App\Exceptions\Auth\UserSuspendedException;

// ========================================
// UserBannedException Tests
// ========================================

test('UserBannedException has default message', function () {
    $exception = new UserBannedException;

    expect($exception->getMessage())->toBe('Account is banned');
});

test('UserBannedException has 403 code', function () {
    $exception = new UserBannedException;

    expect($exception->getCode())->toBe(403);
});

test('UserBannedException accepts custom message', function () {
    $exception = new UserBannedException('Your account has been permanently banned');

    expect($exception->getMessage())->toBe('Your account has been permanently banned')
        ->and($exception->getCode())->toBe(403);
});

test('UserBannedException is throwable', function () {
    throw new UserBannedException;
})->throws(UserBannedException::class, 'Account is banned');

test('UserBannedException extends Exception', function () {
    $exception = new UserBannedException;

    expect($exception)->toBeInstanceOf(Exception::class);
});

// ========================================
// UserSuspendedException Tests
// ========================================

test('UserSuspendedException has default message', function () {
    $exception = new UserSuspendedException;

    expect($exception->getMessage())->toBe('Account is suspended');
});

test('UserSuspendedException has 403 code', function () {
    $exception = new UserSuspendedException;

    expect($exception->getCode())->toBe(403);
});

test('UserSuspendedException accepts custom message', function () {
    $exception = new UserSuspendedException('Your account has been temporarily suspended');

    expect($exception->getMessage())->toBe('Your account has been temporarily suspended')
        ->and($exception->getCode())->toBe(403);
});

test('UserSuspendedException is throwable', function () {
    throw new UserSuspendedException;
})->throws(UserSuspendedException::class, 'Account is suspended');

test('UserSuspendedException extends Exception', function () {
    $exception = new UserSuspendedException;

    expect($exception)->toBeInstanceOf(Exception::class);
});

// ========================================
// Exception Differentiation Tests
// ========================================

test('banned and suspended exceptions are distinct types', function () {
    $banned = new UserBannedException;
    $suspended = new UserSuspendedException;

    expect($banned)->not->toBeInstanceOf(UserSuspendedException::class)
        ->and($suspended)->not->toBeInstanceOf(UserBannedException::class);
});

test('can catch banned exception specifically', function () {
    $caught = null;

    try {
        throw new UserBannedException;
    } catch (UserBannedException $e) {
        $caught = 'banned';
    } catch (UserSuspendedException $e) {
        $caught = 'suspended';
    }

    expect($caught)->toBe('banned');
});

test('can catch suspended exception specifically', function () {
    $caught = null;

    try {
        throw new UserSuspendedException;
    } catch (UserBannedException $e) {
        $caught = 'banned';
    } catch (UserSuspendedException $e) {
        $caught = 'suspended';
    }

    expect($caught)->toBe('suspended');
});

test('both exceptions can be caught as Exception', function () {
    $exceptions = [
        new UserBannedException,
        new UserSuspendedException,
    ];

    foreach ($exceptions as $exception) {
        $caught = false;
        try {
            throw $exception;
        } catch (Exception $e) {
            $caught = true;
        }
        expect($caught)->toBeTrue();
    }
});
