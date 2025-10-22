<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\TextSize;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }


    public  function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([

                    Split::make([
                        SpatieMediaLibraryImageColumn::make('avatar')
                            ->label('Profile Picture')
                            //->extraAttributes(['class' => 'hidden md:block'])
                            ->extraImgAttributes(['class' => 'mx-auto object-cover'])
                            ->collection('avatarImage')
                            ->circular()
//                        ->alignCenter()
                            ->size('80%'),

                        Stack::make([
                            TextColumn::make('name')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::SemiBold)
                                ->color('primary')
                                ->searchable(),
                            TextColumn::make('uuid')->label('Registration'),
                            TextColumn::make('email')
                                ->searchable(),
                            TextColumn::make('email_verified_at')
                                ->sortable(),
                            TextColumn::make('mobile')
                                ->searchable(),
                            TextColumn::make('mobile_verified_at')
                                ->dateTime()
                                ->sortable(),
                            TextColumn::make('referral_code')
                                ->searchable(),
                            TextColumn::make('parent_id')
                                ->numeric()
                                ->sortable(),
                            TextColumn::make('originator_type')
                                ->searchable(),
                            TextColumn::make('originator_id')
                                ->numeric()
                                ->sortable(),
                            TextColumn::make('gender')
                                ->searchable(),
                            TextColumn::make('dob')
                                ->date()
                                ->sortable(),
                            TextColumn::make('type')
                                ->searchable(),
                            TextColumn::make('status')
                                ->searchable(),
                            TextColumn::make('created_at')
                                ->dateTime()
                                ->sortable()
                                ->toggleable(isToggledHiddenByDefault: true),
                            TextColumn::make('updated_at')
                                ->dateTime()
                                ->sortable()
                                ->toggleable(isToggledHiddenByDefault: true),
                        ]),
                    ])
                ])
            ])
            ->contentGrid([
                'default' => 1,
                'md' => 2
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }



}
