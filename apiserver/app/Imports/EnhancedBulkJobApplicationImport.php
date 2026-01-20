<?php

declare(strict_types=1);

namespace App\Imports;

use App\Casts\AddressTypeCast;
use App\Casts\KycStatusCast;
use App\Casts\UserStatusCast;
use App\Casts\UserTypeCast;
use App\Filament\Resources\JobApplications\Schemas\ImportSchema;
use App\Models\Address;
use App\Models\Geo\Block;
use App\Models\Geo\Country;
use App\Models\Geo\State;
use App\Models\Kyc;
use App\Models\Recruitment\JobApplication;
use App\Models\Recruitment\Recruitment;
use App\Models\User;
use App\Notifications\JobApplicationWelcomeNotification;
use EightyNine\ExcelImport\EnhancedDefaultImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Enhanced bulk job application import with KYC, validation, and notifications.
 *
 * Features:
 * - User-friendly Excel column names (not internal column names)
 * - Recruitment title/slug lookup (not ID)
 * - Comprehensive validation with cell-level error reporting
 * - Automatic KYC creation with approval
 * - Welcome notifications (email + SMS)
 * - Smart password generation (last 6 digits of mobile or MMYYYY from DOB)
 * - Geo caching for performance
 */
final class EnhancedBulkJobApplicationImport extends EnhancedDefaultImport
{
    /** @var Collection<int, array> */
    private Collection $headers;

    /** @var Collection<int, array> */
    private Collection $rows;

    /** @var array<int, array> */
    private array $preparedRows = [];

    /** @var array<int, array> */
    private array $validationErrors = [];

    /** @var array<string, bool> */
    private array $emailCache = [];

    /** @var array<string, bool> */
    private array $mobileCache = [];

    /** @var array<string, Recruitment> */
    private array $recruitmentCache = [];

    /** @var array<string, string> */
    private array $countryCache = [];

    /** @var array<string, string> */
    private array $stateCache = [];

    /** @var array<string, int> */
    private array $blockCache = [];

    /** @var array<string, bool> */
    private array $kycNumberCache = [];

    /** @var array<string, string> */
    private array $passwords = [];

    /**
     * USER-FRIENDLY COLUMN HEADERS
     * These are what users see in the Excel sheet
     */
    private const USER_FRIENDLY_HEADERS = ImportSchema::HEADERS;

    /**
     * Mapping from user-friendly column name to internal column name
     */
    private const HEADER_MAP = ImportSchema::HEADER_MAP;

    /**
     * Default values for optional columns
     */
    private const OPTIONAL_DEFAULTS = ImportSchema::OPTIONAL_DEFAULTS;

    /**
     * Main import entry point with enhanced error handling.
     */
    public function collection(Collection $collection): void
    {
        try {
            $this->headers = $collection->first()
                ->map(fn ($h) => trim((string) $h))
                ->values()
                ->collect();

            $this->validateHeaders();

            $this->rows = $collection->skip(1);

            if ($this->rows->isEmpty()) {
                $this->stopImportWithError('No data rows found in Excel file');
                return;
            }

            $this->validateAndPrepareAll();

            if (! empty($this->validationErrors)) {
                $this->handleValidationErrors();
                return;
            }

            DB::transaction(fn () => $this->persistAll());

            $this->stopImportWithSuccess(sprintf(
                'Successfully imported %d job applications',
                count($this->preparedRows)
            ));
        } catch (\Throwable $e) {
            $this->stopImportWithError($e->getMessage());
        }
    }

    /**
     * Validate Excel headers contain all required columns.
     */
    private function validateHeaders(): void
    {
        $foundHeaders = $this->headers->toArray();
        $missing = array_diff(self::USER_FRIENDLY_HEADERS, $foundHeaders);

        if (! empty($missing)) {
            $this->stopImportWithError(
                'Missing required columns: ' . implode(', ', $missing)
            );
        }
    }

