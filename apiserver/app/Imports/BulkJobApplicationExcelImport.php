<?php

declare(strict_types=1);

namespace App\Imports;

use App\Casts\AddressTypeCast;
use App\Models\User;
use App\Models\Geo\Block;
use App\Models\Geo\State;
use App\Models\Geo\Country;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use RuntimeException;
use Throwable;

final class BulkJobApplicationExcelImport implements ToCollection
{
    private Collection $headers;
    private Collection $rows;

    /** Fully validated & normalized rows */
    private array $preparedRows = [];

    /** Excel-level duplicate tracking */
    private array $emails = [];
    private array $mobiles = [];

    /** Geo caches (NO N+1) */
    private array $countryCache = [];
    private array $stateCache   = [];
    private array $blockCache   = [];

    /** Required columns (hard dependency) */
    private const REQUIRED_COLUMNS = [
        'name',
        'email',
        'mobile',
        'address_type',
        'address_1',
        'city',
        'postal_code',
        'state_code',
        'country_code',
        'recruitment_id',
    ];

    public function collection(Collection $collection): void
    {
        try {
            if ($collection->isEmpty()) {
                throw new RuntimeException('Excel file is empty');
            }

            $this->headers = $collection->first()
                ->map(fn ($h) => trim((string) $h));

            $this->assertValidHeaders();

            $this->rows = $collection->skip(1);

            if ($this->rows->isEmpty()) {
                throw new RuntimeException('No data rows found in Excel');
            }

            $this->validateAndPrepareAll();

            DB::transaction(fn () => $this->persistAll());

            Notification::make()
                ->title('Import successful')
                ->success()
                ->body(count($this->preparedRows).' job applications imported.')
                ->send();

        } catch (Throwable $e) {

            Notification::make()
                ->title('Import failed')
                ->danger()
                ->body($e->getMessage())
                ->send();

            throw $e;
        }
    }

    /* ============================================================
     | VALIDATION + NORMALIZATION (NO DB WRITE HERE)
     ============================================================ */

    private function validateAndPrepareAll(): void
    {
        foreach ($this->rows as $index => $row) {
            $rowNumber = $index + 2;

            $data = $this->mapRowStrict($row, $rowNumber);

            $this->normalizeIdentity($data);
            $this->validateRequiredFields($data, $rowNumber);
            $this->validateUniqueUser($data, $rowNumber);

            // ENUM (single parse, reused later)
            $data['address_type'] = $this->validateAddressType($data['address_type'], $rowNumber);

            // GEO normalization (cached)
            $data = $this->normalizeGeo($data, $rowNumber);

            $this->preparedRows[] = $data;
        }
    }

    private function assertValidHeaders(): void
    {
        foreach (self::REQUIRED_COLUMNS as $column) {
            if (! $this->headers->contains($column)) {
                throw new RuntimeException("Missing required column '{$column}' in Excel header");
            }
        }
    }

    private function mapRowStrict(Collection $row, int $rowNumber): array
    {
        if ($row->count() !== $this->headers->count()) {
            throw new RuntimeException(
                "Row {$rowNumber}: Column count mismatch with header"
            );
        }

        return $this->headers
            ->mapWithKeys(fn ($header, $i) => [$header => trim((string) $row[$i])])
            ->toArray();
    }

    private function normalizeIdentity(array &$data): void
    {
        $data['email']  = strtolower(trim($data['email']));
        $data['mobile'] = preg_replace('/\D+/', '', $data['mobile']);
    }

    private function validateRequiredFields(array $data, int $row): void
    {
        foreach (self::REQUIRED_COLUMNS as $key) {
            if (! isset($data[$key]) || $data[$key] === '') {
                throw new RuntimeException("Row {$row}: Missing required field '{$key}'");
            }
        }
    }

    private function validateAddressType(string $value, int $row): AddressTypeCast
    {
        $enum = AddressTypeCast::tryFrom($value);

        if (! $enum) {
            throw new RuntimeException("Row {$row}: Invalid address_type '{$value}'");
        }

        return $enum;
    }

    private function validateUniqueUser(array $data, int $row): void
    {
        if (isset($this->emails[$data['email']])) {
            throw new RuntimeException("Row {$row}: Duplicate email in Excel");
        }

        if (isset($this->mobiles[$data['mobile']])) {
            throw new RuntimeException("Row {$row}: Duplicate mobile in Excel");
        }

        if (
            User::where('email', $data['email'])
                ->orWhere('mobile', $data['mobile'])
                ->exists()
        ) {
            throw new RuntimeException("Row {$row}: Email or mobile already exists in system");
        }

        $this->emails[$data['email']]   = true;
        $this->mobiles[$data['mobile']] = true;
    }

    private function normalizeGeo(array $data, int $row): array
    {
        $data['country_code'] = $this->resolveCountry($data['country_code'], $row);
        $data['state_code']   = $this->resolveState($data['state_code'], $row);

        if (! empty($data['block_id'])) {
            $data['block_id'] = $this->resolveBlock($data['block_id'], $row);
        }

        return $data;
    }

    private function resolveCountry(string $value, int $row): string
    {
        return $this->countryCache[$value]
            ??= optional(
            Country::where('iso_code_2', $value)
                ->orWhere('id', $value)
                ->first()
        )->iso_code_2
            ?? throw new RuntimeException("Row {$row}: Invalid country");
    }

    private function resolveState(string $value, int $row): string
    {
        return $this->stateCache[$value]
            ??= optional(
            State::where('code', $value)
                ->orWhere('id', $value)
                ->first()
        )->code
            ?? throw new RuntimeException("Row {$row}: Invalid state");
    }

    private function resolveBlock(string $value, int $row): int
    {
        return $this->blockCache[$value]
            ??= optional(
            Block::where('id', $value)
                ->orWhere('name', $value)
                ->first()
        )->id
            ?? throw new RuntimeException("Row {$row}: Invalid block");
    }

    /* ============================================================
     | DB WRITE (TRANSACTION SAFE)
     ============================================================ */

    private function persistAll(): void
    {
        foreach ($this->preparedRows as $data) {

            $user = User::create([
                'name'       => $data['name'],
                'email'      => $data['email'],
                'mobile'     => $data['mobile'],
                'password'   => Hash::make(Str::random(16)),
                'status'     => 'active',
                'onboarded'  => true,
                'email_verified_at'  => now(),
                'mobile_verified_at' => now(),
            ]);

            $address = $user->addresses()->create([
                'type'         => $data['address_type'],
                'address_1'    => $data['address_1'],
                'city'         => $data['city'],
                'postal_code'  => $data['postal_code'],
                'state_code'   => $data['state_code'],
                'country_code' => $data['country_code'],
                'block_id'     => $data['block_id'] ?? null,
                'default'      => true,
            ]);

            $user->jobApplications()->create([
                'recruitment_id' => $data['recruitment_id'],
                'address_id'     => $address->id,
                'submitted_at'   => now(),
                'import_data'    => $data,
            ]);
        }
    }
}
