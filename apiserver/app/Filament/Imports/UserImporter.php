<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Checkbox;
use Illuminate\Support\Number;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->example('Rahul Sharma')
                ->rules(['required', 'max:255']),
            ImportColumn::make('email')
                ->requiredMapping()
                ->example('rahul@example.com')
                ->rules(['required', 'email', 'max:255']),
            ImportColumn::make('mobile')
                ->example('9876543210')
                ->rules(['max:15']),
            ImportColumn::make('password')
                ->sensitive()
                ->helperText('Only for new users or password reset during import.')
                ->ignoreBlankState()
                ->rules(['max:255']),
            ImportColumn::make('referral_code')
                ->rules(['max:8']),
            ImportColumn::make('gender')
                ->ignoreBlankState()
                ->rules(['max:255']),
            ImportColumn::make('dob')
                ->ignoreBlankState()
                ->rules(['date']),
            ImportColumn::make('type')
                ->ignoreBlankState()
                ->rules(['max:255']),
            ImportColumn::make('status')
                ->ignoreBlankState()
                ->rules(['max:255']),
            ImportColumn::make('onboarded')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['boolean']),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Checkbox::make('updateExisting')
                ->label('Update existing users by email')
                ->default(true),
        ];
    }

    public function resolveRecord(): ?User
    {
        if ($this->options['updateExisting'] ?? true) {
            return User::firstOrNew([
                'email' => $this->data['email'],
            ]);
        }

        return new User();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your user import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