    /**
     * Validate and normalize all rows before database insertion.
     */
    private function validateAndPrepareAll(): void
    {
        foreach ($this->rows as $index => $row) {
            $rowNumber = $index + 2;
            $maxColumns = $this->headers->count();

            if ($row->count() !== $maxColumns) {
                $this->addError($rowNumber, 'all', 'Column count mismatch', $row->count());
                continue;
            }

            // Map user-friendly headers to internal column names
            $data = $this->mapHeadersToInternal($row);

            // Merge with defaults for optional columns
            $data = $this->mergeWithDefaults($data);

            // Validate the row
            $this->validateRow($data, $rowNumber);
        }
    }

    /**
     * Map user-friendly Excel headers to internal column names.
     */
    private function mapHeadersToInternal(Collection $row): array
    {
        $mapped = [];

        foreach ($this->headers as $index => $header) {
            $value = isset($row[$index]) ? trim((string) $row[$index]) : '';

            // Skip if empty value and not a required field
            if ($value === '' && ! in_array($header, self::USER_FRIENDLY_HEADERS)) {
                continue;
            }

            // Map to internal column name if exists in map
            if (isset(self::HEADER_MAP[$header])) {
                $mapped[self::HEADER_MAP[$header]] = $value;
            } else {
                // If not in map, use as-is (for custom columns)
                $mapped[$header] = $value;
            }
        }

        return $mapped;
    }

    /**
     * Merge row data with default values for optional columns.
     */
    private function mergeWithDefaults(array $data): array
    {
        // Handle boolean defaults with yes/no values
        if (isset($data['is_paid']) && $data['is_paid'] === '') {
            $data['is_paid'] = self::OPTIONAL_DEFAULTS['payment_status'];
        }
        if (isset($data['verify_email']) && $data['verify_email'] === '') {
            $data['verify_email'] = self::OPTIONAL_DEFAULTS['verify_email'];
        }
        if (isset($data['verify_mobile']) && $data['verify_mobile'] === '') {
            $data['verify_mobile'] = self::OPTIONAL_DEFAULTS['verify_mobile'];
        }
        if (isset($data['onboard_user']) && $data['onboard_user'] === '') {
            $data['onboard_user'] = self::OPTIONAL_DEFAULTS['activate_account'];
        }

        return $data;
    }

    /**
     * Validate a single row and collect all validation errors.
     */
    private function validateRow(array $data, int $rowNumber): void
    {
        $hasErrors = false;

        // Required field validation
        foreach (self::USER_FRIENDLY_HEADERS as $header) {
            $internalName = self::HEADER_MAP[$header];
            if (! isset($data[$internalName]) || $data[$internalName] === '') {
                $this->addError($rowNumber, $header, 'Required field is empty', $data[$internalName] ?? null);
                $hasErrors = true;
            }
        }

        // Email validation
        if (! empty($data['email'])) {
            if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->addError($rowNumber, 'email_address', 'Invalid email format', $data['email']);
                $hasErrors = true;
            } elseif (isset($this->emailCache[$data['email']])) {
                $this->addError($rowNumber, 'email_address', 'Duplicate email in file', $data['email']);
                $hasErrors = true;
            } elseif (User::where('email', $data['email'])->exists()) {
                $this->addError($rowNumber, 'email_address', 'Email already exists in system', $data['email']);
                $hasErrors = true;
            } else {
                $this->emailCache[$data['email']] = true;
            }
        }

        // Mobile validation
        if (! empty($data['mobile'])) {
            $mobile = preg_replace('/\D+/', '', $data['mobile']);
            if (strlen($mobile) !== 10) {
                $this->addError($rowNumber, 'mobile_number', 'Mobile must be 10 digits', $data['mobile']);
                $hasErrors = true;
            } elseif (isset($this->mobileCache[$mobile])) {
                $this->addError($rowNumber, 'mobile_number', 'Duplicate mobile in file', $data['mobile']);
                $hasErrors = true;
            } elseif (User::where('mobile', $mobile)->exists()) {
                $this->addError($rowNumber, 'mobile_number', 'Mobile already exists in system', $data['mobile']);
                $hasErrors = true;
            } else {
                $this->mobileCache[$mobile] = true;
                $data['mobile'] = $mobile;
            }
        }

