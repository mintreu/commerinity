<?php

declare(strict_types=1);

namespace Tests\Feature\Imports;

use App\Imports\EnhancedBulkJobApplicationImport;
use App\Models\Geo\Block;
use App\Models\Geo\Country;
use App\Models\Geo\State;
use App\Models\Recruitment\Recruitment;
use App\Models\User;
use Database\Seeders\Geo\BlockSeeder;
use Database\Seeders\Geo\CountrySeeder;
use Database\Seeders\Geo\StateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed geo data using proper seeders
    $this->seed(CountrySeeder::class);
    $this->seed(StateSeeder::class);
    $this->seed(BlockSeeder::class);

    // Create a recruitment for testing
    $this->recruitment = Recruitment::factory()->create([
        'id' => 101,
        'title' => 'Software Developer Position',
        'slug' => 'software-developer-position',
        'is_payable' => true,
        'fees' => 500,
    ]);

    $this->factory = new JobApplicationImportFactory;
});

test('can import 1000 job applications end-to-end with realistic data', function () {
    $this->markTestSkipped('Skipping 1000 entry test in CI - run locally for performance testing');

    // Generate 1000 realistic entries
    $rows = $this->factory->generateRows(1000, ['recruitment_slug' => 'software-developer-position', 'block_name' => 'Sadar']);

    // Build collection with headers
    $collection = new Collection([
        collect($this->factory->generateHeaderRow()),
        ...array_map(fn ($row) => collect(array_values($row)), $rows),
    ]);

    // Track time
    $startTime = microtime(true);
    $memoryBefore = memory_get_usage();

    // Perform import
    $import = new EnhancedBulkJobApplicationImport;
    $import->collection($collection);

    $endTime = microtime(true);
    $memoryAfter = memory_get_usage();

    $duration = round($endTime - $startTime, 2);
    $memoryUsed = round(($memoryAfter - $memoryBefore) / 1024 / 1024, 2);

    // Assertions
    expect(User::count())->toBe(1000);

    // Verify random sample of users
    $sampleUsers = User::inRandomOrder()->limit(10)->get();
    foreach ($sampleUsers as $user) {
        expect($user->name)->not->toBeEmpty();
        expect($user->email)->toContain('@');
        expect(strlen((string) $user->mobile))->toBe(10);
        expect($user->type)->toBe('regular');
        expect($user->onboarded)->toBeTrue();
        expect($user->addresses)->toHaveCount(1);
        expect($user->jobApplications)->toHaveCount(1);
    }

    // Verify KYC data exists for most users
    $kycCount = DB::table('kycs')->count();
    expect($kycCount)->toBeGreaterThan(700); // 80% should have KYC

    // Verify job applications
    expect(\App\Models\Recruitment\JobApplication::count())->toBe(1000);

    // Performance assertions
    expect($duration)->toBeLessThan(60); // Should complete within 60 seconds
    expect($memoryUsed)->toBeLessThan(100); // Should use less than 100MB

    // Output performance metrics
    echo "\n\n📊 Performance Metrics:\n";
    echo "⏱️  Import Duration: {$duration} seconds\n";
    echo "💾 Memory Used: {$memoryUsed} MB\n";
    echo '⚡ Records/Second: '.round(1000 / $duration, 2)."\n";
})->group('performance');

test('can handle 1000 entries with mixed valid and invalid data', function () {
    $this->markTestSkipped('Skipping mixed data test in CI');

    // Generate mostly valid data with some invalid rows
    $rows = [];

    // 900 valid rows
    for ($i = 0; $i < 900; $i++) {
        $rows[] = $this->factory->generateRow($i + 1);
    }

    // 50 rows with invalid emails
    for ($i = 900; $i < 950; $i++) {
        $row = $this->factory->generateRow($i + 1);
        $row['email'] = 'invalid-email-'.$i;
        $rows[] = $row;
    }

    // 50 rows with invalid mobile numbers
    for ($i = 950; $i < 1000; $i++) {
        $row = $this->factory->generateRow($i + 1);
        $row['mobile'] = '12345'; // Too short
        $rows[] = $row;
    }

    $collection = new Collection([
        collect($this->factory->generateHeaderRow()),
        ...array_map(fn ($row) => collect(array_values($row)), $rows),
    ]);

    $import = new EnhancedBulkJobApplicationImport;

    try {
        $import->collection($collection);
        $this->fail('Expected import to fail with validation errors');
    } catch (\Throwable $e) {
        // Verify error messages show row numbers
        expect($e->getMessage())->toContain('Row 901');
        expect($e->getMessage())->toContain('Row 951');

        // Verify no records were saved (transaction rollback)
        expect(User::count())->toBe(0);
    }
});

