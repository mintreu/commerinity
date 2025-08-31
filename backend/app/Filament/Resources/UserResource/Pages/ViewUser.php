<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Casts\AuthStatusCast;
use App\Casts\AuthTypeCast;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('team')
                ->url(fn() => self::$resource::getUrl('members',['record' => $this->record->referral_code]),false),

            Actions\Action::make('view_stats')
                ->label('Stats')
                ->url(fn() => self::$resource::getUrl('stats',['record' => $this->record->referral_code]),false),


            Actions\ActionGroup::make([
                Actions\Action::make('updateStatus')
                    ->label('Change Status')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning') // Can be primary, success, danger, etc.
                    ->modalHeading('Update Status & Feedback')
                    ->fillForm([
                        'status' => $this->record->status,
                        'status_feedback' => $this->record->status_feedback,
                    ])
                    ->form([
                        Radio::make('status')
                            ->label('Status')
                            ->options(fn () => collect(AuthStatusCast::cases())->mapWithKeys(
                                fn ($case) => [$case->value => $case->name]
                            ))
                            ->required(),

                        Textarea::make('status_feedback')
                            ->label('Feedback')
                            ->placeholder('Optional feedback or reason for this status...')
                            ->nullable(),
                    ])
                    ->action(function (array $data): void {
                        $this->record->update($data);

                        Notification::make()
                            ->title('Status updated successfully')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalSubmitActionLabel('Save')
                    ->modalWidth('md'),




                Actions\Action::make('updateType')
                    ->label('Change Type')
                    ->icon('heroicon-o-sparkles')
                    ->color('info') // Can be primary, success, danger, etc.
                    ->modalHeading('Update Auth Type')
                    ->fillForm([
                        'type' => $this->record->type,
                    ])
                    ->form([
                        Radio::make('type')
                            ->label('Type')
                            ->options(fn () => collect(AuthTypeCast::cases())->mapWithKeys(
                                fn ($case) => [$case->value => $case->name]
                            ))
                            ->inline()
                            ->required(),

                    ])
                    ->action(function (array $data): void {
                        $this->record->update($data);

                        Notification::make()
                            ->title('Status updated successfully')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalSubmitActionLabel('Save')
                    ->modalWidth('md')
            ]),




        ];
    }


    public function infolist(Infolist $infolist):Infolist
    {
        return parent::infolist($infolist)
            ->schema([

                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Profile')
                            ->schema([
                                Section::make('Basic Information')
                                    ->aside()
                                    ->columns()
                                    ->schema([
                                        TextEntry::make('name')->columnSpanFull(),
                                        TextEntry::make('uuid')->badge()->label('Registration'),
                                        TextEntry::make('gender')->badge(),
                                        TextEntry::make('dob'),
                                        TextEntry::make('type')->badge(),
                                        TextEntry::make('status')->badge(),
                                    ]),

                                Section::make('Contact Information')
                                    ->aside()
                                    ->columns()
                                    ->schema([
                                        TextEntry::make('email'),
                                        IconEntry::make('email_verified_at')->boolean(),

                                        TextEntry::make('mobile'),
                                        IconEntry::make('mobile_verified_at')->boolean(),
                                    ])
                            ]),
                        Tabs\Tab::make('Affiliate Information')
                            ->schema([

                                Section::make('Affiliate Information')
                                    ->aside()
                                    ->schema([
                                        TextEntry::make('referral_code')
                                            ->label('User Affiliate Code')
                                            ->copyable()
                                            ->columnSpanFull()
                                            ->columnSpanFull(),

                                        Split::make([
                                            TextEntry::make('downline')
                                                ->label('Total Members :')
                                                ->formatStateUsing(fn() => $this->record->descendants()->count())
                                                ->default(0)
                                                ->suffix(' Members'),

                                            TextEntry::make('level.name'),
                                            TextEntry::make('level.stage.name')->label('Stage'),
                                        ]),

                                    ]),


                                Section::make('Upline Information')
                                    ->aside()
                                    ->columns()
                                    ->schema([
                                        TextEntry::make('parent.name')->label('Name')->columnSpanFull(),
                                        TextEntry::make('parent.email')->label('Email'),
                                        TextEntry::make('parent.mobile')->label('Mobile'),
                                        TextEntry::make('parent.affiliate_code')->label('Affiliate Code'),
                                        TextEntry::make('parent.type')->label('Type'),
                                        TextEntry::make('parent.status')->label('Status')
                                    ]),

                                Section::make('Originator Information')
                                    ->aside()
                                    ->columns()
                                    ->schema([
                                        TextEntry::make('originator.name')->label('Name')->columnSpanFull(),
                                        TextEntry::make('originator.email')->label('Email'),
                                        TextEntry::make('originator.mobile')->label('Mobile'),
                                        TextEntry::make('originator.type')->label('Type'),
                                        TextEntry::make('originator.status')->label('Status')

                                    ])
                            ]),
                        Tabs\Tab::make('KYC INFO')
                            ->schema([
                                Section::make('Aadhaar Information')
                                    ->relationship('kyc')
                                    ->aside()
                                    ->columns()
                                    ->schema([
                                        TextEntry::make('aadhaar')->label('Aadhaar'),
                                        SpatieMediaLibraryImageEntry::make('aadhaarImage')
                                            ->label('Screenshot (Aadhaar)')
                                            ->openUrlInNewTab()
                                            ->size('350px')
                                            ->defaultImageUrl('https://placehold.co/600x1200')
                                            ->collection('aadhaarImage'),
                                    ]),

                                Section::make('Pan Information')
                                    ->aside()
                                    ->relationship('kyc')
                                    ->columns()
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        TextEntry::make('pan')->label('PAN NUMBER'),
                                        SpatieMediaLibraryImageEntry::make('panImage')
                                            ->label('Screenshot (PAN)')
                                            ->openUrlInNewTab()
                                            ->size('350px')
                                            ->defaultImageUrl('https://placehold.co/600x1200')
                                            ->collection('panImage'),
                                    ]),


                                Section::make('GST Information')
                                    ->aside()
                                    ->relationship('kyc')
                                    ->columns()
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        TextEntry::make('gst')->label('GST NUMBER'),
                                        SpatieMediaLibraryImageEntry::make('gstImage')
                                            ->label('Screenshot (GST)')
                                            ->openUrlInNewTab()
                                            ->size('350px')
                                            ->defaultImageUrl('https://placehold.co/600x1200')
                                            ->collection('panImage'),
                                    ]),



                            ]),


                        Tabs\Tab::make('Address')
                            ->schema([
                                Section::make('Home Address Information')
                                    ->aside()
                                    ->relationship('homeAddress')
                                    ->columns()
                                    ->schema([
                                        // Contact details
                                        TextEntry::make('person_name')->label('Name'),
                                        TextEntry::make('person_mobile')->label('Mobile'),
                                        TextEntry::make('alternate_contact')->label('Alternate Contact'),

                                        // Address lines
                                        TextEntry::make('pickup_location')->label('Pickup Location'),
                                        TextEntry::make('address_1')->label('Address Line 1'),
                                        TextEntry::make('village')->label('Village'),
                                        TextEntry::make('landmark')->label('Landmark'),

                                        // Location details
                                        TextEntry::make('city')->label('City'),
                                        TextEntry::make('postal_code')->label('Postal Code'),
                                        TextEntry::make('state.name')->label('State Code'),
                                        TextEntry::make('block.name')->label('Block'),
                                        TextEntry::make('country.name')->label('Country'),

                                        // Other info
                                        TextEntry::make('type')->label('Type'),
                                        IconEntry::make('default')
                                            ->boolean()
                                            ->label('Is Default?'),
                                        TextEntry::make('priority')->label('Priority'),
                                    ])
        ]),


                    ]),


            ]);
    }


}