        // Type validation
        if (! empty($data['type'])) {
            try {
                $this->normalizeUserType($data['type']);
            } catch (\ValueError) {
                $validTypes = 'regular, member, promoter, advisor, mentor, applicant';
                $this->addError($rowNumber, 'user_type', "Invalid type. Valid: {$validTypes}", $data['type']);
                $hasErrors = true;
            }
        }

        // Date validation
        if (! empty($data['dob'])) {
            if (! strtotime($data['dob'])) {
                $this->addError($rowNumber, 'date_of_birth', 'Invalid date format. Use YYYY-MM-DD', $data['dob']);
                $hasErrors = true;
            }
        }

        // Recruitment validation (resolve title to ID)
        if (! empty($data['recruitment'])) {
            $recruitment = $this->resolveRecruitment($data['recruitment'], $rowNumber);
            if (! $recruitment) {
                $hasErrors = true;
            } else {
                $data['recruitment_id'] = $recruitment->id;
            }
        }

        // Geo validation
        if (! $this->validateGeoData($data, $rowNumber)) {
            $hasErrors = true;
        }

        // Address type validation
        if (! empty($data['address_type'])) {
            try {
                AddressTypeCast::from($this->normalizeAddressType($data['address_type']));
            } catch (\ValueError) {
                $this->addError($rowNumber, 'address_category', 'Invalid address category. Use: present, permanent, or business', $data['address_type']);
                $hasErrors = true;
            }
        }

        // KYC validation
        if (! $this->validateKycData($data, $rowNumber)) {
            $hasErrors = true;
        }

        // Payment validation
        if (! empty($data['is_paid']) && $data['is_paid'] !== 'no') {
            if (! is_numeric($data['amount']) || $data['amount'] < 0) {
                $this->addError($rowNumber, 'payment_amount', 'Amount must be a positive number', $data['amount']);
                $hasErrors = true;
            }
        }

