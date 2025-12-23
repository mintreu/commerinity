<?php

namespace App\Filament\Resources\Kycs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class KycInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('kycable_type'),
                TextEntry::make('kycable_id')
                    ->numeric(),
                TextEntry::make('kyc_type'),
                TextEntry::make('company_name'),
                TextEntry::make('company_type'),
                TextEntry::make('pan_number'),
                TextEntry::make('aadhaar_number'),
                TextEntry::make('gst_number'),
                TextEntry::make('status'),
                TextEntry::make('submitted_at')
                    ->dateTime(),
                TextEntry::make('reviewed_at')
                    ->dateTime(),
                TextEntry::make('reviewed_by')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
