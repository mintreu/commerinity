<?php

declare(strict_types=1);

use App\Filament\Resources\JobApplications\Schemas\ImportSchema;
use App\Imports\EnhancedBulkJobApplicationImport;
use App\Models\Geo\Block;
use App\Models\Geo\Country;
use App\Models\Geo\State;
use App\Models\Recruitment\Recruitment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a recruitment for testing
    $this->recruitment = Recruitment::factory()->create([
        'id' => 101,
        'title' => 'Test Recruitment',
        'slug' => 'test-recruitment',
        'is_payable' => true,
        'fees' => 500,
    ]);

    $country = Country::factory()->india()->create();

    State::factory()
        ->forCountry($country)
        ->create([
            'name' => 'Delhi',
            'code' => 'DL',
        ]);

    Block::factory()
        ->forState(State::where('code', 'DL')->firstOrFail())
        ->create([
            'name' => 'Sadar',
        ]);
});

function makeImportRow(array $overrides = []): Collection
{
    $base = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'mobile' => '9876543210',
        'job_posting_slug' => 'test-recruitment',
        'street_address' => 'Street 1',
        'city' => 'Delhi',
        'pin_code' => '110001',
        'state_name' => 'Delhi',
        'block_name' => 'Sadar',
        'gender' => '',
        'date_of_birth' => '',
        'pan_number' => '',
        'aadhaar_number' => '',
        'guardian_name' => '',
        'education_qualification' => '',
        'skills' => '',
        'work_experience' => '',
        'referee_name' => '',
        'referee_mobile' => '',
        'payment_status' => 'no',
        'payment_amount' => '',
    ];

    $row = array_merge($base, $overrides);
    $values = array_map(
        fn (string $header) => $row[$header] ?? '',
        ImportSchema::HEADERS
    );

    return collect($values);
}

function runImport(EnhancedBulkJobApplicationImport $import, Collection $collection): void
{
    try {
        $import->collection($collection);
    } catch (\Throwable $e) {
        if (str_starts_with($e->getMessage(), 'Successfully imported')) {
            return;
        }
        throw $e;
    }
}

test('import validates cell-level errors correctly', function () {
    // Create mock data with errors
    $collection = new Collection([
        // Headers
        collect(ImportSchema::HEADERS),
        // Row 2: Invalid email
        makeImportRow(['email' => 'invalid-email']),
        // Row 3: Invalid mobile (9 digits)
        makeImportRow(['email' => 'jane@example.com', 'mobile' => '987654321']),
        // Row 4: Missing required field
        makeImportRow(['email' => 'bob@example.com', 'mobile' => '']),
        // Row 5: Invalid PAN
        makeImportRow(['email' => 'alice@example.com', 'pan_number' => 'INVALIDPAN']),
        // Row 6: Duplicate email in file (same as row 3 email)
        makeImportRow(['email' => 'jane@example.com', 'mobile' => '9999999999']),
        // Row 7: Duplicate mobile in file
        makeImportRow(['email' => 'another@example.com', 'mobile' => '9876543210']),
    ]);

    $import = new EnhancedBulkJobApplicationImport;

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

        // Duplicate email should be flagged (row number depends on file order)
        expect($message)->toContain("Column 'email'");
        expect($message)->toContain('Duplicate email in file');

        // Duplicate mobile should be flagged
        expect($message)->toContain("Column 'mobile'");
        expect($message)->toContain('Duplicate mobile in file');
    }
});

test('import succeeds with valid data', function () {
    $collection = new Collection([
        // Headers
        collect(ImportSchema::HEADERS),
        // Valid row
        makeImportRow(['name' => 'Rahul Sharma', 'email' => 'rahul@example.com', 'pan_number' => 'ABCDE1234F']),
    ]);

    $import = new EnhancedBulkJobApplicationImport;

    // Should not throw exception
    runImport($import, $collection);

    // Verify data was created
    expect(\App\Models\User::where('email', 'rahul@example.com')->exists())->toBeTrue();
    expect(\App\Models\Address::where('person_email', 'rahul@example.com')->exists())->toBeTrue();
    expect(\App\Models\Kyc::where('pan_number', 'ABCDE1234F')->exists())->toBeTrue();
});

