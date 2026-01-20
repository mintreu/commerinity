<?php

declare(strict_types=1);

use App\Imports\EnhancedBulkJobApplicationImport;
use App\Models\Recruitment\Recruitment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a recruitment for testing
    $this->recruitment = Recruitment::factory()->create([
        'id' => 101,
        'title' => 'Test Recruitment',
        'is_payable' => true,
        'fees' => 500,
    ]);
});

test('import validates cell-level errors correctly', function () {
    // Create mock data with errors
    $collection = new Collection([
        // Headers
        collect(['name', 'email', 'mobile', 'type', 'recruitment_id', 'addr_line1', 'city', 'postal_code', 'state', 'country', 'address_type']),
        // Row 2: Invalid email
        collect(['John Doe', 'invalid-email', '9876543210', 'applicant', '101', 'Street 1', 'Delhi', '110001', 'DL', 'IN', 'present']),
        // Row 3: Invalid mobile (9 digits)
        collect(['Jane Smith', 'jane@example.com', '987654321', 'applicant', '101', 'Street 2', 'Delhi', '110001', 'DL', 'IN', 'present']),
        // Row 4: Missing required field
        collect(['Bob Wilson', 'bob@example.com', '', 'applicant', '101', 'Street 3', 'Delhi', '110001', 'DL', 'IN', 'present']),
        // Row 5: Invalid PAN
        collect(['Alice Brown', 'alice@example.com', '9876543210', 'applicant', '101', 'Street 4', 'Delhi', '110001', 'DL', 'IN', 'present', 'personal', 'INVALIDPAN']),
        // Row 6: Duplicate email in file
        collect(['Duplicate User', 'john@example.com', '9999999999', 'applicant', '101', 'Street 5', 'Delhi', '110001', 'DL', 'IN', 'present']),
        // Row 7: Duplicate mobile in file
        collect(['Another User', 'another@example.com', '9876543210', 'applicant', '101', 'Street 6', 'Delhi', '110001', 'DL', 'IN', 'present']),
    ]);

    $import = new EnhancedBulkJobApplicationImport();

    // Expect exception with detailed error message
    try {
        $import->collection($collection);
    } catch (\Throwable $e) {
        $message = $e->getMessage();

        // Verify error messages contain row and column info
        expect($message)->toContain('Row 2');
        expect($message)->toContain("Column 'email'");
        expect($message)->toContain('Invalid email format');

        expect($message)->toContain('Row 3');
        expect($message)->toContain("Column 'mobile'");
        expect($message)->toContain('Mobile must be 10 digits');

        expect($message)->toContain('Row 4');
        expect($message)->toContain("Column 'mobile'");
        expect($message)->toContain('Required field is empty');

        expect($message)->toContain('Row 5');
        expect($message)->toContain("Column 'pan_number'");
        expect($message)->toContain('Invalid PAN format');

        expect($message)->toContain('Row 6');
        expect($message)->toContain("Column 'email'");
        expect($message)->toContain('Duplicate email in file');

        expect($message)->toContain('Row 7');
        expect($message)->toContain("Column 'mobile'");
        expect($message)->toContain('Duplicate mobile in file');
    }
});

test('import succeeds with valid data', function () {
    $collection = new Collection([
        // Headers
        collect(['name', 'email', 'mobile', 'type', 'recruitment_id', 'addr_line1', 'city', 'postal_code', 'state', 'country', 'address_type', 'pan_number']),
        // Valid row
        collect(['Rahul Sharma', 'rahul@example.com', '9876543210', 'applicant', '101', 'Street 1', 'Delhi', '110001', 'DL', 'IN', 'present', 'ABCDE1234F']),
    ]);

    $import = new EnhancedBulkJobApplicationImport();

    // Should not throw exception
    $import->collection($collection);

    // Verify data was created
    expect(\App\Models\User::where('email', 'rahul@example.com')->exists())->toBeTrue();
    expect(\App\Models\Address::where('person_email', 'rahul@example.com')->exists())->toBeTrue();
    expect(\App\Models\Kyc::where('pan_number', 'ABCDE1234F')->exists())->toBeTrue();
});

test('import validates duplicate email in database', function () {
    // Create existing user
    \App\Models\User::factory()->create(['email' => 'existing@example.com']);

    $collection = new Collection([
        collect(['name', 'email', 'mobile', 'type', 'recruitment_id', 'addr_line1', 'city', 'postal_code', 'state', 'country', 'address_type']),
        collect(['Test User', 'existing@example.com', '9876543210', 'applicant', '101', 'Street 1', 'Delhi', '110001', 'DL', 'IN', 'present']),
    ]);

    $import = new EnhancedBulkJobApplicationImport();

    try {
        $import->collection($collection);
    } catch (\Throwable $e) {
        expect($e->getMessage())->toContain("Column 'email'");
        expect($e->getMessage())->toContain('Email already exists in system');
    }
});

test('import validates KYC business fields', function () {
    $collection = new Collection([
        collect(['name', 'email', 'mobile', 'type', 'recruitment_id', 'addr_line1', 'city', 'postal_code', 'state', 'country', 'address_type', 'kyc_type']),
        // Missing company_name and GST for business KYC
        collect(['Business User', 'biz@example.com', '9876543210', 'applicant', '101', 'Street 1', 'Delhi', '110001', 'DL', 'IN', 'present', 'business']),
    ]);

    $import = new EnhancedBulkJobApplicationImport();

    try {
        $import->collection($collection);
    } catch (\Throwable $e) {
        expect($e->getMessage())->toContain("Column 'company_name'");
        expect($e->getMessage())->toContain("Column 'gst_number'");
        expect($e->getMessage())->toContain('required for business KYC');
    }
});

