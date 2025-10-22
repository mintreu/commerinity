<?php

namespace Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages;


use Filament\Schemas\Schema;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource;

class ManageBeneficiaries extends ManageRelatedRecords
{
    protected static string $resource = WalletResource::class;

    protected static string $relationship = 'beneficiaries';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';
    protected static string | \BackedEnum | null $activeNavigationIcon = 'heroicon-m-building-office-2';



    public static function getNavigationLabel(): string
    {
        return 'Banks';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('bank_name')
            ->contentGrid([
                'md' => 2,
                'lg' => 3
            ])
            ->columns([
                Stack::make([
                    TextColumn::make('bank_name')
                        ->size(TextSize::Large)
                        ->weight(FontWeight::SemiBold)
                        ->icon('heroicon-m-building-office')
                        ->color('primary'),
                    TextColumn::make('bank_branch')
                        ->weight(FontWeight::SemiBold)
                        ->sortable()
                        ->icon('heroicon-s-map-pin'),
                    TextColumn::make('ifsc')
                        ->weight(FontWeight::SemiBold)
                        ->sortable()
                        ->icon('heroicon-c-folder'),
                    TextColumn::make('accountable.name')
                        ->icon('heroicon-c-identification')
                        ->color('primary')
                        ->alignRight(),
                    ToggleColumn::make('default')->default(false)->alignRight(),
                    TextColumn::make('status')->badge()->alignRight(),
                ])
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('Create Bank Account'))
                    ->icon('heroicon-s-squares-plus')
                    ->modalIcon('heroicon-m-building-office')
                    ->modalHeading('Add New Bank Account')
                    ->createAnother(false)
                    ->mutateDataUsing(function ($data){
                        return array_merge($data,[
                           'accountable_id' => $this->record->walletable_id,
                            'accountable_type' => $this->record->walletable_type,
                        ]);
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
//                    Tables\Actions\DissociateBulkAction::make(),
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
