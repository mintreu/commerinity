<?php

declare(strict_types=1);

use App\Casts\KycStatusCast;
use App\Models\Kyc;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ========================================
// Status Casting Tests
// ========================================

test('status is cast to KycStatusCast enum', function () {
    $kyc = Kyc::factory()->pending()->create();

    expect($kyc->status)->toBe(KycStatusCast::PENDING);
});

test('status can be pending', function () {
    $kyc = Kyc::factory()->pending()->create();

    expect($kyc->status)->toBe(KycStatusCast::PENDING);
});

test('status can be approved', function () {
    $kyc = Kyc::factory()->approved()->create();

    expect($kyc->status)->toBe(KycStatusCast::APPROVED);
});

test('status can be rejected', function () {
    $kyc = Kyc::factory()->rejected()->create();

    expect($kyc->status)->toBe(KycStatusCast::REJECTED);
});

// ========================================
// Datetime Casting Tests
// ========================================

test('submitted_at is cast to datetime', function () {
    $kyc = Kyc::factory()->create(['submitted_at' => now()]);

    expect($kyc->submitted_at)->toBeInstanceOf(\Carbon\Carbon::class);
});

test('reviewed_at is cast to datetime', function () {
    $kyc = Kyc::factory()->approved()->create(['reviewed_at' => now()]);

    expect($kyc->reviewed_at)->toBeInstanceOf(\Carbon\Carbon::class);
});

// ========================================
// Relationship Tests
// ========================================

test('kyc belongs to user via morphTo', function () {
    $user = User::factory()->create();
    $kyc = Kyc::factory()->create([
        'kycable_type' => User::class,
        'kycable_id' => $user->id,
    ]);

    expect($kyc->kycable)->toBeInstanceOf(User::class)
        ->and($kyc->kycable->id)->toBe($user->id);
});

test('kyc can have a reviewer', function () {
    $reviewer = User::factory()->create();
    $kyc = Kyc::factory()->approved()->create([
        'reviewed_by' => $reviewer->id,
    ]);

    expect($kyc->reviewer)->toBeInstanceOf(User::class)
        ->and($kyc->reviewer->id)->toBe($reviewer->id);
});

test('kyc reviewer can be null', function () {
    $kyc = Kyc::factory()->pending()->create([
        'reviewed_by' => null,
    ]);

    expect($kyc->reviewer)->toBeNull();
});

// ========================================
// Status Check Methods Tests
// ========================================

test('isPending returns true for pending KYC', function () {
    $kyc = Kyc::factory()->pending()->create();

    expect($kyc->isPending())->toBeTrue()
        ->and($kyc->isApproved())->toBeFalse()
        ->and($kyc->isRejected())->toBeFalse();
});

test('isApproved returns true for approved KYC', function () {
    $kyc = Kyc::factory()->approved()->create();

    expect($kyc->isApproved())->toBeTrue()
        ->and($kyc->isPending())->toBeFalse()
        ->and($kyc->isRejected())->toBeFalse();
});

test('isRejected returns true for rejected KYC', function () {
    $kyc = Kyc::factory()->rejected()->create();

    expect($kyc->isRejected())->toBeTrue()
        ->and($kyc->isPending())->toBeFalse()
        ->and($kyc->isApproved())->toBeFalse();
});

// ========================================
// Scope Tests
// ========================================

test('pending scope filters pending KYC records', function () {
    Kyc::factory()->pending()->create();
    Kyc::factory()->pending()->create();
    Kyc::factory()->approved()->create();

    $pending = Kyc::pending()->get();

    expect($pending)->toHaveCount(2);
    foreach ($pending as $kyc) {
        expect($kyc->isPending())->toBeTrue();
    }
});

test('approved scope filters approved KYC records', function () {
    Kyc::factory()->pending()->create();
    Kyc::factory()->approved()->create();
    Kyc::factory()->approved()->create();

    $approved = Kyc::approved()->get();

    expect($approved)->toHaveCount(2);
    foreach ($approved as $kyc) {
        expect($kyc->isApproved())->toBeTrue();
    }
});

test('rejected scope filters rejected KYC records', function () {
    Kyc::factory()->pending()->create();
    Kyc::factory()->rejected()->create();

    $rejected = Kyc::rejected()->get();

    expect($rejected)->toHaveCount(1)
        ->and($rejected->first()->isRejected())->toBeTrue();
});

