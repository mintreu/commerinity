<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Exports\Enums\ExportFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('uuid')
                ->label('UUID'),
            ExportColumn::make('name'),
            ExportColumn::make('email'),
            ExportColumn::make('email_verified_at'),
            ExportColumn::make('mobile'),
            ExportColumn::make('mobile_verified_at')
                ->enabledByDefault(false),
            ExportColumn::make('referral_code'),
            ExportColumn::make('parent.name'),
            ExportColumn::make('level_id'),
            ExportColumn::make('originator_type'),
            ExportColumn::make('originator_id'),
            ExportColumn::make('bio'),
            ExportColumn::make('gender'),
            ExportColumn::make('dob'),
            ExportColumn::make('type'),
            ExportColumn::make('status'),
            ExportColumn::make('status_feedback')
                ->enabledByDefault(false),
            ExportColumn::make('onboarded'),
            ExportColumn::make('subscribed_at'),
            ExportColumn::make('wallet.uuid')
                ->label('Wallet UUID'),
            ExportColumn::make('wallet.status')
                ->label('Wallet Status'),
            ExportColumn::make('wallet.balance')
                ->label('Wallet Balance (Paisa)'),
            ExportColumn::make('wallet.hold_balance')
                ->label('Wallet Hold (Paisa)')
                ->enabledByDefault(false),
            ExportColumn::make('wallet.points')
                ->label('Wallet Points')
                ->enabledByDefault(false),
            ExportColumn::make('kyc.kyc_type')
                ->label('KYC Type'),
            ExportColumn::make('kyc.status')
                ->label('KYC Status'),
            ExportColumn::make('kyc.pan_number')
                ->enabledByDefault(false),
            ExportColumn::make('kyc.aadhaar_number')
                ->enabledByDefault(false),
            ExportColumn::make('kyc.submitted_at')
                ->enabledByDefault(false),
            ExportColumn::make('addresses_count')
                ->counts('addresses')
                ->label('Address Count'),
            ExportColumn::make('default_address_title')
                ->label('Default Address Title')
                ->state(fn (User $record): ?string => self::defaultAddressFor($record)?->title),
            ExportColumn::make('default_address_city')
                ->label('Default Address City')
                ->state(fn (User $record): ?string => self::defaultAddressFor($record)?->city),
            ExportColumn::make('default_address_pin')
                ->label('Default Address PIN')
                ->state(fn (User $record): ?string => self::defaultAddressFor($record)?->postal_code),
            ExportColumn::make('default_address_state')
                ->label('Default Address State')
                ->state(fn (User $record): ?string => self::defaultAddressFor($record)?->state_code),
            ExportColumn::make('default_address_country')
                ->label('Default Address Country')
                ->state(fn (User $record): ?string => self::defaultAddressFor($record)?->country_code),
            ExportColumn::make('active_subscription_uuid')
                ->label('Active Subscription UUID')
                ->state(fn (User $record): ?string => self::activeSubscriptionFor($record)?->uuid),
            ExportColumn::make('active_subscription_status')
                ->label('Subscription Status')
                ->state(fn (User $record): ?string => self::activeSubscriptionFor($record)?->status),
            ExportColumn::make('active_subscription_stage')
                ->label('Subscription Stage')
                ->state(fn (User $record): ?string => self::activeSubscriptionFor($record)?->stage?->name),
            ExportColumn::make('active_subscription_level')
                ->label('Subscription Level')
                ->state(fn (User $record): ?string => self::activeSubscriptionFor($record)?->level?->name),
            ExportColumn::make('active_subscription_starts_at')
                ->label('Subscription Starts At')
                ->state(fn (User $record): ?string => self::activeSubscriptionFor($record)?->starts_at?->toDateTimeString()),
            ExportColumn::make('active_subscription_expires_at')
                ->label('Subscription Expires At')
                ->state(fn (User $record): ?string => self::activeSubscriptionFor($record)?->expires_at?->toDateTimeString()),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public function getFormats(): array
    {
        return [
            ExportFormat::Xlsx,
            ExportFormat::Csv,
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with([
            'wallet',
            'kyc',
            'addresses',
            'subscriptions.stage',
            'subscriptions.level',
        ]);
    }

    private static function defaultAddressFor(User $record): ?\App\Models\Address
    {
        return $record->addresses->firstWhere('default', true) ?? $record->addresses->first();
    }

    private static function activeSubscriptionFor(User $record): ?\App\Models\Membership\UserSubscription
    {
        return $record->subscriptions
            ->filter(fn ($subscription) => $subscription->status === \App\Models\Membership\UserSubscription::STATUS_ACTIVE && $subscription->is_paid)
            ->sortByDesc('created_at')
            ->first();
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your user export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