test('password is generated from last 6 digits of mobile', function () {
    $collection = new Collection([
        collect(['name', 'email', 'mobile', 'type', 'recruitment_id', 'addr_line1', 'city', 'postal_code', 'state', 'country', 'address_type']),
        collect(['Test User', 'test@example.com', '9876543210', 'applicant', '101', 'Street 1', 'Delhi', '110001', 'DL', 'IN', 'present']),
    ]);

    $import = new EnhancedBulkJobApplicationImport();
    $import->collection($collection);

    $user = \App\Models\User::where('email', 'test@example.com')->first();

    // Password should be last 6 digits: 543210
    expect(\Illuminate\Support\Facades\Hash::check('543210', $user->password))->toBeTrue();
    expect($user->onboarded)->toBeTrue(); // Verify onboarded status
});

test('password is generated from DOB when mobile not available', function () {
    $collection = new Collection([
        collect(['name', 'email', 'mobile', 'type', 'recruitment_id', 'addr_line1', 'city', 'postal_code', 'state', 'country', 'address_type', 'dob']),
        collect(['Test User', 'test@example.com', '12345', 'applicant', '101', 'Street 1', 'Delhi', '110001', 'DL', 'IN', 'present', '1998-05-21']),
    ]);

    $import = new EnhancedBulkJobApplicationImport();
    $import->collection($collection);

    $user = \App\Models\User::where('email', 'test@example.com')->first();

    // Password should be MMYYYY: 051998 (May 1998)
    expect(\Illuminate\Support\Facades\Hash::check('051998', $user->password))->toBeTrue();
});

test('user is marked as onboarded complete', function () {
    $collection = new Collection([
        collect(['name', 'email', 'mobile', 'type', 'recruitment_id', 'addr_line1', 'city', 'postal_code', 'state', 'country', 'address_type']),
        collect(['Test User', 'test@example.com', '9876543210', 'applicant', '101', 'Street 1', 'Delhi', '110001', 'DL', 'IN', 'present']),
    ]);

    $import = new EnhancedBulkJobApplicationImport();
    $import->collection($collection);

    $user = \App\Models\User::where('email', 'test@example.com')->first();

    expect($user->onboarded)->toBeTrue();
    expect($user->status)->toBe(\App\Casts\UserStatusCast::ACTIVE->value);
});

test('welcome notification is sent with correct password', function () {
    \Illuminate\Support\Facades\Notification::fake();

    $collection = new Collection([
        collect(['name', 'email', 'mobile', 'type', 'recruitment_id', 'addr_line1', 'city', 'postal_code', 'state', 'country', 'address_type']),
        collect(['Test User', 'test@example.com', '9876543210', 'applicant', '101', 'Street 1', 'Delhi', '110001', 'DL', 'IN', 'present']),
    ]);

    $import = new EnhancedBulkJobApplicationImport();
    $import->collection($collection);

    $user = \App\Models\User::where('email', 'test@example.com')->first();

    \Illuminate\Support\Facades\Notification::assertSentTo(
        $user,
        \App\Notifications\JobApplicationWelcomeNotification::class,
        function ($notification) {
            return $notification->password === '543210'; // Last 6 digits of mobile
        }
    );
});

test('import handles multiple rows successfully', function () {
    $collection = new Collection([
        collect(['name', 'email', 'mobile', 'type', 'recruitment_id', 'addr_line1', 'city', 'postal_code', 'state', 'country', 'address_type']),
        collect(['User One', 'user1@example.com', '9876543210', 'applicant', '101', 'Street 1', 'Delhi', '110001', 'DL', 'IN', 'present']),
        collect(['User Two', 'user2@example.com', '9876543211', 'applicant', '101', 'Street 2', 'Delhi', '110001', 'DL', 'IN', 'present']),
        collect(['User Three', 'user3@example.com', '9876543212', 'applicant', '101', 'Street 3', 'Delhi', '110001', 'DL', 'IN', 'present']),
    ]);

    $import = new EnhancedBulkJobApplicationImport();
    $import->collection($collection);

    expect(\App\Models\User::count())->toBe(3);
    expect(\App\Models\Address::count())->toBe(3);
    expect(\App\Models\Recruitment\JobApplication::count())->toBe(3);
});

test('import skips invalid rows and continues', function () {
    $collection = new Collection([
        collect(['name', 'email', 'mobile', 'type', 'recruitment_id', 'addr_line1', 'city', 'postal_code', 'state', 'country', 'address_type']),
        // Valid row
        collect(['Valid User', 'valid@example.com', '9876543210', 'applicant', '101', 'Street 1', 'Delhi', '110001', 'DL', 'IN', 'present']),
        // Invalid row (bad email)
        collect(['Invalid User', 'bad-email', '9876543211', 'applicant', '101', 'Street 2', 'Delhi', '110001', 'DL', 'IN', 'present']),
    ]);

    $import = new EnhancedBulkJobApplicationImport();

    try {
        $import->collection($collection);
    } catch (\Throwable $e) {
        // Should fail on row 3 (bad email)
        expect($e->getMessage())->toContain('Row 3');
        expect($e->getMessage())->toContain('Invalid email format');

        // Valid user should NOT be created (transaction rollback)
        expect(\App\Models\User::where('email', 'valid@example.com')->exists())->toBeFalse();
    }
});
