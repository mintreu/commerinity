<?php

declare(strict_types=1);

namespace App\Filament\Resources\Advertisements\Schemas;

use App\Casts\AdPlacementCast;
use App\Casts\AdTypeCast;
use App\Casts\AdvertisementPageCast;
use App\Casts\AdvertisementPositionCast;
use App\Casts\UserTypeCast;
use App\Filament\Resources\Advertisements\Schemas\Traits\HasAdvertisementDynamicConfiguration;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

final class AdvertisementForm
{
    use HasAdvertisementDynamicConfiguration;

    public static function configure(Schema $schema): Schema
    {
        return (new self())->build($schema);
    }

    private function build(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make('Basic Configuration')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, callable $set, ?string $old, ?string $state): void {
                                    if (($get('slug') ?? '') !== Str::slug((string) $old)) {
                                        return;
                                    }

                                    $set('slug', Str::slug((string) $state));
                                }),
                            TextInput::make('slug')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->default(true)
                                ->required(),
                            Toggle::make('is_premium')
                                ->default(false)
                                ->required(),
                        ]),
                    Grid::make(4)
                        ->schema([
                            Select::make('type')
                                ->options(AdTypeCast::class)
                                ->required()
                                ->default(AdTypeCast::NATIVE->value)
                                ->live(),
                            $this->syncPositionTypeWithPlacement(
                                Select::make('placement')
                                    ->options(AdPlacementCast::class)
                                    ->required()
                                    ->live()
                            ),
                            Select::make('page_target')
                                ->options(AdvertisementPageCast::class)
                                ->default(AdvertisementPageCast::ALL_PAGES->value)
                                ->required()
                                ->live(),
                            Select::make('position_type')
                                ->options(AdvertisementPositionCast::class)
                                ->required()
                                ->default(AdvertisementPositionCast::TOP_BANNER->value),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('page_pattern')
                                ->placeholder('/shop/*')
                                ->helperText('Use * wildcard for custom page matching.')
                                ->visible(fn (Get $get): bool => $this->formConfigService()->isCustomPageTarget($get('page_target'))),
                            $this->blockField(),
                        ]),
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('starts_at'),
                            DateTimePicker::make('ends_at')
                                ->helperText('Set end date/time if you want time-based expiry.'),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('target_views')
                                ->numeric()
                                ->minValue(1)
                                ->helperText('Set max impressions for counter-based expiry.'),
                            TextInput::make('impressions')
                                ->numeric()
                                ->default(0)
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    Grid::make(4)
                        ->schema([
                            TextInput::make('position')
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->helperText('Order priority inside the same placement.'),
                            TextInput::make('width')->numeric(),
                            TextInput::make('height')->numeric(),
                            Toggle::make('is_responsive')
                                ->default(true),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('show_to_guests')
                                ->default(true),
                            Toggle::make('show_to_members')
                                ->default(true),
                        ]),
                    Select::make('target_user_types')
                        ->label('Target User Types')
                        ->multiple()
                        ->options(UserTypeCast::class)
                        ->searchable(),
                ]),

            SchemaSection::make('Creative Content')
                ->schema([
                    TextInput::make('title')
                        ->maxLength(255)
                        ->visible(fn (Get $get): bool => $this->formConfigService()->isNativeLikeType($get('type'))),
                    Textarea::make('description')
                        ->rows(3)
                        ->visible(fn (Get $get): bool => $this->formConfigService()->isNativeLikeType($get('type'))),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('link_url')
                                ->url()
                                ->visible(fn (Get $get): bool => $this->formConfigService()->isNativeLikeType($get('type'))),
                            TextInput::make('link_text')
                                ->maxLength(100)
                                ->visible(fn (Get $get): bool => $this->formConfigService()->isNativeLikeType($get('type'))),
                        ]),
                    Toggle::make('open_in_new_tab')
                        ->default(true)
                        ->visible(fn (Get $get): bool => $this->formConfigService()->isNativeLikeType($get('type'))),

                    SpatieMediaLibraryFileUpload::make('ad_image')
                        ->collection('ad_image')
                        ->image()
                        ->imageEditor()
                        ->visible(fn (Get $get): bool => $this->formConfigService()->isNativeLikeType($get('type'))),
                    SpatieMediaLibraryFileUpload::make('ad_image_mobile')
                        ->collection('ad_image_mobile')
                        ->image()
                        ->imageEditor()
                        ->visible(fn (Get $get): bool => $this->formConfigService()->isNativeLikeType($get('type'))),
                    SpatieMediaLibraryFileUpload::make('ad_video')
                        ->collection('ad_video')
                        ->visible(fn (Get $get): bool => $get('type') === AdTypeCast::NATIVE->value),
                ]),

            SchemaSection::make('Third-party / Affiliate Configuration')
                ->schema([
                    Textarea::make('ad_code')
                        ->rows(5)
                        ->helperText('HTML/JS snippet from provider.')
                        ->visible(fn (Get $get): bool => $this->formConfigService()->isThirdPartyType($get('type'))),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('ad_unit_id')
                                ->visible(fn (Get $get): bool => $this->formConfigService()->isThirdPartyType($get('type'))),
                            TextInput::make('third_party_script_url')
                                ->url()
                                ->visible(fn (Get $get): bool => $this->formConfigService()->isThirdPartyType($get('type'))),
                        ]),
                    KeyValue::make('third_party_config')
                        ->addActionLabel('Add Config')
                        ->visible(fn (Get $get): bool => $this->formConfigService()->isThirdPartyType($get('type'))),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('affiliate_network')
                                ->maxLength(255)
                                ->visible(fn (Get $get): bool => $this->formConfigService()->isAffiliateType($get('type'))),
                            TextInput::make('affiliate_tracking_id')
                                ->maxLength(255)
                                ->visible(fn (Get $get): bool => $this->formConfigService()->isAffiliateType($get('type'))),
                        ]),
                ]),

            SchemaSection::make('Advanced Routing & Rendering')
                ->collapsed()
                ->collapsible()
                ->schema([
                    Textarea::make('display_pages')
                        ->label('Display Pages')
                        ->rows(3)
                        ->placeholder("/shop/products\n/category/*")
                        ->helperText('One path per line.')
                        ->formatStateUsing(fn ($state): string => is_array($state) ? implode("\n", $state) : (string) ($state ?? ''))
                        ->dehydrateStateUsing(fn (?string $state): array => collect(explode("\n", (string) $state))
                            ->map(fn ($line) => trim($line))
                            ->filter()
                            ->values()
                            ->all()),
                    Textarea::make('exclude_pages')
                        ->label('Exclude Pages')
                        ->rows(3)
                        ->placeholder("/checkout\n/profile")
                        ->helperText('One path per line.')
                        ->formatStateUsing(fn ($state): string => is_array($state) ? implode("\n", $state) : (string) ($state ?? ''))
                        ->dehydrateStateUsing(fn (?string $state): array => collect(explode("\n", (string) $state))
                            ->map(fn ($line) => trim($line))
                            ->filter()
                            ->values()
                            ->all()),
                    KeyValue::make('position_config')
                        ->addActionLabel('Add Position Config'),
                ]),

            SchemaSection::make('Analytics (Read-only)')
                ->collapsed()
                ->collapsible()
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('clicks')
                                ->numeric()
                                ->default(0)
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('last_impression_at')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('last_click_at')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                ]),
        ]);
    }
}
