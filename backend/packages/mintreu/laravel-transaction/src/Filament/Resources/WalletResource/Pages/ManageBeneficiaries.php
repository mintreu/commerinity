<?php

namespace Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages;


use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
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

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $activeNavigationIcon = 'heroicon-m-building-office-2';



    public static function getNavigationLabel(): string
    {
        return 'Banks';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([

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
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('bank_name')
                        ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                        ->weight(FontWeight::SemiBold)
                        ->icon('heroicon-m-building-office')
                        ->color('primary'),
                    Tables\Columns\TextColumn::make('bank_branch')
                        ->weight(FontWeight::SemiBold)
                        ->sortable()
                        ->icon('heroicon-s-map-pin'),
                    Tables\Columns\TextColumn::make('ifsc')
                        ->weight(FontWeight::SemiBold)
                        ->sortable()
                        ->icon('heroicon-c-folder'),
                    Tables\Columns\TextColumn::make('accountable.name')
                        ->icon('heroicon-c-identification')
                        ->color('primary')
                        ->alignRight(),
                    Tables\Columns\ToggleColumn::make('default')->default(false)->alignRight(),
                    Tables\Columns\TextColumn::make('status')->badge()->alignRight(),
                ])
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('Create Bank Account'))
                    ->icon('heroicon-s-squares-plus')
                    ->modalIcon('heroicon-m-building-office')
                    ->modalHeading('Add New Bank Account')
                    ->createAnother(false)
                    ->mutateFormDataUsing(function ($data){
                        return array_merge($data,[
                           'accountable_id' => $this->record->walletable_id,
                            'accountable_type' => $this->record->walletable_type,
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DissociateBulkAction::make(),
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