test('personal scope filters personal KYC records', function () {
    Kyc::factory()->create(['kyc_type' => 'personal']);
    Kyc::factory()->business()->create();

    $personal = Kyc::personal()->get();

    expect($personal)->toHaveCount(1)
        ->and($personal->first()->kyc_type)->toBe('personal');
});

test('business scope filters business KYC records', function () {
    Kyc::factory()->create(['kyc_type' => 'personal']);
    Kyc::factory()->business()->create();
    Kyc::factory()->business()->create();

    $business = Kyc::business()->get();

    expect($business)->toHaveCount(2);
    foreach ($business as $kyc) {
        expect($kyc->kyc_type)->toBe('business');
    }
});

// ========================================
// Approve/Reject Methods Tests
// ========================================

test('approve method updates status to approved', function () {
    $kyc = Kyc::factory()->pending()->create();
    $reviewer = User::factory()->create();

    $kyc->approve($reviewer->id);

    expect($kyc->fresh()->status)->toBe(KycStatusCast::APPROVED)
        ->and($kyc->fresh()->reviewed_at)->not->toBeNull()
        ->and($kyc->fresh()->reviewed_by)->toBe($reviewer->id)
        ->and($kyc->fresh()->rejection_reason)->toBeNull();
});

test('approve method works without reviewer', function () {
    $kyc = Kyc::factory()->pending()->create();

    $kyc->approve();

    expect($kyc->fresh()->status)->toBe(KycStatusCast::APPROVED)
        ->and($kyc->fresh()->reviewed_at)->not->toBeNull()
        ->and($kyc->fresh()->reviewed_by)->toBeNull();
});

test('reject method updates status to rejected with reason', function () {
    $kyc = Kyc::factory()->pending()->create();
    $reviewer = User::factory()->create();

    $kyc->reject('Documents are unclear', $reviewer->id);

    expect($kyc->fresh()->status)->toBe(KycStatusCast::REJECTED)
        ->and($kyc->fresh()->reviewed_at)->not->toBeNull()
        ->and($kyc->fresh()->reviewed_by)->toBe($reviewer->id)
        ->and($kyc->fresh()->rejection_reason)->toBe('Documents are unclear');
});

test('reject method works without reviewer', function () {
    $kyc = Kyc::factory()->pending()->create();

    $kyc->reject('Invalid PAN number');

    expect($kyc->fresh()->status)->toBe(KycStatusCast::REJECTED)
        ->and($kyc->fresh()->rejection_reason)->toBe('Invalid PAN number')
        ->and($kyc->fresh()->reviewed_by)->toBeNull();
});

// ========================================
// KYC Type Tests
// ========================================

test('personal KYC has correct fields', function () {
    $kyc = Kyc::factory()->create([
        'kyc_type' => 'personal',
        'pan_number' => 'ABCDE1234F',
        'aadhaar_number' => '123456789012',
    ]);

    expect($kyc->kyc_type)->toBe('personal')
        ->and($kyc->pan_number)->toBe('ABCDE1234F')
        ->and($kyc->aadhaar_number)->toBe('123456789012');
});

test('business KYC has company fields', function () {
    $kyc = Kyc::factory()->business()->create();

    expect($kyc->kyc_type)->toBe('business')
        ->and($kyc->company_name)->not->toBeNull()
        ->and($kyc->company_type)->not->toBeNull();
});

// ========================================
// Edge Cases
// ========================================

test('rejection reason is stored when rejected', function () {
    $kyc = Kyc::factory()->rejected()->create([
        'rejection_reason' => 'PAN card is not readable',
    ]);

    expect($kyc->rejection_reason)->toBe('PAN card is not readable');
});

test('pending KYC has no rejection reason', function () {
    $kyc = Kyc::factory()->pending()->create();

    expect($kyc->rejection_reason)->toBeNull();
});

test('approved KYC has no rejection reason', function () {
    $kyc = Kyc::factory()->approved()->create();

    expect($kyc->rejection_reason)->toBeNull();
});

test('multiple KYC records can exist for same user', function () {
    $user = User::factory()->create();

    Kyc::factory()->rejected()->create([
        'kycable_type' => User::class,
        'kycable_id' => $user->id,
    ]);
    Kyc::factory()->pending()->create([
        'kycable_type' => User::class,
        'kycable_id' => $user->id,
    ]);

    expect(Kyc::where('kycable_id', $user->id)->count())->toBe(2);
});