        // Boolean field validation
        foreach (['verify_email', 'verify_mobile', 'onboard_user'] as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                if (! in_array($data[$field], ['yes', 'no', '1', '0', 1, 0, true, false], true)) {
                    $friendlyName = $field === 'verify_email' ? 'verify_email' : ($field === 'verify_mobile' ? 'verify_mobile' : 'activate_account');
                    $this->addError($rowNumber, $friendlyName, "Must be yes or no", $data[$field]);
                    $hasErrors = true;
                }
            }
        }

        if (! $hasErrors) {
            $this->preparedRows[] = $data;
        }
    }

    /**
     * Resolve recruitment by title or slug.
     */
    private function resolveRecruitment(string $identifier, int $rowNumber): ?Recruitment
    {
        $key = strtolower(trim($identifier));

        if (! isset($this->recruitmentCache[$key])) {
            $recruitment = Recruitment::where('title', 'like', "%{$identifier}%")
                ->orWhere('slug', $identifier)
                ->first();

            $this->recruitmentCache[$key] = $recruitment;
        }

        if (! $this->recruitmentCache[$key]) {
            $this->addError($rowNumber, 'job_posting_title', 'Job posting not found: ' . $identifier, $identifier);
            return null;
        }

        return $this->recruitmentCache[$key];
    }

    /**
     * Normalize user type to enum value.
     */
    private function normalizeUserType(string $type): string
    {
        return match (strtolower(trim($type))) {
            'member' => 'member',
            'promoter' => 'promoter',
            'advisor' => 'advisor',
            'mentor' => 'mentor',
            'applicant' => 'applicant',
            default => 'regular',
        };
    }

    /**
     * Normalize address type.
     */
    private function normalizeAddressType(string $type): string
    {
        return match (strtolower(trim($type))) {
            'present' => 'present',
            'permanent' => 'permanent',
            default => 'business',
        };
    }

    /**
     * Normalize boolean values (yes/no to boolean).
     */
    private function normalizeBoolean(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['yes', '1', 'true', 'y'], true);
    }

    /**
     * Validate geographic data with caching.
     */
    private function validateGeoData(array &$data, int $rowNumber): bool
    {
        $valid = true;

        // Country validation (handle both names and codes)
        if (! empty($data['country'])) {
            $key = strtolower($data['country']);
            if (! isset($this->countryCache[$key])) {
                $country = Country::where('iso_code_2', strtoupper($data['country']))
                    ->orWhere('name', 'like', "%{$data['country']}%")
                    ->first();

                $this->countryCache[$key] = $country?->iso_code_2 ?? null;
            }

            if (! $this->countryCache[$key]) {
                $this->addError($rowNumber, 'country_name', 'Invalid country', $data['country']);
                $valid = false;
            } else {
                $data['country_code'] = $this->countryCache[$key];
            }
        }

        // State validation (handle both names and codes)
        if (! empty($data['state'])) {
            $key = strtolower($data['state']);
            if (! isset($this->stateCache[$key])) {
                $state = State::where('code', strtoupper($data['state']))
                    ->orWhere('name', 'like', "%{$data['state']}%")
                    ->first();

                $this->stateCache[$key] = $state?->code ?? null;
            }

            if (! $this->stateCache[$key]) {
                $this->addError($rowNumber, 'state_name', 'Invalid state', $data['state']);
                $valid = false;
            } else {
                $data['state_code'] = $this->stateCache[$key];
            }
        }

        // Block validation (optional)
        if (! empty($data['block'])) {
            $key = strtolower($data['block']);
            if (! isset($this->blockCache[$key])) {
                $block = Block::where('id', $data['block'])
                    ->orWhere('name', 'like', "%{$data['block']}%")
                    ->first();

                $this->blockCache[$key] = $block?->id ?? null;
            }

            if (! $this->blockCache[$key]) {
                $this->addError($rowNumber, 'block', 'Invalid block ID or name', $data['block']);
                $valid = false;
            } else {
                $data['block_id'] = $this->blockCache[$key];
            }
        }

        return $valid;
    }

    /**
     * Validate KYC data and store in cache to prevent duplicates.
     */
    private function validateKycData(array $data, int $rowNumber): bool
    {
        $hasKycData = false;
        $valid = true;

        // Check for any KYC data
        if (! empty($data['pan_number'])) {
            $hasKycData = true;
            if (! $this->validatePan($data['pan_number'])) {
                $this->addError($rowNumber, 'pan_number', 'Invalid PAN format (should be: ABCDE1234F)', $data['pan_number']);
                $valid = false;
            } elseif (isset($this->kycNumberCache['pan_' . $data['pan_number']])) {
                $this->addError($rowNumber, 'pan_number', 'PAN already exists in file', $data['pan_number']);
                $valid = false;
            }
        }

        if (! empty($data['aadhaar_number'])) {
            $hasKycData = true;
            $aadhaar = preg_replace('/\D+/', '', $data['aadhaar_number']);
            if (strlen($aadhaar) !== 12) {
                $this->addError($rowNumber, 'aadhaar_number', 'Aadhaar must be 12 digits', $data['aadhaar_number']);
                $valid = false;
            } elseif (isset($this->kycNumberCache['aadhaar_' . $aadhaar])) {
                $this->addError($rowNumber, 'aadhaar_number', 'Aadhaar already exists in file', $data['aadhaar_number']);
                $valid = false;
            }
        }

        if (! empty($data['gst_number'])) {
            $hasKycData = true;
            if (! $this->validateGst($data['gst_number'])) {
                $this->addError($rowNumber, 'gst_number', 'Invalid GST format', $data['gst_number']);
                $valid = false;
            } elseif (isset($this->kycNumberCache['gst_' . $data['gst_number']])) {
                $this->addError($rowNumber, 'gst_number', 'GST already exists in file', $data['gst_number']);
                $valid = false;
            }
        }

        // Cache valid KYC numbers
        if ($valid && $hasKycData) {
            if (! empty($data['pan_number'])) {
                $this->kycNumberCache['pan_' . $data['pan_number']] = true;
            }
            if (! empty($data['aadhaar_number'])) {
                $a = preg_replace('/\D+/', '', $data['aadhaar_number']);
                $this->kycNumberCache['aadhaar_' . $a] = true;
            }
            if (! empty($data['gst_number'])) {
                $this->kycNumberCache['gst_' . $data['gst_number']] = true;
            }
        }

        return $valid;
    }

    /**
     * Validate PAN format.
     */
    private function validatePan(string $pan): bool
    {
        return preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', strtoupper($pan)) === 1;
    }

    /**
     * Validate GST format.
     */
    private function validateGst(string $gst): bool
    {
        return preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}[Z]{1}[0-9A-Z]{1}$/', strtoupper($gst)) === 1;
    }

    /**
     * Add validation error to collection.
     */
    private function addError(int $row, string $column, string $message, mixed $value): void
    {
        $this->validationErrors[] = [
            'row' => $row,
            'column' => $column,
            'value' => $value,
            'message' => $message,
        ];
    }

    /**
     * Handle validation errors - show in UI.
     */
    private function handleValidationErrors(): void
    {
        if (empty($this->validationErrors)) {
            return;
        }

        $message = $this->formatErrorsForDisplay();
        $this->stopImportWithError($message, count($this->validationErrors) . ' validation errors found');
    }

    /**
     * Format errors for display in UI.
     */
    private function formatErrorsForDisplay(): string
    {
        $errors = array_slice($this->validationErrors, 0, 20);
        $message = "Import failed with " . count($this->validationErrors) . " errors:\n\n";

        foreach ($errors as $error) {
            $message .= sprintf(
                "Row %d, Column '%s': %s (Value: %s)\n",
                $error['row'],
                $error['column'],
                $error['message'],
                $error['value'] ?? 'empty'
            );
        }

        if (count($this->validationErrors) > 20) {
            $message .= "\n... and " . (count($this->validationErrors) - 20) . " more errors.";
        }

        return $message;
    }

    /**
     * Persist all validated rows to database.
     */
    private function persistAll(): void
    {
        foreach ($this->preparedRows as $data) {
            // Normalize boolean values
            $verifyEmail = $this->normalizeBoolean($data['verify_email'] ?? 'yes');
            $verifyMobile = $this->normalizeBoolean($data['verify_mobile'] ?? 'yes');
            $onboardUser = $this->normalizeBoolean($data['onboard_user'] ?? 'yes');
            $isPaid = $this->normalizeBoolean($data['is_paid'] ?? 'no');

            // Create User
            $user = $this->createUser($data, $verifyEmail, $verifyMobile, $onboardUser);

            // Create Address
            $address = $this->createAddress($user, $data);

            // Create KYC if data provided
            if ($this->hasKycData($data)) {
                $this->createKyc($user, $data);
            }

            // Create Job Application
            $this->createJobApplication($user, $address, $data, $isPaid);

            // Send welcome notification
            $this->sendWelcomeNotification($user, $data, $verifyEmail);
        }
    }

    /**
     * Create user with verification status.
     */
    private function createUser(array $data, bool $verifyEmail, bool $verifyMobile, bool $onboardUser): User
    {
        $now = now();
        $password = $this->generateDefaultPassword($data);

        // Store password for notification
        $this->passwords[$data['email']] = $password;

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'password' => Hash::make($password),
            'type' => $this->normalizeUserType($data['type']),
            'status' => $onboardUser ? UserStatusCast::ACTIVE->value : UserStatusCast::PENDING->value,
            'bio' => $data['bio'] ?? null,
            'gender' => $data['gender'] ?? null,
            'dob' => $data['dob'] ? date('Y-m-d', strtotime($data['dob'])) : null,
            'onboarded' => true, // Mark as onboarded complete
            'email_verified_at' => $verifyEmail ? $now : null,
            'mobile_verified_at' => $verifyMobile ? $now : null,
        ]);
    }

    /**
     * Generate default password from mobile or DOB.
     */
    private function generateDefaultPassword(array $data): string
    {
        // Priority 1: Last 6 digits of mobile
        if (! empty($data['mobile']) && strlen((string) $data['mobile']) >= 6) {
            return substr((string) $data['mobile'], -6);
        }

        // Priority 2: MMYYYY from DOB
        if (! empty($data['dob']) && $timestamp = strtotime($data['dob'])) {
            return date('mY', $timestamp);
        }

        // Fallback: Random string
        return Str::random(8);
    }

    /**
     * Create address for user.
     */
    private function createAddress(User $user, array $data): Address
    {
        return $user->addresses()->create([
            'type' => AddressTypeCast::from($this->normalizeAddressType($data['address_type'])),
            'title' => 'Primary Address',
            'person_name' => $data['name'],
            'person_email' => $data['email'],
            'person_mobile' => $data['mobile'],
            'address_1' => $data['addr_line1'],
            'address_2' => $data['addr_line2'] ?? null,
            'landmark' => $data['landmark'] ?? null,
            'city' => $data['city'],
            'state_code' => $data['state_code'] ?? null,
            'country_code' => $data['country_code'] ?? null,
            'postal_code' => $data['postal_code'],
            'block_id' => $data['block_id'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'default' => true,
        ]);
    }

    /**
     * Create KYC record for user.
     */
    private function createKyc(User $user, array $data): void
    {
        $kycData = [
            'kyc_type' => 'personal',
            'status' => KycStatusCast::APPROVED->value,
            'submitted_at' => now(),
        ];

        $kycData['pan_number'] = $data['pan_number'] ?? null;
        $kycData['aadhaar_number'] = $data['aadhaar_number'] ?? null;

        $user->kycs()->create($kycData);
    }

    /**
     * Check if row has KYC data.
     */
    private function hasKycData(array $data): bool
    {
        return ! empty($data['pan_number']) || ! empty($data['aadhaar_number']);
    }

    /**
     * Create job application record.
     */
    private function createJobApplication(User $user, Address $address, array $data, bool $isPaid): void
    {
        $jobData = [
            'recruitment_id' => $data['recruitment_id'],
            'applicant_type' => 'user',
            'applicant_id' => $user->id,
            'guardian_name' => $data['guardian_name'] ?? null,
            'address_id' => $address->id,
            'educations' => $data['education'] ? [$data['education']] : [],
            'skills' => $data['skills'] ? explode(',', (string) $data['skills']) : [],
            'experiences' => $data['experience'] ? [$data['experience']] : [],
            'reference_name' => $data['reference_name'] ?? null,
            'reference_contact' => $data['reference_mobile'] ?? null,
        ];

        if ($isPaid) {
            $jobData['is_paid'] = true;
            $jobData['amount'] = (int) $data['amount'];
            $jobData['transaction_id'] = $data['transaction_id'] ?? null;
        }

        $user->jobApplications()->create($jobData);
    }

    /**
     * Send welcome notification to user.
     */
    private function sendWelcomeNotification(User $user, array $data, bool $verifyEmail): void
    {
        try {
            $password = $this->passwords[$data['email']] ?? $this->generateDefaultPassword($data);

            $notification = new JobApplicationWelcomeNotification(
                password: $password,
                isVerified: $verifyEmail
            );

            $user->notify($notification);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
