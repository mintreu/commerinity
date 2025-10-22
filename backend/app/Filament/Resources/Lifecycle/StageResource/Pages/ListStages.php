<?php

namespace App\Filament\Resources\Lifecycle\StageResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
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
            CreateAction::make(),
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

                Stack::make([

                    TextColumn::make('name')
                        ->size(TextSize::Large)
                        ->weight(FontWeight::Medium)
                        ->color('success')
                        ->extraAttributes(['class' => 'mt-2'])
                        ->alignCenter(),

                    TextColumn::make('price')
                        ->size(TextSize::Medium)
                        ->weight(FontWeight::Medium)
                        ->money(LaravelMoney::defaultCurrency())
                        ->prefix('Price : ')
                        ->badge()
                        ->alignCenter(),

                    IconColumn::make('status')
                        ->alignCenter()
                        ->extraAttributes(['class' => 'py-2'])
                        ->boolean(),

                    Split::make([
                        TextColumn::make('max_team_members')
                            ->numeric()
                            ->weight(FontWeight::SemiBold)
                            ->alignCenter()
                            ->icon('heroicon-o-user')
                            ->description('Team Member'),


                        TextColumn::make('estimated_total_joining_points')
                            ->numeric()
                            ->alignCenter()
                            ->weight(FontWeight::SemiBold)
                            ->icon('heroicon-o-fire')
                            ->description('Joining Point'),
                    ]),

                    Split::make([
                        TextColumn::make('estimated_total_purchasing_points')
                            ->numeric()
                            ->alignCenter()
                            ->weight(FontWeight::SemiBold)
                            ->icon('heroicon-o-fire')
                            ->description('Purchasing Point'),

                        TextColumn::make('lavels_count')
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
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
//            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
//            ])
            ;
    }







}
