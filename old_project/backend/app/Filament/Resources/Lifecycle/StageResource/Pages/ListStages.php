<?php

namespace App\Filament\Resources\Lifecycle\StageResource\Pages;

use App\Filament\Resources\Lifecycle\StageResource;
use App\Services\MoneyService\Money;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Mintreu\LaravelMoney\LaravelMoney;

class ListStages extends ListRecords
{
    protected static string $resource = StageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }



    public  function table(Table $table): Table
    {
        return $table
            ->contentGrid(['md' => 2,'lg' => 3])
            ->searchable(false)
            ->paginated(false)
            ->heading(fn() => new HtmlString('<h3 class="text-center text-gray-500">Unlock stages in '.config('app.name').' to enjoy exclusive perks and rewards. Reach premium member status by advancing through all stages!</h3>'))
            ->columns([

                Tables\Columns\Layout\Stack::make([

                    Tables\Columns\TextColumn::make('name')
                        ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                        ->weight(FontWeight::Medium)
                        ->color('success')
                        ->extraAttributes(['class' => 'mt-2'])
                        ->alignCenter(),

                    Tables\Columns\TextColumn::make('price')
                        ->size(Tables\Columns\TextColumn\TextColumnSize::Medium)
                        ->weight(FontWeight::Medium)
                        ->money(LaravelMoney::defaultCurrency())
                        ->prefix('Price : ')
                        ->badge()
                        ->alignCenter(),

                    Tables\Columns\IconColumn::make('status')
                        ->alignCenter()
                        ->extraAttributes(['class' => 'py-2'])
                        ->boolean(),

                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('max_team_members')
                            ->numeric()
                            ->weight(FontWeight::SemiBold)
                            ->alignCenter()
                            ->icon('heroicon-o-user')
                            ->description('Team Member'),


                        Tables\Columns\TextColumn::make('estimated_total_joining_points')
                            ->numeric()
                            ->alignCenter()
                            ->weight(FontWeight::SemiBold)
                            ->icon('heroicon-o-fire')
                            ->description('Joining Point'),
                    ]),

                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('estimated_total_purchasing_points')
                            ->numeric()
                            ->alignCenter()
                            ->weight(FontWeight::SemiBold)
                            ->icon('heroicon-o-fire')
                            ->description('Purchasing Point'),

                        Tables\Columns\TextColumn::make('lavels_count')
                            ->counts('levels')
                            ->default(0)
                            ->alignCenter()
                            ->description('Levels')
                    ]),

                ])
                    ->extraAttributes(['class' => 'bg-gray-100 dark:bg-gray-800 rounded-xl m-1 py-2 px-1']),



//
//                Tables\Columns\TextColumn::make('created_at')
//                    ->dateTime()
//                    ->sortable()
//                    ->toggleable(isToggledHiddenByDefault: true),
//                Tables\Columns\TextColumn::make('updated_at')
//                    ->dateTime()
//                    ->sortable()
//                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
//            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
//            ])
            ;
    }







}