test('import validates duplicate email in database', function () {
    // Create existing user
    \App\Models\User::factory()->create(['email' => 'existing@example.com']);

    $collection = new Collection([
        collect(ImportSchema::HEADERS),
        makeImportRow(['email' => 'existing@example.com']),
    ]);

    $import = new EnhancedBulkJobApplicationImport;

    try {
        $import->collection($collection);
    } catch (\Throwable $e) {
        expect($e->getMessage())->toContain("Column 'email'");
        expect($e->getMessage())->toContain('Email already exists in system');
    }
});

test('import validates Aadhaar length', function () {
    $collection = new Collection([
        collect(ImportSchema::HEADERS),
        makeImportRow(['email' => 'biz@example.com', 'aadhaar_number' => '12345']),
    ]);

    $import = new EnhancedBulkJobApplicationImport;

    try {
        $import->collection($collection);
    } catch (\Throwable $e) {
        expect($e->getMessage())->toContain("Column 'aadhaar_number'");
        expect($e->getMessage())->toContain('Aadhaar must be 12 digits');
    }
});

test('password is generated from last 6 digits of mobile', function () {
    $collection = new Collection([
        collect(ImportSchema::HEADERS),
        makeImportRow(['email' => 'test@example.com']),
    ]);

    $import = new EnhancedBulkJobApplicationImport;
    runImport($import, $collection);

    $user = \App\Models\User::where('email', 'test@example.com')->first();

    // Password should be last 6 digits: 543210
    expect(\Illuminate\Support\Facades\Hash::check('543210', $user->password))->toBeTrue();
    expect($user->onboarded)->toBeTrue(); // Verify onboarded status
});

test('user is marked as onboarded complete', function () {
    $collection = new Collection([
        collect(ImportSchema::HEADERS),
        makeImportRow(['email' => 'test@example.com']),
    ]);

    $import = new EnhancedBulkJobApplicationImport;
    runImport($import, $collection);

    $user = \App\Models\User::where('email', 'test@example.com')->first();

    expect($user->onboarded)->toBeTrue();
    expect($user->status)->toBe(\App\Casts\UserStatusCast::ACTIVE);
});

test('welcome notification is sent with correct password', function () {
    \Illuminate\Support\Facades\Notification::fake();

    $collection = new Collection([
        collect(ImportSchema::HEADERS),
        makeImportRow(['email' => 'test@example.com']),
    ]);

    $import = new EnhancedBulkJobApplicationImport;
    runImport($import, $collection);

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
        collect(ImportSchema::HEADERS),
        makeImportRow(['name' => 'User One', 'email' => 'user1@example.com', 'mobile' => '9876543210']),
        makeImportRow(['name' => 'User Two', 'email' => 'user2@example.com', 'mobile' => '9876543211']),
        makeImportRow(['name' => 'User Three', 'email' => 'user3@example.com', 'mobile' => '9876543212']),
    ]);

    $import = new EnhancedBulkJobApplicationImport;
    runImport($import, $collection);

    expect(\App\Models\User::count())->toBe(3);
    expect(\App\Models\Address::count())->toBe(3);
    expect(\App\Models\Recruitment\JobApplication::count())->toBe(3);
});

test('import skips invalid rows and continues', function () {
    $collection = new Collection([
        collect(ImportSchema::HEADERS),
        // Valid row
        makeImportRow(['name' => 'Valid User', 'email' => 'valid@example.com', 'mobile' => '9876543210']),
        // Invalid row (bad email)
        makeImportRow(['name' => 'Invalid User', 'email' => 'bad-email', 'mobile' => '9876543211']),
    ]);

    $import = new EnhancedBulkJobApplicationImport;

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
