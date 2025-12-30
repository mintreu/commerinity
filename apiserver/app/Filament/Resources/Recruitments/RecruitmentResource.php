<?php

namespace App\Filament\Resources\Recruitments;

use App\Filament\Resources\Recruitments\Pages\CreateRecruitment;
use App\Filament\Resources\Recruitments\Pages\EditRecruitment;
use App\Filament\Resources\Recruitments\Pages\ListRecruitments;
use App\Filament\Resources\Recruitments\Schemas\RecruitmentForm;
use App\Filament\Resources\Recruitments\Tables\RecruitmentsTable;
use App\Models\Recruitment\Recruitment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecruitmentResource extends Resource
{
    protected static ?string $model = Recruitment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|null|\UnitEnum $navigationGroup = 'Recruitment';

    public static function form(Schema $schema): Schema
    {
        return RecruitmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecruitmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecruitments::route('/'),
            'create' => CreateRecruitment::route('/create'),
            'edit' => EditRecruitment::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