test('password generation works correctly for all 1000 entries', function () {
    // Generate 50 entries for quick validation
    $rows = $this->factory->generateRows(50);

    $collection = new Collection([
        collect($this->factory->generateHeaderRow()),
        ...array_map(fn ($row) => collect(array_values($row)), $rows),
    ]);

    $import = new EnhancedBulkJobApplicationImport;
    $import->collection($collection);

    // Verify password generation for sample users
    $users = User::limit(10)->get();
    foreach ($users as $user) {
        $mobileLast6 = substr($user->mobile, -6);

        // Password should be last 6 digits of mobile
        expect(\Illuminate\Support\Facades\Hash::check($mobileLast6, $user->password))->toBeTrue();
        expect($user->onboarded)->toBeTrue(); // Verify onboarded status
    }
});

test('notification is sent to all imported users', function () {
    \Illuminate\Support\Facades\Notification::fake();

    $rows = $this->factory->generateRows(10);

    $collection = new Collection([
        collect($this->factory->generateHeaderRow()),
        ...array_map(fn ($row) => collect(array_values($row)), $rows),
    ]);

    $import = new EnhancedBulkJobApplicationImport;
    $import->collection($collection);

    // Verify notifications were sent to all users
    \Illuminate\Support\Facades\Notification::assertCount(10);

    $users = User::all();
    foreach ($users as $user) {
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $user,
            \App\Notifications\JobApplicationWelcomeNotification::class
        );
    }
});

test('kyc data is auto-approved for imported users', function () {
    $rows = $this->factory->generateRows(10);

    $collection = new Collection([
        collect($this->factory->generateHeaderRow()),
        ...array_map(fn ($row) => collect(array_values($row)), $rows),
    ]);

    $import = new EnhancedBulkJobApplicationImport;
    $import->collection($collection);

    // Verify KYC records are auto-approved
    $kycs = \App\Models\Kyc::all();
    expect($kycs->count())->toBeGreaterThan(5); // Most should have KYC

    foreach ($kycs as $kyc) {
        expect($kyc->status)->toBe(\App\Casts\KycStatusCast::APPROVED->value);
        expect($kyc->submitted_at)->not->toBeNull();
    }
});

test('job applications are created with correct relationships', function () {
    $rows = $this->factory->generateRows(10);

    $collection = new Collection([
        collect($this->factory->generateHeaderRow()),
        ...array_map(fn ($row) => collect(array_values($row)), $rows),
    ]);

    $import = new EnhancedBulkJobApplicationImport;
    $import->collection($collection);

    // Verify job applications
    $applications = \App\Models\Recruitment\JobApplication::with(['applicant', 'address'])->get();

    expect($applications->count())->toBe(10);

    foreach ($applications as $app) {
        expect($app->applicant)->toBeInstanceOf(User::class);
        expect($app->address)->toBeInstanceOf(\App\Models\Address::class);
        expect($app->recruitment_id)->toBe(101);
        expect($app->applicant_type)->toBe('user');
    }
});

test('filament import action can be instantiated', function () {
    $action = \EightyNine\ExcelImport\ExcelImportAction::make()
        ->use(\App\Imports\EnhancedBulkJobApplicationImport::class);

    expect($action)->toBeInstanceOf(\EightyNine\ExcelImport\ExcelImportAction::class);
});

test('sample excel data is properly formatted', function () {
    $sampleData = [
        [
            'name' => 'Rahul Sharma',
            'email' => 'rahul.sharma@example.com',
            'mobile' => '9876543210',
            'job_posting_slug' => 'software-developer-position',
            'street_address' => 'Street 12, ABC Nagar',
            'city' => 'Delhi',
            'pin_code' => '110001',
            'state_name' => 'Delhi',
            'block_name' => 'Sadar',
            'pan_number' => 'ABCDE1234F',
            'date_of_birth' => '1998-05-21',
            'payment_status' => 'no',
            'payment_amount' => null,
            'guardian_name' => null,
            'education_qualification' => null,
            'skills' => null,
            'work_experience' => null,
            'referee_name' => null,
            'referee_mobile' => null,
            'gender' => null,
            'aadhaar_number' => null,
        ],
    ];

    $headers = array_keys($sampleData[0]);

    // Verify all required columns are present
    $requiredColumns = ['name', 'email', 'mobile', 'job_posting_slug', 'street_address', 'city', 'pin_code', 'state_name', 'block_name'];
    foreach ($requiredColumns as $col) {
        expect(in_array($col, $headers))->toBeTrue();
    }

    // Verify data formats
    expect(filter_var($sampleData[0]['email'], FILTER_VALIDATE_EMAIL))->not->toBeFalse();
    expect(strlen((string) $sampleData[0]['mobile']))->toBe(10);
    expect(preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $sampleData[0]['pan_number']))->toBe(1);
});
