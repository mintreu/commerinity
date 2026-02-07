<?php

declare(strict_types=1);

namespace App\Imports;

use App\Casts\AddressTypeCast;
use App\Casts\JobApplicationStatusCast;
use App\Casts\KycStatusCast;
use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Casts\UserStatusCast;
use App\Filament\Resources\JobApplications\Schemas\ImportSchema;
use App\Models\Address;
use App\Models\Geo\Block;
use App\Models\Geo\Country;
use App\Models\Geo\State;
use App\Models\Kyc;
use App\Models\Recruitment\JobApplication;
use App\Models\Recruitment\Recruitment;
use App\Models\Transaction;
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

    private const REQUIRED_HEADERS = ImportSchema::REQUIRED_HEADERS;

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
    public function __construct()
    {
        parent::__construct(JobApplication::class);
    }

    public function collection(Collection $collection): void
    {
        try {
            $this->headers = $collection->first()
                ->map(fn ($h) => trim((string) $h))
                ->values()
                ->collect();

            $this->validateFileHeaders();

            $this->rows = $collection->skip(1)->values();

            if ($this->rows->isEmpty()) {
                $this->stopImportWithError('No data rows found in Excel file');

                return;
            }

            $this->validateAndPrepareAll();

            if (! empty($this->validationErrors)) {
                $this->handleValidationErrors();

                return;
            }

            DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
            DB::statement('SET SESSION innodb_lock_wait_timeout = 200');
            DB::transaction(fn () => $this->persistAll(), 5);

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
    protected function validateFileHeaders(): void
    {
        $foundHeaders = $this->headers->toArray();
        $missing = array_diff(self::REQUIRED_HEADERS, $foundHeaders);

        if (! empty($missing)) {
            $this->stopImportWithError(
                'Missing required columns: '.implode(', ', $missing)
            );
        }
    }

    /**
     * Validate and normalize all rows before database insertion.
     */
    private function validateAndPrepareAll(): void
    {
        $this->preloadGeoCaches($this->rows);

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

        if (! isset($data['is_paid']) || $data['is_paid'] === '') {
            $data['is_paid'] = self::OPTIONAL_DEFAULTS['payment_status'] ?? 'no';
        }

        if (! isset($data['type']) || $data['type'] === '') {
            $data['type'] = 'regular';
        }

        if (! isset($data['country']) || $data['country'] === '') {
            $data['country'] = self::OPTIONAL_DEFAULTS['country_name'] ?? 'India';
        }

        if (! isset($data['address_type']) || $data['address_type'] === '') {
            $data['address_type'] = self::OPTIONAL_DEFAULTS['address_category'] ?? 'home';
        }

        if (isset($data['gender']) && $data['gender'] === '') {
            $data['gender'] = 'other';
        }

        if (isset($data['pan_number']) && trim((string) $data['pan_number']) === '') {
            unset($data['pan_number']);
        }

        if (isset($data['aadhaar_number']) && trim((string) $data['aadhaar_number']) === '') {
            unset($data['aadhaar_number']);
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
        foreach (self::REQUIRED_HEADERS as $header) {
            $internalName = self::HEADER_MAP[$header];
            if (! isset($data[$internalName]) || $data[$internalName] === '') {
                $this->addError($rowNumber, $header, 'Required field is empty', $data[$internalName] ?? null);
                $hasErrors = true;
            }
        }

        // Email validation
        if (! empty($data['email'])) {
            if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->addError($rowNumber, 'email', 'Invalid email format', $data['email']);
                $hasErrors = true;
            } elseif (isset($this->emailCache[$data['email']])) {
                $this->addError($rowNumber, 'email', 'Duplicate email in file', $data['email']);
                $hasErrors = true;
            } elseif (User::where('email', $data['email'])->exists()) {
                $this->addError($rowNumber, 'email', 'Email already exists in system', $data['email']);
                $hasErrors = true;
            } else {
                $this->emailCache[$data['email']] = true;
            }
        }

        // Mobile validation
        if (! empty($data['mobile'])) {
            $mobile = preg_replace('/\D+/', '', $data['mobile']);
            if (strlen($mobile) !== 10) {
                $this->addError($rowNumber, 'mobile', 'Mobile must be 10 digits', $data['mobile']);
                $hasErrors = true;
            } elseif (isset($this->mobileCache[$mobile])) {
                $this->addError($rowNumber, 'mobile', 'Duplicate mobile in file', $data['mobile']);
                $hasErrors = true;
            } elseif (User::where('mobile', $mobile)->exists()) {
                $this->addError($rowNumber, 'mobile', 'Mobile already exists in system', $data['mobile']);
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
                $validTypes = 'regular, member, promoter';
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

        // Recruitment validation (resolve slug or UUID to ID)
        if (! empty($data['recruitment_slug'])) {
            $recruitment = $this->resolveRecruitment($data['recruitment_slug'], $rowNumber);
            if (! $recruitment) {
                $hasErrors = true;
            } else {
                $data['recruitment_id'] = $recruitment->id;
            }
        } else {
            $this->addError($rowNumber, 'job_posting_slug', 'Job posting slug is required', null);
            $hasErrors = true;
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
                $this->addError(
                    $rowNumber,
                    'address_category',
                    'Invalid address category. Use: home, work, delivery, pickup, hub, warehouse, service_point, other (or legacy: present, permanent, business)',
                    $data['address_type']
                );
                $hasErrors = true;
            }
        }

        // KYC validation
        if (! $this->validateKycData($data, $rowNumber)) {
            $hasErrors = true;
        }

        // Payment validation
        if (! empty($data['is_paid']) && $data['is_paid'] !== 'no') {
            if (! is_numeric($data['amount']) || $data['amount'] <= 0) {
                $this->addError($rowNumber, 'payment_amount', 'Amount must be a positive number', $data['amount']);
                $hasErrors = true;
            }
        }

        // Boolean field validation
        if (! $hasErrors) {
            $this->preparedRows[] = $data;
        }
    }

    /**
     * Resolve recruitment by slug or UUID.
     */
    private function resolveRecruitment(string $identifier, int $rowNumber): ?Recruitment
    {
        $key = strtolower(trim($identifier));

        if (! isset($this->recruitmentCache[$key])) {
            $query = Recruitment::query()->where('slug', $identifier);
            if ($this->looksLikeUuid($identifier)) {
                $query->orWhere('uuid', $identifier);
            }
            $recruitment = $query->first();

            $this->recruitmentCache[$key] = $recruitment;
        }

        if (! $this->recruitmentCache[$key]) {
            $this->addError($rowNumber, 'job_posting_slug', 'Job posting not found: '.$identifier, $identifier);

            return null;
        }

        return $this->recruitmentCache[$key];
    }

    private function looksLikeUuid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value
        );
    }

    /**
     * Normalize user type to enum value.
     */
    private function normalizeUserType(string $type): string
    {
        return match (strtolower(trim($type))) {
            'member' => 'member',
            'promoter' => 'promoter',
            default => 'regular',
        };
    }

    /**
     * Normalize address type.
     */
    private function normalizeAddressType(string $type): string
    {
        return match (strtolower(trim($type))) {
            'present', 'permanent', 'home' => 'home',
            'business', 'work' => 'work',
            'delivery' => 'delivery',
            'pickup' => 'pickup',
            'hub' => 'hub',
            'warehouse' => 'warehouse',
            'service_point' => 'service_point',
            default => 'other',
        };
    }

    /**
     * Normalize boolean values (yes/no to boolean).
     */
    private function normalizeBoolean(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['yes', '1', 'true', 'y'], true);
    }

    private function preloadGeoCaches(Collection $rows): void
    {
        $states = [];
        $blocks = [];

        foreach ($rows as $row) {
            $data = $this->mapHeadersToInternal($row);
            if (! empty($data['state'])) {
                $states[] = trim((string) $data['state']);
            }
            if (! empty($data['block'])) {
                $blocks[] = trim((string) $data['block']);
            }
        }

        $states = array_values(array_unique(array_filter($states)));
        if (! empty($states)) {
            $stateCodes = array_map('strtoupper', $states);
            $stateRows = State::whereIn('code', $stateCodes)
                ->orWhereIn('name', $states)
                ->get(['code', 'name']);

            foreach ($stateRows as $state) {
                $this->stateCache[strtolower($state->code)] = $state->code;
                $this->stateCache[strtolower($state->name)] = $state->code;
            }
        }

        $blocks = array_values(array_unique(array_filter($blocks)));
        if (! empty($blocks) && ! empty($this->stateCache)) {
            $stateCodes = array_values(array_unique(array_values($this->stateCache)));
            $blockRows = Block::whereIn('state_code', $stateCodes)
                ->whereIn('name', $blocks)
                ->get(['id', 'name', 'state_code']);

            foreach ($blockRows as $block) {
                $key = strtolower($block->state_code.'|'.$block->name);
                $this->blockCache[$key] = $block->id;
                $this->blockCache[strtolower($block->name)] ??= $block->id;
            }
        }

        if (empty($this->countryCache)) {
            $country = Country::where('iso_code_2', 'IN')
                ->orWhere('name', 'India')
                ->first();
            if ($country) {
                $this->countryCache['in'] = $country->iso_code_2;
                $this->countryCache['india'] = $country->iso_code_2;
            }
        }
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
            $stateCode = $data['state_code'] ?? null;
            $stateKey = $stateCode ? strtolower($stateCode.'|'.$data['block']) : null;

            if ($stateKey && isset($this->blockCache[$stateKey])) {
                $data['block_id'] = $this->blockCache[$stateKey];
            } else {
                if (! isset($this->blockCache[$key])) {
                    $query = Block::query()
                        ->where('name', $data['block']);
                    if ($stateCode) {
                        $query->where('state_code', $stateCode);
                    }
                    $block = $query->first();

                    $this->blockCache[$key] = $block?->id ?? null;
                }

                if ($this->blockCache[$key]) {
                    $data['block_id'] = $this->blockCache[$key];
                }
            }

            if (! isset($data['block_id'])) {
                $this->addError($rowNumber, 'block_name', 'Invalid block ID or name', $data['block']);
                $valid = false;
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

        $hasPan = ! empty($data['pan_number']);
        $hasAadhaar = ! empty($data['aadhaar_number']);
        $hasGst = ! empty($data['gst_number']);

        if (! $hasPan && ($hasAadhaar || $hasGst)) {
            $this->addError($rowNumber, 'pan_number', 'PAN is required when Aadhaar/GST is provided', $data['pan_number'] ?? null);
            $valid = false;
        }

        // Check for any KYC data
        if ($hasPan) {
            $hasKycData = true;
            if (! $this->validatePan($data['pan_number'])) {
                $this->addError($rowNumber, 'pan_number', 'Invalid PAN format (should be: ABCDE1234F)', $data['pan_number']);
                $valid = false;
            } elseif (isset($this->kycNumberCache['pan_'.$data['pan_number']])) {
                $this->addError($rowNumber, 'pan_number', 'PAN already exists in file', $data['pan_number']);
                $valid = false;
            }
        }

        if ($hasAadhaar) {
            $hasKycData = true;
            $aadhaar = preg_replace('/\D+/', '', $data['aadhaar_number']);
            if (strlen($aadhaar) !== 12) {
                $this->addError($rowNumber, 'aadhaar_number', 'Aadhaar must be 12 digits', $data['aadhaar_number']);
                $valid = false;
            } elseif (isset($this->kycNumberCache['aadhaar_'.$aadhaar])) {
                $this->addError($rowNumber, 'aadhaar_number', 'Aadhaar already exists in file', $data['aadhaar_number']);
                $valid = false;
            }
        }

        if ($hasGst) {
            $hasKycData = true;
            if (! $this->validateGst($data['gst_number'])) {
                $this->addError($rowNumber, 'gst_number', 'Invalid GST format', $data['gst_number']);
                $valid = false;
            } elseif (isset($this->kycNumberCache['gst_'.$data['gst_number']])) {
                $this->addError($rowNumber, 'gst_number', 'GST already exists in file', $data['gst_number']);
                $valid = false;
            }
        }

        // Cache valid KYC numbers
        if ($valid && $hasKycData) {
            if (! empty($data['pan_number'])) {
                $this->kycNumberCache['pan_'.$data['pan_number']] = true;
            }
            if (! empty($data['aadhaar_number'])) {
                $a = preg_replace('/\D+/', '', $data['aadhaar_number']);
                $this->kycNumberCache['aadhaar_'.$a] = true;
            }
            if (! empty($data['gst_number'])) {
                $this->kycNumberCache['gst_'.$data['gst_number']] = true;
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
        $this->stopImportWithError($message, count($this->validationErrors).' validation errors found');
    }

    /**
     * Format errors for display in UI.
     */
    private function formatErrorsForDisplay(): string
    {
        $errors = array_slice($this->validationErrors, 0, 20);
        $message = 'Import failed with '.count($this->validationErrors)." errors:\n\n";

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
            $message .= "\n... and ".(count($this->validationErrors) - 20).' more errors.';
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
            $isPaid = $this->normalizeBoolean($data['is_paid'] ?? 'no');

            // Create User
            $user = $this->createUser($data);

            // Create Address
            $address = $this->createAddress($user, $data);

            // Create KYC if data provided
            if ($this->hasKycData($data)) {
                $this->createKyc($user, $data);
            }

            // Create Job Application
            $this->createJobApplication($user, $address, $data, $isPaid);

            // Send welcome notification
            $this->sendWelcomeNotification($user, $data, true);
        }
    }

    /**
     * Create user with verification status.
     */
    private function createUser(array $data): User
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
            'status' => UserStatusCast::ACTIVE->value,
            'bio' => $data['bio'] ?? null,
            'gender' => $data['gender'] ?? null,
            'dob' => $data['dob'] ? date('Y-m-d', strtotime($data['dob'])) : null,
            'onboarded' => true, // Mark as onboarded complete
            'email_verified_at' => $now,
            'mobile_verified_at' => $now,
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
        return ! empty($data['pan_number']);
    }

    /**
     * Create job application record.
     */
    private function createJobApplication(User $user, Address $address, array $data, bool $isPaid): void
    {
        $recruitment = Recruitment::find($data['recruitment_id']);
        if (! $recruitment) {
            throw new RuntimeException('Recruitment not found for application');
        }

        $shouldPay = $recruitment->is_payable && $isPaid;
        $amount = $recruitment->is_payable
            ? ($shouldPay ? (int) $data['amount'] : (int) $recruitment->fees)
            : 0;

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
            'amount' => $amount,
            'status' => $recruitment->is_payable
                ? ($shouldPay ? JobApplicationStatusCast::Submitted : JobApplicationStatusCast::AwaitingPayment)
                : JobApplicationStatusCast::Submitted,
            'submitted_at' => $recruitment->is_payable && ! $shouldPay ? null : now(),
        ];

        $application = $user->jobApplications()->create($jobData);

        if ($shouldPay) {
            $transaction = $this->createImportTransaction($application, $user, $recruitment, $amount ?? 0);
            $application->markAsPaid($transaction->id);
        }
    }

    private function createImportTransaction(
        JobApplication $application,
        User $user,
        Recruitment $recruitment,
        int $amount
    ): Transaction {
        $successUrl = config('app.client_url')."/career/applications/{$application->uuid}?payment=success";
        $failureUrl = config('app.client_url')."/career/applications/{$application->uuid}?payment=failed";

        return $application->transaction()->create([
            'type' => TransactionTypeCast::DEBIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => $amount,
            'currency' => 'INR',
            'payment_method' => PaymentMethodCast::WALLET,
            'purpose' => 'Job Application Fee (Imported)',
            'description' => "Imported payment for {$recruitment->title}",
            'success_url' => $successUrl,
            'failure_url' => $failureUrl,
            'verified' => true,
            'verified_at' => now(),
            'metadata' => [
                'source' => 'excel_import',
                'user_id' => $user->id,
                'recruitment_id' => $recruitment->id,
            ],
        ]);
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
